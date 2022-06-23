<?php

namespace Tests\Models\Morphs;

use App\Models\Morphs\Categorizable;
use App\Models\Morphs\Category;
use Tests\Models\Stubs\CategorizableStub;
use Tests\StubsTestCase;

/**
 * Class MediaTest.
 */
class MediaTest extends StubsTestCase
{
    /**
     * @var Category
     */
    protected Category $category;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()->create();
    }

    /**
     * @return void
     */
    public function testAddExistingMedia(): void
    {
        $key = '/media/defaults/dish.svg';
        $disk = 'public';

        $this->category->addMediaFromDisk($key, $disk);
    }
}
