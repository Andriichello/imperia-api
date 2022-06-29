<?php

namespace Database\Seeders;

use App\Helpers\Interfaces\MediaHelperInterface;
use App\Helpers\MediaHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Class MediaSeeder.
 */
class MediaSeeder extends Seeder
{
    /**
     * @var MediaHelperInterface
     */
    protected MediaHelperInterface $helper;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->helper = new MediaHelper();

        $this->seedFolder('/defaults/');
        $this->seedFolder('/categories/');
    }

    /**
     * @param string $folder
     *
     * @return void
     */
    public function seedFolder(string $folder): void
    {
        foreach (File::allFiles('./public/storage/media' . $folder) as $file) {
            $path = '/media' . $folder . $file->getFilename();

            $media = $this->helper->existing($path, 'public');
            $media->title = ucfirst($file->getFilenameWithoutExtension());

            $media->save();
        }
    }
}
