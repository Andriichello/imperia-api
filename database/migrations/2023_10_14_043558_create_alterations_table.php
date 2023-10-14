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
        Schema::create('alterations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alterable_id');
            $table->string('alterable_type');
            $table->json('metadata');
            $table->dateTime('perform_at')->nullable();
            $table->dateTime('performed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('alterations');
    }
};
