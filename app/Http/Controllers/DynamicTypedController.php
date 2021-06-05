<?php

namespace App\Http\Controllers;

use App\Http\Requests\DynamicTypedFormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class DynamicTypedController extends DynamicController
{
    /**
     * Current type.
     *
     * @var ?string
     */
    protected ?string $type = null;

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getTypeModels(): array
    {
        return [];
    }

    public function __construct(DynamicTypedFormRequest $request, ?string $type = null)
    {
        parent::__construct($request);

        if (isset($type)) {
            $this->switchModel($type);
        }
    }

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
     * @var string|null $type
     */
    public function switchModel(?string $type)
    {
        $model = data_get($this->getTypeModels(), $type);
        if (empty($model)) {
            throw ValidationException::withMessages([
                'type' => ['A :attribute field must be one of (' . implode(',', array_keys($this->getTypeModels())) . ').'],
            ]);
        }

        $this->type = $type;
        $this->model = $model;

        if ($this->request instanceof DynamicTypedFormRequest) {
            $this->request->setType($type);
        }
    }
}
