<?php

namespace App\Services\Conditions;

use BenSampo\Enum\Enum;

/**
 * Class ConditionType
 * @package App\Services\Conditions
 *
 * @method static ConditionType Equal()
 * @method static ConditionType NotEqual()
 * @method static ConditionType OrEqual()
 * @method static ConditionType OrNotEqual()
 * @method static ConditionType Inside()
 * @method static ConditionType NotInside()
 * @method static ConditionType OrInside()
 * @method static ConditionType OrNotInside()
 * @method static ConditionType On()
 * @method static ConditionType OrOn()
 */

final class ConditionType extends Enum
{
    const Equal = 'equal';
    const NotEqual = 'not equal';
    const OrEqual = 'or equal';
    const OrNotEqual = 'or not equal';
    const Inside = 'in';
    const NotInside = 'not in';
    const OrInside = 'or in';
    const OrNotInside = 'or not in';
    const On = 'on';
    const OrOn = 'or on';
}
