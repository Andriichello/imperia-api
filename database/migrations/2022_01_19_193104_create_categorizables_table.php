<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('morph_categorizable', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('categorizable_id');
            $table->string('categorizable_type');
            $table->timestamps();

            $table->unique(['category_id', 'categorizable_id', 'categorizable_type'], 'categorizables_ids_type_unique');
            $table->foreign('category_id')->references('id')->on('morph_categories')
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('rinvex.categories.tables.categorizables'));
    }
};
