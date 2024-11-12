<?php

namespace App\Http\Requests\Restaurant;

use App\Http\Requests\Crud\ShowRequest;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Spatie\QueryBuilder\QueryBuilder as SpatieBuilder;

/**
 * Class ShowRestaurantRequest.
 */
class ShowRestaurantRequest extends ShowRequest
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
        return array_merge(
            parent::rules(),
            [
                //
            ]
        );
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
}
