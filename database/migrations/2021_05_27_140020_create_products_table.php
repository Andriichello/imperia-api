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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title', 50)->unique('products_unique_TITLE');
            $table->string('description')->nullable();
            $table->decimal('price')->unsigned();
            $table->decimal('weight')->unsigned()->nullable();
            $table->unsignedBigInteger('menu_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('menu_id')
                ->references('id')->on('imperia_menus');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
