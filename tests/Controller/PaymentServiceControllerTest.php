<?php 
namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;
use App\Controller\PaymentServiceController;
use App\Service\PriceCalculator;
use App\Service\PaymentProcessorFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class PaymentServiceControllerTest extends TestCase
{
    public function testPurchaseSuccess(): void
    {
        $priceCalculatorMock = $this->createMock(PriceCalculator::class);
        $priceCalculatorMock->method('calculatePrice')->willReturn(['finalPrice' => 100.0]);

        $paymentProcessorMock = $this->createMock(\App\Service\PaymentProcessorInterface::class);
        $paymentProcessorMock->expects($this->once())->method('processPayment');

        $paymentFactoryMock = $this->createMock(PaymentProcessorFactory::class);
        $paymentFactoryMock->method('getProcessor')->willReturn($paymentProcessorMock);

        $controller = new PaymentServiceController($priceCalculatorMock, $paymentFactoryMock);

        $request = new Request([], [], [], [], [], [], json_encode([
            'product' => 1,
            'taxNumber' => 'DE123456789',
            'couponCode' => 'DISCOUNT10',
            'paymentProcessor' => 'paypal',
        ]));

        $response = $controller->purchase($request, $this->getMockSerializer(), $this->getMockValidator());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    private function getMockSerializer()
    {
        $mock = $this->createMock(\Symfony\Component\Serializer\SerializerInterface::class);
        $mock->method('deserialize')->willReturn(new \App\DTO\PurchaseRequest(
            product: 1,
            taxNumber: 'DE123456789',
            couponCode: 'DISCOUNT10',
            paymentProcessor: 'paypal'
        ));
        return $mock;
    }

    private function getMockValidator()
    {
        $mock = $this->createMock(\Symfony\Component\Validator\Validator\ValidatorInterface::class);
        $mock->method('validate')->willReturn(new \Symfony\Component\Validator\ConstraintViolationList());
        return $mock;
    }
}
