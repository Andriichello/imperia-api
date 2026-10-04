<?php

namespace Tests\Jobs\Media;

use App\Jobs\Media\DispatchMakeWebPs;
use App\Jobs\Media\MakeWebP;
use App\Models\Morphs\Media;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Class MakeWebPTest.
 *
 * The smaller copies (WebP) of photos, which pages show instead of them.
 */
class MakeWebPTest extends TestCase
{
    /**
     * Where the files are kept.
     *
     * @var FilesystemAdapter
     */
    protected FilesystemAdapter $disk;

    /**
     * Set up the test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->disk = Storage::fake('public');

        // photos are downloaded by their addresses: the files of the disk
        Http::fake(function (Request $request) {
            $path = Str::after(parse_url($request->url(), PHP_URL_PATH), '/storage');

            return Http::response($this->disk->get($path));
        });
    }

    /**
     * A photo (600x600) with its file.
     *
     * @param array $attributes
     *
     * @return Media
     */
    protected function photo(array $attributes = []): Media
    {
        // not of the factory: its titles run out
        /** @var Media $photo */
        $photo = Media::query()->create([
            'name' => Str::random(32),
            'extension' => 'image/jpeg',
            'title' => 'Margherita',
            'disk' => 'public',
            'folder' => '/',
            ...$attributes,
        ]);

        $this->disk->put(
            $photo->folder . $photo->name,
            file_get_contents(storage_path('app/public/media/examples/margherita.jpg'))
        );

        return $photo;
    }

    /**
     * Copies of the photo, by their names.
     *
     * @param Media $photo
     *
     * @return array<string, Media>
     */
    protected function copiesOf(Media $photo): array
    {
        /** @var Collection<int, Media> $copies */
        $copies = $photo->variants()->get();

        return $copies->keyBy(fn (Media $copy) => $copy->getFromJson('metadata', 'variant') ?? 'full')->all();
    }

    /**
     * Test that a photo gets a small copy for lists and a large one for dish pages, which know
     * their sizes (never bigger than the photo), and that it doesn't get them again.
     *
     * @return void
     */
    public function testPhotoGetsItsCopies()
    {
        $photo = $this->photo();

        MakeWebP::dispatchSync($photo);

        $copies = $this->copiesOf($photo);

        $this->assertEqualsCanonicalizing(['small', 'large'], array_keys($copies));

        foreach ($copies as $copy) {
            $this->assertSame('image/webp', $copy->getAttributes()['extension']);
            $this->assertSame($photo->id, $copy->original_id);
            $this->disk->assertExists($copy->folder . $copy->name);
        }

        // the shorter side is 360px, the large one isn't scaled up
        $this->assertSame([360, 360], [
            $copies['small']->getFromJson('metadata', 'width'),
            $copies['small']->getFromJson('metadata', 'height'),
        ]);
        $this->assertSame([600, 600], [
            $copies['large']->getFromJson('metadata', 'width'),
            $copies['large']->getFromJson('metadata', 'height'),
        ]);

        MakeWebP::dispatchSync($photo);

        $this->assertCount(2, $photo->variants()->get());
    }

    /**
     * Test that a photo with a WebP of its full size (made before the copies) gets the copies
     * too, and keeps it.
     *
     * @return void
     */
    public function testPhotoWithItsFullWebPGetsTheCopies()
    {
        $photo = $this->photo();
        $full = $this->photo(['original_id' => $photo->id, 'extension' => 'image/webp']);

        MakeWebP::dispatchSync($photo);

        $this->assertEqualsCanonicalizing(['full', 'small', 'large'], array_keys($this->copiesOf($photo)));
        $this->assertModelExists($full);
    }

    /**
     * Test that only photos, which lack any of the copies, get them made.
     *
     * @return void
     */
    public function testOnlyPhotosWithoutCopiesGetThem()
    {
        Queue::fake();

        $without = $this->photo();
        $withFull = $this->photo();
        $this->photo(['original_id' => $withFull->id, 'extension' => 'image/webp']);
        $withSmall = $this->photo();
        $this->photo([
            'original_id' => $withSmall->id,
            'extension' => 'image/webp',
            'metadata' => json_encode(['variant' => 'small', 'width' => 360, 'height' => 360]),
        ]);

        $done = $this->photo();
        foreach (array_keys(MakeWebP::SIZES) as $name) {
            $this->photo([
                'original_id' => $done->id,
                'extension' => 'image/webp',
                'metadata' => json_encode(['variant' => $name]),
            ]);
        }

        $this->photo(['extension' => 'image/webp']);
        $this->photo(['extension' => 'image/svg+xml']);

        (new DispatchMakeWebPs(10))->handle();

        $queued = [];
        Queue::assertPushed(MakeWebP::class, function (MakeWebP $job) use (&$queued) {
            $queued[] = (fn () => $this->media->id)->call($job);

            return true;
        });

        $this->assertEqualsCanonicalizing([$without->id, $withFull->id, $withSmall->id], $queued);
    }

    /**
     * Test that the command makes the copies of the photos, which lack them, right away.
     *
     * @return void
     */
    public function testCommandMakesTheCopies()
    {
        $first = $this->photo();
        $second = $this->photo();

        $this->artisan('media:make-copies', ['--limit' => 1])
            ->expectsOutputToContain('1 photos got their copies.')
            ->assertSuccessful();

        $this->assertCount(2, $first->variants()->get());
        $this->assertCount(0, $second->variants()->get());

        $this->artisan('media:make-copies')->assertSuccessful();
        $this->assertCount(2, $second->variants()->get());

        $this->artisan('media:make-copies')
            ->expectsOutputToContain('All photos have their copies.')
            ->assertSuccessful();
    }
}
