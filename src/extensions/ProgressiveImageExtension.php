<?php
namespace NSWDPC\ProgressiveImage;
use Silverstripe\Assets\Image;
use Silverstripe\Core\Config\Config;
use Silverstripe\Core\Extension;
use SilverStripe\Assets\Storage\DBFile;
use SilverStripe\ORM\FieldType\DBField;

/**
 * Extension provides methods to aid in progressive image loading techniques, e.g by being able to define quality at request time
 * @author James Ellis <james.ellis@dpc.nsw.gov.au>
 */
class ProgressiveImageExtension extends Extension {

	private $default_quality = 80;// store default for reset post image processing

	private function getBackend() {
		$backend = $this->owner->getImageBackend();
		return $backend;
	}

	private function getDefaultQuality() {
		$backend = $this->getBackend();
		return $backend->getQuality();
	}

	private function setDefaultQuality() {
		$backend = $this->getBackend();
		$backend->setQuality( $this->default_quality );
	}

	/**
	 * Set quality in Config
	 */
	private function setQuality($quality) {
		if(!$quality || $quality <= 0 || $quality >= 100 ) {
			$quality = $this->getDefaultQuality();
		}

		$this->default_quality = $this->getDefaultQuality();// store the default quality set in config
		// set the new quality
		$backend = $this->getBackend();
		$backend->setQuality( (int) $quality );
		return $quality;
	}

	/**
	 * Reset quality in config to previously stored value
	 */
	private function resetQuality() {
		$this->setDefaultQuality();
	}

	/**
	 * Returns the tag used to load the image, with container and padding block
	 */
	private function AsTag(DBFile $image, $width, $height, DBFile $tiny) {
		// <img class="img-small" onload="progressive_image_loader(this);" src="$AltHeroImage.ProgressiveCroppedImage(42,28, 1).URL" data-final="$AltHeroImage.ProgressiveCroppedImage(420,280, 80).URL" alt="" />
		$final_url = $image->getURL();
		if($height >  0 && $width > 0) {
			$padding_value = round(($height / $width) * 100, 2);
		} else {
			$padding_value = 66.6;
		}
		$tiny_url = $tiny->getURL();
		$tag = "<div class=\"pil-ph\"><img class=\"pil-small\""
					. " onload=\"pil_process(this);\""
					. " data-final=\"{$final_url}\""
					. " src=\"{$tiny_url}\"><div style=\"padding-bottom: {$padding_value}%;\"></div></div>";
		$field = DBField::create_field( 'HTMLText', $tag );
		return $field;
	}

	/**
	 * ScaleWidth handler
	 */
	public function ProgressiveScaleWidth($width, $quality = 75) {
		$quality = $this->setQuality($quality);
		$image = $this->owner->isWidth($width) && !Config::inst()->get(Image::class, 'force_resample')
			? $this
			: $this->owner->ScaleWidth($width);
		$this->resetQuality($quality);
		return $image;
	}

	/**
	 * Fill to width and height with quality
	 */
	public function ProgressiveFill($width, $height, $quality = 75, $as_tag = true) {
		$quality = $this->setQuality($quality);
		$image = $this->owner->isSize($width, $height) && !Config::inst()->get(Image::class, 'force_resample')
			? $this
			: $this->owner->Fill($width, $height);
		$this->resetQuality($quality);

		if($as_tag) {
			// Rendering as a tag.. create the tiny version
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
	 * CroppedImage handler -> Fill
	 */
	public function ProgressiveCroppedImage($width, $height, $quality = 75, $as_tag = true) {
		return $this->ProgressiveFill($width, $height, $quality, $as_tag);
	}

}
