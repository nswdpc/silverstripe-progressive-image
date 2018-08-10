# Progressive Image Loader

This module does basic progressive image loading, based partly on Medium image loading techniques.

## Usage

Include ```ProgressiveImageScript``` in the page ```<head>```:

```
<% include ProgressiveImageScript %>
```

### Templates

```templates\Includes\ProgressiveImageLoaderScript.ss``` will include ```ProgressiveImageLoaderStyle```
to provide some nice transitions for loading images. You can provide your own in a theme if required.

Waypoints 4.0.x (non jQuery/Zepto version) is included in ```ProgressiveImageLoaderScript.ss```. This is done to remove an HTTP request on an asset. You can provide your own Waypoints if required in a theme, we will update this as upstream release new versions.

### Images in a template
Using CroppedImage as an example, where you wish to use progressive image loading, add the following
```
$Image.ProgressiveCroppedImage(420,280,80)
```
In place of
```
$Image.CroppedImage(420,280)
```

## How it works

A thumbnail with 10% of the width/height of the requested size and a quality of 1 will be created. This will be the main image to load.
The final image will be created using the requested size and quality (420x280 @ 80 in the above example)

When the page loads, the tiny, low quality image will display by default.

When loaded, the ```pil_process``` script fires to load the final image in dynamically when it appears in the viewport using Waypoints (inlined).

Images loaded this way that never appear in the viewport, for example if thet are never scrolled to, will never load.

## Notes

Only ScaleWidth and Fill image thumbnailing is supported at the moment.

## Licence

MIT
