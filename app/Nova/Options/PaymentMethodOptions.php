<?php

namespace App\Nova\Options;

use App\Enums\PaymentMethod;

/**
 * Class PaymentMethodOptions.
 */
class PaymentMethodOptions extends Options
{
    /**
     * Get all options.
     *
     * @return array
     */
    public static function all(): array
    {
        return PaymentMethod::getValues();
    }
}
