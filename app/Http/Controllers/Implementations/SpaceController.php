<?php

namespace App\Http\Controllers\Implementations;

use App\Constrainters\Constrainter;
use App\Http\Controllers\DynamicController;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Validator\Constraints as Assert;

class SpaceController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var string
     */
    protected $model = Space::class;

    /**
     * Get filtered and sorted collection of the model instances with intervals if beg and/or end datetime are specified.
     *
     * @param array|null $filters where conditions [[key, comparison, value]]
     * @param array|null $sorts orderBy conditions [key, order]
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Validation\ValidationException
     */
    public function all($filters = null, $sorts = null)
    {
        $collection = parent::all($filters, $sorts);
        if ($collection->count() === 0) {
            return $collection;
        }

        $begDatetime = $this->extractDatetime(request()->all(), 'beg_datetime');
        $endDatetime = $this->extractDatetime(request()->all(), 'end_datetime');

        foreach ($collection as $item) {
            $item->intervals = $this->loadIntervals($item, $begDatetime, $endDatetime);
        }
        return $collection;
    }

    /**
     * Get validated datetime from data.
     *
     * @param array $data
     * @param string $datetimeKey
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function extractDatetime($data, $datetimeKey)
    {
        $validator = Validator::make($data, [
            $datetimeKey => Constrainter::getRules(false, [new Assert\DateTime()]),
        ]);
        $queries = $validator->validated();
        return $queries[$datetimeKey] ?? null;
    }

    /**
     * Loads Space's business intervals for the specified period of time.
     *
     * @param Space $item
     * @return \Illuminate\Database\Eloquent\Collection|Collection
     */
    protected function loadIntervals($item, $begDatetime, $endDatetime)
    {
        // setting beginning to current datetime as default
        if (empty($begDatetime)) {
            $begDatetime = Carbon::now()->toDateTimeString();
        }

        if ($item instanceof Space) {
            $builder = $item->intervals();
            if (isset($begDatetime) && isset($endDatetime)) {
                // captures all intervals that intersect the specified interval
                $builder->where(function ($builder) use ($begDatetime, $endDatetime) {
                    $builder->where(function ($builder) use ($begDatetime, $endDatetime) {
                        // starts between/on beginning and end
                        $builder->where('beg_datetime', '>=', $begDatetime) // starts after/on beginning
                        ->where('beg_datetime', '<=', $endDatetime); // starts before/on ending
                    })->orWhere(function ($builder) use ($begDatetime, $endDatetime) {
                        // ends between/on beginning and end
                        $builder->where('end_datetime', '>=', $begDatetime) // ends after/on beginning
                        ->where('end_datetime', '<=', $endDatetime); // ends before/on ending
                    })->orWhere(function ($builder) use ($begDatetime, $endDatetime) {
                        // starts before/on beginning and ends after/on ending
                        $builder->where('beg_datetime', '<=', $begDatetime) // starts before/on beginning
                        ->where('end_datetime', '>=', $endDatetime); // ends after/on ending
                    });
                });
            } else if (isset($begDatetime)) {
                // captures all intervals starting from specified datetime
                $builder->where(function ($builder) use ($begDatetime) {
                    $builder->where(function ($builder) use ($begDatetime) {
                        $builder->where('end_datetime', '>=', $begDatetime) // ends after/on beginning
                        ->where('beg_datetime', '<=', $begDatetime); // begins before/on beginning
                    })->orWhere('beg_datetime', '>=', $begDatetime); // begins after/on beginning
                });
            }
            return $builder->get();
        }

        return new Collection();
    }

}
