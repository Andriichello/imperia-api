<?php

namespace App\Nova\Tools;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\BackupTool\BackupTool as SpatieBackupTool;

/**
 * Class BackupTool.
 */
class BackupTool extends SpatieBackupTool
{
    /**
     * Determine if tool should be visible to user, who makes request.
     *
     * @param Request $request
     * @return bool
     */
    public function authorizedToSee(Request $request): bool
    {
        /** @var User|null $user */
        $user = $request->user();

        return $user && $user->isAdmin();
    }
}
