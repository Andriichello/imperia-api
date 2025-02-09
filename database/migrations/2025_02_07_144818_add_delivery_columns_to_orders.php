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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('slug', 10)
                ->nullable()
                ->after('state');

            $table->string('recipient', 100)
                ->nullable()
                ->after('slug');

            $table->string('phone', 25)
                ->nullable()
                ->after('recipient');

            $table->string('address', 255)
                ->nullable()
                ->after('phone');

            $table->time('delivery_time')
                ->nullable()
                ->after('address');

            $table->date('delivery_date')
                ->nullable()
                ->after('delivery_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('slug');
            $table->dropColumn('recipient');
            $table->dropColumn('phone');
            $table->dropColumn('address');
            $table->dropColumn('delivery_time');
            $table->dropColumn('delivery_date');
        });
    }
};
