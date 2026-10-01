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
    public function up(): void
    {
        Schema::table('dish_variants', function (Blueprint $table) {
            $table->boolean('archived')
                ->default(false)
                ->after('preparation_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('dish_variants', function (Blueprint $table) {
            $table->dropColumn('archived');
        });
    }
};
