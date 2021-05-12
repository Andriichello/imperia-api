<?php

namespace App\Http\Controllers\Implementations;

use App\Constrainters\Implementations\ApiTokenConstrainter;
use App\Constrainters\Implementations\NameConstrainter;
use App\Constrainters\Implementations\PasswordConstrainter;
use App\Http\Controllers\DynamicController;
use App\Http\Resources\Resource;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

class UserController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Get the user with api_token from name and password.
     *
     * @return Response
     */
    public function login()
    {
        $data = \request()->all();

        try {
            $data = $this->validateRules($data, [
                'name' => NameConstrainter::getRules(false, ['required_without:api_token']),
                'password' => PasswordConstrainter::getRules(false, ['required_without:api_token']),
                'api_token' => ApiTokenConstrainter::getRules(false, ['required_without_all:name,password']),
            ]);

            if (isset($data['api_token'])) {
                unset($data['name']);
                unset($data['password']);
            }

            $user = User::select()
                ->where($data)
                ->first();

            if (isset($user)) {
                return $this->toResponse(new Resource($user));
            } else {
                throw new \Exception('Not found', 404);
            }
        } catch (\Exception $exception) {
            return $this->toResponse($exception);
        }
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
