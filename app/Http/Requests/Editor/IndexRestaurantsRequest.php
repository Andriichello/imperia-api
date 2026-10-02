<?php

namespace App\Http\Requests\Editor;

use App\Http\Requests\BaseRequest;

/**
 * Class IndexRestaurantsRequest.
 *
 * Restaurants the user can edit.
 */
class IndexRestaurantsRequest extends BaseRequest
{
    /**
     * Only admins can edit restaurants.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }
}
