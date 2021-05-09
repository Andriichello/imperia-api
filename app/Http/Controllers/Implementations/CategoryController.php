<?php

namespace App\Http\Controllers\Implementations;

use App\Constrainters\Constrainter;
use App\Constrainters\Implementations\ItemTypeConstrainter;
use App\Http\Controllers\DynamicController;
use App\Models\Categories\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\Validator\Constraints as Assert;

class CategoryController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Model's primary keys.
     *
     * @var string[]
     */
    protected $primaryKeys = ['id'];

    public function index($type = null)
    {
        $fail = $this->switchModel(['type' => $type], 'type');
        if (isset($fail)) {
            return $fail;
        }
        return parent::index();
    }

    public function show($type = null, $id = null)
    {
        $fail = $this->switchModel(['type' => $type], 'type');
        if (isset($fail)) {
            return $fail;
        }
        return parent::show($id);
    }

    public function store($type = null)
    {
        $fail = $this->switchModel(['type' => $type], 'type');
        if (isset($fail)) {
            return $fail;
        }
        return parent::store();
    }

    public function update($type = null, $id = null)
    {
        $fail = $this->switchModel(['type' => $type], 'type');
        if (isset($fail)) {
            return $fail;
        }
        return parent::update($id);
    }

    public function destroy($type = null, $id = null)
    {
        $fail = $this->switchModel(['type' => $type], 'type');
        if (isset($fail)) {
            return $fail;
        }
        return parent::destroy($id);
    }

    /**
     * Switches controller's model class name depending on specified type.
     *
     * @var array|null $data
     * @var string|null $dataKey
     * @return array|null
     */
    protected function switchModel($data = null, $dataKey = null)
    {
        if (empty($data)) {
            $data = \request()->all();
        }

        if (empty($dataKey)) {
            $validator = Validator::make($data, [
                ItemTypeConstrainter::getRules(true, [Rule::in(Category::getTypes())])
            ]);
        } else {
            $validator = Validator::make($data, [
                $dataKey => ItemTypeConstrainter::getRules(true, [Rule::in(Category::getTypes())])
            ]);
        }


        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        if (empty($dataKey)) {
            $type = $data[array_key_first($data)];
        } else {
            $type = $this->obtain($data, $dataKey);
        }
        $this->model = Category::getTypeCategoryClass($type);
        return null;
    }
}
