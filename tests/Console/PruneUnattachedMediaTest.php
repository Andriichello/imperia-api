<?php

namespace Tests\Console;

use App\Models\DishMenu;
use App\Models\Morphs\Media;
use App\Models\Restaurant;
use Database\Factories\Morphs\MediaFactory;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Class PruneUnattachedMediaTest.
 */
class PruneUnattachedMediaTest extends TestCase
{
    /**
     * Create a photo with its file, uploaded hours ago.
     *
     * @param int $hours
     * @param array $attributes
     *
     * @return Media
     */
    protected function photo(int $hours, array $attributes = []): Media
    {
        /** @var Media $media */
        $media = MediaFactory::new()->create($attributes);
        $media->forceFill(['created_at' => now()->subHours($hours)])->save();

        Storage::disk('public')->put($media->folder . $media->name, 'image');

        return $media;
    }

    /**
     * Test that old unattached photos are only listed, and deleted with their versions when asked to.
     *
     * @return void
     */
    public function testOldUnattachedPhotosAreDeletedWhenAskedTo()
    {
        $disk = Storage::fake('public');

        $restaurant = Restaurant::factory()->create();
        $menu = DishMenu::factory()->withRestaurant($restaurant)->create();

        $unsaved = $this->photo(48);
        $webp = $this->photo(48, ['original_id' => $unsaved->id]);
        $recent = $this->photo(1);
        $attached = $this->photo(48);
        $menu->setMedia($attached);

        $this->artisan('media:prune-unattached')
            ->expectsOutputToContain('1 unattached photos. Run with --delete')
            ->assertSuccessful();

        $this->assertModelExists($unsaved);

        $this->artisan('media:prune-unattached', ['--delete' => true])
            ->expectsOutputToContain('1 unattached photos were deleted.')
            ->assertSuccessful();

        $this->assertModelMissing($unsaved);
        $this->assertModelMissing($webp);
        $this->assertModelExists($recent);
        $this->assertModelExists($attached);

        $disk->assertMissing($unsaved->folder . $unsaved->name);
        $disk->assertMissing($webp->folder . $webp->name);
        $disk->assertExists($attached->folder . $attached->name);
    }
}
