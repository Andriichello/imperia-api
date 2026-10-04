<?php

namespace App\Helpers\Interfaces;

use App\Models\Morphs\Media;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

/**
 * Interface ConversionHelperInterface.
 */
interface ConversionHelperInterface
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
    ): mixed;
}
