<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banquet_order', function (Blueprint $table) {
            $table->unsignedBigInteger('banquet_id')->index();
            $table->unsignedBigInteger('order_id')->index();

            $table->unique(['order_id']);
            $table->primary(['banquet_id', 'order_id']);

            $table->foreign('banquet_id')->references('id')->on('banquets')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('banquet_order');
    }
};
