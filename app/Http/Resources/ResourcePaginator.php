<?php

namespace App\Http\Resources;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator as PaginatorInterface;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class ResourcePaginator.
 */
class ResourcePaginator extends Paginator
{
    /**
     * JsonResource classname.
     *
     * @var string
     */
    protected string $collectionClass;

    /**
     * Paginator given in the constructor.
     *
     * @var PaginatorInterface
     */
    protected PaginatorInterface $paginator;

    /**
     * @param Paginator|LengthAwarePaginator $paginator
     * @param string $collectionClass
     *
     * @throws Exception
     */
    public function __construct(Paginator|LengthAwarePaginator $paginator, string $collectionClass)
    {
        parent::__construct($paginator->items, $paginator->perPage, $paginator->currentPage, $paginator->options);

        $this->paginator = $paginator;
        $this->collectionClass = $collectionClass;

        if (!is_subclass_of($collectionClass, ResourceCollection::class)) {
            throw new Exception('Invalid collectionClass. It must be an instance of ResourceCollection.');
        }
    }

    /**
     * Get paginator that was passed to the constructor.
     *
     * @return PaginatorInterface
     */
    public function getPaginator(): PaginatorInterface
    {
        return $this->paginator;
    }

    /**
     * Get number of records in the database.
     *
     * @return int|null
     */
    public function total(): ?int
    {
        return $this->paginator instanceof LengthAwarePaginator ? $this->paginator->total() : null;
    }

    /**
     * Get last page number.
     *
     * @return int|null
     */
    public function lastPage(): ?int
    {
        return $this->paginator instanceof LengthAwarePaginator ? $this->paginator->lastPage() : null;
    }

    /**
     * Get collection of items.
     *
     * @return Collection
     */
    public function getCollection(): Collection
    {
        return parent::getCollection();
    }

    /**
     * Get resource collection of items.
     *
     * @return ResourceCollection
     */
    public function getResourceCollection(): ResourceCollection
    {
        return new $this->collectionClass($this->items);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = $this->getResourceCollection();
        $meta = [
            'from' => $this->firstItem(),
            'to' => $this->lastItem(),
            'path' => $this->path(),
            'per_page' => $this->perPage(),
            'current_page' => $this->currentPage(),
            'last_page' => $this->lastPage(),
            'total' => $this->total(),
            'links' => [
                'first' => $this->url(1),
                'prev' => $this->previousPageUrl(),
                'self' => $this->url($this->currentPage()),
                'next' => $this->nextPageUrl(),
                'last' => $this->url($this->lastPage()),
            ],
        ];

        return compact('data', 'meta');
    }

    /**
     * Get pagination size parameter name.
     *
     * @return string
     */
    public function pageSizeParam(): string
    {
        return sprintf(
            '%s[%s]',
            config('pagination.pagination_parameter'),
            config('pagination.size_parameter'),
        );
    }

    /**
     * Get pagination number parameter name.
     *
     * @return string
     */
    public function pageNumberParam(): string
    {
        return sprintf(
            '%s[%s]',
            config('pagination.pagination_parameter'),
            config('pagination.number_parameter'),
        );
    }

    /**
     * Get the URL for a given page number.
     *
     * @param int $page
     *
     * @return string
     */
    public function url($page): string
    {
        $params = request()->query->all();
        $params[$this->pageNumberParam()] = $page;
        $params[$this->pageSizeParam()] = $this->perPage;

        $path = Str::of($this->path())
            ->rtrim('/');

        return $path . '?' . http_build_query($params);
    }

    /**
     * Determine if there are more items in the data source.
     *
     * @return bool
     */
    public function hasMorePages(): bool
    {
        $total = $this->total() ?? 0;
        $limit = $this->currentPage * $this->perPage;
        return ($total - $limit) > 0;
    }

    /**
     * @OA\Schema(
     *   schema="Links",
     *   description="Pagination meta links object.",
     *   required = {"first", "prev", "self", "next", "last"},
     *   @OA\Property(property="first", type="string"),
     *   @OA\Property(property="prev", type="string", nullable="true", example=null),
     *   @OA\Property(property="self", type="string"),
     *   @OA\Property(property="next", type="string", nullable="true", example=null),
     *   @OA\Property(property="last", type="string", nullable="true", example=null),
     * ),
     * @OA\Schema(
     *   schema="PaginationMeta",
     *   description="Pagination meta object.",
     *   required = {"from", "to", "total", "path", "per_page", "current_page", "last_page", "links"},
     *   @OA\Property(property="from", type="integer", nullable="true", example=1),
     *   @OA\Property(property="to", type="integer", nullable="true", example=1),
     *   @OA\Property(property="total", type="integer", example=30),
     *   @OA\Property(property="path", type="integer", nullable="true", example=null),
     *   @OA\Property(property="per_page", type="integer", example=1),
     *   @OA\Property(property="current_page", type="integer", example=1),
     *   @OA\Property(property="last_page", type="integer", example=2),
     *   @OA\Property(property="links", ref ="#/components/schemas/Links"),
     * )
     */
}
