<?php

namespace App\Helpers\Interfaces;

use App\Models\Morphs\Media;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

/**
 * Interface MediaHelperInterface.
 */
interface MediaHelperInterface
{
    /**
     * Get bucket for the given disk.
     *
     * @param string $disk
     *
     * @return FilesystemAdapter
     */
    public function bucket(string $disk): FilesystemAdapter;

    /**
     * Get name for given file.
     *
     * @param string $file
     *
     * @return string
     */
    public function name(string $file): string;

    /**
     * Get extension for given file.
     *
     * @param string $file
     *
     * @return string
     */
    public function extension(string $file): string;

    /**
     * Get folder for given file.
     *
     * @param string $file
     *
     * @return string
     */
    public function folder(string $file): string;

    /**
     * Get array of attributes for Media.
     *
     * @param string $file
     * @param string $disk
     *
     * @return array
     */
    public function attributes(string $file, string $disk): array;

    /**
     * Upload file to disk.
     *
     * @param File|UploadedFile $from
     * @param string $to
     * @param string $disk
     *
     * @return string
     */
    public function upload(File|UploadedFile $from, string $to, string $disk): string;

    /**
     * Upload file to disk and store a media record for it in database.
     *
     * @param File|UploadedFile $from
     * @param string $to
     * @param string $disk
     *
     * @return Media
     */
    public function store(File|UploadedFile $from, string $to, string $disk): Media;

    /**
     * Update file on disk and it's media record in database.
     *
     * @param Media $media
     * @param File|UploadedFile|null $from
     * @param string $to
     * @param string $disk
     *
     * @return Media
     */
    public function update(Media $media, File|UploadedFile|null $from, string $to, string $disk): Media;

    /**
     * Move file from one location to the other.
     *
     * @param string $from
     * @param string $to
     * @param string $disk
     *
     * @return bool
     */
    public function move(string $from, string $to, string $disk): bool;

    /**
     * Delete file on disk from its Media record or path.
     *
     * @param Media|string $file
     * @param string $disk
     *
     * @return bool
     */
    public function delete(Media|string $file, string $disk): bool;

    /**
     * Create media record from existing file on disk.
     *
     * @param string $file
     * @param string $disk
     *
     * @return Media
     */
    public function existing(string $file, string $disk): Media;
}
