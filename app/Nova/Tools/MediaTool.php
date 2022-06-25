<?php

namespace App\Nova\Tools;

use Andriichello\Media\Media;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Class MediaTool.
 */
class MediaTool extends Media
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
