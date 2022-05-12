<?php

namespace App\Http\Requests\Crud;

use App\Http\Requests\CrudRequest;
use App\Http\Requests\Interfaces\WithTargetInterface;
use App\Http\Requests\Traits\WithTarget;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RestoreRequest.
 */
class RestoreRequest extends CrudRequest implements WithTargetInterface
{
    use WithTarget;

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

    /**
     * Get ability, which should be checked for the request.
     *
     * @return string|null
     */
    public function getAbility(): ?string
    {
        return 'restore';
    }

    /**
     * Get model with target id.
     *
     * @param Model|string $model
     *
     * @return ?Model
     */
    public function target(Model|string $model): ?Model
    {
        return $this->trashedTarget($model);
    }
}
