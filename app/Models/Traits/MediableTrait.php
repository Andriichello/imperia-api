<?php

namespace App\Models\Traits;

use App\Models\Menu;
use App\Models\Morphs\Category;
use App\Models\Product;
use App\Models\Service;
use App\Models\Space;
use App\Models\Ticket;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Trait MediableTrait.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
trait MediableTrait
{
    use InteractsWithMedia;

    /**
     * Determines whether media, which is attached to the model
     * should be deleted when model is deleted.
     *
     * @var bool
     */
    protected bool $deletePreservingMedia = false;

    /**
     * Class name to default media relative path mappings.
     *
     * @var array
     */
    protected static array $defaultMediaMap = [
        Menu::class => 'defaults/menu.svg',
        Category::class => 'defaults/category.svg',
        Space::class => 'defaults/table.svg',
        Ticket::class => 'defaults/ticket.svg',
        Product::class => 'defaults/dish.svg',
        Service::class => 'defaults/action.svg',
    ];
}
