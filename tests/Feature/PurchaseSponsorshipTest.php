<?php

namespace Tests\Feature;

use App\PaymentGateway;
use App\SponsorShip;
use App\Sponsorable;
use App\SponsorableSlot;
use Tests\TestCase;
use Tests\FakePaymentGateway;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseSponsorshipTest extends TestCase
{
    use RefreshDatabase;

    protected static $paymentGateway;

    function setUp()
    {
        parent::setUp();

        self::$paymentGateway = $this->app->instance(PaymentGateway::class, new FakePaymentGateway);
    }

    /** @test */
    function purchasing_available_sponsorship_slots()
    {
        //$this->markTestSkipped();
        
        $sponsorable = factory(Sponsorable::class)->create(['slug' => 'full-stack-radio', 'name' => 'Full Stack Radio']);

        $slotA = factory(SponsorableSlot::class)->create(['price' => 50000, 'sponsorable_id' => $sponsorable, 'publish_date' => now()->addMonth(1)]);
        $slotB = factory(SponsorableSlot::class)->create(['price' => 30000, 'sponsorable_id' => $sponsorable, 'publish_date' => now()->addMonth(2)]);
        $slotC = factory(SponsorableSlot::class)->create(['price' => 25000, 'sponsorable_id' => $sponsorable, 'publish_date' => now()->addMonth(3)]);

        $response = $this->postJson('/full-stack-radio/sponsorships', [
            'email' => 'john@example.com',
            'company_name' => 'DigitalTechnosoft Inc.',
            'payment_token' => self::$paymentGateway->validTestToken(),
            'sponsorable_slots' => [
                $slotA->getKey(),
                $slotC->getKey(),
            ]
        ]);

        $response->assertStatus(201);

        $this->assertEquals(1, SponsorShip::count());
        $sponsorship = SponsorShip::first();

        $this->assertEquals('john@example.com', $sponsorship->email);
        $this->assertEquals('DigitalTechnosoft Inc.', $sponsorship->company_name);
        $this->assertEquals(75000, $sponsorship->amount);

        $this->assertEquals($sponsorship->getKey(), $slotA->fresh()->sponsorship_id);
        $this->assertEquals($sponsorship->getKey(), $slotC->fresh()->sponsorship_id);

        $this->assertNull($slotB->fresh()->sponsorship_id);

        $this->assertCount(1, self::$paymentGateway->charges());
        
        $charge = self::$paymentGateway->charges()->first();
        $this->assertEquals('john@example.com', $charge->email());
        $this->assertEquals(75000, $charge->amount());
        $this->assertEquals('Full Stack Radio sponsorship', $charge->description());
    }

    /** @test */
    function sponsorship_is_not_created_if_payment_token_cannot_be_charged()
    {
        //$this->markTestSkipped();
        
        $sponsorable = factory(Sponsorable::class)->create(['slug' => 'full-stack-radio']);

        $slot = factory(SponsorableSlot::class)->create(['price' => 50000, 'sponsorable_id' => $sponsorable, 'publish_date' => now()->addMonth(1)]);

        $response = $this->postJson('/full-stack-radio/sponsorships', [
            'email' => 'john@example.com',
            'company_name' => 'DigitalTechnosoft Inc.',
            'payment_token' => 'not-a-valid-token',
            'sponsorable_slots' => [
                $slot->getKey(),
            ]
        ]);

        $response->assertStatus(422);

        $this->assertEquals(0, SponsorShip::count());
        $this->assertNull($slot->fresh()->sponsorship_id);
        $this->assertCount(0, self::$paymentGateway->charges());
    }

    /** @test */
    function company_name_is_required()
    {   
        $sponsorable = factory(Sponsorable::class)->create(['slug' => 'full-stack-radio']);

        $slot = factory(SponsorableSlot::class)->create(['price' => 50000, 'sponsorable_id' => $sponsorable, 'publish_date' => now()->addMonth(1)]);

        $response = $this->withExceptionHandling()->postJson('/full-stack-radio/sponsorships', [
            'email' => 'john@example.com',
            'company_name' => '',
            'payment_token' => self::$paymentGateway->validTestToken(),
            'sponsorable_slots' => [
                $slot->getKey(),
            ]
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('company_name');

        $this->assertEquals(0, SponsorShip::count());
        $this->assertNull($slot->fresh()->sponsorship_id);
        $this->assertCount(0, self::$paymentGateway->charges());
    }

    /** @test */
    function email_is_required()
    {   
        $sponsorable = factory(Sponsorable::class)->create(['slug' => 'full-stack-radio']);

        $slot = factory(SponsorableSlot::class)->create(['price' => 50000, 'sponsorable_id' => $sponsorable, 'publish_date' => now()->addMonth(1)]);

        $response = $this->withExceptionHandling()->postJson('/full-stack-radio/sponsorships', [
            'email' => '',
            'company_name' => 'DigitalTechnosoft Inc.',
            'payment_token' => self::$paymentGateway->validTestToken(),
            'sponsorable_slots' => [
                $slot->getKey(),
            ]
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');

        $this->assertEquals(0, SponsorShip::count());
        $this->assertNull($slot->fresh()->sponsorship_id);
        $this->assertCount(0, self::$paymentGateway->charges());
    }

    /** @test */
    function email_must_be_valid()
    {   
        $sponsorable = factory(Sponsorable::class)->create(['slug' => 'full-stack-radio']);

        $slot = factory(SponsorableSlot::class)->create(['price' => 50000, 'sponsorable_id' => $sponsorable, 'publish_date' => now()->addMonth(1)]);

        $response = $this->withExceptionHandling()->postJson('/full-stack-radio/sponsorships', [
            'email' => 'fakeemail',
            'company_name' => 'DigitalTechnosoft Inc.',
            'payment_token' => self::$paymentGateway->validTestToken(),
            'sponsorable_slots' => [
                $slot->getKey(),
            ]
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');

        $this->assertEquals(0, SponsorShip::count());
        $this->assertNull($slot->fresh()->sponsorship_id);
        $this->assertCount(0, self::$paymentGateway->charges());
    }

    /** @test */
    function payment_token_is_required()
    {   
        $sponsorable = factory(Sponsorable::class)->create(['slug' => 'full-stack-radio']);

        $slot = factory(SponsorableSlot::class)->create(['price' => 50000, 'sponsorable_id' => $sponsorable, 'publish_date' => now()->addMonth(1)]);

        $response = $this->withExceptionHandling()->postJson('/full-stack-radio/sponsorships', [
            'email' => 'john@example.com',
            'company_name' => 'DigitalTechnosoft Inc.',
            'payment_token' => null,
            'sponsorable_slots' => [
                $slot->getKey(),
            ]
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('payment_token');

        $this->assertEquals(0, SponsorShip::count());
        $this->assertNull($slot->fresh()->sponsorship_id);
        $this->assertCount(0, self::$paymentGateway->charges());
    }

    /** @test */
    function cannot_sponsor_another_sponsorables_slots()
    {   
        $sponsorable = factory(Sponsorable::class)->create(['slug' => 'full-stack-radio']);
        $otherSponsorable = factory(Sponsorable::class)->create(['slug' => 'sisc']);

        $slotA = factory(SponsorableSlot::class)->create(['price' => 50000, 'sponsorable_id' => $sponsorable, 'publish_date' => now()->addMonth(1)]);
        $slotB = factory(SponsorableSlot::class)->create(['price' => 30000, 'sponsorable_id' => $sponsorable, 'publish_date' => now()->addMonth(2)]);
        $otherSlot = factory(SponsorableSlot::class)->create(['price' => 25000, 'sponsorable_id' => $otherSponsorable, 'publish_date' => now()->addMonth(3)]);

        $response = $this->withExceptionHandling()->postJson('/full-stack-radio/sponsorships', [
            'email' => 'john@example.com',
            'company_name' => 'DigitalTechnosoft Inc.',
            'payment_token' => self::$paymentGateway->validTestToken(),
            'sponsorable_slots' => [
                $slotA->getKey(),
                $slotB->getKey(),
                $otherSlot->getKey(),
            ]
        ]);

        $response->assertStatus(400);

        $this->assertEquals(0, SponsorShip::count());
        $this->assertNull($slotA->fresh()->sponsorship_id);
        $this->assertNull($slotB->fresh()->sponsorship_id);
        $this->assertNull($otherSlot->fresh()->sponsorship_id);
        $this->assertCount(0, self::$paymentGateway->charges());
    }
}
