<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRestaurantIdToBanquetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('banquets', function (Blueprint $table) {
            $table->unsignedBigInteger('restaurant_id')->nullable()->after('id');

            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('banquets', function (Blueprint $table) {
            $table->dropForeign(['restaurant_id']);

            $table->dropColumn('restaurant_id');
        });
    }
}
