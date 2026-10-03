<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Changes of a scheduled version, one per changed item (a menu, category, dish, size, note or
 * the restaurant itself). Each changed field keeps its value from when the change was planned
 * (to notice, that the live one has changed since) and the new one:
 * `{"price": {"live": 185, "new": 195}, "title": {"live": {"en": "…"}, "new": {"en": "…"}}}`.
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
        Schema::create('menu_version_changes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('version_id');
            $table->string('target_type', 50);
            // none for a new item, which is created when the version goes live
            $table->unsignedBigInteger('target_id')->nullable();
            // of a new item: the category of a dish, the dish of a size, the restaurant of a note
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->json('fields');
            $table->timestamps();

            $table->foreign('version_id')
                ->references('id')
                ->on('menu_versions')
                ->cascadeOnDelete();
            $table->unique(['version_id', 'target_type', 'target_id']);
            $table->index(['target_type', 'target_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_version_changes');
    }
};
