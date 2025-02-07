<?php

namespace App\Http\Requests\Restaurant;

use App\Enums\Weekday;
use App\Http\Requests\Crud\UpdateRequest;
use DateTimeZone;
use Illuminate\Validation\Rule;

/**
 * Class UpdateRestaurantRequest.
 */
class UpdateRestaurantRequest extends UpdateRequest
{
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
                'name' => [
                    'sometimes',
                    'string',
                    'min:2',
                    'max:255',
                    Rule::unique('restaurants')
                        ->ignore($this->id()),
                ],
                'country' => [
                    'sometimes',
                    'string',
                    'max:255',
                ],
                'city' => [
                    'sometimes',
                    'string',
                    'max:255',
                ],
                'place' => [
                    'sometimes',
                    'string',
                    'max:255',
                ],
                'phone' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:20',
                    'regex:/(\+?[0-9]{1,2})?[0-9]{10,12}/',
                ],
                'timezone' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:255',
                    Rule::in(DateTimeZone::listIdentifiers()),
                ],
                'schedules' => [
                    'sometimes',
                    'array',
                    'min:0',
                ],
                'schedules.*' => [
                    'required',
                    'array',
                ],
                'schedules.*.weekday' => [
                    'required',
                    'string',
                    Weekday::getValidationRule(),
                ],
                'schedules.*.beg_hour' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:23',
                ],
                'schedules.*.end_hour' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:23',
                ],
                'schedules.*.beg_minute' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:59',
                ],
                'schedules.*.end_minute' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:59',
                ],
                'schedules.*.archived' => [
                    'sometimes',
                    'boolean',
                ],
            ]
        );
    }

    /**
     * @OA\Schema(
     *   schema="ScheduleForUpdateRestaurantRequest",
     *   description="Request body for updating restaurant's schedules details",
     *   required = {"weekday", "beg_hour", "end_hour",
     *     "beg_minute", "end_minute", "archived"},
     *   @OA\Property(property="weekday", type="string", example="monday",
     *     enum={"monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"}),
     *   @OA\Property(property="beg_hour", type="integer", example=9,
     *     description="Start hour of the day [0 ; 23]."),
     *   @OA\Property(property="end_hour", type="integer", example=23,
     *     description="End hour of the day [0 ; 23]. If less then beg_hour it,
     *   then it meens that it's a cross day schedule"),
     *   @OA\Property(property="beg_minute", type="integer", example=0,
     *     description="Start minute of the day [0 ; 59]."),
     *   @OA\Property(property="end_minute", type="integer", example=30,
     *     description="End minute of the day [0 ; 59]."),
     *   @OA\Property(property="archived", type="boolean", example=false),
     * )
     *
     * @OA\Schema(
     *   schema="UpdateRestaurantRequest",
     *   description="Request body for updating restaurant details",
     *   @OA\Property(property="name", type="string", example="Examplace",
     *     description="The name of the restaurant."),
     *   @OA\Property(property="country", type="string", nullable=true, example="Ukraine",
     *     description="The country where the restaurant is located."),
     *   @OA\Property(property="city", type="string", nullable=true, example="Uzhhorod",
     *     description="The city where the restaurant is located."),
     *   @OA\Property(property="place", type="string", nullable=true, example="Teatralna Square",
     *     description="Additional information about the restaurant's location."),
     *   @OA\Property(property="phone", type="string", nullable=true, example="+380501234567",
     *     description="The contact phone number of the restaurant."),
     *   @OA\Property(property="schedules", type="array",
     *     @OA\Items(ref ="#/components/schemas/ScheduleForUpdateRestaurantRequest")),
     * )
     */
}
