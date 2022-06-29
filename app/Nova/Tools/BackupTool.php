<?php

namespace App\Nova\Tools;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
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

    /**
     * Build the menu that renders the navigation links for the tool.
     *
     * @param Request $request
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function menu(Request $request): mixed
    {
        return self::section($request);
    }

    /**
     * Build the menu that renders the navigation links for the tool.
     *
     * @param Request $request
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function section(Request $request): mixed
    {
        return MenuSection::make('Backups')
            ->path('/backups')
            ->icon('database');
    }
}
