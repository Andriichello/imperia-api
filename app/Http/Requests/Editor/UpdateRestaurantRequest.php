<?php

namespace App\Http\Requests\Editor;

use App\Enums\Establishment;
use App\Helpers\ColorHelper;
use App\Models\Restaurant;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use OpenApi\Annotations as OA;

/**
 * Class UpdateRestaurantRequest.
 *
 * Details of the restaurant (name, type, contacts) and its brand colors.
 * Fields, which are left out, stay as they are.
 */
class UpdateRestaurantRequest extends EditorRequest
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
        $hex = 'regex:/^#[0-9a-fA-F]{6}$/';

        return [
            ...$this->translationRules('name', true, 255, partial: true),
            'establishment' => ['sometimes', 'string', Rule::in(Establishment::getValues())],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            ...$this->translationRules('address', false, 255, partial: true),
            // both colors, or neither
            'brand_primary' => ['nullable', 'string', $hex, 'required_with:brand_primary_content'],
            'brand_primary_content' => ['nullable', 'string', $hex, 'required_with:brand_primary'],
            // of prices, none for the one between those two
            'brand_accent' => ['nullable', 'string', $hex],
        ];
    }

    /**
     * Text on the brand color's tints and prices in the accent color have to be readable.
     *
     * @return array
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $primary = $this->input('brand_primary');
                $text = $this->input('brand_primary_content');

                if (!ColorHelper::isHex($primary) || !ColorHelper::isHex($text)) {
                    return;
                }

                $contrast = ColorHelper::contrastOnTints($primary, $text);

                if ($contrast < ColorHelper::READABLE) {
                    $validator->errors()->add(
                        'brand_primary_content',
                        sprintf(
                            'Text on the tinted backgrounds has %.1f : 1 contrast, it needs at least %.1f : 1. '
                            . 'Pick a darker text color.',
                            floor($contrast * 10) / 10,
                            ColorHelper::READABLE
                        )
                    );
                }
            },
            function (Validator $validator) {
                $accent = $this->input('brand_accent');

                if (!ColorHelper::isHex($accent)) {
                    return;
                }

                $contrast = ColorHelper::contrastOnList($accent);

                if ($contrast < ColorHelper::READABLE_LARGE) {
                    $validator->errors()->add(
                        'brand_accent',
                        sprintf(
                            'Prices have %.1f : 1 contrast on the menu, they need at least %.1f : 1. '
                            . 'Pick a darker accent color.',
                            floor($contrast * 10) / 10,
                            ColorHelper::READABLE_LARGE
                        )
                    );
                }
            },
        ];
    }

    /**
     * @OA\Schema(
     *   schema="EditorUpdateRestaurantRequest",
     *   description="Details and brand colors of a restaurant. Fields, which are left out, stay as they are.",
     *   @OA\Property(property="name", ref="#/components/schemas/EditorTranslations",
     *     description="Required in the restaurant's default language."),
     *   @OA\Property(property="establishment", type="string", example="restaurant",
     *     enum={"restaurant", "cafe", "bakery", "bistro", "pizzeria", "bar"}),
     *   @OA\Property(property="phone", type="string", nullable=true, example="+380441234567"),
     *   @OA\Property(property="address", ref="#/components/schemas/EditorTranslations"),
     *   @OA\Property(property="brand_primary", type="string", nullable=true, example="#3bb517",
     *     description="Both colors or neither. Text on the color's tints needs 4.5 : 1 contrast."),
     *   @OA\Property(property="brand_primary_content", type="string", nullable=true, example="#284625"),
     *   @OA\Property(property="brand_accent", type="string", nullable=true, example="#327e1e",
     *     description="Of prices, none for the one between those two. Needs 3 : 1 contrast on the menu."),
     * ),
     */
}
