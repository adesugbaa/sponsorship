<?php

namespace Tests\Unit;

use Exception;
use App\Sponsorable;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SponsorableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function finding_a_sponsorable_by_slug()
    {
        $sponsorable = factory(Sponsorable::class)->create(['slug' => 'full-stack-radio']);
        $foundSponsorable = Sponsorable::findOrFailBySlug('full-stack-radio');
        $this->assertTrue($foundSponsorable->is($sponsorable));
    }


    /** @test */
    function an_exception_is_thrown_if_a_sponsorable_cannot_be_found_by_slug()
    {
        // try {
        //     Sponsorable::findOrFailBySlug('slug-that-does-not-exist');
        //     $this->fail('Expected an exception for slug that does not exist');
        // } catch (Exception $e) {
        //     $this->assertInstanceOf(ModelNotFoundException::class, $e);
        // }
        $this->expectException(ModelNotFoundException::class);
        Sponsorable::findOrFailBySlug('slug-that-does-not-exist');
    }
}
