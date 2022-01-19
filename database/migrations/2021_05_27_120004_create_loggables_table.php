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
        Schema::create('loggables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('log_id');
            $table->unsignedBigInteger('loggable_id');
            $table->unsignedBigInteger('loggable_type');
            $table->timestamps();

            $table->unique(['log_id', 'loggable_id', 'loggable_type'], 'loggable_ids_type_unique');

            $table->foreign('log_id')
                ->references('id')->on('logs')
                ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loggables');
    }
};
