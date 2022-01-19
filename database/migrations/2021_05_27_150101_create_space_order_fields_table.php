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
        Schema::create('space_order_fields', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('space_id')->index('space_id');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->timestamps();
            $table->softDeletes();

            $table->primary(['order_id', 'space_id']);

            $table->foreign('order_id')
                ->references('id')->on('orders');
            $table->foreign('space_id')
                ->references('id')->on('spaces');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('space_order_fields');
    }
};
