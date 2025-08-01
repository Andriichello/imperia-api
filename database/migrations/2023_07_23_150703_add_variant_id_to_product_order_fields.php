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
        Schema::table('product_order_fields', function (Blueprint $table) {
            $table->unsignedBigInteger('variant_id')
                ->nullable()
                ->after('product_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_order_fields', function (Blueprint $table) {
            $table->dropColumn('variant_id');
        });
    }
};
