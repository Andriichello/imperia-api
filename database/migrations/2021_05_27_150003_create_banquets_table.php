<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banquets', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('state_id');
            $table->unsignedBigInteger('creator_id');
            $table->unsignedBigInteger('customer_id');
            $table->string('title', 50);
            $table->string('description')->nullable();
            $table->decimal('advance_amount')->unsigned();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('state_id')
                ->references('id')->on('banquet_states');
            $table->foreign('creator_id')
                ->references('id')->on('users');
            $table->foreign('customer_id')
                ->references('id')->on('customers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('banquets');
    }
};
