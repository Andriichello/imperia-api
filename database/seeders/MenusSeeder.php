<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Menu;
use App\Models\Morphs\Category;
use App\Models\User;
use App\Repositories\MenuRepository;
use Illuminate\Database\Seeder;

/**
 * Class MenusSeeder.
 */
class MenusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->processCategoriesByProducts();
    }

    /**
     * Attaches categories that are derived for menu
     * from its products.
     *
     * @return void
     */
    public function processCategoriesByProducts(): void
    {
        /** @var MenuRepository $repo */
        $repo = app(MenuRepository::class);

        $menus = Menu::query()->get();

        /** @var Menu $menu */
        foreach ($menus as $menu) {
            $categories = $menu->categoriesByProducts()->get();

            /** @var Category $category */
            foreach ($categories as $category) {
                $repo->attachCategory($menu, $category);
            }
        }
    }
}
