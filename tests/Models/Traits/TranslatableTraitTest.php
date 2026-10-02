<?php

namespace Tests\Models\Traits;

use App\Helpers\ContentLocale;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\Morphs\Alteration;
use App\Models\Restaurant;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

/**
 * Class TranslatableTraitTest.
 *
 * Content in several languages, read in the current one (or a fallback),
 * and in the restaurant's default one in the admin panel.
 */
class TranslatableTraitTest extends TestCase
{
    /**
     * Restaurant, which writes its content in Ukrainian first.
     *
     * @var Restaurant
     */
    protected Restaurant $restaurant;

    /**
     * @var Dish
     */
    protected Dish $dish;

    /**
     * Set up the test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::factory()->create(['locale' => 'uk']);
        $menu = DishMenu::factory()->withRestaurant($this->restaurant)->create();

        $this->dish = Dish::factory()
            ->withMenu($menu)
            ->withCategory(DishCategory::factory()->withMenu($menu)->create())
            ->create(['title' => ['en' => 'Borscht', 'uk' => 'Борщ'], 'badge' => ['uk' => 'Новинка']]);
    }

    /**
     * Test that attributes are read (and serialized) in the current language.
     *
     * @return void
     */
    public function testAttributesAreInTheCurrentLanguage()
    {
        $this->assertSame('Borscht', $this->dish->title);
        $this->assertSame('Borscht', $this->dish->toArray()['title']);

        App::setLocale('uk');

        $this->assertSame('Борщ', $this->dish->title);
        $this->assertSame('Борщ', $this->dish->toArray()['title']);
        $this->assertSame(['en' => 'Borscht', 'uk' => 'Борщ'], $this->dish->getTranslations('title'));
    }

    /**
     * Test that a missing translation falls back to another language.
     *
     * @return void
     */
    public function testMissingTranslationsFallBack()
    {
        $this->dish->putTranslations('description', ['en' => 'Beetroot soup']);

        // only in Ukrainian
        $this->assertSame('Новинка', $this->dish->badge);

        App::setLocale('uk');

        // only in English
        $this->assertSame('Beetroot soup', $this->dish->description);
    }

    /**
     * Test that the admin panel reads and writes the restaurant's default language.
     *
     * @return void
     */
    public function testAdminPanelUsesTheDefaultLanguage()
    {
        ContentLocale::instance()->useDefaults();

        $this->assertSame('Борщ', $this->dish->title);

        $this->dish->update(['title' => 'Червоний борщ']);

        $this->assertSame(
            ['en' => 'Borscht', 'uk' => 'Червоний борщ'],
            $this->dish->fresh()->getTranslations('title')
        );
    }

    /**
     * Test that new models know their default language while being filled,
     * whatever the order of the attributes.
     *
     * @return void
     */
    public function testNewModelsKnowTheirDefaultLanguage()
    {
        ContentLocale::instance()->useDefaults();

        $menu = new DishMenu(['title' => 'Напої', 'restaurant_id' => $this->restaurant->id]);
        $menu->save();

        $this->assertSame(['uk' => 'Напої'], $menu->fresh()->getTranslations('title'));
    }

    /**
     * Test that empty translations are left out, and the attribute is null without any.
     *
     * @return void
     */
    public function testEmptyTranslationsAreLeftOut()
    {
        $this->dish->putTranslations('description', ['en' => ' Beetroot soup ', 'uk' => '']);
        $this->dish->putTranslations('badge', ['en' => null, 'uk' => '  ']);
        $this->dish->save();

        $dish = $this->dish->fresh();

        $this->assertSame(['en' => 'Beetroot soup'], $dish->getTranslations('description'));
        $this->assertNull($dish->getAttributes()['badge']);
    }

    /**
     * Test that scheduled changes of texts change the restaurant's default language.
     *
     * @return void
     */
    public function testScheduledChangesUseTheDefaultLanguage()
    {
        $alteration = Alteration::factory()
            ->withModel($this->dish)
            ->withValues(['title' => 'Зелений борщ'])
            ->create();

        $alteration->perform();

        $this->assertSame(
            ['en' => 'Borscht', 'uk' => 'Зелений борщ'],
            $this->dish->fresh()->getTranslations('title')
        );
    }
}
