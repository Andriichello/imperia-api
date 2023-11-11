<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\Controller;
use App\Http\Requests\ModelMedia\GetModelMediaRequest;
use App\Http\Requests\ModelMedia\SetModelMediaRequest;
use App\Http\Resources\Media\MediaCollection;
use App\Http\Responses\ApiResponse;
use App\Models\BaseModel;
use App\Models\Interfaces\MediableInterface;
use Exception;
use Illuminate\Database\Eloquent\Relations\Relation;
use OpenApi\Annotations as OA;

/**
 * Class ModelMediaController.
 */
class ModelMediaController extends Controller
{
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

        $target = $this->findMediable($modelId, $modelType)
            ->setMedia(...$request->ids());

        $data = new MediaCollection($target->media()->get());
        return ApiResponse::make(compact('data'));
    }

    /**
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
