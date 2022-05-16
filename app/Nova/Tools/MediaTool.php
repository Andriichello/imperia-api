<?php

namespace App\Nova\Tools;

use App\Models\User;
use ClassicO\NovaMediaLibrary\NovaMediaLibrary;
use Illuminate\Http\Request;

/**
 * Class MediaTool.
 */
class MediaTool extends NovaMediaLibrary
{
    /**
     * Determine if tool should be visible to user, who makes request.
     *
     * @param Request $request
     * @return bool
     */
    public function authorizedToSee(Request $request): bool
    {
        $nonEditing = $request->isMethod('get')
            || str_ends_with($request->path(), '/nova-media-library/get')
            || str_ends_with($request->path(), '/nova-media-library/folders');

        if ($nonEditing) {
            return true;
        }

        /** @var User|null $user */
        $user = $request->user();

        return $user && $user->isAdmin();
    }
}
