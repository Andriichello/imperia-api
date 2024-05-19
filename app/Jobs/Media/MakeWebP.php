<?php

namespace App\Jobs\Media;

use App\Helpers\ConversionHelper;
use App\Jobs\AsyncJob;
use App\Models\Morphs\Media;
use App\Repositories\MediaRepository;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\File;

/**
 * Class MakeWebP.
 */
class MakeWebP extends AsyncJob
{
    /**
     * @var Media
     */
    protected Media $media;

    /**
     * Create a new job instance.
     *
     * @param Media $media
     */
    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        /* @phpstan-ignore-next-line */
        if ($this->media->variants()->exists()) {
            return;
        }

        $file = (new ConversionHelper())
            ->toWebP($this->media, 25);

        /** @var MediaRepository $repo */
        $repo = app(MediaRepository::class);
        $repo->createVariant($this->media, new File(pathOf($file)));
    }
}
