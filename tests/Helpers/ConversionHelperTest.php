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

    /**
     * Test that a copy is scaled down to the width, or so that its shorter side is the given
     * length, and never up.
     *
     * @return void
     */
    public function testScaledDown()
    {
        // 800x400
        $wide = tmpfile();
        imagejpeg(imagecreatetruecolor(800, 400), pathOf($wide));

        $helper = new ConversionHelper();
        $size = fn ($file) => array_slice(getimagesize(pathOf($file)), 0, 2);

        $this->assertSame([400, 200], $size($helper->toWebP(pathOf($wide), 70, width: 400)));
        $this->assertSame([720, 360], $size($helper->toWebP(pathOf($wide), 70, shorterSide: 360)));
        $this->assertSame([800, 400], $size($helper->toWebP(pathOf($wide), 70, width: 1200)));
    }
}
