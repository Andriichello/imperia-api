<?php

namespace Tests\Models\Traits;

use App\Models\BaseModel;
use App\Models\Traits\JsonFieldTrait;
use Tests\TestCase;

/**
 * Class JsonFieldTraitTest.
 */
class JsonFieldTraitTest extends TestCase
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

        $this->instance = new class extends BaseModel {
            use JsonFieldTrait;
        };
    }


    /**
     * Test JSON field getters and setters.
     *
     * @return void
     */
    public function testGettersAndSetters()
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
}
