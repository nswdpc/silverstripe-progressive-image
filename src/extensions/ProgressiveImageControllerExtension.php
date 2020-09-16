<?php

namespace NSWDPC\ProgressiveImage;

use SilverStripe\Core\Extension;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;
use SilverStripe\View\TemplateGlobalProvider;

/**
 * Loads Progressive Image requirements via Requirements API
 * @author James
 */
class ProgressiveImageControllerExtension extends Extension implements TemplateGlobalProvider
{

    public static function get_template_global_variables()
    {
        return [
            'ProgressiveImageStyle' => 'get_progressive_image_style',
            'ProgressiveImageScript' => 'get_progressive_image_script'
        ];
    }

    public static function get_progressive_image_style() {
        return ArrayData::create()->renderWith('NSWDPC/ProgressiveImage/Style');
    }

    public static function get_progressive_image_script() {
        return ArrayData::create()->renderWith('NSWDPC/ProgressiveImage/Script');
    }

    public function onAfterInit()
    {
        $this->owner->loadProgressiveImageRequirements();
    }

    public function loadProgressiveImageRequirements() {
        $script = self::get_progressive_image_script()->forTemplate();
        $css = self::get_progressive_image_style()->forTemplate();
        Requirements::customCSS(
            $css,
            'progressive-image-style' // unique id
        );
        Requirements::customScript(
            $script,
            'progressive-image-script'
        );
    }

}
