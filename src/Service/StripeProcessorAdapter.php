<?php

namespace App\Service;

use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

class StripeProcessorAdapter implements PaymentProcessorInterface
{
    private StripePaymentProcessor $stripeProcessor;

    public function __construct()
    {
        $this->stripeProcessor = new StripePaymentProcessor();
    }

    public function processPayment(float $amount): void
    {
        $this->stripeProcessor->processPayment($amount);
    }
} 
