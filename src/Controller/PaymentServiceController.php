<?php
namespace App\Controller;

use App\DTO\PurchaseRequest;
// здесь добавим сервисы
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
        // 1. Преобразуем JSON в DTO
        $dto = $serializer->deserialize(
            $request->getContent(),
            PurchaseRequest::class,
            'json'
        );

        // 2. Валидируем DTO
        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return new JsonResponse([
                'errors' => (string) $errors
            ], 422);
        }

        // 3. Рассчитываем итоговую цену
        try {
            $finalPrice = $this->priceCalculator->calculatePrice(
                $dto->product,
                $dto->taxNumber,
                $dto->couponCode
            );
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 400);
        }

        // 4. Инициализируем платёж через указанный процессор
        try {
            $paymentProcessor = $this->paymentProcessorFactory->getProcessor($dto->paymentProcessor);
            $paymentProcessor->processPayment($finalPrice);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 400);
        }

        // 5. Успешный ответ
        return new JsonResponse([
            'message' => 'Payment successful',
            'amount' => $finalPrice
        ], 200);
    }
}