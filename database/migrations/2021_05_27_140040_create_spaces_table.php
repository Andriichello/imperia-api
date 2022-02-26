<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spaces', function (Blueprint $table) {
            $table->id();
            $table->string('title', 50)->unique('spaces_unique_TITLE');
            $table->smallInteger('number');
            $table->smallInteger('floor');
            $table->string('description')->nullable();
            $table->decimal('price')->unsigned();
            $table->boolean('archived')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['title', 'number', 'floor'], 'spaces_unique_TITLE_and_NUMBER_and_FLOOR');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spaces');
    }
};
