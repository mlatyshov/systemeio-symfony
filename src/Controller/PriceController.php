<?php

namespace App\Controller;

use App\DTO\CalculatePriceRequest;
use App\Service\PriceCalculator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PriceController
{
    public function __construct(private PriceCalculator $priceCalculator) {}

    public function calculatePrice(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        $dto = $serializer->deserialize(
            $request->getContent(),
            CalculatePriceRequest::class,
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

        return new JsonResponse($priceDetails, 200);
    }
}
