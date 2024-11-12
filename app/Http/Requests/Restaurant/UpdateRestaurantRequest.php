<?php

namespace App\Http\Requests\Restaurant;

use App\Enums\Weekday;
use App\Http\Requests\Crud\UpdateRequest;
use App\Models\Restaurant;
use App\Models\Schedule;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\QueryBuilder as SpatieBuilder;

/**
 * Class UpdateRestaurantRequest.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UpdateRestaurantRequest extends UpdateRequest
{
    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                'schedules',
            ]
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'slug' => [
                'sometimes',
                'string',
                'min:2',
                Rule::unique(Restaurant::class, 'slug')
                    ->ignore($this->id())
            ],
            'name' => [
                'sometimes',
                'string',
                'min:2',
                'max:255',
            ],
            'country' => [
                'sometimes',
                'string',
                'min:1',
                'max:255',
            ],
            'city' => [
                'sometimes',
                'string',
                'min:1',
                'max:255',
            ],
            'place' => [
                'sometimes',
                'string',
                'min:1',
                'max:255',
            ],
            'timezone' => [
                'sometimes',
                'string',
                'min:1',
                'max:255',
            ],
            'email' => [
                'sometimes',
                'nullable',
                'email',
            ],
            'phone' => [
                'sometimes',
                'nullable',
                'regex:/(\+?[0-9]{1,2})?[0-9]{10,12}/',
            ],
            'location' => [
                'sometimes',
                'nullable',
                'url',
            ],
            'website' => [
                'sometimes',
                'nullable',
                'url',
            ],
            'schedules' => [
                'sometimes',
                'nullable',
                'array',
            ],
            'schedules.*' => [
                'required',
                'array',
            ],
            'schedules.*.id' => [
                'sometimes',
                'integer',
                Rule::exists(Schedule::class, 'id'),
            ],
            'schedules.*.restaurant_id' => [
                'sometimes',
                'integer',
                function (string $attribute, int $value, Closure $fail) {
                    if ($value !== ((int) $this->id())) {
                        $fail('Must be same as current restaurant\'s id.');
                    }
                }
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
            'schedules.*.beg_minute' => [
                'required',
                'integer',
                'min:0',
                'max:59',
            ],
            'schedules.*.end_hour' => [
                'required',
                'integer',
                'min:0',
                'max:23',
            ],
            'schedules.*.end_minute' => [
                'required',
                'integer',
                'min:0',
                'max:59',
            ],
            'schedules.*.archived' => [
                'required',
                'boolean',
            ],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        if (!$user || !$user->isAdmin()) {
            return false;
        }

        return $user->restaurant_id === null
            || $user->restaurant_id === ((int) $this->id());
    }

    /**
     * Apply allowed options to spatie builder.
     *
     * @param Builder|EloquentBuilder|SpatieBuilder $builder
     *
     * @return SpatieBuilder
     */
    public function spatieBuilder(SpatieBuilder|EloquentBuilder|Builder $builder): SpatieBuilder
    {
        /** @phpstan-ignore-next-line */
        return parent::spatieBuilder($builder)
            ->with('schedules');
    }

    /**
     * @OA\Schema(
     *   schema="ScheduleForUpdateRestaurantRequest",
     *   description="Schedule object for update restaurant request.",
     *   required={"weekday", "beg_hour", "beg_minute", "end_hour", "end_minute"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="restaurant_id", type="integer", example=1),
     *   @OA\Property(property="weekday", type="string", example="monday",
     *     enum={"monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"}),
     *   @OA\Property(property="beg_hour", type="integer", example=8),
     *   @OA\Property(property="beg_minute", type="integer", example=30),
     *   @OA\Property(property="end_hour", type="integer", example=8),
     *   @OA\Property(property="end_minute", type="integer", example=30),
     *   @OA\Property(property="archived", type="boolean", example=false),
     * ),
     *
     * @OA\Schema(
     *   schema="UpdateRestaurantRequest",
     *   description="Update restaurant request",
     *   @OA\Property(property="slug", type="string", example="imperia",
     *     description="Must be unique within the database."),
     *   @OA\Property(property="name", type="string", example="Imperia"),
     *   @OA\Property(property="country", type="string", example="Ukraine"),
     *   @OA\Property(property="city", type="string", example="Uzhhorod"),
     *   @OA\Property(property="place", type="string", example="Dovzhenka St, 12"),
     *   @OA\Property(property="timezone", type="string", example="Europe/Kyiv"),
     *   @OA\Property(property="email", type="string", nullable=true, example="imperia@email.com"),
     *   @OA\Property(property="phone", type="string", nullable=true, example="+380507777777",
     *     description="Phone number may start with a plus and must contain only digits 0-9."),
     *   @OA\Property(property="location", type="string", nullable=true,
     *     example="https://maps.app.goo.gl/uPWnGp3eTw2LGctEA",
     *     description="Link to restaurant's location on Google Maps."),
     *   @OA\Property(property="website", type="string", nullable=true,
     *     description="Link to restaurant's website."),
     *   @OA\Property(property="schedules", type="array", nullable=true,
     *     @OA\Items(ref ="#/components/schemas/ScheduleForUpdateRestaurantRequest"))),
     * )
     */
}
