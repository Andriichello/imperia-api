<?php

namespace App\Http\Requests\Crud;

use App\Http\Requests\CrudRequest;
use App\Http\Requests\Interfaces\WithTargetInterface;
use App\Http\Requests\Traits\WithTarget;

/**
 * Class DestroyRequest.
 */
class DestroyRequest extends CrudRequest implements WithTargetInterface
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
                'force' => [
                    'boolean',
                ],
            ]
        );
    }

    /**
     * Get force parameter.
     *
     * @return bool
     */
    public function force(): bool
    {
        return (bool) $this->get('force', false);
    }

    /**
     * Get ability, which should be checked for the request.
     *
     * @return string|null
     */
    public function getAbility(): ?string
    {
        return $this->force() ? 'forceDelete' : 'delete';
    }

    /**
     * @OA\Schema(
     *   schema="DestroyRequest",
     *   description="Destroy request",
     *   @OA\Property(property="force", type="boolean", example="false",
     *     description="Determines whether resource should be deleted without chance of restoring."),
     *  )
     */
}
