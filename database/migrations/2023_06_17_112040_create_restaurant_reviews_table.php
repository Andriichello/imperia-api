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
        Schema::create('restaurant_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id');
            $table->ipAddress('ip')->nullable();
            $table->string('reviewer');
            $table->smallInteger('score')->nullable();
            $table->string('title')->nullable();
            $table->string('description', 510)->nullable();
            $table->timestamps();

            $table->unique(['ip', 'restaurant_id']);

            $table->foreign('restaurant_id')
                ->references('id')
                ->on('restaurants')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('restaurant_reviews');
    }
};
