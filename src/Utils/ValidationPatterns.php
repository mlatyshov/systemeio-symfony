<?php

namespace App\Utils;

class ValidationPatterns
{
    public const TAX_NUMBER_REGEX = '/^(DE\\d{9}|IT\\d{11}|GR\\d{9}|FR[A-Z]{2}\\d{9})$/';
}
