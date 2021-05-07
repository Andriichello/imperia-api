<?php

namespace App\Http\Resources;

use App\Custom\Collection;
use Illuminate\Http\Resources\Json\ResourceCollection as BaseResourceCollection;

class ResourceCollection extends BaseResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $items = new Collection($this->collection);
        $paginator = $items->paginate();

        return [
            'pagination' => [
                'size' => (int)$paginator->perPage(),
                'pages' => (int)$paginator->lastPage(),
                'items' => (int)$paginator->total(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'prev' => $paginator->previousPageUrl(),
                'self' => $paginator->url($paginator->currentPage()),
                'next' => $paginator->nextPageUrl(),
                'last' => $paginator->url($paginator->lastPage()),
            ],
            'data' => array_values($paginator->all()),
        ];
    }
}
