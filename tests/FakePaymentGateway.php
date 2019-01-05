<?php

namespace Tests;

use App\Charge;
use App\Exceptions\PaymentFailedException;
use Illuminate\Support\Collection;

class FakePaymentGateway
{
    private $charges;
    
    public function __construct()
    {
        $this->charges = new Collection;
    }

    public function validTestToken()
    {
        return 'valid_test_token';
    }

    public function charge($email, $amount, $token, $description)
    {
        if ($token !== $this->validTestToken()) {
            throw new PaymentFailedException;
        }

        $this->charges->push(new Charge($email, $amount, $description));
    }

    public function charges()
    {
        return $this->charges;
    }
}