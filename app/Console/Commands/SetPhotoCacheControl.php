<?php

namespace App\Console\Commands;

use App\Models\Morphs\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Spatie\GoogleCloudStorage\GoogleCloudStorageAdapter;
use Throwable;

/**
 * Class SetPhotoCacheControl.
 *
 * Files of photos in Cloud Storage get the caching of their disk (`metadata.cacheControl`)
 * when they're uploaded. This gives it to the ones uploaded before.
 */
class SetPhotoCacheControl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:cache-control';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give the files of photos in Cloud Storage the caching of their disk';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $updated = 0;
        $failed = [];

        foreach (Media::query()->distinct()->pluck('disk') as $name) {
            $disk = Storage::disk($name);
            $cacheControl = config("filesystems.disks.$name.metadata.cacheControl");

            if (!$disk instanceof GoogleCloudStorageAdapter || !$cacheControl) {
                $this->line("Photos on the \"$name\" disk are left as they are: it isn't Cloud Storage with caching.");

                continue;
            }

            $bucket = $disk->getClient()->bucket(config("filesystems.disks.$name.bucket"));

            /** @var Collection<int, Media> $photos */
            $photos = Media::query()->where('disk', $name)->orderBy('id')->get();

            $this->info("\"$name\" disk: $cacheControl");

            $update = function (Media $photo) use ($bucket, $disk, $cacheControl, &$updated, &$failed) {
                try {
                    $bucket->object($disk->path($photo->folder . $photo->name))
                        ->update(['cacheControl' => $cacheControl]);

                    $updated++;
                } catch (Throwable $exception) {
                    $failed[] = "Photo #$photo->id: {$exception->getMessage()}";
                }
            };

            $this->withProgressBar($photos, $update);

            $this->newLine();
        }

        foreach ($failed as $message) {
            $this->error($message);
        }

        $this->info("$updated files got the caching of their disk.");

        return empty($failed) ? self::SUCCESS : self::FAILURE;
    }
}
