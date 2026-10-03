<?php

namespace App\Http\Requests\Editor;

use App\Models\MenuVersion;
use OpenApi\Annotations as OA;

/**
 * Class UpdateVersionRequest.
 *
 * Rename or reschedule a scheduled version.
 */
class UpdateVersionRequest extends EditorRequest
{
    /**
     * Class of the model the request is about.
     *
     * @return class-string<MenuVersion>
     */
    protected function targetClass(): string
    {
        return MenuVersion::class;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'nullable', 'string', 'max:120'],
            'goes_live_at' => ['sometimes', 'nullable', 'date'],
        ];
    }

    /**
     * @OA\Schema(
     *   schema="EditorUpdateVersionRequest",
     *   description="Fields, which are left out, stay as they are. A scheduled version stays in the future.",
     *   @OA\Property(property="name", type="string", nullable=true, example="Winter menu"),
     *   @OA\Property(property="goes_live_at", type="string", nullable=true, example="2026-11-01 00:00",
     *     description="In the restaurant's time zone, unless it has an offset."),
     * ),
     */
}
