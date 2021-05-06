<?php

namespace App\Rules;

use App\Constrainters\Constrainter;
use Illuminate\Contracts\Validation\Rule;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;

class SymfonyRule implements Rule
{
    /**
     * Validator instance
     *
     * @var ValidatorInterface
     */
    protected static $validator;

    /**
     * Array of symfony constraints.
     *
     * @var array
     */
    protected $constraints = [];

    /**
     * Array of constraint violations.
     *
     * @var array
     */
    protected $violations = [];

    /**
     * Create a new rule instance.
     *
     * @param Constraint|Constraint[] $constraints
     * @return void
     */
    public function __construct($constraints = null)
    {
        if (isset($constraints)) {
            $this->append($constraints);
        }
    }

    /**
     * Append more constraints.
     *
     * @param Constraint|Constraint[] $constraints
     * @return SymfonyRule
     */
    public function append($constraints): SymfonyRule
    {
        if (isset($constraints)) {
            if (is_array($constraints)) {
                $this->constraints = array_merge($this->constraints, $constraints);
            } else {
                $this->constraints[] = $constraints;
            }

        }
        return $this;
    }

    /**
     * Perform validation.
     *
     * @param string $attribute
     * @param mixed $value
     * @return array|ConstraintViolationList
     */
    public function validate($attribute, $value)
    {
        if (empty($this->constraints)) {
            return [];
        }

        try {
            if (empty(self::$validator)) {
                self::$validator = (new ValidatorBuilder())->getValidator();
            }

            $violations = self::$validator->validate($value, $this->constraints);
        } catch (\Exception $exception) {
            $violations[] = $exception->getMessage();
        }

        return $violations;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $this->violations = $this->validate($attribute, $value);
        return count($this->violations) === 0;
    }

    /**
     * Get the validation error message.
     *
     * @return ?string
     */
    public function message(): ?string
    {
        if (count($this->violations) > 0) {
            $message = '';
            for ($i = 0; $i < count($this->violations); $i++) {
                if ($i !== 0) {
                    $message .= ' ';
                }

                if (is_string($this->violations[$i])) {
                    $message .= "(:attribute) => {$this->violations[$i]}";
                } else {
                    $message .= "(:attribute) => {$this->violations[$i]->getMessage()}";
                }
            }

            return $message;
        }
        return null;
    }
}
