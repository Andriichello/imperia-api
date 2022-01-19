<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('name', 50);
            $table->string('description', 100)->nullable();
            $table->decimal('price')->unsigned();
            $table->unsignedSmallInteger('category_id')->index('category_id');
            $table->unsignedInteger('period_id')->nullable()->index('period_id');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['name', 'category_id'], 'tickets_unique_NAME_in_CATEGORY');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
}
