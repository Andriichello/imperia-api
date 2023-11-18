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
    public function up(): void
    {
        Schema::table('product_order_fields', function (Blueprint $table) {
            $table->string('batch', 4)
                ->after('amount')
                ->nullable();

            $table->dropForeign(['order_id']);
            $table->dropForeign(['product_id']);

            $table->dropUnique(['order_id', 'product_id', 'variant_id']);

            $table->unique(['order_id', 'product_id', 'variant_id', 'batch']);

            $table->foreign('order_id')
                ->references('id')->on('orders')
                ->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')->on('products');
            $table->foreign('variant_id')
                ->references('id')->on('product_variants');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('product_order_fields', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['variant_id']);

            $table->dropUnique(['order_id', 'product_id', 'variant_id', 'batch']);

            $table->dropColumn('batch');

            $table->unique(['order_id', 'product_id', 'variant_id']);

            $table->foreign('order_id')
                ->references('id')->on('orders')
                ->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')->on('products');
        });
    }
};
