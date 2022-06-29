<?php

namespace Database\Factories\Morphs;

use App\Models\Morphs\Media;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class MediaFactory.
 *
 * @method Media|Collection create($attributes = [], ?Model $parent = null)
 */
class MediaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Media::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->title();
        $extension = 'svg';

        return [
            'name' => $title . '.' . $extension,
            'extension' => $extension ,
            'title' => $title,
            'description' => $this->faker->sentence(),
            'disk' => 'public',
            'folder' => '/',
        ];
    }

    /**
     * Indicate disk.
     *
     * @param string $disk
     *
     * @return static
     * @throws Exception
     */
    public function withDisk(string $disk): static
    {
        return $this->state(
            function (array $attributes) use ($disk) {
                $attributes['disk'] = $disk;
                return $attributes;
            }
        );
    }

    /**
     * Indicate folder.
     *
     * @param string $folder
     *
     * @return static
     * @throws Exception
     */
    public function withFolder(string $folder): static
    {
        return $this->state(
            function (array $attributes) use ($folder) {
                $attributes['folder'] = $folder;
                return $attributes;
            }
        );
    }
}
