<?php

namespace App\Http\Requests\Banquet;

use App\Http\Requests\Crud\DestroyRequest;
use App\Http\Requests\Traits\GuardsBanquet;
use App\Models\Banquet;

/**
 * Class DestroyBanquetRequest.
 */
class DestroyBanquetRequest extends DestroyRequest
{
    use GuardsBanquet;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        /** @var Banquet $banquet */
        $banquet = Banquet::query()
            ->findOrFail($this->id());

        return $this->canEdit($this->user(), $banquet);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                //
            ]
        );
    }
}
