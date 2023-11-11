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
        Schema::table('holidays', function (Blueprint $table) {
            $table->foreignId('restaurant_id')
                ->after('id')
                ->nullable();

            $table->foreign('restaurant_id')
                ->references('id')
                ->on('restaurants')
                ->onDelete('cascade');
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->foreignId('restaurant_id')
                ->after('id')
                ->nullable();

            $table->foreign('restaurant_id')
                ->references('id')
                ->on('restaurants')
                ->onDelete('cascade');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('restaurant_id')
                ->after('id')
                ->nullable();

            $table->foreign('restaurant_id')
                ->references('id')
                ->on('restaurants')
                ->onDelete('cascade');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('restaurant_id')
                ->after('id')
                ->nullable();

            $table->foreign('restaurant_id')
                ->references('id')
                ->on('restaurants')
                ->onDelete('cascade');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('restaurant_id')
                ->after('id')
                ->nullable();

            $table->foreign('restaurant_id')
                ->references('id')
                ->on('restaurants')
                ->onDelete('cascade');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('restaurant_id')
                ->after('id')
                ->nullable();

            $table->foreign('restaurant_id')
                ->references('id')
                ->on('restaurants')
                ->onDelete('cascade');
        });

        Schema::table('spaces', function (Blueprint $table) {
            $table->foreignId('restaurant_id')
                ->after('id')
                ->nullable();

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
        Schema::table('holidays', function (Blueprint $table) {
            $table->dropColumn('restaurant_id');
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn('restaurant_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('restaurant_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('restaurant_id');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('restaurant_id');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('restaurant_id');
        });

        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn('restaurant_id');
        });
    }
};
