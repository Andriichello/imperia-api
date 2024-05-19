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
     * Convert given media to WebP format.
     *
     * @param Media|string $media
     * @param int $quality
     *
     * @return false|resource
     */
    public function toWebP(Media|string $media, int $quality = 90): mixed
    {
        $image = $this->read($media)
            ->encode(new WebpEncoder($quality));

        $image->save(pathOf($file = tmpfile()));

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
