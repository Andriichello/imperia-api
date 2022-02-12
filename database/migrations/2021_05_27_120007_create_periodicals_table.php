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
        Schema::create('periodicals', function (Blueprint $table) {
            $table->unsignedBigInteger('period_id');
            $table->unsignedBigInteger('periodical_id');
            $table->string('periodical_type');
            $table->timestamps();

            $table->unique(['period_id', 'periodical_id', 'periodical_type'], 'periodicals_ids_type_unique');
            $table->foreign('period_id')->references('id')->on('periods')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('periods');
    }
};
