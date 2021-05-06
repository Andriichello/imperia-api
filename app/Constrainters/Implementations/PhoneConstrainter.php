<?php

namespace App\Constrainters\Implementations;

use App\Constrainters\Constrainter;
use Symfony\Component\Validator\Constraints as Assert;

class PhoneConstrainter extends Constrainter
{
    public static function getConstraints(bool $isMandatory = false, array $additionalConstrains = []): array
    {
        $additionalConstrains[] = new Assert\Length([
            'max' => 25,
        ]);
        $additionalConstrains[] = new Assert\Regex([
            'pattern' => '(^[+][0-9]+([ -][0-9]+)*$)',
            'message' => 'This value must start with \'+\' and contain only digits (0-9) and optionally spaces or/and \'-\'.'
        ]);
        return parent::getConstraints(
            $isMandatory,
            $additionalConstrains
        );
    }
}
