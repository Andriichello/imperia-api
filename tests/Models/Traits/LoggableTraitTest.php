<?php

namespace Tests\Models\Traits;

use App\Models\BaseModel;
use App\Models\Morphs\Log;
use App\Models\Traits\LoggableTrait;
use Tests\Models\StubModel;
use Tests\StubsTestCase;

/**
 * Class LoggableTraitTest.
 */
class LoggableTraitTest extends StubsTestCase
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

        $this->instance = new class extends StubModel {
            use LoggableTrait;
        };
        $this->instance->save();
    }

    /**
     * Test attachLogs method.
     *
     * @return void
     */
    public function testAttachLogs()
    {
        $metas = [
            [
                'title' => 'One',
                'price' => 111.1,
            ],
            [
                'title' => 'Two',
                'price' => 222.2,
            ],
        ];

        $this->instance->attachLogs($metas[0]);
        $this->assertTrue($this->instance->hasLogs());
        $this->assertEquals(1, $this->instance->logs()->count());

        /** @var Log $log */
        $log = $this->instance->logs()->first();
        $this->assertEquals($metas[0], $log->getJson('metadata'));

        $this->instance->attachLogs($metas[1]);
        $this->assertTrue($this->instance->hasLogs());
        $this->assertEquals(2, $this->instance->logs()->count());

        /** @var Log $log */
        $log = $this->instance->logs()->offset(1)->first();
        $this->assertEquals($metas[1], $log->getJson('metadata'));
    }

    /**
     * Test hasLogs method.
     *
     * @return void
     */
    public function testHasLogs()
    {
        $this->assertFalse($this->instance->hasLogs());

        $metadata = [
            'title' => 'One',
            'price' => 111.1,
        ];

        $log = Log::factory()
            ->withMetadata($metadata)
            ->withModel($this->instance)
            ->create();

        $this->assertEquals($metadata, $log->getJson('metadata'));

        $this->assertTrue($this->instance->hasLogs());
        $this->assertEquals(1, $this->instance->logs()->count());
        $this->assertEquals($metadata, $this->instance->logs->first()->getJson('metadata'));
    }
}
