<?php

namespace App\Constrainters\Implementations;

use App\Constrainters\Constrainter;
use Symfony\Component\Validator\Constraints as Assert;

class EmailConstrainter extends Constrainter
{
    public static function getConstraints(bool $isMandatory = false, array $additionalConstrains = []): array
    {
        $additionalConstrains[] = new Assert\Length([
            'max' => 50,
        ]);
        $additionalConstrains[] = new Assert\Email();

        return parent::getConstraints(
            $isMandatory,
            $additionalConstrains
        );
    }
}
