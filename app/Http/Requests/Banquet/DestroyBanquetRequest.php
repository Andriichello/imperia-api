<?php

namespace App\Http\Requests\Banquet;

use App\Http\Requests\Crud\DestroyRequest;
use App\Models\Banquet;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * Class DestroyBanquetRequest.
 */
class DestroyBanquetRequest extends DestroyRequest
{
    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws AuthorizationException
     */
    protected function failedAuthorization()
    {
        $message = 'Banquet can\'t be deleted,'
            . ' because it\'s in a on-editable state.';
        throw new AuthorizationException($message);
    }

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
        return $banquet->canBeEdited();
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
