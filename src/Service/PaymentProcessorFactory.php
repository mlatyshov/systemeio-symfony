<?php

namespace App\Service;

class PaymentProcessorFactory
{
    public function getProcessor(string $processorType): PaymentProcessorInterface
    {
        return match ($processorType) {
            'paypal' => new PaypalProcessorAdapter(),
            'stripe' => new StripeProcessorAdapter(),
            default => throw new \InvalidArgumentException('Unsupported payment processor: ' . $processorType),
        };
    } 
}
