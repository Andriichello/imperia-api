<?php

namespace App\Http\Requests\Banquet;

use App\Http\Filters\BanquetsSearchFilter;
use App\Http\Requests\Crud\IndexRequest;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder as SpatieBuilder;

/**
 * Class IndexBanquetRequest.
 */
class IndexBanquetRequest extends IndexRequest
{
    public function getAllowedFilters(): array
    {
        return array_merge(
            parent::getAllowedFilters(),
            [
                AllowedFilter::custom('search', new BanquetsSearchFilter()),
                AllowedFilter::callback('from', fn() => null),
                AllowedFilter::callback('until', fn() => null),
                AllowedFilter::exact('restaurant_id'),
                AllowedFilter::exact('state'),

            ]
        );
    }

    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                'creator',
                'customer',
                'comments',
                'discounts',
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
        $builder = parent::spatieBuilder($builder);

        $filters = $this->get('filter', []);

        $from = data_get($filters, 'from');
        $until = data_get($filters, 'until');

        if ($from && !$until) {
            $builder->where('start_at', '>=', Carbon::make($from)->setTime(0, 0));
        }

        if (!$from && $until) {
            $builder->where('end_at', '<=', Carbon::make($until)->setTime(23, 59));
        }

        if ($from && $until) {
            $builder->where(function (EloquentBuilder $q) use ($from, $until) {
                $q->where('start_at', '>=', Carbon::make($from)->setTime(0, 0))
                    ->where('end_at', '<=', Carbon::make($until)->setTime(23, 59));
            });
        }

        return $builder;
    }
}
