<?php

namespace App\Console\Commands;

use App\Enums\Hotness;
use App\Enums\ProductFlag;
use App\Helpers\ContentLocale;
use App\Models\BaseModel;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\DishVariant;
use App\Models\Menu;
use App\Models\Morphs\Alteration;
use App\Models\Morphs\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\Editor\VersionEditorRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Class CopyOldMenuToDishes.
 *
 * Copies the old menu structure (menus, categories, products and their variants)
 * into the dish tables, in place:
 * - each menu becomes a dish menu (menus without a restaurant are skipped);
 * - each product becomes a dish in every menu it belongs to, under its most popular category;
 * - categories are created per menu, for the categories its dishes use;
 * - product variants, image links, flags and pending scheduled changes are copied too;
 * - texts are written in the default language of their restaurant;
 * - archived records become hidden from guests (what archived meant before), deleted ones are skipped.
 *
 * It's safe to run repeatedly: copies remember their source in `metadata.copied_from`
 * and are skipped on the next run (also when they were deleted in the meantime).
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CopyOldMenuToDishes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dishes:copy-old-menu
                            {--restaurant= : Only copy the menus of this restaurant (id)}
                            {--dry-run : Show what would be copied, without saving anything}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy the old menus, categories and products into the dish tables';

    /**
     * Old product hotness values and their flags.
     *
     * @var array<string, string>
     */
    protected const HOTNESS_FLAGS = [
        Hotness::Low => ProductFlag::LowHotness,
        Hotness::Medium => ProductFlag::MediumHotness,
        Hotness::High => ProductFlag::HighHotness,
        Hotness::Ultra => ProductFlag::ExtremeHotness,
    ];

    /**
     * Old product boolean attributes and their flags.
     *
     * @var array<string, string>
     */
    protected const BOOLEAN_FLAGS = [
        'is_vegan' => ProductFlag::Vegan,
        'is_vegetarian' => ProductFlag::Vegetarian,
        'is_low_calorie' => ProductFlag::LowCalorie,
        'has_eggs' => ProductFlag::WithEggs,
        'has_nuts' => ProductFlag::WithNuts,
    ];

    /**
     * Copied records per type.
     *
     * @var array<string, array{copied: int}>
     */
    protected array $counts = [];

    /**
     * Problems worth a look after copying.
     *
     * @var string[]
     */
    protected array $warnings = [];

    /**
     * Copies of old menus, by old menu id.
     *
     * @var array<int, DishMenu>
     */
    protected array $menus = [];

    /**
     * Copies of old categories, by "old category id:old menu id".
     *
     * @var array<string, DishCategory>
     */
    protected array $categories = [];

    /**
     * Copies of old products, by "old product id:old menu id".
     *
     * @var array<string, Dish>
     */
    protected array $dishes = [];

    /**
     * Old menu ids of the copies from previous runs, per type and key.
     *
     * @var array<string, array<string|int, int>>
     */
    protected array $previous = [];

    /**
     * Old menu ids, which were processed in this run.
     *
     * @var array<int, bool>
     */
    protected array $processed = [];

    /**
     * Scheduled versions, which the old scheduled changes become.
     *
     * @var VersionEditorRepository
     */
    protected VersionEditorRepository $versions;

    /**
     * Execute the console command.
     *
     * @param VersionEditorRepository $versions
     *
     * @return int
     * @throws Throwable
     */
    public function handle(VersionEditorRepository $versions): int
    {
        $this->versions = $versions;
        $dryRun = (bool) $this->option('dry-run');

        // the same instance is reused when the command is called again in the same process
        $this->counts = $this->warnings = $this->previous = $this->processed = [];
        $this->menus = $this->categories = $this->dishes = [];

        // like the migration, which made the content translatable, did with the existing texts
        ContentLocale::instance()->useDefaults();

        $this->loadPreviousCopies();

        DB::beginTransaction();

        try {
            foreach ($this->oldMenus() as $menu) {
                $this->copyMenu($menu);
            }
        } catch (Throwable $throwable) {
            DB::rollBack();

            throw $throwable;
        }

        $dryRun ? DB::rollBack() : DB::commit();

        $this->report($dryRun);

        return self::SUCCESS;
    }

    /**
     * Find the records copied by previous runs (deleted ones included).
     *
     * @return void
     */
    protected function loadPreviousCopies(): void
    {
        /** @var DishMenu $menu */
        foreach (DishMenu::query()->withoutGlobalScopes()->get() as $menu) {
            $source = $menu->getFromJson('metadata', 'copied_from');

            if (($source['type'] ?? null) === 'menus') {
                $this->menus[$source['id']] = $menu;
                $this->previous['menus'][$source['id']] = $source['id'];
            }
        }

        /** @var DishCategory $category */
        foreach (DishCategory::query()->withoutGlobalScopes()->get() as $category) {
            $source = $category->getFromJson('metadata', 'copied_from');

            if (($source['type'] ?? null) === 'categories') {
                $this->categories["{$source['id']}:{$source['menu_id']}"] = $category;
                $this->previous['categories']["{$source['id']}:{$source['menu_id']}"] = $source['menu_id'];
            }
        }

        /** @var Dish $dish */
        foreach (Dish::query()->withoutGlobalScopes()->get() as $dish) {
            $source = $dish->getFromJson('metadata', 'copied_from');

            if (($source['type'] ?? null) === 'products') {
                $this->dishes["{$source['id']}:{$source['menu_id']}"] = $dish;
                $this->previous['dishes']["{$source['id']}:{$source['menu_id']}"] = $source['menu_id'];
            }
        }
    }

    /**
     * Old menus to copy (not deleted, archived included).
     *
     * @return Collection<int, Menu>
     */
    protected function oldMenus(): Collection
    {
        /** @var Collection<int, Menu> $menus */
        $menus = Menu::query()
            ->withoutGlobalScopes()
            ->whereNull('menus.deleted_at')
            ->when($this->option('restaurant'), function ($query, $restaurant) {
                $query->where('menus.restaurant_id', $restaurant);
            })
            ->orderBy('menus.id')
            ->get();

        return $menus;
    }

    /**
     * Copy the menu and its products.
     *
     * @param Menu $old
     *
     * @return void
     */
    protected function copyMenu(Menu $old): void
    {
        if (!$old->restaurant_id) {
            $this->warnings[] = "Menu #{$old->id} \"{$old->title}\" has no restaurant, so it wasn't copied.";

            return;
        }

        $this->processed[$old->id] = true;

        $menu = $this->menus[$old->id] ?? null;

        if (!$menu) {
            $menu = new DishMenu([
                'restaurant_id' => $old->restaurant_id,
                'slug' => $this->uniqueMenuSlug($old),
                'title' => $old->title,
                'description' => $old->description,
                'is_hidden' => (bool) $old->archived,
                'popularity' => $old->popularity,
            ]);
            $menu->setToJson('metadata', 'copied_from', ['type' => 'menus', 'id' => $old->id]);
            $menu->save();

            $this->copyMedia($old, $menu);
            $this->menus[$old->id] = $menu;
            $this->count('menus');
        }

        // a copy deleted in the admin stays deleted, so its products aren't copied into it
        if ($menu->getAttribute('deleted_at')) {
            return;
        }

        foreach ($this->oldProducts($old) as $product) {
            $this->copyProduct($product, $old, $menu);
        }
    }

    /**
     * Old products of the menu (not deleted, archived included).
     *
     * @param Menu $menu
     *
     * @return Collection<int, Product>
     */
    protected function oldProducts(Menu $menu): Collection
    {
        $ids = DB::table('menu_product')
            ->where('menu_id', $menu->id)
            ->pluck('product_id');

        /** @var Collection<int, Product> $products */
        $products = Product::query()
            ->withoutGlobalScopes()
            ->whereNull('products.deleted_at')
            ->whereIn('products.id', $ids)
            ->orderBy('products.id')
            ->get();

        return $products;
    }

    /**
     * Copy the product into the menu, with its category, variants, images and scheduled changes.
     *
     * @param Product $product
     * @param Menu $old
     * @param DishMenu $menu
     *
     * @return void
     */
    protected function copyProduct(Product $product, Menu $old, DishMenu $menu): void
    {
        $key = "{$product->id}:{$old->id}";

        if (isset($this->dishes[$key])) {
            return;
        }

        $category = $this->copyCategory($product, $old, $menu);

        if (!$category) {
            $this->warnings[] = "Product #{$product->id} \"{$product->title}\" in menu \"{$old->title}\" has no "
                . "category, so its dish isn't shown on the site until it gets one.";
        }

        $dish = new Dish([
            'menu_id' => $menu->id,
            'category_id' => $category?->id,
            'slug' => $product->slug,
            'title' => $product->title,
            'description' => $product->description,
            'badge' => $product->badge,
            'price' => $product->price ?? 0,
            'weight' => $product->weight,
            'weight_unit' => $product->weight_unit,
            'calories' => $product->calories,
            'preparation_time' => $product->preparation_time,
            'is_hidden' => (bool) $product->archived,
            'popularity' => $product->popularity,
            'flags' => $this->flags($product),
        ]);
        $dish->setToJson('metadata', 'copied_from', [
            'type' => 'products',
            'id' => $product->id,
            'menu_id' => $old->id,
        ]);
        $dish->save();

        $this->dishes[$key] = $dish;
        $this->count('dishes');

        $this->copyMedia($product, $dish);
        $this->copyAlterations($product, $dish);

        foreach ($this->oldVariants($product) as $oldVariant) {
            /** @var DishVariant $variant */
            $variant = DishVariant::query()->create([
                'dish_id' => $dish->id,
                'price' => $oldVariant->price,
                'weight' => $oldVariant->weight,
                'weight_unit' => $oldVariant->weight_unit,
            ]);

            $this->count('variants');
            $this->copyAlterations($oldVariant, $variant);
        }
    }

    /**
     * Copy the product's category into the menu (once per menu).
     *
     * @param Product $product
     * @param Menu $old
     * @param DishMenu $menu
     *
     * @return DishCategory|null
     */
    protected function copyCategory(Product $product, Menu $old, DishMenu $menu): ?DishCategory
    {
        $source = $this->oldCategory($product);

        if (!$source) {
            return null;
        }

        $key = "{$source->id}:{$old->id}";

        if (isset($this->categories[$key])) {
            return $this->categories[$key];
        }

        $category = new DishCategory([
            'menu_id' => $menu->id,
            'slug' => $source->slug,
            'title' => $source->title,
            'description' => $source->description,
            'is_hidden' => (bool) $source->archived,
            'popularity' => $source->popularity,
        ]);
        $category->setToJson('metadata', 'copied_from', [
            'type' => 'categories',
            'id' => $source->id,
            'menu_id' => $old->id,
        ]);
        $category->save();

        $this->copyMedia($source, $category);
        $this->categories[$key] = $category;
        $this->count('categories');

        return $category;
    }

    /**
     * The product's most popular category (one meant for products), archived included.
     *
     * @param Product $product
     *
     * @return Category|null
     */
    protected function oldCategory(Product $product): ?Category
    {
        /** @var Category|null $category */
        $category = Category::query()
            ->withoutGlobalScopes()
            ->select('categories.*')
            ->join('categorizables', 'categorizables.category_id', '=', 'categories.id')
            ->where('categorizables.categorizable_type', $product->getMorphClass())
            ->where('categorizables.categorizable_id', $product->id)
            ->where(function ($query) use ($product) {
                $query->whereNull('categories.target')
                    ->orWhere('categories.target', $product->getMorphClass());
            })
            ->orderByDesc('categories.popularity')
            ->orderBy('categories.id')
            ->first();

        return $category;
    }

    /**
     * Old variants of the product (not deleted).
     *
     * @param Product $product
     *
     * @return Collection<int, ProductVariant>
     */
    protected function oldVariants(Product $product): Collection
    {
        /** @var Collection<int, ProductVariant> $variants */
        $variants = ProductVariant::query()
            ->withoutGlobalScopes()
            ->where('product_id', $product->id)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        return $variants;
    }

    /**
     * The dish flags: the product's flags plus its older boolean and hotness attributes.
     *
     * @param Product $product
     *
     * @return string[]
     */
    protected function flags(Product $product): array
    {
        $flags = $product->flags;

        foreach (static::BOOLEAN_FLAGS as $attribute => $flag) {
            if ($product->getAttribute($attribute)) {
                $flags[] = $flag;
            }
        }

        $hotness = static::HOTNESS_FLAGS[$product->hotness?->value] ?? null;

        if ($hotness) {
            $flags[] = $hotness;
        }

        return array_values(array_unique(array_intersect($flags, ProductFlag::getValues())));
    }

    /**
     * Link the images of the old record to its copy (files are shared, not duplicated).
     *
     * @param BaseModel $from
     * @param BaseModel $to
     *
     * @return void
     */
    protected function copyMedia(BaseModel $from, BaseModel $to): void
    {
        $links = DB::table('mediables')
            ->where('mediable_type', $from->getMorphClass())
            ->where('mediable_id', $from->getKey())
            ->orderBy('order')
            ->get();

        foreach ($links as $link) {
            DB::table('mediables')->insert([
                'media_id' => $link->media_id,
                'mediable_id' => $to->getKey(),
                'mediable_type' => $to->getMorphClass(),
                'order' => $link->order,
            ]);

            $this->count('images');
        }
    }

    /**
     * Copy the pending (not performed, not failed) scheduled changes of the old record, each as
     * a version of one change. The old menu's `archived` is what's hidden now; a change of a
     * product's size is a change of its dish's first size.
     *
     * @param BaseModel $from
     * @param Dish|DishVariant $to
     *
     * @return void
     */
    protected function copyAlterations(BaseModel $from, Dish|DishVariant $to): void
    {
        $alterations = Alteration::query()
            ->where('alterable_type', $from->getMorphClass())
            ->where('alterable_id', $from->getKey())
            ->whereNull('performed_at')
            ->whereNull('failed_at')
            ->get();

        /** @var Alteration $alteration */
        foreach ($alterations as $alteration) {
            $metadata = $alteration->getJson('metadata');

            if (array_key_exists('archived', $metadata)) {
                $metadata['is_hidden'] = (bool) $metadata['archived'];
                unset($metadata['archived']);
            }

            $changes = $to instanceof Dish
                ? [[$to, Arr::except($metadata, Dish::SIZE)], [$to->firstSize(), Arr::only($metadata, Dish::SIZE)]]
                : [[$to, $metadata]];

            foreach ($changes as [$target, $values]) {
                $goesLiveAt = $alteration->perform_at ?? Carbon::now();

                if ($target && $values && $this->versions->scheduleChange($target, $values, $goesLiveAt, null)) {
                    $this->count('scheduled changes');
                }
            }
        }
    }

    /**
     * The old menu's slug, unless a dish menu of the restaurant already uses it.
     *
     * @param Menu $old
     *
     * @return string|null
     */
    protected function uniqueMenuSlug(Menu $old): ?string
    {
        if (!$old->slug) {
            return null;
        }

        $taken = DishMenu::query()
            ->withoutGlobalScopes()
            ->where('restaurant_id', $old->restaurant_id)
            ->where('slug', $old->slug)
            ->exists();

        return $taken ? "{$old->slug}-{$old->id}" : $old->slug;
    }

    /**
     * Count a copied record.
     *
     * @param string $type
     *
     * @return void
     */
    protected function count(string $type): void
    {
        $this->counts[$type]['copied'] = ($this->counts[$type]['copied'] ?? 0) + 1;
    }

    /**
     * Number of copies from previous runs, which belong to the menus processed in this run.
     *
     * @param string $type
     *
     * @return int
     */
    protected function countPrevious(string $type): int
    {
        $menuIds = array_filter($this->previous[$type] ?? [], fn (int $menuId) => isset($this->processed[$menuId]));

        return count($menuIds);
    }

    /**
     * Print the summary and warnings.
     *
     * @param bool $dryRun
     *
     * @return void
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    protected function report(bool $dryRun): void
    {
        $rows = [];

        foreach (['menus', 'categories', 'dishes', 'variants', 'images', 'scheduled changes'] as $type) {
            $rows[] = [
                ucfirst($type),
                $this->counts[$type]['copied'] ?? 0,
                $this->countPrevious($type),
            ];
        }

        $this->table(['', $dryRun ? 'Would copy' : 'Copied', 'Already copied'], $rows);

        foreach ($this->warnings as $warning) {
            $this->warn($warning);
        }

        if ($dryRun) {
            $this->info('Dry run: nothing was saved.');
        }
    }
}
