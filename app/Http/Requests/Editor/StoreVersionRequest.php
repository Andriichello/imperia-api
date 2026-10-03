<?php

namespace App\Http\Requests\Editor;

use App\Models\Restaurant;
use Illuminate\Validation\Validator;
use OpenApi\Annotations as OA;

/**
 * Class StoreVersionRequest.
 *
 * A new scheduled version of the restaurant: a draft, or scheduled right away. It can start
 * with changes (e.g. a change scheduled on its own, or the editor's unsaved changes).
 */
class StoreVersionRequest extends EditorRequest
{
    use VersionChangeRules;

    /**
     * Class of the model the request is about.
     *
     * @return class-string<Restaurant>
     */
    protected function targetClass(): string
    {
        return Restaurant::class;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['nullable', 'string', 'max:120'],
            'goes_live_at' => ['nullable', 'date', 'required_if:schedule,true'],
            'schedule' => ['sometimes', 'boolean'],
            'changes' => ['sometimes', 'array', 'max:500'],
        ];

        foreach (array_keys((array) $this->input('changes', [])) as $index) {
            $rules = [...$rules, ...$this->changeRules("changes.$index")];
        }

        return $rules;
    }

    /**
     * Check the brand colors of the changes.
     *
     * @return array
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                foreach (array_keys((array) $this->input('changes', [])) as $index) {
                    $this->checkBrandContrast($validator, "changes.$index.fields");
                }
            },
        ];
    }

    /**
     * @OA\Schema(
     *   schema="EditorStoreVersionRequest",
     *   description="A new version: a draft, or scheduled right away (then it needs its date).",
     *   @OA\Property(property="name", type="string", nullable=true, example="Winter menu",
     *     description="None for a change scheduled on its own: it's named after the change."),
     *   @OA\Property(property="goes_live_at", type="string", nullable=true, example="2026-11-01 00:00",
     *     description="In the restaurant's time zone, unless it has an offset."),
     *   @OA\Property(property="schedule", type="boolean", example=true),
     *   @OA\Property(property="changes", type="array",
     *     @OA\Items(ref="#/components/schemas/EditorPutVersionChangeRequest")),
     * ),
     */
}
