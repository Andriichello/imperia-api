<?php

namespace App\Constrainters\Implementations;

use App\Constrainters\Constrainter;
use Symfony\Component\Validator\Constraints as Assert;

class DescriptionConstrainter extends Constrainter
{
    public static function getConstraints(bool $isMandatory = false, array $additionalConstrains = []): array
    {
        $additionalConstrains[] = new Assert\Length([
            'min' => 2,
            'max' => 100,
        ]);
        $additionalConstrains[] = new Assert\Regex([
            'pattern' => '(^\S(.*)\S$)',
            'message' => 'This value must start and end with non-space character.'
        ]);
        return parent::getConstraints(
            $isMandatory,
            $additionalConstrains
        );
    }
}
