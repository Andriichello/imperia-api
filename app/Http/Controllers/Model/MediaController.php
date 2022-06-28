<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Crud\DestroyRequest;
use App\Http\Requests\CrudRequest;
use App\Http\Requests\Media\GetModelMediaRequest;
use App\Http\Requests\Media\IndexMediaRequest;
use App\Http\Requests\Media\SetModelMediaRequest;
use App\Http\Requests\Media\ShowMediaRequest;
use App\Http\Requests\Media\StoreMediaRequest;
use App\Http\Requests\Media\UpdateMediaRequest;
use App\Http\Resources\Media\MediaCollection;
use App\Http\Resources\Media\MediaResource;
use App\Http\Responses\ApiResponse;
use App\Models\BaseModel;
use App\Models\Interfaces\MediableInterface;
use App\Policies\MediaPolicy;
use App\Queries\MediaQueryBuilder;
use App\Repositories\MediaRepository;
use Exception;
use Illuminate\Database\Eloquent\Relations\Relation;

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

        $this->actions['index'] = IndexMediaRequest::class;
        $this->actions['show'] = ShowMediaRequest::class;
        $this->actions['store'] = StoreMediaRequest::class;
        $this->actions['update'] = UpdateMediaRequest::class;
        $this->actions['destroy'] = DestroyRequest::class;
    }

    /**
     * Get eloquent query builder instance.
     *
     * @param CrudRequest $request
     *
     * @return MediaQueryBuilder
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function builder(CrudRequest $request): MediaQueryBuilder
    {
        /** @var MediaQueryBuilder $builder */
        $builder = parent::builder($request);

        return $builder;
    }

    /**
     * Find mediable model by id and type.
     *
     * @param int $modelId
     * @param string $modelType
     *
     * @return MediableInterface
     * @throws Exception
     */
    protected function findMediable(int $modelId, string $modelType): MediableInterface
    {
        /** @var BaseModel|null $model */
        $model = Relation::getMorphedModel($modelType);

        if (empty($model)) {
            throw new Exception(
                'There is no such class with morph: ' . $modelType
            );
        }

        /** @var MediableInterface $target */
        $target = $model::query()->findOrFail($modelId);

        if (!($target instanceof MediableInterface)) {
            throw new Exception(
                'Target model must implement MediableInterface.'
            );
        }

        return $target;
    }

    /**
     * Get model's media.
     *
     * @param GetModelMediaRequest $request
     *
     * @return ApiResponse
     * @throws Exception
     */
    public function getModelMedia(GetModelMediaRequest $request): ApiResponse
    {
        $modelId = $request->get('model_id');
        $modelType = $request->get('model_type');

        $target = $this->findMediable($modelId, $modelType);

        $data = new MediaCollection($target->media()->get());
        return ApiResponse::make(compact('data'));
    }

    /**
     * Set model's media.
     *
     * @param SetModelMediaRequest $request
     *
     * @return ApiResponse
     * @throws Exception
     */
    public function setModelMedia(SetModelMediaRequest $request): ApiResponse
    {
        $modelId = $request->get('model_id');
        $modelType = $request->get('model_type');

        $target = $this->findMediable($modelId, $modelType);

        $attached = $target->media()->pluck('id')->all();
        $given = $request->ids();

        $add = array_diff($given, $attached);
        $target->media()->attach($add);

        $remove = array_diff($attached, $given);
        $target->media()->detach($remove);

        $data = new MediaCollection($target->media()->get());
        return ApiResponse::make(compact('data'));
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
     *   @OA\Parameter(name="filter[name]", required=false, in="query", example="Cafe",
     *     @OA\Schema(type="string"), description="Can be used for searches. Is partial."),
     *   @OA\Parameter(name="filter[extension]", required=false, in="query", example="svg",
     *     @OA\Schema(type="string"), description="Can be used for searches. Is partial."),
     *   @OA\Parameter(name="filter[disk]", required=false, in="query", example="public",
     *     @OA\Schema(type="string"), description="Can be used for searches. Is partial."),
     *   @OA\Parameter(name="filter[folder]", required=false, in="query", example="/categories/",
     *     @OA\Schema(type="string"), description="Can be used for searches. Is partial."),
     *
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
     * ),
     * @OA\Get(
     *   path="/api/model-media",
     *   summary="Get model's media.",
     *   operationId="getModelMedia",
     *   security={{"bearerAuth": {}}},
     *   tags={"media"},
     *
     *   @OA\Parameter(name="model_id", required=true, in="query", example="1",
     *     @OA\Schema(type="integer"), description="Id of the target model."),
     *   @OA\Parameter(name="model_type", required=true, in="query", example="products",
     *     @OA\Schema(type="string"), description="Morph type of the target model."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Get model's media response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/GetModelMediaResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Post(
     *   path="/api/model-media",
     *   summary="Set model's media.",
     *   operationId="setModelMedia",
     *   security={{"bearerAuth": {}}},
     *   tags={"media"},
     *
     *  @OA\RequestBody(
     *     required=false,
     *     description="Set model's media request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/SetModelMediaRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Create media response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/SetModelMediaResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
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
     * ),
     * @OA\Schema(
     *   schema="GetModelMediaResponse",
     *   description="Get model's media response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Media")),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="SetModelMediaResponse",
     *   description="Set model's media response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Media")),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     */
}
