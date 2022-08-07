<?php

namespace App\Helpers\Objects;

use Carbon\Carbon;

/**
 * Class Signature.
 */
class Signature
{
    /**
     * @var array
     */
    protected array $payload = [];

    /**
     * Signature constructor.
     */
    public function __construct(array $payload = [])
    {
        $this->payload = $payload;
    }

    /**
     * @param array $payload
     *
     * @return static
     */
    public function setPayload(array $payload): static
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return data_get($this->payload, 'uid');
    }

    /**
     * @param int|null $id
     *
     * @return static
     */
    public function setUserId(?int $id): static
    {
        data_set($this->payload, 'uid', $id);

        return $this;
    }

    /**
     * @return Carbon|null
     */
    public function getExpiration(): ?Carbon
    {
        $expiration = data_get($this->payload, 'exp');

        return $expiration ? Carbon::createFromTimestamp($expiration) : null;
    }

    /**
     * @param Carbon|null $expiration
     *
     * @return static
     */
    public function setExpiration(?Carbon $expiration): static
    {
        data_set($this->payload, 'exp', $expiration->timestamp ?? null);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return data_get($this->payload, 'path');
    }

    /**
     * @param string|null $path
     *
     * @return static
     */
    public function setPath(?string $path): static
    {
        data_set($this->payload, 'path', $path);

        return $this;
    }
}
