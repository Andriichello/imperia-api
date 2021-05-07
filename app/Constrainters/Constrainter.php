<?php

namespace App\Constrainters;
use App\Rules\SymfonyRule;
use Illuminate\Validation\Rule;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;


abstract class Constrainter
{
    /**
     * Get an array of validation constraints.
     *
     * @param bool $isMandatory
     * @param Constraint[] $additionalConstrains
     * @return array
     */
    public static function getConstraints(bool $isMandatory = false, array $additionalConstrains = []): array {
        $constraints = $isMandatory ? [new Assert\NotBlank()] : [];

        return array_merge(
            $constraints,
            $additionalConstrains
        );
    }

    /**
     * Get an array of validation rules.
     *
     * @param bool $required
     * @param array $additionalConstrains
     * @return array
     */
    public static function getRules(bool $required = false, array $additionalConstrains = []): array {
        $rules = $required ? ['required'] : [];

        $constraints = static::getConstraints(false, $additionalConstrains);
        foreach ($constraints as $constraint) {
            if ($constraint instanceof Constraint) {
                $rules[] = new SymfonyRule($constraint);
            } else {
                $rules[] = $constraint;
            }
        }

        return $rules;
    }
}
