<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When the restaurant's page (its details, hours, menus, ...) was last saved, and by whom
 * ("Last saved today at 14:32 by Anna" on the admin's dashboard).
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
        Schema::table('restaurants', function (Blueprint $table) {
            $table->timestamp('last_saved_at')->nullable()->after('closed_reason');
            $table->unsignedBigInteger('last_saved_by')->nullable()->after('last_saved_at');

            $table->foreign('last_saved_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropForeign(['last_saved_by']);
            $table->dropColumn(['last_saved_at', 'last_saved_by']);
        });
    }
};
