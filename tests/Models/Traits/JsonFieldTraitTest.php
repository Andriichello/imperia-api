<?php

namespace Tests\Models\Traits;

use Tests\Models\Stubs\BaseStub;
use Tests\StubsTestCase;

/**
 * Class JsonFieldTraitTest.
 */
class JsonFieldTraitTest extends StubsTestCase
{
    /**
     * Instance of the tested class.
     *
     * @var BaseStub
     */
    protected BaseStub $instance;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = new BaseStub([
            'name' => 'Stub',
            'metadata' => '{}',
        ]);
        $this->instance->save();
    }


    /**
     * Test JSON field getters and setters.
     *
     * @return void
     */
    public function testGettersAndSetters()
    {
        $this->instance->setJson('metadata', ['key' => 'value']);
        $this->instance->save();

        $this->instance = $this->instance->fresh();
        $metadata = $this->instance->getJson('metadata');
        $this->assertIsArray($metadata);
        $this->assertNotEmpty($metadata);
        $this->assertArrayHasKey('key', $metadata);

        $this->instance->setToJson('metadata', 'title', 'something');
        $this->instance->save();

        $this->instance = $this->instance->fresh();
        $metadata = $this->instance->getJson('metadata');
        $this->assertIsArray($metadata);
        $this->assertNotEmpty($metadata);
        $this->assertArrayHasKey('key', $metadata);
        $this->assertArrayHasKey('title', $metadata);

        $this->assertEquals('value', $this->instance->getFromJson('metadata', 'key'));
        $this->assertEquals('something', $this->instance->getFromJson('metadata', 'title'));
    }
}
