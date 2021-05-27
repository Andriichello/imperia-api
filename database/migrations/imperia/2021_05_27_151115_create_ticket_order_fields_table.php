<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketOrderFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_order_fields', function (Blueprint $table) {
            $table->unsignedInteger('order_id');
            $table->unsignedSmallInteger('ticket_id')->index('ticket_id');
            $table->unsignedSmallInteger('amount');

            $table->dateTime('paid_at')->nullable();

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->dateTime('deleted_at')->nullable();
            $table->primary(['order_id', 'ticket_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ticket_order_fields');
    }
}
