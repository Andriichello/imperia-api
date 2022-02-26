<?php

namespace App\Nova\Options;

use App\Enums\FamilyRelation;

/**
 * Class FamilyRelationOptions.
 */
class FamilyRelationOptions extends Options
{
    /**
     * Get all options.
     *
     * @return array
     */
    public static function all(): array
    {
        return FamilyRelation::getValues();
    }
}
