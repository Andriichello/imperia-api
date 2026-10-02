<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Special days: holidays and short days, which override the weekly schedules
 * from `starts_on` till `ends_on` (both included).
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
        Schema::create('schedule_exceptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_id');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->boolean('is_closed')->default(false);
            $table->unsignedTinyInteger('beg_hour')->nullable();
            $table->unsignedTinyInteger('beg_minute')->nullable();
            $table->unsignedTinyInteger('end_hour')->nullable();
            $table->unsignedTinyInteger('end_minute')->nullable();
            $table->json('reason')->nullable();
            $table->timestamps();

            $table->foreign('restaurant_id')
                ->references('id')
                ->on('restaurants')
                ->cascadeOnDelete();
            $table->index(['restaurant_id', 'starts_on']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_exceptions');
    }
};
