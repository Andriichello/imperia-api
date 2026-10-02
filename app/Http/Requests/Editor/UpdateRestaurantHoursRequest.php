<?php

namespace App\Http\Requests\Editor;

use App\Enums\Weekday;
use App\Helpers\ContentLocale;
use App\Models\Restaurant;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use OpenApi\Annotations as OA;

/**
 * Class UpdateRestaurantHoursRequest.
 *
 * Time zone, weekly hours (a day can have several intervals, none means it's closed),
 * special days and a temporary closure of the restaurant. Everything is replaced:
 * intervals and special days, which are left out, are deleted.
 */
class UpdateRestaurantHoursRequest extends EditorRequest
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
        $weekdays = implode(',', Weekday::getValues());

        return [
            'timezone' => ['required', 'string', 'timezone:all'],

            'weekdays' => ['present', "array:$weekdays"],
            'weekdays.*' => ['array', 'max:3'],
            'weekdays.*.*.beg_hour' => ['required', 'integer', 'between:0,23'],
            'weekdays.*.*.beg_minute' => ['required', 'integer', 'between:0,59'],
            'weekdays.*.*.end_hour' => ['required', 'integer', 'between:0,23'],
            'weekdays.*.*.end_minute' => ['required', 'integer', 'between:0,59'],

            'exceptions' => ['present', 'array', 'max:50'],
            'exceptions.*.id' => [
                'nullable',
                'integer',
                'distinct',
                Rule::exists('schedule_exceptions', 'id')->where('restaurant_id', $this->restaurant()->id),
            ],
            'exceptions.*.starts_on' => ['required', 'date_format:Y-m-d'],
            'exceptions.*.ends_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:exceptions.*.starts_on'],
            'exceptions.*.is_closed' => ['required', 'boolean'],
            'exceptions.*.beg_hour' => ['nullable', 'integer', 'between:0,23'],
            'exceptions.*.beg_minute' => ['nullable', 'integer', 'between:0,59'],
            'exceptions.*.end_hour' => ['nullable', 'integer', 'between:0,23'],
            'exceptions.*.end_minute' => ['nullable', 'integer', 'between:0,59'],
            'exceptions.*.reason' => ['nullable', "array:$locales"],
            'exceptions.*.reason.*' => ['nullable', 'string', 'max:60'],

            'closed_until' => ['nullable', 'date_format:Y-m-d'],
            ...$this->translationRules('closed_reason', false, 120),
        ];
    }

    /**
     * Opening and closing times have to differ, intervals of a day can't overlap,
     * and special days, which aren't closed, need their hours.
     *
     * @return array
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                foreach ($this->input('weekdays', []) as $weekday => $intervals) {
                    $fields = array_map(fn ($index) => "weekdays.$weekday.$index", array_keys($intervals));

                    $this->validateIntervals($validator, array_combine($fields, $intervals));
                }

                foreach ($this->input('exceptions', []) as $index => $exception) {
                    if (filter_var($exception['is_closed'], FILTER_VALIDATE_BOOLEAN)) {
                        continue;
                    }

                    foreach (['beg_hour', 'beg_minute', 'end_hour', 'end_minute'] as $key) {
                        if (!isset($exception[$key])) {
                            $validator->errors()->add(
                                "exceptions.$index.$key",
                                'Opening and closing times are required, unless it\'s closed.'
                            );

                            return;
                        }
                    }

                    $this->validateIntervals($validator, ["exceptions.$index" => $exception]);
                }
            },
        ];
    }

    /**
     * Check intervals of a day: opening and closing times differ, the intervals don't overlap.
     * A closing time earlier than the opening one is after midnight.
     *
     * @param Validator $validator
     * @param array<string, array> $intervals by their fields, e.g. `weekdays.monday.0`
     *
     * @return void
     */
    protected function validateIntervals(Validator $validator, array $intervals): void
    {
        $ranges = [];

        foreach ($intervals as $field => $interval) {
            $beg = $interval['beg_hour'] * 60 + $interval['beg_minute'];
            $end = $interval['end_hour'] * 60 + $interval['end_minute'];

            if ($beg === $end) {
                $validator->errors()->add("$field.end_hour", 'Closing time has to differ from the opening time.');

                continue;
            }

            $range = [$beg, $end < $beg ? $end + 24 * 60 : $end];

            foreach ($ranges as $other) {
                if ($range[0] < $other[1] && $other[0] < $range[1]) {
                    $validator->errors()->add("$field.beg_hour", 'Intervals of a day can\'t overlap.');

                    continue 2;
                }
            }

            $ranges[] = $range;
        }
    }

    /**
     * @OA\Schema(
     *   schema="EditorScheduleExceptionInput",
     *   description="Special day (or days). Hours are required, unless it's closed.",
     *   required={"starts_on", "is_closed"},
     *   @OA\Property(property="id", type="integer", nullable=true, example=1),
     *   @OA\Property(property="starts_on", type="string", format="date", example="2026-12-24"),
     *   @OA\Property(property="ends_on", type="string", format="date", nullable=true, example="2026-12-24"),
     *   @OA\Property(property="is_closed", type="boolean", example=false),
     *   @OA\Property(property="beg_hour", type="integer", nullable=true, example=10),
     *   @OA\Property(property="beg_minute", type="integer", nullable=true, example=0),
     *   @OA\Property(property="end_hour", type="integer", nullable=true, example=18),
     *   @OA\Property(property="end_minute", type="integer", nullable=true, example=0),
     *   @OA\Property(property="reason", ref="#/components/schemas/EditorTranslations"),
     * ),
     * @OA\Schema(
     *   schema="EditorUpdateRestaurantHoursRequest",
     *   description="Time zone, weekly hours, special days and the temporary closure. All of it is replaced.",
     *   required={"timezone", "weekdays", "exceptions"},
     *   @OA\Property(property="timezone", type="string", example="Europe/Kyiv"),
     *   @OA\Property(property="weekdays", ref="#/components/schemas/EditorWeekdays"),
     *   @OA\Property(property="exceptions", type="array",
     *     @OA\Items(ref="#/components/schemas/EditorScheduleExceptionInput")),
     *   @OA\Property(property="closed_until", type="string", format="date", nullable=true, example="2026-10-15"),
     *   @OA\Property(property="closed_reason", ref="#/components/schemas/EditorTranslations"),
     * ),
     */
}
