<?php

namespace App\Utils;

class TaxRules
{
    public const RULES = [
        'DE' => ['rate' => 19, 'regex' => '/^DE\\d{9}$/'],
        'IT' => ['rate' => 22, 'regex' => '/^IT\\d{11}$/'],
        'FR' => ['rate' => 20, 'regex' => '/^FR[A-Z]{2}\\d{9}$/'],
        'GR' => ['rate' => 24, 'regex' => '/^GR\\d{9}$/'],
    ];

    /**
     * 
     *
     * @param string $countryCode
     * @return float|null
     */
    public static function getRate(string $countryCode): ?float
    {
        return self::RULES[$countryCode]['rate'] ?? null;
    }

    /**
     * 
     *
     * @param string $countryCode
     * @return string|null
     */
    public static function getRegex(string $countryCode): ?string
    {
        return self::RULES[$countryCode]['regex'] ?? null;
    }
}
