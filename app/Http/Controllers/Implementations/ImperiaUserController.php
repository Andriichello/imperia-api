<?php

namespace App\Http\Controllers\Implementations;

use App\Http\Controllers\DynamicController;
use App\Http\Requests\Implementations\ImperiaUserRequest;
use App\Models\ImperiaUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class ImperiaUserController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var ?string
     */
    protected ?string $model = ImperiaUser::class;

    public function __construct(ImperiaUserRequest $request)
    {
        parent::__construct($request);
    }

    /**
     * Get the user with api_token from name and password.
     *
     * @return Response
     */
    public function login()
    {
        $data = $this->request()->validated();

        if (isset($data['api_token'])) {
            unset($data['name']);
            unset($data['password']);
        }

        $user = ImperiaUser::where($data)->first();
        if (!isset($user)) {
            abort(401, 'Invalid credentials');
        }

        return $this->toResponse($this->request(), new JsonResource($user));
    }

    /**
     * Create new Model instance and store it in the database.
     *
     * @param array $columns
     * @return Model
     */
    public function createModel(array $columns): Model
    {
        $columns['api_token'] = hash('sha256', uniqid());
        return parent::createModel($columns);
    }
}
