<?php

namespace App\Http\Requests\Traits;

use App\Http\Requests\BaseRequest;

/**
 * Trait WithTarget.
 *
 * @mixin BaseRequest
 */
trait WithTarget
{
    /**
     * Get route id parameter.
     *
     * @return mixed
     */
    public function id(): mixed
    {
        return $this->route('id');
    }
}
