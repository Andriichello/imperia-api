<?php

namespace App\Jobs\Media;

use App\Helpers\ConversionHelper;
use App\Jobs\AsyncJob;
use App\Models\Morphs\Media;
use App\Repositories\MediaRepository;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\File;
use Illuminate\Support\Collection;

/**
 * Class MakeWebP.
 *
 * Makes the smaller copies (WebP) of a photo, which pages show instead of it. Each copy knows
 * its size (`metadata.variant`, `width` and `height`), so a page loads the smallest one, which
 * is sharp where it's shown (see `photoSources()` of the site).
 */
class MakeWebP extends AsyncJob
{
    /**
     * Types of photos, which get copies.
     *
     * @var string[]
     */
    public const TYPES = ['image/jpeg', 'image/png', 'image/x-png'];

    /**
     * Copies, by their names: the small one fills a photo of a list (a 112px square, on a screen
     * of up to 3 pixels per point), the large one a dish page (up to 448px wide).
     *
     * @var array<string, array{width?: int, shorter_side?: int}>
     */
    public const SIZES = [
        'small' => ['shorter_side' => 360],
        'large' => ['width' => 1200],
    ];

    /**
     * Quality of the copies.
     *
     * @var int
     */
    public const QUALITY = 70;

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
        /** @var Collection<int, Media> $variants */
        $variants = $this->media->variants()->get();
        $made = $variants->map(fn (Media $variant) => $variant->getFromJson('metadata', 'variant'))->all();

        $missing = array_diff_key(static::SIZES, array_flip(array_filter($made)));

        if (empty($missing)) {
            return;
        }

        // downloaded once for all copies (it's kept until this job ends)
        $original = $this->media->asTemporaryFile();

        $helper = new ConversionHelper();

        /** @var MediaRepository $repo */
        $repo = app(MediaRepository::class);

        foreach ($missing as $name => $size) {
            $copy = $helper->toWebP(
                pathOf($original),
                static::QUALITY,
                $size['width'] ?? null,
                $size['shorter_side'] ?? null,
            );

            [$width, $height] = getimagesize(pathOf($copy));

            $repo->createVariant($this->media, new File(pathOf($copy)), [
                'variant' => $name,
                'width' => $width,
                'height' => $height,
            ]);
        }
    }
}
