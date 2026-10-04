<?php

namespace App\Console\Commands;

use App\Jobs\Media\MakeWebP;
use App\Models\Morphs\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Class MakePhotoCopies.
 *
 * Makes the smaller copies (WebP) of photos, which lack them, right away: the scheduled
 * `DispatchMakeWebPs` makes them too, a few at a time, but it needs a queue worker.
 */
class MakePhotoCopies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:make-copies
                            {--limit= : Only this many photos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make the smaller copies (WebP) of photos, which lack them';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        /** @var Collection<int, Media> $photos */
        $photos = Media::query()
            ->withoutCopies()
            ->orderBy('id')
            ->when($this->option('limit'), fn ($query, $limit) => $query->limit((int) $limit))
            ->get();

        if ($photos->isEmpty()) {
            $this->info('All photos have their copies.');

            return self::SUCCESS;
        }

        $failed = [];

        $this->withProgressBar($photos, function (Media $photo) use (&$failed) {
            try {
                MakeWebP::dispatchSync($photo);
            } catch (Throwable $exception) {
                $failed[] = "Photo #$photo->id: {$exception->getMessage()}";
            }
        });

        $this->newLine();

        foreach ($failed as $message) {
            $this->error($message);
        }

        $made = $photos->count() - count($failed);
        $this->info("$made photos got their copies.");

        return empty($failed) ? self::SUCCESS : self::FAILURE;
    }
}
