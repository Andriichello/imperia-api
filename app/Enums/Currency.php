<?php

namespace App\Enums;

/**
 * Enum Currency.
 *
 * ISO 4217 codes of the currencies, which the website has a symbol for.
 *
 * @method static Currency Hryvnia()
 * @method static Currency Euro()
 * @method static Currency Dollar()
 * @method static Currency Pound()
 * @method static Currency Zloty()
 * @method static Currency Koruna()
 *
 * @SuppressWarnings(PHPMD)
 */
class Currency extends Enum
{
    public const Hryvnia = 'UAH';
    public const Euro = 'EUR';
    public const Dollar = 'USD';
    public const Pound = 'GBP';
    public const Zloty = 'PLN';
    public const Koruna = 'CZK';

    /**
     * Currencies with their labels.
     *
     * @return array<string, string>
     */
    public static function getLabels(): array
    {
        return [
            self::Hryvnia => 'UAH · Ukrainian hryvnia (₴)',
            self::Euro => 'EUR · Euro (€)',
            self::Dollar => 'USD · US dollar ($)',
            self::Pound => 'GBP · British pound (£)',
            self::Zloty => 'PLN · Polish złoty (zł)',
            self::Koruna => 'CZK · Czech koruna (Kč)',
        ];
    }
}
