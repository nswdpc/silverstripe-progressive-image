<?php

namespace NSWDPC\ProgressiveImage;

use Silverstripe\Assets\Image;
use Silverstripe\Core\Config\Config;
use Silverstripe\Core\Extension;
use SilverStripe\View\ArrayData;
use SilverStripe\View\ViewableData;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\View\Requirements;

/**
 * Extension provides methods to aid in progressive image loading techniques
 * e.g by being able to define quality at request time
 * @author James Ellis <james.ellis@dpc.nsw.gov.au>
 */
class ProgressiveImageExtension extends Extension
{

    /**
     * @var int
     * Store default for reset post image processing
     */
    private $default_quality = 80;

    private $_cache_filter_style = null;

    private static $requirements_completed = false;

    /**
     * Returns the current image backend
     */
    private function getBackend()
    {
        $backend = $this->owner->getImageBackend();
        return $backend;
    }

    /**
     * Gets a default quality on the backend
     */
    private function getDefaultQuality()
    {
        $backend = $this->getBackend();
        return $backend->getQuality();
    }

    /**
     * Sets a default quality on the backend
     */
    private function setDefaultQuality()
    {
        $backend = $this->getBackend();
        $backend->setQuality($this->default_quality);
    }

    /**
     * Set backend quality
     * @param int $quality quality 0-100
     */
    private function setQuality($quality)
    {
        if (!$quality || $quality <= 0 || $quality >= 100) {
            $quality = $this->getDefaultQuality();
        }

        $this->default_quality = $this->getDefaultQuality();// store the default quality set in config
        // set the new quality
        $backend = $this->getBackend();
        $backend->setQuality((int) $quality);
        return $quality;
    }

    /**
     * Reset quality in config to previously stored value
     */
    private function resetQuality()
    {
        $this->setDefaultQuality();
    }

    /**
     * Get a filter style applied to the tiny URL image
     */
    private function getFilterStyle()
    {
        if (!is_null($this->_cache_filter_style)) {
            return $this->_cache_filter_style;
        }
        $this->_cache_filter_style = $this->owner->config()->get('pil_filter_style');
        return $this->_cache_filter_style;
    }

    /**
     * Returns the tag used to load the image, with container and padding block
     * @param ViewableData $image
     * @param int $width
     * @param int $height
     * @param ViewableData $tiny a version of $image that is tiny
     */
    public function getProgressiveTag(ViewableData $image, $width = null, $height = null, ViewableData $tiny)
    {
        if (!$width && !$height) {
            return null;
        }

        $final_url = $image->getURL();
        if ($height >  0 && $width > 0) {
            $padding_value = round(($height / $width) * 100, 2);
        } else {
            $padding_value = 66.6;
        }
        $alt = $this->owner->getTitle();
        $tiny_url = $tiny->getURL();
        $data = [
            'PaddingValue' => $padding_value,
            'AlternateText' => $alt,
            'FinalURL' => $final_url,
            'TinyURL' => $tiny_url,
            'Style' => $this->getFilterStyle()
        ];

        // push data into image
        $tag = ArrayData::create($data)->renderWith('NSWDPC/ProgressiveImage/ProgressiveImage');
        $field = DBField::create_field('HTMLText', $tag);

        // push requirements out
        $this->owner->loadProgressiveImageRequirements();

        return $field;
    }

    public static function getHashAlgo()
    {
        return "sha256";
    }

    public static function getIntegrityHash($contents)
    {
        $hash = self::getHashAlgo();
        return "{$hash}-" . base64_encode(hash($hash, $contents, true));
    }

