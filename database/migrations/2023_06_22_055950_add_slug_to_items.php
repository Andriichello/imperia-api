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
            $table->string('slug')->unique()
                ->nullable()
                ->after('id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->unique()
                ->nullable()
                ->after('id');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->string('slug')->unique()
                ->nullable()
                ->after('id');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('slug')->unique()
                ->nullable()
                ->after('id');
        });

        Schema::table('spaces', function (Blueprint $table) {
            $table->string('slug')->unique()
                ->nullable()
                ->after('id');
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
            $table->dropColumn('slug');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
