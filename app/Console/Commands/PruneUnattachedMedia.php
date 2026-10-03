<?php

namespace App\Console\Commands;

use App\Helpers\MediaHelper;
use App\Models\MenuVersionChange;
use App\Models\Morphs\Media;
use App\Queries\MenuVersionQueryBuilder;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Class PruneUnattachedMedia.
 *
 * Photos are uploaded first and attached when the editor's panel is saved, so ones of
 * panels, which were never saved, stay unattached. So do photos removed in the admin.
 * This lists them (and deletes them, with their files and WebP versions, when asked to).
 */
class PruneUnattachedMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:prune-unattached
                            {--hours=24 : Only photos uploaded at least this many hours ago}
                            {--delete : Delete them, instead of only listing them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List (or delete) photos, which aren\'t attached to anything';

    /**
     * Execute the console command.
     *
     * @param MediaHelper $helper
     *
     * @return int
     */
    public function handle(MediaHelper $helper): int
    {
        /** @var Collection<int, Media> $media */
        $media = Media::query()
            ->whereNull('original_id')
            ->where('created_at', '<=', Carbon::now()->subHours((int) $this->option('hours')))
            ->whereNotExists(fn (Builder $query) => $query->selectRaw('1')
                ->from('mediables')
                ->whereColumn('mediables.media_id', 'media.id'))
            // added in a version, which hasn't gone live yet
            ->whereNotIn('id', $this->scheduledMediaIds())
            ->orderBy('id')
            ->get();

        if ($media->isEmpty()) {
            $this->info('There are no unattached photos.');

            return self::SUCCESS;
        }

        $this->table(
            ['Id', 'Restaurant', 'Uploaded', 'File'],
            $media->map(fn (Media $item) => [
                $item->id,
                $item->restaurant_id,
                $item->created_at?->toDateTimeString(),
                $item->folder . $item->name,
            ])->all()
        );

        if (!$this->option('delete')) {
            $this->info("{$media->count()} unattached photos. Run with --delete to delete them.");

            return self::SUCCESS;
        }

        $deleted = 0;

        foreach ($media as $item) {
            try {
                // WebP versions first, their rows would be gone with the original's one
                foreach ($item->variants as $variant) {
                    $helper->delete($variant, $variant->disk);
                }

                $helper->delete($item, $item->disk);
                $deleted++;
            } catch (Throwable $exception) {
                $this->error("Photo #$item->id wasn't deleted: {$exception->getMessage()}");
            }
        }

        $this->info("$deleted unattached photos were deleted.");

        return self::SUCCESS;
    }

    /**
     * Ids of the photos, which versions, which haven't gone live yet, add.
     *
     * @return int[]
     */
    protected function scheduledMediaIds(): array
    {
        $query = MenuVersionChange::query();
        // @phpstan-ignore-next-line
        $query->whereHas('version', fn (MenuVersionQueryBuilder $versions) => $versions->pending());

        $ids = [];

        /** @var MenuVersionChange $change */
        foreach ($query->get() as $change) {
            $ids = [...$ids, ...Arr::pluck($change->fields['media']['new'] ?? [], 'id')];
        }

        return array_values(array_unique($ids));
    }
}
