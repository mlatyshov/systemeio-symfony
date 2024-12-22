<?php

namespace App\Controller;

use App\DTO\CalculatePriceRequest;
use App\Utils\TaxRules;
use App\Entity\Coupon;
use App\Repository\ProductRepository;
use App\Repository\CouponRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PriceController
{
    public function __construct(
        private ProductRepository $productRepository,
        private CouponRepository $couponRepository
    ) {}

    public function calculatePrice(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        // JSON в DTO
        $dto = $serializer->deserialize(
            $request->getContent(),
            CalculatePriceRequest::class,
            'json'
        );

        // Валидация DTO
        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], 422);
        }

        
        // Получаем продукт из базы данных
        $product = $this->productRepository->find($dto->product);

        if (!$product) {
            return new JsonResponse([
                'error' => 'Product not found with ID: ' . $dto->product
            ], 404);
        }

        $productPrice = $product->getPrice();

        if ($dto->couponCode) {
            $coupon = $this->couponRepository->findOneBy(['code' => $dto->couponCode]);

            if (!$coupon) {
                return new JsonResponse([
                    'error' => 'Invalid coupon code: ' . $dto->couponCode
                ], 400);
            }

            if ($coupon->getType() === \App\Enum\CouponType::FIXED) {
                $discountedPrice -= $coupon->getValue();
            } elseif ($coupon->getType() === \App\Enum\CouponType::PERCENTAGE) {
                $discountedPrice -= $productPrice * ($coupon->getValue() / 100);
            }

            $discountedPrice = max(0, $discountedPrice); // Не допускаем отрицательной цены
        }

        // Получаем код страны и считаем налог            
        $countryCode = substr($dto->taxNumber, 0, 2);
        $taxRate = TaxRules::getRate($countryCode);
        if ($taxRate === null) {
            return new JsonResponse([
                'error' => 'Tax rate not found for country: ' . $countryCode
            ], 400);
        }

        $taxAmount = $productPrice * ($taxRate / 100);
        $totalPrice = $productPrice + $taxAmount;

        return new JsonResponse([
            'productPrice' => $productPrice,
            'discountedPrice' => $discountedPrice,
            'taxRate' => $taxRate,
            'taxAmount' => $taxAmount,
            'totalPrice' => $totalPrice
        ], 200);
    }
}
