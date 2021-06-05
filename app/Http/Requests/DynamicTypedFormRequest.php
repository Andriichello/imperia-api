<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Route;

class DynamicTypedFormRequest extends DynamicFormRequest
{
    protected ?string $type = null;

    public function getType(?string $type = null): string
    {
        if (empty($type)) {
            return $this->type ?? data_get(Route::current()->parameters(), 'type');
        }
        return $type;
    }

    public function setType(?string $type = null): static
    {
        $this->type = $type;
        return $this;
    }

    public function getTypes(): array
    {
        return [];
    }

    public function __construct(?string $type = null)
    {
        parent::__construct();
        $this->setType($type);
    }

    public function rules(?string $action = null): array
    {
        return array_merge(
            ['type' => 'in:' . implode(',', $this->getTypes())],
            parent::rules($action)
        );
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'type' => $this->getType($this->type),
        ]);
    }
}
