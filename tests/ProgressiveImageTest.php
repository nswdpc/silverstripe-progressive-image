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

    public function setUp() {
        parent::setUp();
        TestAssetStore::activate('data');
        $images = Image::get()->exclude(['ClassName' =>[ Folder::class ] ]);
        foreach ($images as $image) {
            $source_path = __DIR__ . '/data/' . $image->Name;
            $image->setFromLocalFile($source_path, $image->Filename);
            $image->write();
        }
    }

    public function testCreateThumb() {

        $image = $this->objFromFixture(Image::class, 'image1');

        $this->assertTrue($image instanceof Image, "Image is not the correct type");

        $pad = $image->ProgressivePad(100, 100, 'FF0000', 0, 90, true);
        $pad_final = $image->Pad(100, 100, 'FF0000', 0);
        $pad_final_link =$pad_final->Link();

        $fill = $image->ProgressiveFill(100, 100, 90, true);
        $fill_final = $image->Fill(100, 100);
        $fill_final_link =$fill_final->Link();

        $scale_width = $image->ProgressiveScaleWidth(100, 90, true);
        $scale_width_final = $image->ScaleWidth(100);
        $scale_width_final_link = $scale_width_final->Link();

        $this->assertTrue( strpos($fill, "data-final=\"{$fill_final_link}\"") !== false, "Final Link not in image ProgressiveFill tag" );
        $this->assertTrue( strpos($pad, "data-final=\"{$pad_final_link}\"") !== false, "Final Link not in image ProgressivePad tag" );
        $this->assertTrue( strpos($scale_width, "data-final=\"{$scale_width_final_link}\"") !== false, "Final Link not in image ProgressiveScaleWidth tag" );

        $backend = Requirements::backend();
        $js = $backend->getJavascript();
        $css = $backend->getCSS();

        // expected sha512 hashes
        $expected_hash_js = "sha512-ZWQxNTZhNTZjMGZhYTE2MmI3YzcyMmYxYTBhMmNmMjljMTU0NDM1ZDQxNDdmYjk3NjA1ZjQ3OTc1ZTMwOWEyYzc3ZTNiOWY4MjM5NmQxZmRlNGUxNzdjMWQyMzJlMWY5NDFmMmZjMWVmNmFlMjdlZmY2MWZiZTQ1YmU1MjZjZmU=";
        $expected_hash_css  = "sha512-MjViZDRkYWY2NjQyNTAyYzQ3NTZmMDcxYTdkMjIzNjNkZjI0OGRiMmNkMWYyODhhMTRmNmUxOTkxMTg2OGIzZTJkNzA2MmY5ZDZlM2ZjOGVmZWY0NWQ4NzRkNTM4YjcyYTM4ODJjYmU5NTdhMTJhYTBkZjhkODIwZjNjMDJiNzE=";

        $item = current($js);
        $this->assertEquals($expected_hash_js, $item['integrity'], "Script integrity mismatch");

        $item = current($css);
        $this->assertEquals($expected_hash_css, $item['integrity'], "CSS integrity mismatch");


    }

}
