<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Snapshots of the dishes guests see on a restaurant's pages: a gzipped JSON file of each language,
 * named after its content (it never changes, so it's cached for long), which is built once after
 * something on the pages changed. The restaurant's content version goes up on every such change.
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
        Schema::table('restaurants', function (Blueprint $table) {
            $table->unsignedInteger('content_version')->default(1);
        });

        Schema::create('menu_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->cascadeOnDelete();
            $table->string('locale', 8);
            // the restaurant's content version it was built of
            $table->unsignedInteger('content_version');
            $table->string('path');
            // of the JSON (the file is named after it)
            $table->char('hash', 40);
            $table->unsignedInteger('dish_count');
            $table->unsignedInteger('size');
            $table->unsignedInteger('gzip_size');
            $table->unsignedInteger('built_ms');
            $table->timestamp('created_at')->nullable();

            $table->unique(['restaurant_id', 'locale', 'content_version']);
            $table->index('path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_snapshots');

        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('content_version');
        });
    }
};
