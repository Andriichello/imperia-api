<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Scheduled changes of menus, categories, dishes and sizes are versions now: each pending one
 * becomes a version of one change, at its time. Texts were changed in the restaurant's default
 * language, so the other languages stay as they are. Attributes, which versions don't change
 * (e.g. the order), are left out. Changes of the old menu stay where they are.
 */
return new class extends Migration
{
    /**
     * Fields versions change, by the morph type of the model: `[table, kinds by field]`.
     *
     * @var array<string, array{0: string, 1: array<string, string>}>
     */
    protected array $types = [
        'dish-menus' => ['dish_menus', [
            'title' => 'text', 'description' => 'text', 'is_hidden' => 'bool', 'archived' => 'bool',
        ]],
        'dish-categories' => ['dish_categories', [
            'title' => 'text', 'description' => 'text', 'is_hidden' => 'bool', 'archived' => 'bool',
        ]],
        'dishes' => ['dishes', [
            'title' => 'text', 'description' => 'text', 'badge' => 'text', 'is_hidden' => 'bool',
            'archived' => 'bool', 'flags' => 'flags',
        ]],
        'dish-variants' => ['dish_variants', [
            'price' => 'price', 'weight' => 'weight', 'weight_unit' => 'value', 'calories' => 'number',
            'preparation_time' => 'number', 'is_hidden' => 'bool', 'archived' => 'bool',
        ]],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $alterations = DB::table('alterations')
            ->whereIn('alterable_type', array_keys($this->types))
            ->whereNull('performed_at')
            ->whereNull('failed_at')
            ->orderBy('id')
            ->get();

        foreach ($alterations as $alteration) {
            [$table, $kinds] = $this->types[$alteration->alterable_type];
            $row = DB::table($table)->where('id', $alteration->alterable_id)->first();
            $restaurantId = $row ? $this->restaurantId($alteration->alterable_type, $row) : null;

            // changes of deleted models would only fail
            if (!$row || !$restaurantId) {
                continue;
            }

            $metadata = json_decode($alteration->metadata ?? '{}', true) ?: [];
            $locale = $this->locale($restaurantId);
            $fields = [];

            foreach (Arr::only($metadata, array_keys($kinds)) as $field => $value) {
                $live = $this->live($row, $field, $kinds[$field]);
                $new = $kinds[$field] === 'text'
                    ? array_merge($live ?? [], [$locale => $this->value($value)])
                    : $this->normalize($kinds[$field], $value);

                if ($new !== $live) {
                    $fields[$field] = ['live' => $live, 'new' => $new];
                }
            }

            if ($fields) {
                $versionId = DB::table('menu_versions')->insertGetId([
                    'restaurant_id' => $restaurantId,
                    'status' => 'scheduled',
                    // as soon as possible, when it had no time
                    'goes_live_at' => $alteration->perform_at ?? Carbon::now(),
                    'created_at' => $alteration->created_at ?? Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                DB::table('menu_version_changes')->insert([
                    'version_id' => $versionId,
                    'target_type' => $alteration->alterable_type,
                    'target_id' => $alteration->alterable_id,
                    'fields' => json_encode($fields, JSON_UNESCAPED_UNICODE),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            DB::table('alterations')->where('id', $alteration->id)->delete();
        }
    }

    /**
     * Reverse the migrations: pending versions of one change become scheduled changes again
     * (texts in the default language), others are left out.
     *
     * @return void
     */
    public function down(): void
    {
        $versions = DB::table('menu_versions')
            ->whereIn('status', ['draft', 'scheduled', 'inactive', 'failed'])
            ->orderBy('id')
            ->get();

        foreach ($versions as $version) {
            $changes = DB::table('menu_version_changes')->where('version_id', $version->id)->get();
            $change = $changes->first();

            if ($changes->count() !== 1 || !$change->target_id || !isset($this->types[$change->target_type])) {
                continue;
            }

            $kinds = $this->types[$change->target_type][1];
            $locale = $this->locale($version->restaurant_id);
            $metadata = [];

            foreach (json_decode($change->fields, true) ?: [] as $field => $values) {
                if (isset($kinds[$field])) {
                    $metadata[$field] = $kinds[$field] === 'text' ? ($values['new'][$locale] ?? null) : $values['new'];
                }
            }

            if ($metadata) {
                DB::table('alterations')->insert([
                    'restaurant_id' => $version->restaurant_id,
                    'alterable_type' => $change->target_type,
                    'alterable_id' => $change->target_id,
                    'metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE),
                    'perform_at' => $version->goes_live_at,
                    'created_at' => $version->created_at,
                    'updated_at' => Carbon::now(),
                ]);
            }

            DB::table('menu_versions')->where('id', $version->id)->delete();
        }
    }

    /**
     * Restaurant of the model's row.
     *
     * @param string $type
     * @param object $row
     *
     * @return int|null
     */
    protected function restaurantId(string $type, object $row): ?int
    {
        $menuId = match ($type) {
            'dish-menus' => $row->id,
            'dish-variants' => DB::table('dishes')->where('id', $row->dish_id)->value('menu_id'),
            default => $row->menu_id,
        };

        $restaurantId = DB::table('dish_menus')->where('id', $menuId)->value('restaurant_id');

        return $restaurantId ? (int) $restaurantId : null;
    }

    /**
     * Default content language of the restaurant.
     *
     * @param int $restaurantId
     *
     * @return string
     */
    protected function locale(int $restaurantId): string
    {
        $metadata = json_decode(DB::table('restaurants')->where('id', $restaurantId)->value('metadata') ?? '{}', true);
        $supported = config('app.supported_locales', [config('app.locale')]);

        return in_array($metadata['locale'] ?? null, $supported, true) ? $metadata['locale'] : config('app.locale');
    }

    /**
     * Live value of the field.
     *
     * @param object $row
     * @param string $field
     * @param string $kind
     *
     * @return mixed
     */
    protected function live(object $row, string $field, string $kind): mixed
    {
        if ($kind === 'text') {
            $texts = json_decode($row->$field ?? 'null', true);
            $result = [];

            foreach (config('app.supported_locales', [config('app.locale')]) as $locale) {
                $result[$locale] = $this->value(is_array($texts) ? ($texts[$locale] ?? null) : null);
            }

            return $result;
        }

        if ($kind === 'flags') {
            return $this->normalize($kind, (json_decode($row->metadata ?? '{}', true) ?: [])['flags'] ?? []);
        }

        return $this->normalize($kind, $row->$field ?? null);
    }

    /**
     * Normalize a value of the kind (like the versions do).
     *
     * @param string $kind
     * @param mixed $value
     *
     * @return mixed
     */
    protected function normalize(string $kind, mixed $value): mixed
    {
        return match ($kind) {
            'price' => is_numeric($value) ? round((float) $value, 2) : null,
            'weight' => is_numeric($value) ? (string) ($value + 0) : null,
            'number' => is_numeric($value) ? (int) $value : null,
            'bool' => (bool) $value,
            'flags' => $this->flags($value),
            default => $this->value($value),
        };
    }

    /**
     * Flags without repetitions, in a stable order.
     *
     * @param mixed $value
     *
     * @return string[]
     */
    protected function flags(mixed $value): array
    {
        $flags = array_values(array_unique(array_map('strval', is_array($value) ? $value : [])));
        sort($flags);

        return $flags;
    }

    /**
     * A trimmed string, empty ones are null.
     *
     * @param mixed $value
     *
     * @return string|null
     */
    protected function value(mixed $value): ?string
    {
        $value = is_scalar($value) ? trim((string) $value) : '';

        return $value === '' ? null : $value;
    }
};
