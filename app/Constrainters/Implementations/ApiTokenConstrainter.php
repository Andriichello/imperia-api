<?php

namespace App\Constrainters\Implementations;

use App\Constrainters\Constrainter;
use Symfony\Component\Validator\Constraints as Assert;

class ApiTokenConstrainter extends Constrainter
{
    public static function getConstraints(bool $isMandatory = false, array $additionalConstrains = []): array
    {
        $additionalConstrains[] = new Assert\Length([
            'min' => 64,
        ]);
        $additionalConstrains[] = new Assert\Regex([
            'pattern' => '([.0-9a-zA-Z])',
        ]);
        return parent::getConstraints(
            $isMandatory,
            $additionalConstrains
        );
    }
}
