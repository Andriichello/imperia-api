<?php

namespace App\Http\Controllers\Implementations;

use App\Constrainters\Implementations\ApiTokenConstrainter;
use App\Constrainters\Implementations\NameConstrainter;
use App\Constrainters\Implementations\PasswordConstrainter;
use App\Http\Controllers\DynamicController;
use App\Http\Requests\ImperiaUserLoginRequest;
use App\Http\Requests\ImperiaUserStoreRequest;
use App\Http\Requests\ImperiaUserUpdateRequest;
use App\Http\Resources\Resource;
use App\Models\ImperiaUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;

class ImperiaUserController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var string
     */
    protected ?string $model = ImperiaUser::class;

    /**
     * Controller's store method form request class name. Must extend DataFieldRequest.
     *
     * @var ?string
     */
    protected ?string $storeFormRequest = ImperiaUserStoreRequest::class;

    /**
     * Controller's update method form request class name. Must extend DataFieldRequest.
     *
     * @var ?string
     */
    protected ?string $updateFormRequest = ImperiaUserUpdateRequest::class;

    /**
     * Get the user with api_token from name and password.
     *
     * @return Response
     */
    public function login()
    {
        $request = App::make(ImperiaUserLoginRequest::class);
        $data = $request->validated();

        if (isset($data['api_token'])) {
            unset($data['name']);
            unset($data['password']);
        }

        $user = ImperiaUser::select()
            ->where($data)
            ->first();

        if (!isset($user)) {
            abort(401, 'Invalid credentials');
        }

        return $this->toResponse(request(), new Resource($user));
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