    /**
     * Load requirements via the Requirements API
     * @return void
     */
    public function loadProgressiveImageRequirements()
    {
        if (self::$requirements_completed) {
            return;
        }

        $css = self::get_progressive_image_style();
        $css_base64 = base64_encode($css);
        $css_uri = 'data:text/css;charset=utf-8;base64,' . $css_base64;
        $css_integrity = self::getIntegrityHash($css);

        $script = self::get_progressive_image_script();
        $script_base64 = base64_encode($script);
        $script_uri = 'data:application/javascript;charset=utf-8;base64,' . base64_encode($script);
        $script_integrity = self::getIntegrityHash($script);

        Requirements::css(
            $css_uri,
            'screen',
            [
                'integrity' => $css_integrity,
                'crossorigin' => 'anonymous'
            ]
        );

        Requirements::javascript(
            $script_uri,
            [
                'integrity' => $script_integrity,
                'crossorigin' => 'anonymous'
            ]
        );

        self::$requirements_completed = true;
    }

    /**
     * Set self::$requirements_completed, causes requirements to be re-required
     * Used by tests
     */
    public function resetRequirementsCompleted()
    {
        self::$requirements_completed = false;
    }

    /**
     * Return custom CSS
     */
    public static function get_progressive_image_style()
    {
        $content = trim(ArrayData::create()->renderWith('NSWDPC/ProgressiveImage/Style')->forTemplate());
        return $content;
    }

    /**
     * Return custom JS
     * @param float $threshold between 0 and 0.99 (1 doesn't seem to work). Default to 0.
     * See: https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API#intersection_observer_options
     */
    public static function get_progressive_image_script(float $threshold = 0)
    {
        $data = [
            'Threshold' => $threshold
        ];
        $content = trim(ArrayData::create($data)->renderWith('NSWDPC/ProgressiveImage/Script')->forTemplate());
        return $content;
    }

    /**
     * ScaleWidth handler for progressive image loading
     * @param int $width
     * @param int $quality quality of larger image
     * @param boolean $as_tag
     */
    public function ProgressiveScaleWidth($width, $quality = 80, $as_tag = true)
    {
        $quality = $this->setQuality($quality);
        $image = $this->owner->ScaleWidth($width);
        $height = $image->getHeight();
        $backend = $this->getBackend();
        if (method_exists($backend, 'ResetFilters')) {
            $backend->ResetFilters();
        }
        $this->resetQuality($quality);
        if ($as_tag) {
            // Rendering as a tag... create the tiny version
            $tiny_width = round($width / 10);
            $tiny_height = round($height / 10);
            $tiny_quality = 1;
            $tiny = $this->ProgressiveScaleWidth($tiny_width, $tiny_quality, false);
            return $this->getProgressiveTag($image, $width, $height, $tiny);
        } else {
            return $image;
        }
    }

    /**
     * Fill to width and height with quality
     * @param int $width
     * @param int $height
     * @param int $quality quality of larger image
     * @param boolean $as_tag
     */
    public function ProgressiveFill($width, $height, $quality = 80, $as_tag = true)
    {
        $quality = $this->setQuality($quality);
        $image = $this->owner->Fill($width, $height);
        $backend = $this->getBackend();
        if (method_exists($backend, 'ResetFilters')) {
            $backend->ResetFilters();
        }
        $this->resetQuality($quality);
        if ($as_tag) {
            // Rendering as a tag... create the tiny version
            $tiny_height = round($height / 10);
            $tiny_width = round($width / 10);
            $tiny_quality = 1;
            $tiny = $this->ProgressiveFill($tiny_width, $tiny_height, $tiny_quality, false);
            return $this->getProgressiveTag($image, $width, $height, $tiny);
        } else {
            return $image;
        }
    }

    /**
     * Fill to width and height with quality, without upsampling
     * @param int $width
     * @param int $height
     * @param int $quality quality of larger image
     * @param boolean $as_tag
     */
    public function ProgressiveFillMax($width, $height, $quality = 80, $as_tag = true)
    {
        $quality = $this->setQuality($quality);
        $image = $this->owner->FillMax($width, $height);
        $backend = $this->getBackend();
        if (method_exists($backend, 'ResetFilters')) {
            $backend->ResetFilters();
        }
        $this->resetQuality($quality);
        if ($as_tag) {
            // Rendering as a tag... create the tiny version
            $tiny_height = round($height / 10);
            $tiny_width = round($width / 10);
            $tiny_quality = 1;
            $tiny = $this->ProgressiveFillMax($tiny_width, $tiny_height, $tiny_quality, false);
            return $this->getProgressiveTag($image, $width, $height, $tiny);
        } else {
            return $image;
        }
    }

