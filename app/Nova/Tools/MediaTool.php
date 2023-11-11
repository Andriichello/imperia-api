<?php

namespace App\Nova\Tools;

use Andriichello\Media\Media;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;

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
        return self::section($request, __('nova.dashboard.media'));
    }

    /**
     * Build the menu that renders the navigation links for the tool.
     *
     * @param Request $request
     * @param string $name
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function section(Request $request, string $name): mixed
    {
        return MenuSection::make($name)
            ->path('/media')
            ->icon('photograph');
    }
}
