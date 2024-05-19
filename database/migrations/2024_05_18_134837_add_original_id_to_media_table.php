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
        Schema::table('media', function (Blueprint $table) {
            $table->foreignId('original_id')
                ->after('restaurant_id')
                ->nullable();

            $table->foreign('original_id')
                ->references('id')
                ->on('media')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropForeign(['original_id']);

            $table->dropColumn('original_id');
        });
    }
};
