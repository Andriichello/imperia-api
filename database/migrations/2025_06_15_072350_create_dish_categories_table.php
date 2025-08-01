<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dish_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id');
            $table->string('slug')->nullable();
            $table->string('title');
            $table->string('description')->nullable();
            $table->boolean('archived')->default(false);
            $table->integer('popularity')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();

            $table->foreign('menu_id')
                ->references('id')
                ->on('dish_menus')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dish_categories');
    }
};
