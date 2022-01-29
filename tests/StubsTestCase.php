<?php

namespace Tests;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;

/**
 * Class StubsTestCase.
 */
abstract class StubsTestCase extends TestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createStubsTable();
    }

    /**
     * Create stubs table.
     *
     * @return void
     */
    protected function createStubsTable(): void
    {
        Schema::create('stubs', function (Blueprint $table) {
            $table->temporary();
            $table->increments('id');
            $table->string('name')->default('Stub');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
