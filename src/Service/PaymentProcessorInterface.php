<?php

namespace App\Service;

interface PaymentProcessorInterface
{
    /**
     * Processes a payment with the given amount.
     *
     * @param float $amount The amount to process.
     *
     * @return void
     *
     * @throws \Exception If the payment fails.
     */
    public function processPayment(float $amount): void;
} 
