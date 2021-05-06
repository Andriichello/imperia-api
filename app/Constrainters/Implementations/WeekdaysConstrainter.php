<?php

namespace App\Constrainters\Implementations;

use App\Constrainters\Constrainter;
use Symfony\Component\Validator\Constraints as Assert;

class WeekdaysConstrainter extends Constrainter
{
    public static function getConstraints(bool $isMandatory = false, array $additionalConstrains = []): array
    {
        $additionalConstrains[] = new Assert\Regex(
            [
                'pattern' => '(^$|^([1-7][,]){0,6}([1-7])$)',
                'message' => 'This value must only contain digits in range [1;7] separated by comma.'
            ]
        );
        return parent::getConstraints(
            $isMandatory,
            $additionalConstrains,
        );
    }
}
