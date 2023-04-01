<?php

use App\Enums\Weekday;
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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->enum('weekday', Weekday::getValues());
            $table->unsignedTinyInteger('beg_hour');
            $table->unsignedTinyInteger('beg_minute')->default(0);
            $table->unsignedTinyInteger('end_hour');
            $table->unsignedTinyInteger('end_minute')->default(0);
            $table->unsignedBigInteger('restaurant_id')->nullable();
            $table->timestamps();

            $table->unique(['weekday', 'restaurant_id']);
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedules');
    }
};
