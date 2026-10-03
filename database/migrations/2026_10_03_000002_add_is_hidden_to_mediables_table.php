<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Photos can be hidden: they stay in the restaurant's or dish's gallery, but guests don't see
 * them. It's a property of the link, the file itself can be used elsewhere too.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('mediables', function (Blueprint $table) {
            $table->boolean('is_hidden')->default(false)->after('order');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('mediables', function (Blueprint $table) {
            $table->dropColumn('is_hidden');
        });
    }
};
