<?php

namespace App\Http\Requests\RestaurantReview;

use App\Http\Requests\Crud\IndexRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder as SpatieBuilder;

/**
 * Class IndexRestaurantReviewRequest.
 */
class IndexRestaurantReviewRequest extends IndexRequest
{
    /**
     * Get sorts fields for spatie query builder.
     *
     * @return array
     */
    public function getAllowedSorts(): array
    {
        return array_merge(
            parent::getAllowedSorts(),
            [
                AllowedSort::field('created_at'),
            ]
        );
    }

    public function getAllowedFilters(): array
    {
        return array_merge(
            parent::getAllowedFilters(),
            [
                AllowedFilter::exact('restaurant_id'),
                AllowedFilter::exact('ip'),
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
                'is_approved' => [
                    'sometimes',
                ],
            ]
        );
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $filter = $this->get('filter', []);

        if (!isset($filter['ip'])) {
            $this->merge(['is_approved' => true]);
        }
    }

    public function withValidator(Validator $validator)
    {
        if ($this->missing('is_approved')) {
            return;
        }

        $validator->after(function (Validator $validator) {
            if ($this->user() && $this->user()->isStaff()) {
                return;
            }

            if (!$this->boolean('is_approved')) {
                $validator->errors()
                    ->add(
                        'is_approved',
                        'You don\'t have permission to see'
                        . ' reviews that haven\'t been approved'
                    );
            }
        });
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
        $builder = parent::spatieBuilder($builder)
            ->defaultSort('-created_at');

        if ($this->has('is_approved')) {
            $builder->where('is_approved', $this->boolean('is_approved'));
        }

        $builder->where('is_rejected', false);

        return $builder;
    }
}
