<?php

namespace App\Service;

use SystemeIo\TestForCandidates\PaypalPaymentProcessor;

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
