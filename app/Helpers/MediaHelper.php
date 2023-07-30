<?php

namespace App\Helpers;

use App\Helpers\Interfaces\MediaHelperInterface;
use App\Models\Morphs\Media;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Class MediaHelper.
 */
class MediaHelper implements MediaHelperInterface
{
    /**
     * Get bucket for the given disk.
     *
     * @param string $disk
     *
     * @return FilesystemAdapter
     */
    public function bucket(string $disk): FilesystemAdapter
    {
        // @phpstan-ignore-next-line
        return Storage::disk($disk);
    }

    /**
     * Get name for given file.
     *
     * @param string $file
     *
     * @return string
     */
    public function name(string $file): string
    {
        return Str::afterLast($file, '/');
    }

    /**
     * Get extension for given file.
     *
     * @param string $file
     *
     * @return string
     */
    public function extension(string $file): string
    {
        return Str::afterLast($file, '.');
    }

    /**
     * Get folder for given file.
     *
     * @param string $file
     *
     * @return string
     */
    public function folder(string $file): string
    {
        return Str::finish(Str::beforeLast($file, '/'), '/');
    }

    /**
     * Get array of attributes for Media.
     *
     * @param string $file
     * @param string $disk
     *
     * @return array
     */
    public function attributes(string $file, string $disk): array
    {
        return [
            'name' => $this->name($file),
            'extension' => $this->extension($file),
            'folder' => $this->folder($file),
            'disk' => $disk,
        ];
    }

    /**
     * Upload file to disk.
     *
     * @param File|UploadedFile $from
     * @param string $to
     * @param string $disk
     *
     * @return string
     */
    public function upload(File|UploadedFile $from, string $to, string $disk): string
    {
        return $this->bucket($disk)
            ->putFileAs($this->folder($to), $from, $this->name($to));
    }

    /**
     * Upload file to disk and store a media record for it in database.
     *
     * @param File|UploadedFile $from
     * @param string $to
     * @param string $disk
     *
     * @return Media
     * @throws FileNotFoundException
     */
    public function store(File|UploadedFile $from, string $to, string $disk): Media
    {
        $this->upload($from, $to, $disk);

        $media = $this->existing($to, $disk);
        $media->touch();

        return $media;
    }

    /**
     * Move file from one location to the other.
     *
     * @param string $from
     * @param string $to
     * @param string $disk
     *
     * @return bool
     * @throws FileNotFoundException
     */
    public function move(string $from, string $to, string $disk): bool
    {
        $media = $this->existing($from, $disk);

        if ($this->bucket($disk)->move($from, $to)) {
            $media->fill($this->attributes($to, $disk));
            $media->touch();

            return true;
        }

        return false;
    }

    /**
     * Update file on disk and it's media record in database.
     *
     * @param Media $media
     * @param File|UploadedFile|null $from
     * @param string $to
     * @param string $disk
     *
     * @return Media
     * @throws FileNotFoundException
     */
    public function update(Media $media, File|UploadedFile|null $from, string $to, string $disk): Media
    {
        $path = $media->folder . $media->name;

        if ($from !== null) {
            $this->upload($from, $to, $disk);

            $media->fill($this->attributes($to, $disk));
            $media->touch();

            if ($media->exists && $path !== $to) {
                $this->delete($path, $disk);
            }

            return $media;
        }

        if ($to && $to !== $path) {
            $this->move($path, $to, $disk);

            $media->fill($this->attributes($to, $disk));
            $media->touch();

            return $media;
        }

        return $media;
    }

    /**
     * Delete file on disk from its Media record or path.
     *
     * @param Media|string $file
     * @param string $disk
     *
     * @return bool
     * @throws FileNotFoundException
     */
    public function delete(Media|string $file, string $disk): bool
    {
        $bucket = $this->bucket($disk);

        if ($file instanceof Media) {
            return $bucket->delete($file->folder . $file->name)
                && $file->delete();
        }

        $media = $this->existing($file, $disk);
        if ($media->exists) {
            return $this->delete($media, $disk);
        }

        return $bucket->delete($file, $disk);
    }

    /**
     * Find existing or create new instance of media from existing file on disk.
     *
     * @param string $file
     * @param string $disk
     *
     * @return Media
     * @throws FileNotFoundException
     */
    public function existing(string $file, string $disk): Media
    {
        $bucket = $this->bucket($disk);

        if ($bucket->missing($file)) {
            throw new FileNotFoundException('There is no such file: ' . $file);
        }

        $attributes = $this->attributes($file, $disk);

        /** @var Media $media */
        $media = Media::query()
            ->folder($attributes['folder'])
            ->disk($attributes['disk'])
            ->name($attributes['name'])
            ->firstOrNew();

        return $media->fill($attributes);
    }
}
