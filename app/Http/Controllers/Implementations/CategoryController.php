<?php

namespace App\Http\Controllers\Implementations;

use App\Http\Controllers\DynamicController;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\Categories\Category;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class CategoryController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var string
     */
    protected ?string $model = Category::class;

    /**
     * Controller's store method form request class name. Must extend DataFieldRequest.
     *
     * @var ?string
     */
    protected ?string $storeFormRequest = CategoryStoreRequest::class;

    /**
     * Controller's update method form request class name. Must extend DataFieldRequest.
     *
     * @var ?string
     */
    protected ?string $updateFormRequest = CategoryUpdateRequest::class;

    public function index($type = null): Response
    {
        $this->switchModel($type);
        return parent::index();
    }

    public function show($type = null, $id = null): Response
    {
        $this->switchModel($type);
        return parent::show($id);
    }

    public function store($type = null): Response
    {
        $this->switchModel($type);
        return parent::store();
    }

    public function update($type = null, $id = null): Response
    {
        $this->switchModel($type);
        return parent::update($id);
    }

    public function destroy($type = null, $id = null): Response
    {
        $this->switchModel($type);
        return parent::destroy($id);
    }

    /**
     * Switches controller's model class name depending on specified type.
     *
     * @throws ValidationException
     * @var string|null $dataKey
     * @var array|null $data
     */
    protected function switchModel($data = null, $dataKey = null)
    {
        if (empty($dataKey)) {
            if (is_array($data)) {
                $type = $data[array_key_first($data)];
            } else {
                $type = $data;
            }
        } else {
            $type = data_get($data, $dataKey);
        }

        if (!in_array($type, Category::getTypes())) {
            throw ValidationException::withMessages([
                'type' => ['A type attribute must be one of (' . implode(', ', Category::getTypes()) . ').'],
            ]);
        }

        $this->model = Category::getTypeCategoryClass($type);
    }
}
