<?php

namespace App\Http\Requests\Editor;

use App\Models\MenuVersion;

/**
 * Class VersionActionRequest.
 *
 * Show, schedule, deactivate, activate, apply, duplicate or delete a scheduled version.
 */
class VersionActionRequest extends EditorRequest
{
    /**
     * Class of the model the request is about.
     *
     * @return class-string<MenuVersion>
     */
    protected function targetClass(): string
    {
        return MenuVersion::class;
    }
}
