<?php

namespace Tests\Fakes;

use Tests\FakePaymentGateway;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    /** @test */
    function retrieving_charges()
    {
        $paymentGateway = new FakePaymentGateway;

        $paymentGateway->charge('john@example.com', 25000, $paymentGateway->validTestToken(), 'Example Description A');
        $paymentGateway->charge('jane@example.org', 5000, $paymentGateway->validTestToken(), 'Example Description B');
        $paymentGateway->charge('jeff@example.net', 7500, $paymentGateway->validTestToken(), 'Example Description C');

        $charges = $paymentGateway->charges();
        $this->assertCount(3, $charges);

        $this->assertEquals('john@example.com', $charges[0]->email());
        $this->assertEquals(25000, $charges[0]->amount());
        $this->assertEquals('Example Description A', $charges[0]->description());

        $this->assertEquals('jane@example.org', $charges[1]->email());
        $this->assertEquals(5000, $charges[1]->amount());
        $this->assertEquals('Example Description B', $charges[1]->description());

        $this->assertEquals('jeff@example.net', $charges[2]->email());
        $this->assertEquals(7500, $charges[2]->amount());
        $this->assertEquals('Example Description C', $charges[2]->description());
    }
}