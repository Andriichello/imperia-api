<?php

namespace Tests\Models\Traits;

use App\Jobs\Morph\LogIfModelChanged;
use App\Models\Morphs\Log;
use Illuminate\Support\Facades\Bus;
use Tests\Models\Stubs\LoggableStub;
use Tests\StubsTestCase;

/**
 * Class LoggableTraitTest.
 */
class LoggableTraitTest extends StubsTestCase
{
    /**
     * Instance of the tested class.
     *
     * @var LoggableStub
     */
    protected LoggableStub $instance;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = new LoggableStub();
        $this->instance->saveQuietly();
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

    /**
     * Test model created event log job dispatching.
     *
     * @return void
     */
    public function testModelCreatedLogJobDispatching()
    {
        $instance = new LoggableStub([
            'name' => 'Just a name',
            'metadata' => '{}',
        ]);

        Bus::fake();
        $instance->save();
        Bus::assertDispatched(LogIfModelChanged::class);
    }

    /**
     * Test model updated event log job dispatching.
     *
     * @return void
     */
    public function testModelUpdatedLogJobDispatching()
    {
        Bus::fake();
        $this->instance->update(['name' => 'New name']);
        Bus::assertDispatched(LogIfModelChanged::class);

        $this->instance->update(['metadata' => '{"key": "value"}']);
        Bus::assertDispatched(LogIfModelChanged::class);
    }

    /**
     * Test model created and updated events logging.
     *
     * @return void
     */
    public function testModelLogging()
    {
        $instance = new LoggableStub([
            'name' => 'Just a name',
            'metadata' => '{}',
        ]);
        $instance->save();
        $this->assertEquals(1, $instance->logs()->count());

        /** @var Log $log */
        $log = $instance->logs()->first();
        $this->assertEquals('created', $log->title);
        $this->assertEquals($instance->name, $log->getFromJson('metadata', 'name'));
        $this->assertNull($log->getFromJson('metadata', 'metadata'));

        $instance->update(['name' => 'New name']);
        $this->assertEquals(2, $instance->logs()->count());

        $instance = $instance->fresh();
        /** @var Log $log */
        $log = $instance->logs()->offset(1)->first();
        $this->assertEquals($instance->name, $log->getFromJson('metadata', 'name'));

        if (!in_array('metadata', $instance->getLogFields())) {
            $this->instance->update(['metadata' => '{"key": "value"}']);
            $this->assertEquals(2, $instance->logs()->count());
        }
    }
}
