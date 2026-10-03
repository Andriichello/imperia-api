<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Editor\IndexRestaurantsRequest;
use App\Http\Requests\Editor\ShowRestaurantRequest;
use App\Http\Requests\Editor\UpdateRestaurantHoursRequest;
use App\Http\Requests\Editor\UpdateRestaurantNotesRequest;
use App\Http\Requests\Editor\UpdateRestaurantPhotosRequest;
use App\Http\Requests\Editor\UpdateRestaurantRequest;
use App\Http\Requests\Editor\UploadRestaurantPhotoRequest;
use App\Http\Resources\Editor\EditorDashboardResource;
use App\Http\Resources\Editor\EditorRestaurantResource;
use App\Http\Resources\Media\MediaResource;
use App\Http\Responses\ApiResponse;
use App\Models\Restaurant;
use App\Repositories\Editor\RestaurantEditorRepository;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use OpenApi\Annotations as OA;

/**
 * Class RestaurantEditorController.
 *
 * The admin editor's restaurant: everything of it in all languages (hidden and archived
 * menus, categories and dishes included), and its details, notes, photos and hours.
 * Each change responds with everything of the restaurant again.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RestaurantEditorController extends Controller
{
    /**
     * RestaurantEditorController constructor.
     *
     * @param RestaurantEditorRepository $repository
     */
    public function __construct(protected RestaurantEditorRepository $repository)
    {
    }

    /**
     * Restaurants the user can edit.
     *
     * @param IndexRestaurantsRequest $request
     *
     * @return ApiResponse
     */
    public function index(IndexRestaurantsRequest $request): ApiResponse
    {
        $data = $this->repository->editableBy($request->user());

        return ApiResponse::make(compact('data'));
    }

    /**
     * Everything of the restaurant.
     *
     * @param ShowRestaurantRequest $request
     *
     * @return ApiResponse
     */
    public function show(ShowRestaurantRequest $request): ApiResponse
    {
        return $this->respond($request->restaurant());
    }

    /**
     * What the admin's dashboard shows of the restaurant.
     *
     * @param ShowRestaurantRequest $request
     *
     * @return ApiResponse
     */
    public function dashboard(ShowRestaurantRequest $request): ApiResponse
    {
        $data = new EditorDashboardResource($this->repository->dashboard($request->restaurant()));

        return ApiResponse::make(compact('data'));
    }

    /**
     * Update details of the restaurant (name, type, contacts) and its brand colors.
     *
     * @param UpdateRestaurantRequest $request
     *
     * @return ApiResponse
     */
    public function update(UpdateRestaurantRequest $request): ApiResponse
    {
        $this->repository->update($request->restaurant(), $request->validated());

        return $this->respond($request->restaurant());
    }

    /**
     * Replace notes of the restaurant.
     *
     * @param UpdateRestaurantNotesRequest $request
     *
     * @return ApiResponse
     */
    public function updateNotes(UpdateRestaurantNotesRequest $request): ApiResponse
    {
        $this->repository->replaceNotes($request->restaurant(), $request->validated('notes'));

        return $this->respond($request->restaurant());
    }

    /**
     * Set photos of the restaurant.
     *
     * @param UpdateRestaurantPhotosRequest $request
     *
     * @return ApiResponse
     */
    public function updatePhotos(UpdateRestaurantPhotosRequest $request): ApiResponse
    {
        $this->repository->setPhotos($request->restaurant(), $request->validated('media'));

        return $this->respond($request->restaurant());
    }

    /**
     * Upload a photo of the restaurant (of itself or of a dish), to be set as one of the photos.
     *
     * @param UploadRestaurantPhotoRequest $request
     *
     * @return ApiResponse
     * @throws FileNotFoundException
     */
    public function uploadPhoto(UploadRestaurantPhotoRequest $request): ApiResponse
    {
        $media = $this->repository->uploadPhoto($request->restaurant(), $request->file('file'));

        return ApiResponse::make(['data' => new MediaResource($media)], 201, 'Created');
    }

    /**
     * Replace time zone, weekly hours, special days and the temporary closure of the restaurant.
     *
     * @param UpdateRestaurantHoursRequest $request
     *
     * @return ApiResponse
     */
    public function updateHours(UpdateRestaurantHoursRequest $request): ApiResponse
    {
        $this->repository->updateHours($request->restaurant(), $request->validated());

        return $this->respond($request->restaurant());
    }

    /**
     * Everything of the restaurant, freshly loaded.
     *
     * @param Restaurant $restaurant
     *
     * @return ApiResponse
     */
    protected function respond(Restaurant $restaurant): ApiResponse
    {
        /** @var Restaurant $restaurant */
        $restaurant = $restaurant->fresh();

        $data = new EditorRestaurantResource($this->repository->load($restaurant));

        return ApiResponse::make(compact('data'));
    }

    /**
     * @OA\Get(
     *   path="/api/editor/restaurants",
     *   summary="Restaurants the user can edit.",
     *   operationId="getEditorRestaurants",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorRestaurantsResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     * ),
     * @OA\Get(
     *   path="/api/editor/restaurants/{id}",
     *   summary="Everything of the restaurant, in all languages.",
     *   operationId="getEditorRestaurant",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the restaurant."),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorRestaurantResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     * ),
     * @OA\Get(
     *   path="/api/editor/restaurants/{id}/dashboard",
     *   summary="What the admin's dashboard shows of the restaurant.",
     *   operationId="getEditorDashboard",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the restaurant."),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorDashboardResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     * ),
     * @OA\Patch(
     *   path="/api/editor/restaurants/{id}",
     *   summary="Update details and brand colors of the restaurant.",
     *   operationId="updateEditorRestaurant",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the restaurant."),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/EditorUpdateRestaurantRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorRestaurantResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     *   @OA\Response(response=422, description="Invalid values.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * ),
     * @OA\Put(
     *   path="/api/editor/restaurants/{id}/notes",
     *   summary="Replace notes of the restaurant.",
     *   operationId="updateEditorRestaurantNotes",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the restaurant."),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/EditorUpdateRestaurantNotesRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorRestaurantResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     *   @OA\Response(response=422, description="Invalid values.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * ),
     * @OA\Put(
     *   path="/api/editor/restaurants/{id}/photos",
     *   summary="Set photos of the restaurant.",
     *   operationId="updateEditorRestaurantPhotos",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the restaurant."),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/EditorUpdateRestaurantPhotosRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorRestaurantResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     *   @OA\Response(response=422, description="Invalid values.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * ),
     * @OA\Post(
     *   path="/api/editor/restaurants/{id}/media",
     *   summary="Upload a photo of the restaurant, to be set as one of its or its dishes' photos.",
     *   operationId="uploadEditorRestaurantPhoto",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the restaurant."),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(mediaType="multipart/form-data",
     *       @OA\Schema(ref="#/components/schemas/EditorUploadPhotoRequest"))
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Created.",
     *     @OA\JsonContent(ref="#/components/schemas/StoreMediaResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     *   @OA\Response(response=422, description="Invalid values.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * ),
     * @OA\Put(
     *   path="/api/editor/restaurants/{id}/hours",
     *   summary="Replace hours, special days and closure of the restaurant.",
     *   operationId="updateEditorRestaurantHours",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the restaurant."),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/EditorUpdateRestaurantHoursRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorRestaurantResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     *   @OA\Response(response=422, description="Invalid values.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * ),
     * @OA\Schema(
     *   schema="EditorRestaurantsResponse",
     *   description="Restaurants the user can edit.",
     *   required={"data", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/EditorRestaurantItem")),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="EditorDashboardResponse",
     *   description="What the dashboard shows of the restaurant.",
     *   required={"data", "message"},
     *   @OA\Property(property="data", ref="#/components/schemas/EditorDashboard"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="EditorRestaurantResponse",
     *   description="Everything of the restaurant.",
     *   required={"data", "message"},
     *   @OA\Property(property="data", ref="#/components/schemas/EditorRestaurant"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     */
}
