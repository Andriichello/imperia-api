<?php

namespace App\Http\Controllers\Model;

use App\Helpers\MediaHelper;
use App\Http\Controllers\CrudController;
use App\Http\Requests\Crud\DestroyRequest;
use App\Http\Requests\Crud\IndexRequest;
use App\Http\Requests\Crud\ShowRequest;
use App\Http\Requests\Media\StoreMediaRequest;
use App\Http\Requests\Media\UpdateMediaRequest;
use App\Http\Resources\Media\MediaCollection;
use App\Http\Resources\Media\MediaResource;
use App\Http\Responses\ApiResponse;
use App\Models\Morphs\Media;
use App\Policies\MediaPolicy;
use App\Repositories\MediaRepository;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Throwable;

/**
 * Class MediaController.
 */
class MediaController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = MediaResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = MediaCollection::class;

    /**
     * BanquetController constructor.
     *
     * @param MediaRepository $repository
     * @param MediaPolicy $policy
     */
    public function __construct(MediaRepository $repository, MediaPolicy $policy)
    {
        parent::__construct($repository, $policy);

        $this->actions['index'] = IndexRequest::class;
        $this->actions['show'] = ShowRequest::class;
        $this->actions['destroy'] = DestroyRequest::class;
    }

    /**
     * Store file and record it as media.
     *
     * @param StoreMediaRequest $request
     *
     * @return ApiResponse
     * @throws FileNotFoundException
     */
    public function store(StoreMediaRequest $request): ApiResponse
    {
        $disk = $request->get('disk');

        $from = $request->file('file');
        $to = $request->get('folder') . $request->get('name');

        $helper = new MediaHelper();
        $media = $helper->store($from, $to, $disk);

        $media->title = $request->get('title');
        $media->description = $request->get('description');

        $metadata = $request->get('metadata');
        if ($metadata) {
            $media->setJson('metadata', (array)$metadata);
        }

        return $this->asResourceResponse($media);
    }

    /**
     * Update file and/or it's media record.
     *
     * @param UpdateMediaRequest $request
     *
     * @return ApiResponse
     * @throws FileNotFoundException
     */
    public function update(UpdateMediaRequest $request): ApiResponse
    {
        /** @var Media $media */
        $media = $request->targetOrFail(Media::class);

        $disk = $request->get('disk', $media->disk);

        $from = $request->file('file');
        $to = $request->get('folder', $media->folder) . $request->get('name', $media->name);

        $helper = new MediaHelper();
        $media = $helper->update($media, $from, $to, $disk);

        if ($request->has('title')) {
            $media->title = $request->get('title');
        }
        if ($request->has('description')) {
            $media->description = $request->get('description');
        }

        if ($request->has('metadata')) {
            $media->setJson('metadata', (array)$request->get('metadata'));
        }

        return $this->asResourceResponse($media->fresh());
    }

    /**
     * Delete file and it's media record.
     *
     * @param DestroyRequest $request
     *
     * @return ApiResponse
     * @throws FileNotFoundException|Throwable
     */
    public function destroy(DestroyRequest $request): ApiResponse
    {
        /** @var Media $media */
        $media = $request->targetOrFail(Media::class);

        $helper = new MediaHelper();
        $helper->delete($media, $media->disk);

        return $this->handleDestroy($request);
    }

    /**
     * @OA\Get(
     *   path="/api/media",
     *   summary="Index media.",
     *   operationId="indexMedia",
     *   security={{"bearerAuth": {}}},
     *   tags={"media"},
     *
     *   @OA\Parameter(name="page[size]", in="query", @OA\Schema(ref ="#/components/schemas/PageSize")),
     *   @OA\Parameter(name="page[number]", in="query", @OA\Schema(ref ="#/components/schemas/PageNumber")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index media response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexMediaResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/media/{id}",
     *   summary="Show media by id.",
     *   operationId="showMedia",
     *   security={{"bearerAuth": {}}},
     *   tags={"media"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the media."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show media response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowMediaResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Post(
     *   path="/api/media",
     *   summary="Store media.",
     *   operationId="storeMedia",
     *   security={{"bearerAuth": {}}},
     *   tags={"media"},
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Store media request object.",
     *     @OA\MediaType(mediaType="multipart/form-data",
     *        @OA\Schema(
     *           allOf={
     *              @OA\Schema(ref ="#/components/schemas/StoreMediaRequest"),
     *              @OA\Schema(@OA\Property(property="file", type="string",
     *                    format="binary", description="File"))
     *           })),
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Create media response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreMediaResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Patch(
     *   path="/api/media/{id}",
     *   summary="Update media.",
     *   operationId="updateMedia",
     *   security={{"bearerAuth": {}}},
     *   tags={"media"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the media."),
     *  @OA\RequestBody(
     *     required=true,
     *     description="Update media request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateMediaRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Update media response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateMediaResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Delete(
     *   path="/api/media/{id}",
     *   summary="Delete media.",
     *   operationId="destroyMedia",
     *   security={{"bearerAuth": {}}},
     *   tags={"media"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the media."),
     *
     *  @OA\RequestBody(
     *     required=false,
     *     description="Delete request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DestroyRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Delete media response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DestroyMediaResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * )
     *
     * @OA\Schema(
     *   schema="IndexMediaResponse",
     *   description="Index media response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Media")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowMediaResponse",
     *   description="Show media response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Media"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="StoreMediaResponse",
     *   description="Store media response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Media"),
     *   @OA\Property(property="message", type="string", example="Created"),
     * ),
     * @OA\Schema(
     *   schema="UpdateMediaResponse",
     *   description="Update media response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Media"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="DestroyMediaResponse",
     *   description="Delete banquet response object.",
     *   required = {"message"},
     *   @OA\Property(property="message", type="string", example="Deleted"),
     * )
     */
}
