<?php

namespace NSWDPC\ProgressiveImage\Tests;

use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use Silverstripe\Assets\Dev\TestAssetStore;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Image;
use SilverStripe\Assets\Storage\DBFile;
use SilverStripe\Forms\LiteralField;
use SilverStripe\View\Requirements;

/**
 * Unit test to verify custom thumnbnails
 * @author James
 */
class ProgressiveImageTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected static $fixture_file = 'ProgressiveImageTest.yml';

    public function setUp()
    {
        parent::setUp();
        TestAssetStore::activate('data');
        $images = Image::get()->exclude(['ClassName' =>[ Folder::class ] ]);
        foreach ($images as $image) {
            $source_path = __DIR__ . '/data/' . $image->Name;
            $image->setFromLocalFile($source_path, $image->Filename);
            $image->write();
        }
    }

    public function testCreateThumb()
    {
        $image = $this->objFromFixture(Image::class, 'image1');

        $this->assertTrue($image instanceof Image, "Image is not the correct type");

        $width = $image->getWidth();
        $height = $image->getHeight();

        $this->assertEquals(1920, $width);
        $this->assertEquals(1280, $height);

        $pad = $image->ProgressivePad(100, 100, 'FF0000', 0, 90, true);
        $pad_final = $image->Pad(100, 100, 'FF0000', 0);
        $pad_final_link =$pad_final->Link();

        $fill = $image->ProgressiveFill(100, 100, 90, true);
        $fill_final = $image->Fill(100, 100);
        $fill_final_link =$fill_final->Link();

        $scale_width = $image->ProgressiveScaleWidth(100, 90, true);
        $scale_width_final = $image->ScaleWidth(100);
        $scale_width_final_link = $scale_width_final->Link();

        $this->assertTrue(strpos($fill, "data-final=\"{$fill_final_link}\"") !== false, "Final Link not in image ProgressiveFill tag");
        $this->assertTrue(strpos($pad, "data-final=\"{$pad_final_link}\"") !== false, "Final Link not in image ProgressivePad tag");
        $this->assertTrue(strpos($scale_width, "data-final=\"{$scale_width_final_link}\"") !== false, "Final Link not in image ProgressiveScaleWidth tag");
    }

    public function testFocusPoint()
    {
        $image = $this->objFromFixture(Image::class, 'image1');
        $image->resetRequirementsCompleted();

        $this->assertTrue($image instanceof Image, "Image is not the correct type");

        $width = $image->getWidth();
        $height = $image->getHeight();

        $this->assertEquals(1920, $width);
        $this->assertEquals(1280, $height);

        $focusFill = $image->ProgressiveFocusFill(1800, 1400, 90, true);
        $focusFillFinal = $image->FocusFill(1800, 1400);
        $focusFillFinalLink = $focusFillFinal->Link();
        $this->assertTrue(strpos($focusFill, "data-final=\"{$focusFillFinalLink}\"") !== false, "Final Link not in image ProgressiveFocusFill tag");

        // will upscale height to 1400
        $this->assertEquals(1800, $focusFillFinal->getWidth());
        $this->assertEquals(1400, $focusFillFinal->getHeight());

        $focusFillMax = $image->ProgressiveFocusFillMax(1800, 1400, 90, true);
        $focusFillMaxFinal = $image->FocusFillMax(1800, 1400);
        $focusFillMaxFinalLink = $focusFillMaxFinal->Link();

        // will retain height at 1280, downscaling width
        $this->assertEquals(1646, $focusFillMaxFinal->getWidth());
        $this->assertEquals(1280, $focusFillMaxFinal->getHeight());
        $this->assertTrue(strpos($focusFillMax, "data-final=\"{$focusFillMaxFinalLink}\"") !== false, "Final Link not in image ProgressiveFocusFillMax tag");
    }

    public function testBackend()
    {
        $image = $this->objFromFixture(Image::class, 'image1');
        $image->resetRequirementsCompleted();

        $tag = $image->ProgressiveScaleWidth(100, 90, true);

        $backend = Requirements::backend();
        $js = $backend->getJavascript();

        $css = $backend->getCSS();

        // expected sha256 hashes
        $expected_hash_js = "sha256-8VDCn/f0G2aDnEbz+Wo0b33V44if0WaKA1EOwnDG0tM=";
        $expected_hash_css  = "sha256-WrXqmkKGIbIcuZIbIkCqHwMooOXuNf6c1ImeMSYW/B4=";

        $item = current($js);
        $this->assertEquals($expected_hash_js, $item['integrity'], "Script integrity mismatch");

        $item = current($css);
        $this->assertEquals($expected_hash_css, $item['integrity'], "CSS integrity mismatch");
    }
}
