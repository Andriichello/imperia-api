<?php

namespace App\Services\Conditions;

use PHPUnit\Util\Exception;

class ConditionFactory
{
    public static function make(ConditionType|string $type, string $name, mixed $value): Condition
    {
        if (is_string($type)) {
            $type = ConditionType::fromValue($type);
        }

        if ($type == ConditionType::Equal()) {
            return new Equal($name, $value);
        } else if ($type == ConditionType::NotEqual()) {
            return new NotEqual($name, $value);
        } else if ($type == ConditionType::OrEqual()) {
            return new OrEqual($name, $value);
        } else if ($type == ConditionType::OrNotEqual()) {
            return new OrNotEqual($name, $value);
        } else if ($type == ConditionType::Inside()) {
            return new In($name, $value);
        } else if ($type == ConditionType::NotInside()) {
            return new NotIn($name, $value);
        } else if ($type == ConditionType::OrInside()) {
            return new OrIn($name, $value);
        } else if ($type == ConditionType::OrNotInside()) {
            return new OrNotIn($name, $value);
        } else if ($type == ConditionType::On()) {
            return new On($name, $value);
        } else if ($type == ConditionType::OrOn()) {
            return new OrOn($name, $value);
        }

        throw new Exception('There is no such condition: ' . $type);
    }
}
