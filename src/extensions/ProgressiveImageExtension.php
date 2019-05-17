<?php
namespace NSWDPC\ProgressiveImage;

use Silverstripe\Assets\Image;
use Silverstripe\Core\Config\Config;
use Silverstripe\Core\Extension;
use SilverStripe\View\ViewableData;
use SilverStripe\ORM\FieldType\DBField;

/**
 * Extension provides methods to aid in progressive image loading techniques, e.g by being able to define quality at request time
 * @author James Ellis <james.ellis@dpc.nsw.gov.au>
 */
class ProgressiveImageExtension extends Extension
{
    private $default_quality = 80;// store default for reset post image processing

    private $_cache_filter_style = null;

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
    private function AsTag(ViewableData $image, $width, $height, ViewableData $tiny)
    {
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
        $tag = $this->owner->renderWith('ProgressiveImage', $data);
        $field = DBField::create_field('HTMLText', $tag);
        return $field;
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
        $backend = $this->getBackend();
        if (method_exists($backend, 'ResetFilters')) {
            $backend->ResetFilters();
        }
        $this->resetQuality($quality);
        if ($as_tag) {
            // Rendering as a tag... create the tiny version
            $tiny_width = round($width / 10);
            $tiny_quality = 1;
            $tiny = $this->ProgressiveScaleWidth($tiny_width, $tiny_quality, false);

            $tag = $this->AsTag($image, $width, 0, $tiny);
            return $tag;
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

            $tag = $this->AsTag($image, $width, $height, $tiny);
            return $tag;
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

            $tag = $this->AsTag($image, $width, $height, $tiny);
            return $tag;
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
