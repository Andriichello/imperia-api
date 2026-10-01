<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables, which have the `description` column.
     *
     * @var array
     */
    protected array $tables = [
        'dish_menus',
        'dish_categories',
        'dishes',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->text('description')
                    ->nullable()
                    ->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * Descriptions longer than 255 characters would have to be truncated first.
     *
     * @return void
     */
    public function down(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->string('description')
                    ->nullable()
                    ->change();
            });
        }
    }
};
