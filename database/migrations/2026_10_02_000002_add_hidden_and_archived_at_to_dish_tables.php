<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menus, categories and dishes can be hidden (off the site for a while, staying in the lists)
 * or archived (moved out of the lists, restorable). Until now `archived` was what the admin
 * shows as "Live", which means hidden, so those records become hidden ones. Scheduled changes
 * of it change `is_hidden` now.
 */
return new class extends Migration
{
    /**
     * Tables of the models that can be hidden.
     *
     * @var string[]
     */
    protected array $tables = ['dish_menus', 'dish_categories', 'dishes'];

    /**
     * Morph types of those models (see `MorphServiceProvider`).
     *
     * @var string[]
     */
    protected array $morphs = ['dish-menus', 'dish-categories', 'dishes'];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->boolean('is_hidden')->default(false)->after('archived');
                $blueprint->timestamp('archived_at')->nullable()->after('is_hidden');
            });

            DB::table($table)->update([
                'is_hidden' => DB::raw('archived'),
                'archived' => false,
            ]);
        }

        $this->renameAlteredAttribute('archived', 'is_hidden');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        $this->renameAlteredAttribute('is_hidden', 'archived');

        foreach ($this->tables as $table) {
            DB::table($table)->update([
                'archived' => DB::raw('archived or is_hidden'),
            ]);

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn(['is_hidden', 'archived_at']);
            });
        }
    }

    /**
     * Rename the attribute in scheduled changes of menus, categories and dishes.
     *
     * @param string $from
     * @param string $to
     *
     * @return void
     */
    protected function renameAlteredAttribute(string $from, string $to): void
    {
        DB::table('alterations')
            ->whereIn('alterable_type', $this->morphs)
            ->orderBy('id')
            ->chunkById(200, function ($alterations) use ($from, $to) {
                foreach ($alterations as $alteration) {
                    $metadata = json_decode($alteration->metadata ?? '{}', true);

                    if (!is_array($metadata) || !array_key_exists($from, $metadata)) {
                        continue;
                    }

                    $metadata[$to] = $metadata[$from];
                    unset($metadata[$from]);

                    DB::table('alterations')
                        ->where('id', $alteration->id)
                        ->update(['metadata' => json_encode($metadata)]);
                }
            });
    }
};
