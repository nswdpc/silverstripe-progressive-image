<?php

namespace NSWDPC\ProgressiveImage;

use SilverStripe\Core\Extension;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;
use SilverStripe\View\TemplateGlobalProvider;

/**
 * @deprecated see ProgressiveImageExtension::loadProgressiveImageRequirements
 * @author James
 */
class ProgressiveImageControllerExtension extends Extension implements TemplateGlobalProvider
{

    /**
     * @deprecated see ProgressiveImageExtension::loadProgressiveImageRequirements
     */
    public static function get_template_global_variables()
    {
        return [
            'ProgressiveImageStyle' => 'get_progressive_image_style',
            'ProgressiveImageScript' => 'get_progressive_image_script'
        ];
    }

    /**
     * @deprecated see ProgressiveImageExtension::loadProgressiveImageRequirements
     */
    public static function get_progressive_image_style()
    {
        return ProgressiveImageExtension::get_progressive_image_style();
    }

    /**
     * @deprecated see ProgressiveImageExtension::loadProgressiveImageRequirements
     */
    public static function get_progressive_image_script()
    {
        return ProgressiveImageExtension::get_progressive_image_script();
    }
}
