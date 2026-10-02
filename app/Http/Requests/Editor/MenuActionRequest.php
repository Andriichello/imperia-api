<?php

namespace App\Http\Requests\Editor;

use App\Models\DishMenu;

/**
 * Class MenuActionRequest.
 *
 * Archive, restore, duplicate or delete a menu.
 */
class MenuActionRequest extends EditorRequest
{
    /**
     * Class of the model the request is about.
     *
     * @return class-string<DishMenu>
     */
    protected function targetClass(): string
    {
        return DishMenu::class;
    }
}
