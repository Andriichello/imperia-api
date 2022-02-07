<?php

namespace Tests\Models;

use App\Models\BaseModel;
use Tests\TestCase;

/**
 * Class BaseModelTest.
 */
class BaseModelTest extends TestCase
{
    /**
     * Instance of the tested class.
     *
     * @var BaseModel
     */
    protected BaseModel $instance;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->instance = new BaseModel();
    }

    /**
     * Test type attribute.
     *
     * @return void
     */
    public function testTypeAttribute()
    {
        $this->assertEquals($this->instance->type, slugClass(get_class($this->instance)));
    }
}
