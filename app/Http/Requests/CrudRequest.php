<?php

namespace App\Http\Requests;

use App\Http\Requests\Traits\WithSpatie;

/**
 * Class CrudRequest.
 */
class CrudRequest extends BaseRequest
{
    use WithSpatie;

    /**
     * Ability, which should be checked for the request.
     *
     * @var string|null
     */
    protected ?string $ability = null;

    /**
     * Get ability, which should be checked for the request.
     *
     * @return string|null
     */
    public function getAbility(): ?string
    {
        return $this->ability;
    }
}
