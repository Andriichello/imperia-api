<?php

namespace App\Http\Requests\Editor;

use App\Helpers\ContentLocale;
use App\Models\Restaurant;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class UpdateRestaurantNotesRequest.
 *
 * All notes of the restaurant, in their order. Notes, which are left out, are deleted,
 * ones without an id are added.
 */
class UpdateRestaurantNotesRequest extends EditorRequest
{
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
        $locales = implode(',', ContentLocale::supported());
        $default = $this->restaurant()->getDefaultLocale();

        return [
            'notes' => ['present', 'array', 'max:20'],
            'notes.*.id' => [
                'nullable',
                'integer',
                'distinct',
                Rule::exists('restaurant_notes', 'id')->where('restaurant_id', $this->restaurant()->id),
            ],
            'notes.*.text' => ['required', "array:$locales"],
            "notes.*.text.$default" => ['required', 'string', 'max:120'],
            'notes.*.text.*' => ['nullable', 'string', 'max:120'],
            'notes.*.is_hidden' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @OA\Schema(
     *   schema="EditorUpdateRestaurantNotesRequest",
     *   description="All notes in their order. Left out ones are deleted, ones without an id are added.",
     *   required={"notes"},
     *   @OA\Property(property="notes", type="array", @OA\Items(
     *     required={"text"},
     *     @OA\Property(property="id", type="integer", nullable=true, example=1),
     *     @OA\Property(property="text", ref="#/components/schemas/EditorTranslations",
     *       description="Required in the restaurant's default language, 120 characters at most."),
     *     @OA\Property(property="is_hidden", type="boolean", example=false),
     *   )),
     * ),
     */
}
