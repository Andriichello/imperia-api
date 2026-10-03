<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Every size of a dish becomes a variant. Until now the dish itself was its first size and its
 * variants the others, so a size could change places with the dish and lose its id. The dish's
 * own size columns stay: they mirror its first size (the cheapest one shown to guests).
 *
 * Sizes can be hidden (off the menu for a while) and archived, like dishes: until now `archived`
 * was what the admin shows as "Live", which means hidden. Scheduled changes follow the sizes.
 */
return new class extends Migration
{
    /**
     * Attributes of a size, on the dish and on its variants.
     *
     * @var string[]
     */
    protected array $size = ['price', 'weight', 'weight_unit', 'calories', 'preparation_time'];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('dish_variants', function (Blueprint $table) {
            $table->boolean('is_hidden')->default(false)->after('archived');
            $table->timestamp('archived_at')->nullable()->after('is_hidden');
        });

        DB::table('dish_variants')->update([
            'is_hidden' => DB::raw('archived'),
            'archived' => false,
        ]);

        $this->renameAlteredAttribute('archived', 'is_hidden');

        DB::table('dishes')
            ->orderBy('id')
            ->chunkById(200, function ($dishes) {
                foreach ($dishes as $dish) {
                    $variantId = DB::table('dish_variants')->insertGetId([
                        'dish_id' => $dish->id,
                        ...Arr::only((array) $dish, $this->size),
                        'created_at' => $dish->created_at ?? Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);

                    $this->moveAlteredSize('dishes', $dish->id, 'dish-variants', $variantId);
                    $this->mirrorFirstSize($dish);
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table('dishes')
            ->orderBy('id')
            ->chunkById(200, function ($dishes) {
                foreach ($dishes as $dish) {
                    // the dish mirrors its first size, which becomes the dish itself again
                    $first = $this->firstSize($dish->id);

                    if (!$first) {
                        continue;
                    }

                    $this->moveAlteredSize('dish-variants', $first->id, 'dishes', $dish->id);

                    DB::table('dish_variants')->where('id', $first->id)->delete();
                }
            });

        $this->renameAlteredAttribute('is_hidden', 'archived');

        DB::table('dish_variants')->update([
            'archived' => DB::raw('archived or is_hidden'),
        ]);

        Schema::table('dish_variants', function (Blueprint $table) {
            $table->dropColumn(['is_hidden', 'archived_at']);
        });
    }

    /**
     * The first size of the dish: its cheapest variant shown to guests.
     *
     * @param int $dishId
     *
     * @return object|null
     */
    protected function firstSize(int $dishId): ?object
    {
        return DB::table('dish_variants')
            ->where('dish_id', $dishId)
            ->where('archived', false)
            ->where('is_hidden', false)
            ->whereNull('deleted_at')
            ->orderBy('price')
            ->orderBy('id')
            ->first();
    }

    /**
     * Set the dish's own size columns to its first size, when they differ
     * (a variant may have been cheaper than the dish).
     *
     * @param object $dish
     *
     * @return void
     */
    protected function mirrorFirstSize(object $dish): void
    {
        $first = $this->firstSize($dish->id);

        if (!$first) {
            return;
        }

        $values = Arr::only((array) $first, $this->size);

        if ($values != Arr::only((array) $dish, $this->size)) {
            DB::table('dishes')->where('id', $dish->id)->update($values);
        }
    }

    /**
     * Move size attributes of pending scheduled changes from one model to another
     * (e.g. a price change of a dish becomes one of its first size).
     *
     * @param string $fromType
     * @param int $fromId
     * @param string $toType
     * @param int $toId
     *
     * @return void
     */
    protected function moveAlteredSize(string $fromType, int $fromId, string $toType, int $toId): void
    {
        $alterations = DB::table('alterations')
            ->where('alterable_type', $fromType)
            ->where('alterable_id', $fromId)
            ->whereNull('performed_at')
            ->get();

        foreach ($alterations as $alteration) {
            $metadata = json_decode($alteration->metadata ?? '{}', true);
            $sized = is_array($metadata) ? Arr::only($metadata, $this->size) : [];

            if (empty($sized)) {
                continue;
            }

            $rest = Arr::except($metadata, $this->size);

            if (empty($rest)) {
                DB::table('alterations')
                    ->where('id', $alteration->id)
                    ->update(['alterable_type' => $toType, 'alterable_id' => $toId]);

                continue;
            }

            DB::table('alterations')
                ->where('id', $alteration->id)
                ->update(['metadata' => json_encode($rest)]);

            DB::table('alterations')->insert([
                ...Arr::except((array) $alteration, ['id']),
                'alterable_type' => $toType,
                'alterable_id' => $toId,
                'metadata' => json_encode($sized),
            ]);
        }
    }

    /**
     * Rename the attribute in scheduled changes of variants.
     *
     * @param string $from
     * @param string $to
     *
     * @return void
     */
    protected function renameAlteredAttribute(string $from, string $to): void
    {
        DB::table('alterations')
            ->where('alterable_type', 'dish-variants')
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
