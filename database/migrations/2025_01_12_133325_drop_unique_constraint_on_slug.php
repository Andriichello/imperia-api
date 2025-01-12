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
        Schema::table('menus', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });

        Schema::table('spaces', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->unique(['slug']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->unique(['slug']);
        });

        Schema::table('spaces', function (Blueprint $table) {
            $table->unique(['slug']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unique(['slug']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->unique(['slug']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->unique(['slug']);
        });
    }
};
