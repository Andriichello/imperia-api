<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\User\DestroyUserRequest;
use App\Http\Requests\User\IndexUserRequest;
use App\Http\Requests\User\MeUserRequest;
use App\Http\Requests\User\RestoreUserRequest;
use App\Http\Requests\User\ShowUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;

/**
 * Class UserController.
 */
class UserController extends CrudController
{
    /**
     * Available controller's actions.
     *
     * @var string[]
     */
    protected array $actions = [
        'index' => IndexUserRequest::class,
        'show' => ShowUserRequest::class,
        'store' => StoreUserRequest::class,
        'update' => UpdateUserRequest::class,
        'destroy' => DestroyUserRequest::class,
        'restore' => RestoreUserRequest::class,
    ];

    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = UserResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $resourceCollectionClass = UserCollection::class;

    /**
     * UserController constructor.
     *
     * @var UserRepository
     */
    public function __construct(UserRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Get currently logged-in user.
     *
     * @param MeUserRequest $request
     *
     * @return JsonResponse
     */
    public function me(MeUserRequest $request): JsonResponse
    {
        return $this->asResourceResponse($request->user());
    }
}
