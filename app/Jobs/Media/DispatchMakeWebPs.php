<?php

namespace App\Jobs\Media;

use App\Jobs\AsyncJob;
use App\Models\Morphs\Media;
use Exception;
use Illuminate\Support\Collection;

/**
 * Class DispatchMakeWebPs.
 */
class DispatchMakeWebPs extends AsyncJob
{
    /**
     * Max number of media to be queued.
     *
     * @var int
     */
    protected int $limit;

    /**
     * DispatchNotifications constructor.
     *
     * @param int $limit
     */
    public function __construct(int $limit)
    {
        $this->limit = $limit;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        /** @var Collection<int, Media> $collection */
        $collection = Media::query()
            ->withoutCopies()
            ->orderBy('id')
            ->limit($this->limit)
            ->get();

        $collection->each(fn(Media $media) => dispatch(new MakeWebP($media)));
    }
}
