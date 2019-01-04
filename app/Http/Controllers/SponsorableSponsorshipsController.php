<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Sponsorable;
use App\Sponsorship;
use App\SponsorableSlot;

class SponsorableSponsorshipsController extends Controller
{
    private $paymentGateway;

    public function __construct($paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

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
        $sponsorable = Sponsorable::findOrFailBySlug($slug);
        $slots = SponsorableSlot::whereIn('id', request('sponsorable_slots'))->get();
        $sponsorship = Sponsorship::create([
            'email' => request('email'),
            'company_name' => request('company_name'),
            'amount' => $slots->sum('price'),
        ]);

        $this->paymentGateway->charge(request('email'), $slots->sum('price'), request('token'), "{$sponsorable->name} sponsorship");

        $slots->each->update(['sponsorship_id' => $sponsorship->id]);

        return response()->json([], 201);
    }
}
