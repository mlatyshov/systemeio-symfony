<?php

namespace App\Controller;

use App\DTO\PurchaseRequest;
use App\Service\PaymentProcessorFactory;
use App\Service\PriceCalculator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PaymentServiceController
{
    public function __construct(
        private PriceCalculator $priceCalculator,
        private PaymentProcessorFactory $paymentProcessorFactory
    ) {}

    public function purchase(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        $dto = $serializer->deserialize(
            $request->getContent(),
            PurchaseRequest::class,
            'json'
        );

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], 422);
        }

        try {
            $priceDetails = $this->priceCalculator->calculatePrice(
                $dto->product,
                $dto->taxNumber,
                $dto->couponCode
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        $finalPrice = $priceDetails['finalPrice'];

        try {
            $paymentProcessor = $this->paymentProcessorFactory->getProcessor($dto->paymentProcessor);
            $paymentProcessor->processPayment($finalPrice);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        return new JsonResponse([
            'message' => 'Payment successful',
            'amount' => $finalPrice
        ], 200);
    }
}