    /**
     * Fill to width and height with quality, *without* upscaling, using jonom/focuspoint
     * @param int $width
     * @param int $height
     * @param int $quality quality of larger image
     * @param boolean $as_tag
     */
    public function ProgressiveFocusFillMax($width, $height, $quality = 80, $as_tag = true)
    {
        $quality = $this->setQuality($quality);
        $image = $this->owner->FocusFillMax($width, $height);
        $backend = $this->getBackend();
        if (method_exists($backend, 'ResetFilters')) {
            $backend->ResetFilters();
        }
        $this->resetQuality($quality);
        if ($as_tag) {
            // Rendering as a tag... create the tiny version
            $tiny_height = round($height / 10);
            $tiny_width = round($width / 10);
            $tiny_quality = 1;
            $tiny = $this->ProgressiveFocusFillMax($tiny_width, $tiny_height, $tiny_quality, false);
            return $this->getProgressiveTag($image, $width, $height, $tiny);
        } else {
            return $image;
        }
    }

    /**
     * Fill to width and height with quality, with upscaling, using jonom/focuspoint
     * @param int $width
     * @param int $height
     * @param int $quality quality of larger image
     * @param boolean $as_tag
     */
    public function ProgressiveFocusFill($width, $height, $quality = 80, $as_tag = true)
    {
        $quality = $this->setQuality($quality);
        $image = $this->owner->FocusFill($width, $height, true);
        $backend = $this->getBackend();
        if (method_exists($backend, 'ResetFilters')) {
            $backend->ResetFilters();
        }
        $this->resetQuality($quality);
        if ($as_tag) {
            // Rendering as a tag... create the tiny version
            $tiny_height = round($height / 10);
            $tiny_width = round($width / 10);
            $tiny_quality = 1;
            $tiny = $this->ProgressiveFocusFill($tiny_width, $tiny_height, $tiny_quality, false);
            return $this->getProgressiveTag($image, $width, $height, $tiny);
        } else {
            return $image;
        }
    }

    /**
     * Fit image to specified dimensions and fill leftover space with a solid colour (default white)
     * @param int $width
     * @param int $height
     * @param string $backgroundColor
     * @param int $transparencyPercent
     * @param int $quality quality of larger image
     * @param boolean $as_tag
     */
    public function ProgressivePad($width, $height, $backgroundColor = 'FFFFFF', $transparencyPercent = 0, $quality = 80, $as_tag = true)
    {
        $quality = $this->setQuality($quality);
        $image = $this->owner->Pad($width, $height, $backgroundColor, $transparencyPercent);
        $backend = $this->getBackend();
        if (method_exists($backend, 'ResetFilters')) {
            $backend->ResetFilters();
        }
        $this->resetQuality($quality);
        if ($as_tag) {
            // Rendering as a tag... create the tiny version
            $tiny_height = round($height / 10);
            $tiny_width = round($width / 10);
            $tiny_quality = 1;
            $tiny = $this->ProgressivePad($tiny_width, $tiny_height, $backgroundColor, $transparencyPercent, $tiny_quality, false);
            return $this->getProgressiveTag($image, $width, $height, $tiny);
            ;
        } else {
            return $image;
        }
    }

    /**
     * CroppedImage handler -> Fill
     * @deprecated
     */
    public function ProgressiveCroppedImage($width, $height, $quality = 75, $as_tag = true)
    {
        return $this->ProgressiveFill($width, $height, $quality, $as_tag);
    }
}
