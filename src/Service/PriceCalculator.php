<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\Coupon;
use App\Repository\ProductRepository;
use App\Repository\CouponRepository;
use App\Utils\TaxRules;

class PriceCalculator
{
    public function __construct(
        private ProductRepository $productRepository,
        private CouponRepository $couponRepository
    ) {}

    public function calculatePrice(int $productId, string $taxNumber, ?string $couponCode): array
    {
        // 1. Получаем продукт
        $product = $this->productRepository->find($productId);
        if (!$product) {
            throw new \InvalidArgumentException("Product not found with ID: $productId");
        }
        $productPrice = $product->getPrice();

        // 2. Применяем скидку
        $discountedPrice = $productPrice;
        if ($couponCode) {
            $coupon = $this->couponRepository->findOneBy(['code' => $couponCode]);
            if (!$coupon) {
                throw new \InvalidArgumentException("Invalid coupon code: $couponCode");
            }

            if ($coupon->getType() === \App\Enum\CouponType::FIXED) {
                $discountedPrice -= $coupon->getValue();
            } elseif ($coupon->getType() === \App\Enum\CouponType::PERCENTAGE) {
                $discountedPrice -= $productPrice * ($coupon->getValue() / 100);
            }

            $discountedPrice = max(0, $discountedPrice); // Не допускаем отрицательной цены
        }

        // 3. Рассчитываем налог
        $countryCode = substr($taxNumber, 0, 2);
        $taxRate = TaxRules::getRate($countryCode);
        if ($taxRate === null) {
            throw new \InvalidArgumentException("Tax rate not found for country: $countryCode");
        }

        $taxAmount = $discountedPrice * ($taxRate / 100);
        $finalPrice = $discountedPrice + $taxAmount;

        return [
            'productPrice' => $productPrice,
            'discountedPrice' => $discountedPrice,
            'taxRate' => $taxRate,
            'taxAmount' => $taxAmount,
            'finalPrice' => $finalPrice,
        ];
    }
}
