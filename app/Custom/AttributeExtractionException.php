<?php

namespace App\Custom;

class AttributeExtractionException extends \Exception
{
    protected ?string $key;
    protected ?string $reason;

    /**
     * AttributeAbsenceException constructor.
     * @param string|null $key
     * @param string|null $reason
     */
    public function __construct(?string $key = null, ?string $reason = null)
    {
        $this->key = $key;
        $this->reason = $reason;
    }

    /**
     * @return string|null
     */
    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * @param string|null $key
     */
    public function setKey(?string $key): void
    {
        $this->key = $key;
    }

    /**
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * @param string|null $reason
     */
    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }
}
