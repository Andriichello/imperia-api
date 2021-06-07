<?php

namespace App\Http\Controllers\Implementations;

use App\Http\Actions\FilterSpaceIntervals;
use App\Http\Controllers\DynamicController;
use App\Http\Requests\Implementations\SpaceRequest;
use App\Models\Space;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use PHPUnit\Util\Filter;

class SpaceController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var ?string
     */
    protected ?string $model = Space::class;

    protected FilterSpaceIntervals $filterIntervals;

    protected array $additionalFilters = ['banquet_id', 'beg_datetime', 'end_datetime'];

    public function __construct(SpaceRequest $request, FilterSpaceIntervals $filterIntervals)
    {
        parent::__construct($request);
        $this->filterIntervals = $filterIntervals;
    }

    protected function applyAdditionalFilters(Builder|Collection $data, array $filters): Collection|Builder
    {
        if ($data instanceof Builder) {
            return $data;
        }

        foreach ($data as $instance) {
            $this->filterIntervals->execute($instance, $filters);
        }

        // appending banquet filters to applied
        $this->appliedFilters = array_merge(
            $this->appliedFilters,
            $this->filterIntervals->getAppliedFilters()
        );

        return $data;
    }
}
