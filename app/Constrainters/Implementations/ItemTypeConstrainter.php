<?php

namespace App\Constrainters\Implementations;

use App\Constrainters\Constrainter;
use Symfony\Component\Validator\Constraints as Assert;

class ItemTypeConstrainter extends Constrainter
{
    public static function getConstraints(bool $isMandatory = false, array $additionalConstrains = []): array
    {
        $additionalConstrains[] = new Assert\Length([
            'min' => 2,
            'max' => 35,
        ]);
        $additionalConstrains[] = new Assert\Regex([
            'pattern' => '([a-z_.]*)',
            'message' => 'This value must contain only lowercase letters (a-z) and/or underscores and/or dots.'
        ]);
        return parent::getConstraints(
            $isMandatory,
            $additionalConstrains
        );
    }
}
