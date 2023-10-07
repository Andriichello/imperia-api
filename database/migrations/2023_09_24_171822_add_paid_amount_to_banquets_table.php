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
        Schema::table('banquets', function (Blueprint $table) {
            $table->decimal('paid_amount')->unsigned()
                ->nullable()
                ->after('advance_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('banquets', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
        });
    }
};
