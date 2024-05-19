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
     * Convert given media to WebP format.
     *
     * @param Media|string $media
     * @param int $quality
     *
     * @return false|resource
     */
    public function toWebP(Media|string $media, int $quality = 90): mixed;
}
