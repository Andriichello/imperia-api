<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A day can have several opening intervals (e.g. a lunch break), so a restaurant
 * can have more than one schedule for the same weekday.
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
        // MySQL then uses this index for the restaurant's foreign key too, so it stays when reverting
        if (!Schema::hasIndex('schedules', ['restaurant_id', 'weekday'])) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->index(['restaurant_id', 'weekday']);
            });
        }

        if (Schema::hasIndex('schedules', ['weekday', 'restaurant_id'], 'unique')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->dropUnique(['weekday', 'restaurant_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // only the earliest interval of each day is kept
        $extra = DB::table('schedules')
            ->orderBy('beg_hour')
            ->orderBy('beg_minute')
            ->orderBy('id')
            ->get(['id', 'restaurant_id', 'weekday'])
            ->groupBy(fn ($schedule) => $schedule->restaurant_id . '-' . $schedule->weekday)
            ->flatMap(fn ($schedules) => $schedules->slice(1)->pluck('id'))
            ->all();

        DB::table('schedules')->whereIn('id', $extra)->delete();

        if (!Schema::hasIndex('schedules', ['weekday', 'restaurant_id'], 'unique')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->unique(['weekday', 'restaurant_id']);
            });
        }
    }
};
