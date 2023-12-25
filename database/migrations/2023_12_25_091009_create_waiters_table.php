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
        Schema::create('waiters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id');
            $table->string('uuid', 6)->nullable();
            $table->string('name');
            $table->string('surname');
            $table->string('phone', 25)->nullable();
            $table->string('email')->nullable();
            $table->date('birthdate')->nullable();
            $table->text('about')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['restaurant_id', 'uuid'], 'waiters_unique_UUID');
            $table->unique(['restaurant_id', 'phone'], 'waiters_unique_PHONE');
            $table->unique(['restaurant_id', 'email'], 'waiters_unique_EMAIL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('waiters');
    }
};
