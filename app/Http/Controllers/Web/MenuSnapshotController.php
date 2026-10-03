<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\ShowMenuSnapshotRequest;
use App\Models\MenuSnapshot;
use App\Repositories\MenuSnapshotRepository;
use Illuminate\Http\Response;
use OpenApi\Annotations as OA;

/**
 * Class MenuSnapshotController.
 *
 * The dishes guests see on a restaurant's pages, all of them at once, from the restaurant's current
 * snapshot (a gzipped JSON file, see `MenuSnapshotRepository`). After a change of the pages, the
 * first request builds the next snapshot.
 */
class MenuSnapshotController extends Controller
{
    /**
     * MenuSnapshotController constructor.
     *
     * @param MenuSnapshotRepository $snapshots
     */
    public function __construct(protected MenuSnapshotRepository $snapshots)
    {
    }

    /**
     * The dishes of the restaurant's pages in the language, gzipped. A request with the hash of the
     * current snapshot is cached for long (its address changes with the content); the others are
     * checked again every time (by their ETag).
     *
     * @param ShowMenuSnapshotRequest $request
     *
     * @return Response
     */
    public function show(ShowMenuSnapshotRequest $request): Response
    {
        $restaurant = $request->restaurant();
        $locale = $request->validated('locale');

        $snapshot = $this->snapshots->current($restaurant, $locale);
        $gzip = null;

        if (!$snapshot) {
            [$snapshot, $gzip] = $this->snapshots->build($restaurant, $locale);
        }

        $headers = ['Content-Type' => 'application/json', 'Vary' => 'Accept-Encoding', 'Cache-Control' => 'no-cache'];

        if ($snapshot) {
            $headers['ETag'] = "\"$snapshot->hash\"";

            if ($request->validated('hash') === $snapshot->hash) {
                $headers['Cache-Control'] = MenuSnapshotRepository::CACHE_CONTROL;
            }

            if (in_array($headers['ETag'], $request->getETags(), true)) {
                return response('', 304, $headers);
            }
        }

        $gzip ??= $this->gzipOf($snapshot, $request);

        // clients, which don't take gzip, get it unzipped
        if (!str_contains((string) $request->header('Accept-Encoding'), 'gzip')) {
            return response((string) gzdecode($gzip), 200, $headers);
        }

        return response($gzip, 200, [...$headers, 'Content-Encoding' => 'gzip']);
    }

    /**
     * The snapshot's gzipped JSON from its file, or built again, when the file can't be read.
     *
     * @param MenuSnapshot $snapshot
     * @param ShowMenuSnapshotRequest $request
     *
     * @return string
     */
    protected function gzipOf(MenuSnapshot $snapshot, ShowMenuSnapshotRequest $request): string
    {
        return $this->snapshots->read($snapshot)
            ?? $this->snapshots->render($request->restaurant(), $snapshot->locale)['gzip'];
    }

    /**
     * @OA\Get(
     *   path="/api/restaurants/{id}/dishes",
     *   summary="All dishes guests see on the restaurant's pages, in the language (gzipped JSON).",
     *   operationId="getRestaurantDishes",
     *   tags={"dish-dishes"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="string"),
     *     description="Id or slug of the restaurant."),
     *   @OA\Parameter(name="locale", required=true, in="query", example="en", @OA\Schema(type="string")),
     *   @OA\Parameter(name="hash", required=false, in="query", @OA\Schema(type="string"),
     *     description="Of the snapshot the page knows: when it's the current one, the answer is cached for long."),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(required={"data"},
     *       @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Dish")))
     *   ),
     *   @OA\Response(response=304, description="Not modified (the ETag matches)."),
     *   @OA\Response(response=404, description="The restaurant doesn't exist."),
     *   @OA\Response(response=422, description="Invalid values.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * )
     */
}
