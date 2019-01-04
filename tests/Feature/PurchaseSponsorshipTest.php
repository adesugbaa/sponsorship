<?php

namespace Tests\Feature;

use App\SponsorShip;
use App\Sponsorable;
use App\SponsorableSlot;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseSponsorshipTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function purchasing_available_sponsorship_slots()
    {
        //$this->markTestSkipped();

        $sponsorable = factory(Sponsorable::class)->create(['slug' => 'full-stack-radio']);

        $slotA = factory(SponsorableSlot::class)->create(['sponsorable_id' => $sponsorable, 'publish_date' => now()->addMonth(1)]);
        $slotB = factory(SponsorableSlot::class)->create(['sponsorable_id' => $sponsorable, 'publish_date' => now()->addMonth(2)]);
        $slotC = factory(SponsorableSlot::class)->create(['sponsorable_id' => $sponsorable, 'publish_date' => now()->addMonth(3)]);

        $response = $this->postJson('/full-stack-radio/sponsorships', [
            'sponsorable_slots' => [
                $slotA->getKey(),
                $slotC->getKey(),
            ]
        ]);

        $response->assertStatus(201);

        $this->assertEquals(1, SponsorShip::count());
        $sponsorship = SponsorShip::first();

        $this->assertEquals($sponsorship->getKey(), $slotA->fresh()->sponsorship_id);
        $this->assertEquals($sponsorship->getKey(), $slotC->fresh()->sponsorship_id);

        $this->assertNull($slotB->fresh()->sponsorship_id);
    }
}
