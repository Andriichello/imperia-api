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
        Schema::create('dish_variants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dish_id');

            $table->decimal('price')->unsigned();
            $table->string('weight')->nullable();
            $table->string('weight_unit', 50)->nullable();
            $table->unsignedInteger('calories')->nullable();
            $table->unsignedInteger('preparation_time')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('dish_id')
                ->references('id')
                ->on('dishes')
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
        Schema::dropIfExists('dish_variants');
    }
};
