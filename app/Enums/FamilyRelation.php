<?php

namespace App\Enums;

/**
 * Enum FamilyRelation.
 *
 * @method static FamilyRelation Child()
 * @method static FamilyRelation Parent()
 * @method static FamilyRelation Grandparent()
 * @method static FamilyRelation Partner()
 *
 * @SuppressWarnings(PHPMD)
 */
class FamilyRelation extends Enum
{
    public const Child = 'child';
    public const Parent = 'parent';
    public const GrandParent = 'grandparent';
    public const Partner = 'partner';
}
