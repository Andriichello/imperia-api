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
            $table->text('description')
                ->nullable()->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->text('description')
                ->nullable()->change();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->text('description')
                ->nullable()->change();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->text('description')
                ->nullable()->change();
        });

        Schema::table('spaces', function (Blueprint $table) {
            $table->text('description')
                ->nullable()->change();
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
            $table->string('description')
                ->nullable()->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('description')
                ->nullable()->change();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->string('description')
                ->nullable()->change();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('description')
                ->nullable()->change();
        });

        Schema::table('spaces', function (Blueprint $table) {
            $table->string('description')
                ->nullable()->change();
        });
    }
};
