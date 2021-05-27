<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImperiaUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imperia_users', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('role_id')->index('role_id');
            $table->string('name', 50)->unique('users_unique_NAME');
            $table->string('password', 50);
            $table->string('api_token', 64);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->dateTime('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('imperia_users');
    }
}
