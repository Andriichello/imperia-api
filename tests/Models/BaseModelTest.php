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
     * Test JSON field methods.
     *
     * @return void
     */
    public function testJsonFieldMethods()
    {
        $this->instance->setJson('metadata', ['key' => 'value']);

        $metadata = $this->instance->getJson('metadata');
        $this->assertIsArray($metadata);
        $this->assertNotEmpty($metadata);
        $this->assertArrayHasKey('key', $metadata);

        $this->instance->setToJson('metadata', 'title', 'something');

        $metadata = $this->instance->getJson('metadata');
        $this->assertIsArray($metadata);
        $this->assertNotEmpty($metadata);
        $this->assertArrayHasKey('key', $metadata);
        $this->assertArrayHasKey('title', $metadata);

        $this->assertEquals('value', $this->instance->getFromJson('metadata', 'key'));
        $this->assertEquals('something', $this->instance->getFromJson('metadata', 'title'));
    }

    /**
     * Test type attribute.
     *
     * @return void
     */
    public function testTypeAttribute()
    {
        $type = $this->instance->type;
        $this->assertEmpty($type);
    }
}
