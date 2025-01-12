<?php

namespace App\Rules\Interfaces;

use Closure;

/**
 * Interface ValidationRule.
 */
interface ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): bool;
}
