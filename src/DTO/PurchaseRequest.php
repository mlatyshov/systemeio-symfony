<?php 
namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class PurchaseRequest
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    public int $product;

    #[Assert\NotBlank]
    #[Assert\Regex('/^(DE\d{9}|IT\d{11}|GR\d{9}|FR[A-Z]{2}\d{9})$/')]
    public string $taxNumber;

    #[Assert\Length(max: 255)]
    public ?string $couponCode = null;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['paypal', 'stripe'], message: 'Invalid payment processor.')]
    public string $paymentProcessor;

    public function __construct(
        int $product,
        string $taxNumber,
        ?string $couponCode,
        string $paymentProcessor
    ) {
        $this->product = $product;
        $this->taxNumber = $taxNumber;
        $this->couponCode = $couponCode;
        $this->paymentProcessor = $paymentProcessor;
    }
}
