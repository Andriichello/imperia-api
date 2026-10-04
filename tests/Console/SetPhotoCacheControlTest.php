<?php

namespace Tests\Console;

use App\Models\Morphs\Media;
use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\StorageObject;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter as FlysystemAdapter;
use Mockery;
use Mockery\MockInterface;
use Spatie\GoogleCloudStorage\GoogleCloudStorageAdapter;
use Tests\TestCase;

/**
 * Class SetPhotoCacheControlTest.
 */
class SetPhotoCacheControlTest extends TestCase
{
    /**
     * The bucket of the Cloud Storage disk.
     *
     * @var Bucket&MockInterface
     */
    protected Bucket&MockInterface $bucket;

    /**
     * Set up the test: a Cloud Storage disk of a fake bucket.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'filesystems.disks.uploads.bucket' => 'photos',
            'filesystems.disks.uploads.metadata.cacheControl' => 'public, max-age=31536000, immutable',
        ]);

        /** @var Bucket&MockInterface $bucket */
        $bucket = Mockery::mock(Bucket::class);
        $this->bucket = $bucket;

        /** @var StorageClient&MockInterface $client */
        $client = Mockery::mock(StorageClient::class);
        $client->shouldReceive('bucket')->with('photos')->andReturn($this->bucket);

        $adapter = new FlysystemAdapter($this->bucket);

        Storage::set('uploads', new GoogleCloudStorageAdapter(
            new Filesystem($adapter),
            $adapter,
            config('filesystems.disks.uploads'),
            $client,
        ));
    }

    /**
     * A photo on the disk.
     *
     * @param string $disk
     * @param string $name
     *
     * @return Media
     */
    protected function photo(string $disk, string $name): Media
    {
        /** @var Media $photo */
        $photo = Media::query()->create([
            'name' => $name,
            'extension' => 'image/jpeg',
            'title' => $name,
            'disk' => $disk,
            'folder' => '/media/uploaded/1/',
        ]);

        return $photo;
    }

    /**
     * Test that files of photos in Cloud Storage get the caching of their disk, others are left
     * as they are, and a missing file is reported.
     *
     * @return void
     */
    public function testFilesGetTheCachingOfTheirDisk()
    {
        $this->photo('uploads', 'first');
        $this->photo('uploads', 'missing');
        $this->photo('public', 'local');

        $first = Mockery::mock(StorageObject::class);
        $first->shouldReceive('update')->once()->with(['cacheControl' => 'public, max-age=31536000, immutable']);

        $missing = Mockery::mock(StorageObject::class);
        $missing->shouldReceive('update')->once()->andThrow(new NotFoundException('No such object'));

        $this->bucket->shouldReceive('object')->with('media/uploaded/1/first')->andReturn($first);
        $this->bucket->shouldReceive('object')->with('media/uploaded/1/missing')->andReturn($missing);

        $this->artisan('media:cache-control')
            ->expectsOutputToContain('"public" disk are left as they are')
            ->expectsOutputToContain('No such object')
            ->expectsOutputToContain('1 files got the caching of their disk.')
            ->assertFailed();
    }
}
