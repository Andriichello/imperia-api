<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Scheduled versions: named sets of changes of a restaurant's page, which go live together
 * at one date and time. A change scheduled on its own is a version of one change.
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
        Schema::create('menu_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_id');
            // none for a change scheduled on its own: it's named after the change
            $table->string('name')->nullable();
            // draft, scheduled, inactive, applied or failed
            $table->string('status', 20)->default('draft');
            $table->dateTime('goes_live_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->dateTime('applied_at')->nullable();
            $table->dateTime('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->foreign('restaurant_id')
                ->references('id')
                ->on('restaurants')
                ->cascadeOnDelete();
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->index(['restaurant_id', 'status']);
            $table->index(['status', 'goes_live_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_versions');
    }
};
