<?php
namespace App\DTO;

use App\Utils\ValidationPatterns;
use Symfony\Component\Validator\Constraints as Assert;

class PurchaseRequest
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    public int $product;

    #[Assert\NotBlank]
    #[Assert\Regex(ValidationPatterns::TAX_NUMBER_REGEX)]
    public string $taxNumber;

    #[Assert\Length(max: 255)]
    public ?string $couponCode = null;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['paypal', 'stripe'], message: 'Invalid payment processor.')]
    public string $paymentProcessor;
}