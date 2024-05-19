<?php

namespace Tests\Helpers;

use App\Helpers\ConversionHelper;
use Tests\TestCase;

/**
 * Class ConversionHelperTest.
 */
class ConversionHelperTest extends TestCase
{
    /**
     * Test if jpeg converted to webp is smaller.
     *
     * @return void
     */
    public function testSmallerSize()
    {
        $path = storage_path("app/public/media/examples/margherita.jpg");

        $file = (new ConversionHelper())
            ->toWebP($path, 25);

        $original = filesize($path);
        $converted = filesize(pathOf($file));

        $this->assertLessThan($original, $converted);
    }
}
