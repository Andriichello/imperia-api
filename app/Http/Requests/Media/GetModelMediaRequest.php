<?php

namespace App\Http\Requests\Media;

use App\Http\Requests\CrudRequest;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Class GetModelMediaRequest.
 */
class GetModelMediaRequest extends CrudRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $morphs = array_keys(Relation::morphMap());

        return [
            'model_id' => [
                'required',
                'integer',
                'min:1',
            ],
            'model_type' => [
                'required',
                'string',
                'in:' . implode(',', $morphs),
            ],
        ];
    }

    /**
     * Get ability, which should be checked for the request.
     *
     * @return string|null
     */
    public function getAbility(): ?string
    {
        return 'viewAttachedMedia';
    }
}
