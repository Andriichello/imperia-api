<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpaceOrderFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('space_order_fields', function (Blueprint $table) {
            $table->unsignedInteger('order_id');
            $table->unsignedSmallInteger('space_id')->index('space_id');
            $table->dateTime('beg_datetime');
            $table->dateTime('end_datetime');

            $table->dateTime('paid_at')->nullable();

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->dateTime('deleted_at')->nullable();

            $table->primary(['order_id', 'space_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('space_order_fields');
    }
}
