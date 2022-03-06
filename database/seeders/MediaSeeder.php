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
        $files = File::allFiles('./public/storage/defaults');
        foreach ($files as $file) {
            MediaModel::query()
                ->create([
                    'type' => "Image",
                    'name' => $file->getFilename(),
                    'title' => $file->getFilenameWithoutExtension(),
                    'folder' => '/defaults/',
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
