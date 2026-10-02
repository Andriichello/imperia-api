<?php

namespace App\Http\Requests\Editor;

use App\Models\DishCategory;

/**
 * Class CategoryActionRequest.
 *
 * Archive, restore, duplicate or delete a category.
 */
class CategoryActionRequest extends EditorRequest
{
    /**
     * Class of the model the request is about.
     *
     * @return class-string<DishCategory>
     */
    protected function targetClass(): string
    {
        return DishCategory::class;
    }
}
