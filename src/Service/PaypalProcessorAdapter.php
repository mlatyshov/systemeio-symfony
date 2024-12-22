<?php

namespace App\Service;

use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;

class PaypalProcessorAdapter implements PaymentProcessorInterface
{
    private PaypalPaymentProcessor $paypalProcessor;

    public function __construct()
    {
        $this->paypalProcessor = new PaypalPaymentProcessor();
    }

    public function processPayment(float $amount): void
    {
        $this->paypalProcessor->pay($amount);
    }
} 
