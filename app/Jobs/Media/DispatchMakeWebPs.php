<?php

namespace App\Jobs\Media;

use App\Jobs\AsyncJob;
use App\Models\Morphs\Media;
use Exception;

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
        /* @phpstan-ignore-next-line  */
        $collection = \App\Models\Morphs\Media::query()
            ->whereNull('original_id')
            ->whereNotNull('extension')
            ->whereIn('extension', ['image/jpeg', 'image/png', 'image/x-png'])
            ->doesntHave('variants')
            ->limit($this->limit)
            ->get();

        $collection->each(fn(Media $media) => dispatch(new MakeWebP($media)));
    }
}
