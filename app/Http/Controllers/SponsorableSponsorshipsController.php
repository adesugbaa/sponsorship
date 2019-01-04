<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Sponsorable;
use App\Sponsorship;
use App\SponsorableSlot;

class SponsorableSponsorshipsController extends Controller
{
    public function new($slug)
    {
        $sponsorable = Sponsorable::findOrFailBySlug($slug);

        $sponsorableSlots = $sponsorable->slots()->sponsorable()->orderBy('publish_date')->get();

        return view('sponsorable-sponsorships.new', [
            'sponsorable' => $sponsorable,
            'sponsorableSlots' => $sponsorableSlots
        ]);
    }

    public function store($slug)
    {
        $sponsorship = Sponsorship::create();

        $slots = SponsorableSlot::whereIn('id', request('sponsorable_slots'))->get();
        //dd($slots);
        $slots->each->update(['sponsorship_id' => $sponsorship->id]);

        return response()->json([], 201);
    }
}
