<?php

namespace Database\Factories;

use App\Models\BaseModel;
use App\Models\MenuVersion;
use App\Models\MenuVersionChange;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class MenuVersionChangeFactory.
 *
 * @extends Factory<MenuVersionChange>
 */
class MenuVersionChangeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<MenuVersionChange>
     */
    protected $model = MenuVersionChange::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'fields' => [],
        ];
    }

    /**
     * Change in the version.
     *
     * @param MenuVersion $version
     *
     * @return static
     */
    public function inVersion(MenuVersion $version): static
    {
        return $this->state(fn () => ['version_id' => $version->id]);
    }

    /**
     * Change of the item: its fields' live values are taken from it.
     *
     * @param BaseModel $target
     * @param array $values new values of its fields
     *
     * @return static
     */
    public function changing(BaseModel $target, array $values): static
    {
        $fields = [];

        foreach ($values as $field => $value) {
            $fields[$field] = ['live' => $target->getAttribute($field), 'new' => $value];
        }

        return $this->state(fn () => [
            'target_type' => $target->getMorphClass(),
            'target_id' => $target->getKey(),
            'fields' => $fields,
        ]);
    }
}
