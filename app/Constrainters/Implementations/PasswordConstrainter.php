<?php

namespace App\Constrainters\Implementations;

use App\Constrainters\Constrainter;
use Symfony\Component\Validator\Constraints as Assert;

class PasswordConstrainter extends Constrainter
{
    public static function getConstraints(bool $isMandatory = false, array $additionalConstrains = []): array
    {
        $additionalConstrains[] = new Assert\Length([
            'min' => 8,
            'max' => 50,
        ]);
        return parent::getConstraints(
            $isMandatory,
            $additionalConstrains
        );
    }
}
