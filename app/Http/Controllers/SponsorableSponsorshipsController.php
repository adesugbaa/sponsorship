<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Sponsorable;
use App\Sponsorship;
use App\SponsorableSlot;
use App\Exceptions\PaymentFailedException;

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
        try {
            $sponsorable = Sponsorable::findOrFailBySlug($slug);

            request()->validate([
                'email' => ['required', 'email'],
                'company_name' => ['required'],
                'payment_token' => ['required'],
                'sponsorable_slots' => ['bail', 'required', 'array', function ($attribute, $value, $fail) use($sponsorable) {
                    if (collect($value)->unique()->count() !== count($value)) {
                        $fail("You cannot sponsor the same slot more than once.");
                    }

                    

                    // $slotCount = $sponsorable->slots()->whereIn('id', request('sponsorable_slots'))->count();
                    // if ($slotCount !== count(request('sponsorable_slots'))) {
                    //     $fail("Slots have mismatched parents.");
                    // }
                }],
                //'sponsorable_slots.*' => ['distinct'],
                //'sposonrable_slots.*' => 'exists:sponsorable_slots;sponsorable_id;$sponsorable->getKey()'
            ]);
            
            $slots = $sponsorable->slots()->findOrFail(request('sponsorable_slots'));
            //$slots = $sponsorable->slots()->whereIn('id', request('sponsorable_slots'))->get();
            //abort_unless($slots->count() === count(request('sponsorable_slots')), 400);

            $this->paymentGateway->charge(request('email'), $slots->sum('price'), request('payment_token'), "{$sponsorable->name} sponsorship");

            $sponsorship = Sponsorship::create([
                'email' => request('email'),
                'company_name' => request('company_name'),
                'amount' => $slots->sum('price'),
            ]);

            $slots->each->update(['sponsorship_id' => $sponsorship->id]);

            return response()->json([], 201);
        } catch (PaymentFailedException $e) {
            return response()->json([], 422);
        }
    }
}
