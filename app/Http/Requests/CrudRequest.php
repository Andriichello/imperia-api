<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Http\Requests\Traits\WithSpatie;
use App\Models\User;

/**
 * Class CrudRequest.
 */
class CrudRequest extends BaseRequest
{
    use WithSpatie;

    /**
     * Get ability, which should be checked for the request.
     *
     * @return string|null
     */
    public function getAbility(): ?string
    {
        return null;
    }
}
