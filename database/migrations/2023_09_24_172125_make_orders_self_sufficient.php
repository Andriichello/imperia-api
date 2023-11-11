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
            $table->unsignedBigInteger('restaurant_id')->nullable()
                ->after('id');
            $table->unsignedBigInteger('customer_id')->nullable()
                ->after('banquet_id');
            $table->unsignedBigInteger('creator_id')->nullable()
                ->after('banquet_id');

            $table->string('state', 20)->nullable()
                ->after('customer_id');

            $table->decimal('paid_amount')->unsigned()
                ->nullable()
                ->after('state');

            $table->timestamp('paid_at')->nullable()
                ->after('paid_amount');

            $table->foreign('restaurant_id')
                ->references('id')
                ->on('restaurants');

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers');

            $table->foreign('creator_id')
                ->references('id')
                ->on('users');

            $table->foreign('banquet_id')
                ->references('id')
                ->on('banquets');
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
            $table->dropForeign(['restaurant_id']);
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['creator_id']);
            $table->dropForeign(['banquet_id']);

            $table->dropColumn('restaurant_id');
            $table->dropColumn('customer_id');
            $table->dropColumn('creator_id');

            $table->dropColumn('state');
            $table->dropColumn('paid_amount');
            $table->dropColumn('paid_at');
        });
    }
};
