<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Editor\IndexVersionsRequest;
use App\Http\Requests\Editor\PutVersionChangeRequest;
use App\Http\Requests\Editor\StoreVersionRequest;
use App\Http\Requests\Editor\UpdateVersionRequest;
use App\Http\Requests\Editor\VersionActionRequest;
use App\Http\Resources\Editor\EditorVersionResource;
use App\Http\Responses\ApiResponse;
use App\Models\MenuVersion;
use App\Models\MenuVersionChange;
use App\Repositories\Editor\VersionEditorRepository;
use OpenApi\Annotations as OA;

/**
 * Class VersionEditorController.
 *
 * Scheduled versions of a restaurant's page: changes, which go live together at one date and
 * time. Each change of a version responds with the whole version again (its counts change too).
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VersionEditorController extends Controller
{
    /**
     * VersionEditorController constructor.
     *
     * @param VersionEditorRepository $repository
     */
    public function __construct(protected VersionEditorRepository $repository)
    {
    }

    /**
     * Versions of the restaurant: pending ones by their date, then the ones, which went live.
     *
     * @param IndexVersionsRequest $request
     *
     * @return ApiResponse
     */
    public function index(IndexVersionsRequest $request): ApiResponse
    {
        $versions = $this->repository->ofRestaurant($request->restaurant(), $request->boolean('pending'));
        $data = EditorVersionResource::collection($versions);

        return ApiResponse::make(compact('data'));
    }

    /**
     * Create a version of the restaurant: a draft, or scheduled right away, with its first changes.
     *
     * @param StoreVersionRequest $request
     *
     * @return ApiResponse
     */
    public function store(StoreVersionRequest $request): ApiResponse
    {
        $version = $this->repository->create($request->restaurant(), $request->validated(), $request->user());

        return $this->respond($version, 201, 'Created');
    }

    /**
     * The version with its changes, their items and conflicts.
     *
     * @param VersionActionRequest $request
     *
     * @return ApiResponse
     */
    public function show(VersionActionRequest $request): ApiResponse
    {
        return $this->respond($this->version($request));
    }

    /**
     * Rename or reschedule the version.
     *
     * @param UpdateVersionRequest $request
     *
     * @return ApiResponse
     */
    public function update(UpdateVersionRequest $request): ApiResponse
    {
        return $this->respond($this->repository->update($this->version($request), $request->validated()));
    }

    /**
     * Put a change of an item into the version.
     *
     * @param PutVersionChangeRequest $request
     *
     * @return ApiResponse
     */
    public function putChange(PutVersionChangeRequest $request): ApiResponse
    {
        $version = $this->version($request);
        $this->repository->putChange($version, $request->validated());

        return $this->respond($version);
    }

    /**
     * Remove a change from the version.
     *
     * @param VersionActionRequest $request
     *
     * @return ApiResponse
     */
    public function removeChange(VersionActionRequest $request): ApiResponse
    {
        $version = $this->version($request);
        /** @var MenuVersionChange $change */
        $change = $version->itemChanges()->findOrFail((int) $request->route('change'));

        $this->repository->removeChange($version, $change);

        return $this->respond($version);
    }

    /**
     * Schedule the version at its date.
     *
     * @param VersionActionRequest $request
     *
     * @return ApiResponse
     */
    public function schedule(VersionActionRequest $request): ApiResponse
    {
        return $this->respond($this->repository->schedule($this->version($request)));
    }

    /**
     * Deactivate the scheduled version.
     *
     * @param VersionActionRequest $request
     *
     * @return ApiResponse
     */
    public function deactivate(VersionActionRequest $request): ApiResponse
    {
        return $this->respond($this->repository->deactivate($this->version($request)));
    }

    /**
     * Activate the inactive version.
     *
     * @param VersionActionRequest $request
     *
     * @return ApiResponse
     */
    public function activate(VersionActionRequest $request): ApiResponse
    {
        return $this->respond($this->repository->activate($this->version($request)));
    }

    /**
     * Apply the version now. When it can't be applied, it's failed with the reason.
     *
     * @param VersionActionRequest $request
     *
     * @return ApiResponse
     */
    public function apply(VersionActionRequest $request): ApiResponse
    {
        $version = $this->version($request);
        $applied = $this->repository->apply($version);

        return $this->respond($version, $applied ? 200 : 422, $applied ? 'Applied' : 'Failed');
    }

    /**
     * Copy the version as a draft.
     *
     * @param VersionActionRequest $request
     *
     * @return ApiResponse
     */
    public function duplicate(VersionActionRequest $request): ApiResponse
    {
        $copy = $this->repository->duplicate($this->version($request), $request->user());

        return $this->respond($copy, 201, 'Created');
    }

    /**
     * Delete the version.
     *
     * @param VersionActionRequest $request
     *
     * @return ApiResponse
     */
    public function destroy(VersionActionRequest $request): ApiResponse
    {
        $this->repository->delete($this->version($request));

        return ApiResponse::make([], 200, 'Deleted');
    }

    /**
     * The version of the request.
     *
     * @param VersionActionRequest|UpdateVersionRequest|PutVersionChangeRequest $request
     *
     * @return MenuVersion
     */
    protected function version(
        VersionActionRequest|UpdateVersionRequest|PutVersionChangeRequest $request
    ): MenuVersion {
        /** @var MenuVersion $version */
        $version = $request->target();

        return $version;
    }

    /**
     * The version with its changes, their items and conflicts.
     *
     * @param MenuVersion $version
     * @param int $status
     * @param string $message
     *
     * @return ApiResponse
     */
    protected function respond(MenuVersion $version, int $status = 200, string $message = 'Success'): ApiResponse
    {
        $data = new EditorVersionResource($this->repository->load($version->fresh()));

        return ApiResponse::make(compact('data'), $status, $message);
    }

    /**
     * @OA\Get(
     *   path="/api/editor/restaurants/{id}/versions",
     *   summary="Versions of the restaurant: pending ones by their date, then the ones, which went live.",
     *   operationId="getEditorVersions",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the restaurant."),
     *   @OA\Parameter(name="pending", required=false, in="query", example=1, @OA\Schema(type="boolean"),
     *     description="Only the ones, which haven't gone live."),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorVersionsResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     * ),
     * @OA\Post(
     *   path="/api/editor/restaurants/{id}/versions",
     *   summary="Create a version: a draft, or scheduled right away, with its first changes.",
     *   operationId="storeEditorVersion",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the restaurant."),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/EditorStoreVersionRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Created.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorVersionResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     *   @OA\Response(response=422, description="Invalid values.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * ),
     * @OA\Get(
     *   path="/api/editor/versions/{id}",
     *   summary="The version with its changes, their items and conflicts.",
     *   operationId="getEditorVersion",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the version."),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorVersionResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     * ),
     * @OA\Patch(
     *   path="/api/editor/versions/{id}",
     *   summary="Rename or reschedule the version.",
     *   operationId="updateEditorVersion",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the version."),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/EditorUpdateVersionRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorVersionResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     *   @OA\Response(response=422, description="Invalid values, or the version has gone live.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * ),
     * @OA\Delete(
     *   path="/api/editor/versions/{id}",
     *   summary="Delete the version with its changes.",
     *   operationId="deleteEditorVersion",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the version."),
     *   @OA\Response(response=200, description="Deleted."),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     * ),
     * @OA\Put(
     *   path="/api/editor/versions/{id}/changes",
     *   summary="Put a change of an item (or a new one) into the version.",
     *   operationId="putEditorVersionChange",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the version."),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/EditorPutVersionChangeRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorVersionResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     *   @OA\Response(response=422, description="Invalid values, or the version has gone live.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * ),
     * @OA\Delete(
     *   path="/api/editor/versions/{id}/changes/{change}",
     *   summary="Remove a change from the version: the item stays as it is.",
     *   operationId="removeEditorVersionChange",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the version."),
     *   @OA\Parameter(name="change", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the change."),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorVersionResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     *   @OA\Response(response=404, description="The change isn't in the version."),
     *   @OA\Response(response=422, description="The version has gone live, or a dish would lose its last size.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * ),
     * @OA\Post(
     *   path="/api/editor/versions/{id}/schedule",
     *   summary="Schedule the version at its date, which has to be in the future.",
     *   operationId="scheduleEditorVersion",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the version."),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorVersionResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     *   @OA\Response(response=422, description="It has no date in the future, or it has gone live.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * ),
     * @OA\Post(
     *   path="/api/editor/versions/{id}/deactivate",
     *   summary="Deactivate the scheduled version: it keeps its date, but doesn't go live.",
     *   operationId="deactivateEditorVersion",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the version."),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorVersionResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     *   @OA\Response(response=422, description="It isn't scheduled.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * ),
     * @OA\Post(
     *   path="/api/editor/versions/{id}/activate",
     *   summary="Activate the inactive version: it's scheduled again.",
     *   operationId="activateEditorVersion",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the version."),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorVersionResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     *   @OA\Response(response=422, description="It isn't inactive, or its date has passed.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * ),
     * @OA\Post(
     *   path="/api/editor/versions/{id}/apply",
     *   summary="Apply the version now: all of its changes, or none (then it's failed, with the reason).",
     *   operationId="applyEditorVersion",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the version."),
     *   @OA\Response(
     *     response=200,
     *     description="Applied.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorVersionResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     *   @OA\Response(response=422, description="It failed (the version, with the reason) or has gone live.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorVersionResponse")),
     * ),
     * @OA\Post(
     *   path="/api/editor/versions/{id}/duplicate",
     *   summary="Copy the version as a draft with the same date and changes.",
     *   operationId="duplicateEditorVersion",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the version."),
     *   @OA\Response(
     *     response=201,
     *     description="Created.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorVersionResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     * ),
     * @OA\Schema(
     *   schema="EditorVersionsResponse",
     *   description="Versions of the restaurant.",
     *   required={"data", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/EditorVersion")),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="EditorVersionResponse",
     *   description="The version with its changes, their items and conflicts.",
     *   required={"data", "message"},
     *   @OA\Property(property="data", ref="#/components/schemas/EditorVersion"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     */
}
