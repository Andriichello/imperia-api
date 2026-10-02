<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Notes get their own table, so each one can be ordered, hidden and translated.
 * Until now they were a list of strings in the restaurant's metadata.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('restaurant_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_id');
            $table->json('text');
            $table->boolean('is_hidden')->default(false);
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();

            $table->foreign('restaurant_id')
                ->references('id')
                ->on('restaurants')
                ->cascadeOnDelete();
            $table->index(['restaurant_id', 'order']);
        });

        $supported = config('app.supported_locales', [config('app.locale')]);

        foreach (DB::table('restaurants')->get(['id', 'metadata']) as $restaurant) {
            $metadata = json_decode($restaurant->metadata ?? '{}', true) ?: [];
            $notes = array_values(array_filter(
                (array) ($metadata['notes'] ?? []),
                fn ($note) => is_string($note) && trim($note) !== '',
            ));

            $locale = in_array($metadata['locale'] ?? null, $supported, true)
                ? $metadata['locale'] : config('app.locale');

            foreach ($notes as $order => $note) {
                DB::table('restaurant_notes')->insert([
                    'restaurant_id' => $restaurant->id,
                    'text' => json_encode([$locale => $note], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    // numbered from 1, like the admin's sortable lists do
                    'order' => $order + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (array_key_exists('notes', $metadata)) {
                unset($metadata['notes']);

                DB::table('restaurants')
                    ->where('id', $restaurant->id)
                    ->update(['metadata' => json_encode($metadata)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        $supported = config('app.supported_locales', [config('app.locale')]);

        foreach (DB::table('restaurants')->get(['id', 'metadata']) as $restaurant) {
            $metadata = json_decode($restaurant->metadata ?? '{}', true) ?: [];
            $locale = in_array($metadata['locale'] ?? null, $supported, true)
                ? $metadata['locale'] : config('app.locale');

            // the visible notes, in the restaurant's default language (or any other one)
            $metadata['notes'] = DB::table('restaurant_notes')
                ->where('restaurant_id', $restaurant->id)
                ->where('is_hidden', false)
                ->orderBy('order')
                ->pluck('text')
                ->map(function (string $text) use ($locale) {
                    $translations = json_decode($text, true) ?: [];

                    return $translations[$locale] ?? reset($translations);
                })
                ->filter()
                ->values()
                ->all();

            DB::table('restaurants')
                ->where('id', $restaurant->id)
                ->update(['metadata' => json_encode($metadata)]);
        }

        Schema::dropIfExists('restaurant_notes');
    }
};
