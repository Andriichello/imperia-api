<?php

namespace App\Helpers;

use App\Helpers\Interfaces\ConversionHelperInterface;
use App\Models\Morphs\Media;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

/**
 * Class ConversionHelper.
 */
class ConversionHelper implements ConversionHelperInterface
{
    /**
     * Convert given media to WebP format, scaled down (never up) to the width, or so that its
     * shorter side is the given length.
     *
     * @param Media|string $media
     * @param int $quality
     * @param int|null $width
     * @param int|null $shorterSide
     *
     * @return false|resource
     */
    public function toWebP(
        Media|string $media,
        int $quality = 90,
        ?int $width = null,
        ?int $shorterSide = null,
    ): mixed {
        $image = $this->read($media);

        if ($width) {
            $image->scaleDown(width: $width);
        }

        if ($shorterSide) {
            $image->width() < $image->height()
                ? $image->scaleDown(width: $shorterSide)
                : $image->scaleDown(height: $shorterSide);
        }

        $image->encode(new WebpEncoder($quality))
            ->save(pathOf($file = tmpfile()));

        return $file;
    }

    /**
     * Read image from given media.
     *
     * @param Media|string $media Media record or file path.
     *
     * @return ImageInterface
     */
    protected function read(Media|string $media): ImageInterface
    {
        if ($media instanceof Media) {
            $media = $media->asTemporaryFile();
        }

        return ImageManager::gd()->read($media);
    }
}
