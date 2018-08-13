# Progressive Image Loader for SilverStripe

This module does progressive image loading, based partly on Medium image loading techniques.

## Usage

Include ```ProgressiveImageScript``` in the page ```<head>```:

```
<% include ProgressiveImageScript %>
```
Supports SilverStripe 4.x

### Templates

```templates\Includes\ProgressiveImageLoaderScript.ss``` will include ```ProgressiveImageLoaderStyle```
to provide some nice transitions for loading images. You can provide your own in a theme if required.

Waypoints 4.0.x (non jQuery/Zepto version) is included in ```ProgressiveImageWaypoints.ss```.
This is done to remove an HTTP request on an asset and we will update this as upstream release new versions.

You can provide your own Waypoints if required in a theme.

### Images in a template
Using the Fill method as an example, add the following where you wish to use progressive image loading:
```
$Image.ProgressiveFill(420,280,80)
```
In place of
```
$Image.ProgressiveFill(420,280)
```

## How it works

A thumbnail with 10% of the width/height of the requested size and a quality of 1 will be created. This will be the main image to load.
The final image will be created using the requested size and quality (420x280 @ 80% quality in the above example)

When the page loads, the tiny, low quality image will display by default.

When loaded, the ```pil_process``` script fires to load the final image in dynamically when it appears in the viewport using Waypoints (inlined).

Images loaded this way that do not appear in the viewport will never load, for instance if the site visitor never scrolls to the image.

## Notes

Only ScaleWidth and Fill image thumbnailing is supported at the moment.

## Thanks

+ [Waypoints](https://github.com/imakewebthings/waypoints)
+ Some inspiration provided by: https://jmperezperez.com/medium-image-progressive-loading-placeholder/

## Licence

BSD-3 Clause
