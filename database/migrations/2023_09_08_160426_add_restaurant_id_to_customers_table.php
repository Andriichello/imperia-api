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
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('restaurant_id')
                ->after('user_id')->nullable();

            $table->foreign('restaurant_id')
                ->references('id')
                ->on('restaurants');

            $table->dropUnique('customers_unique_PHONE');
            $table->dropUnique('customers_unique_EMAIL');

            $table->unique(['restaurant_id', 'phone'], 'customers_unique_PHONE');
            $table->unique(['restaurant_id', 'email'], 'customers_unique_EMAIL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['restaurant_id']);
            $table->dropColumn('restaurant_id');

            $table->dropUnique('customers_unique_PHONE');
            $table->unique('phone', 'customers_unique_PHONE');

            $table->dropUnique('customers_unique_EMAIL');
            $table->unique('phone', 'customers_unique_EMAIL');
        });
    }
};
