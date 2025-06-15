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
        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('slug')->nullable();
            $table->string('title');
            $table->string('description')->nullable();
            $table->decimal('price')->unsigned();
            $table->string('weight')->nullable();
            $table->string('weight_unit', 50)->nullable();
            $table->unsignedInteger('calories')->nullable();
            $table->unsignedInteger('preparation_time')->nullable();
            $table->boolean('archived')->default(false);
            $table->integer('popularity')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['menu_id', 'category_id']);

            $table->foreign('menu_id')
                ->references('id')
                ->on('dish_menus')
                ->onDelete('cascade');

            $table->foreign('category_id')
                ->references('id')
                ->on('dish_categories')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dishes');
    }
};
