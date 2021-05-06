<?php

namespace App\Custom;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as BaseCollection;

class Collection extends BaseCollection
{
    /**
     * Paginate a standard Laravel Collection.
     *
     * @param int $perPage
     * @param int $total
     * @param int $page
     * @param string $pageName
     * @return LengthAwarePaginator
     */
    public function paginate($perPage = null, $page = null, $pageName = 'page[number]')
    {
        $options = request()->query('page');
        if (isset($options)) {
            if (isset($options['number'])) {
                $page = $options['number'];
            }
            if (isset($options['size'])) {
                $perPage = $options['size'];
            }
        }

        $perPage = $perPage ?: $this->count();
        $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

        $paginator = new LengthAwarePaginator(
            $this->forPage($page, $perPage),
            $this->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]
        );
        $paginator->appends('page[size]', $perPage);
        return $paginator;
    }
}
