<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Content gets translations: each value becomes JSON by language, `{"uk": "Супи"}`.
 * Existing values are put under the default language of their restaurant.
 */
return new class extends Migration
{
    /**
     * Translatable columns, with their original definitions (for reverting).
     *
     * @var array<string, array<string, array>>
     */
    protected array $columns = [
        'restaurants' => [
            'name' => ['type' => 'string', 'length' => 255, 'nullable' => false],
        ],
        'dish_menus' => [
            'title' => ['type' => 'string', 'length' => 255, 'nullable' => false],
            'description' => ['type' => 'text', 'nullable' => true],
        ],
        'dish_categories' => [
            'title' => ['type' => 'string', 'length' => 255, 'nullable' => false],
            'description' => ['type' => 'text', 'nullable' => true],
        ],
        'dishes' => [
            'title' => ['type' => 'string', 'length' => 255, 'nullable' => false],
            'description' => ['type' => 'text', 'nullable' => true],
            'badge' => ['type' => 'string', 'length' => 25, 'nullable' => true],
        ],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $locales = $this->restaurantLocales();
        $menus = DB::table('dish_menus')->pluck('restaurant_id', 'id')->all();

        foreach ($this->columns as $table => $columns) {
            // text first, so the values can be rewritten as JSON before the column becomes one
            Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                foreach (array_keys($columns) as $column) {
                    $blueprint->text($column)->nullable()->change();
                }
            });

            $this->rewrite($table, array_keys($columns), function (object $row, string $column, ?string $value) use ($table, $columns, $locales, $menus) {
                // empty values of optional columns become null, required ones stay as they are
                if ($value === null || ($value === '' && $columns[$column]['nullable'])) {
                    return null;
                }

                $restaurantId = match ($table) {
                    'restaurants' => $row->id,
                    'dish_menus' => $row->restaurant_id,
                    default => $menus[$row->menu_id] ?? null,
                };

                $locale = $locales[$restaurantId] ?? config('app.locale');

                return json_encode([$locale => $value], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            });

            Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                foreach ($columns as $column => $definition) {
                    $blueprint->json($column)->nullable($definition['nullable'])->change();
                }
            });
        }

        Schema::table('restaurants', function (Blueprint $table) {
            $table->json('address')->nullable()->after('place');
            $table->date('closed_until')->nullable()->after('timezone');
            $table->json('closed_reason')->nullable()->after('closed_until');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['address', 'closed_until', 'closed_reason']);
        });

        $locales = $this->restaurantLocales();
        $menus = DB::table('dish_menus')->pluck('restaurant_id', 'id')->all();

        foreach ($this->columns as $table => $columns) {
            Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                foreach (array_keys($columns) as $column) {
                    $blueprint->text($column)->nullable()->change();
                }
            });

            // the value in the restaurant's default language, or any other one
            $this->rewrite($table, array_keys($columns), function (object $row, string $column, ?string $value) use ($table, $locales, $menus) {
                $translations = $value ? json_decode($value, true) : null;

                if (!is_array($translations) || empty($translations)) {
                    return $value === null ? null : '';
                }

                $restaurantId = match ($table) {
                    'restaurants' => $row->id,
                    'dish_menus' => $row->restaurant_id,
                    default => $menus[$row->menu_id] ?? null,
                };

                $locale = $locales[$restaurantId] ?? config('app.locale');

                return $translations[$locale] ?? reset($translations);
            });

            Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                foreach ($columns as $column => $definition) {
                    $definition['type'] === 'string'
                        ? $blueprint->string($column, $definition['length'])->nullable($definition['nullable'])->change()
                        : $blueprint->text($column)->nullable($definition['nullable'])->change();
                }
            });
        }
    }

    /**
     * Default languages of restaurants, by their ids.
     *
     * @return array<int, string>
     */
    protected function restaurantLocales(): array
    {
        $supported = config('app.supported_locales', [config('app.locale')]);

        return DB::table('restaurants')
            ->pluck('metadata', 'id')
            ->map(function (?string $metadata) use ($supported) {
                $locale = data_get(json_decode($metadata ?? '{}', true), 'locale');

                return in_array($locale, $supported, true) ? $locale : config('app.locale');
            })
            ->all();
    }

    /**
     * Rewrite values of the columns in every row (soft-deleted ones included).
     *
     * @param string $table
     * @param string[] $columns
     * @param Closure $callback receives the row, the column and its value, returns the new value
     *
     * @return void
     */
    protected function rewrite(string $table, array $columns, Closure $callback): void
    {
        DB::table($table)
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($table, $columns, $callback) {
                foreach ($rows as $row) {
                    $values = [];

                    foreach ($columns as $column) {
                        $values[$column] = $callback($row, $column, $row->$column);
                    }

                    DB::table($table)->where('id', $row->id)->update($values);
                }
            });
    }
};
