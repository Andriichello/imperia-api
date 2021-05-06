<?php

namespace App\Constrainters\Implementations;

use App\Constrainters\Constrainter;
use Symfony\Component\Validator\Constraints as Assert;

class DatetimeConstrainter extends Constrainter
{
    public static function getHoursConstraints(bool $isTemplatable = false): array
    {
        return self::withTemplatability(
            [
                new Assert\Range([
                    'min' => 0,
                    'max' => 23,
                ]),
            ],
            $isTemplatable
        );
    }

    public static function getMinutesConstraints(bool $isTemplatable = false): array
    {
        return self::withTemplatability(
            [
                new Assert\Range([
                    'min' => 0,
                    'max' => 59,
                ]),
            ],
            $isTemplatable
        );
    }

    public static function getDayConstraints(bool $isTemplatable = false): array
    {
        return self::withTemplatability(
            [
                new Assert\Range([
                    'min' => 1,
                    'max' => 31,
                ]),
            ],
            $isTemplatable
        );
    }

    public static function getMonthConstraints(bool $isTemplatable = false): array
    {
        return self::withTemplatability(
            [
                new Assert\Range([
                    'min' => 1,
                    'max' => 12,
                ]),
            ],
            $isTemplatable
        );
    }

    public static function getYearConstraints(bool $isTemplatable = false): array
    {
        return self::withTemplatability(
            [
                new Assert\Range([
                    'min' => 1900,
                ]),
            ],
            $isTemplatable
        );
    }

    public static function getConstraints(bool $isNonTemplatable = true, array $additionalConstraints = []): array
    {
        $additionalConstraints['hours'] = self::getHoursConstraints($isNonTemplatable);
        $additionalConstraints['minutes'] = self::getMinutesConstraints($isNonTemplatable);
        $additionalConstraints['day'] = self::getDayConstraints($isNonTemplatable);
        $additionalConstraints['month'] = self::getMonthConstraints($isNonTemplatable);
        $additionalConstraints['year'] = self::getYearConstraints($isNonTemplatable);

        return $additionalConstraints;
    }

    private static function withTemplatability(array $constraints, bool $isTemplatable): array
    {
        if (!$isTemplatable) {
            $constraints[] = new Assert\NotBlank(
                [
                    'message' => 'In a non-templatable datetime all fields must be specified.'
                ]
            );
        }
        return $constraints;
    }
}
