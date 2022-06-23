<?php

namespace Database\Seeders;

use App\Models\Morphs\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

/**
 * Class MediaSeeder.
 */
class MediaSeeder extends Seeder
{
    /**
     * @var Category
     */
    protected Category $category;

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws FileDoesNotExist|FileIsTooBig
     */
    public function run(): void
    {
        $this->category = Category::factory()
            ->create([
                'slug' => 'temporary',
                'title' => 'Temporary',
                'target' => null,
                'description' => null,
            ]);

        $this->seedFolder('/defaults/', 'defaults');
        $this->seedFolder('/categories/', 'categories');

        $this->category->delete();
    }

    /**
     * @param string $folder
     * @param string $collection
     *
     * @return void
     * @throws FileDoesNotExist|FileIsTooBig
     */
    public function seedFolder(string $folder, string $collection): void
    {
        foreach (File::allFiles('./public/storage/media' . $folder) as $file) {
            $path = '/media' . $folder . $file->getFilename();
            $this->category->addMediaFromDisk($path, 'public')
                ->preservingOriginal()
                ->toMediaCollection($collection);
        }
    }
}
