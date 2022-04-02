<?php

namespace Database\Seeders;

use ClassicO\NovaMediaLibrary\Core\Model as MediaModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Class MediaSeeder.
 */
class MediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedFolder('/defaults/');
        $this->seedFolder('/categories/');
    }

    /**
     * @param string $folder
     *
     * @return void
     */
    public function seedFolder(string $folder)
    {
        foreach (File::allFiles('./public/storage/media' . $folder) as $file) {
            MediaModel::query()
                ->create([
                    'type' => "image",
                    'name' => $file->getFilename(),
                    'title' => $file->getFilenameWithoutExtension(),
                    'folder' => $folder,
                    'private' => false,
                    'lp' => false,
                    'options' => [
                        'mime' => 'image',
                        'size' => round($file->getSize() / 1024.0, 3) . ' kb',
                    ],
                ]);
        }
    }
}
