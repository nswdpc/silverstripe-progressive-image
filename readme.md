# Progressive Image Loader for SilverStripe

This module does progressive image loading, based partly on Medium image loading techniques, using IntersectionObserver

[Browser support for IntersectionObserver](https://caniuse.com/#search=intersectionobserver) (currently all the important ones).

## Usage

Include ```ProgressiveImageLoaderScript``` in the page ```<head>```:

```
<% include ProgressiveImageLoaderScript %>
```

> Supports SilverStripe 4.x

### Images in a template
Using the Fill method as an example, add the following where you wish to use progressive image loading, the third parameter being the desired quality level to be passed to the Image backend: 
```
$Image.ProgressiveFill(420,280,80)
```
In place of
```
$Image.Fill(420,280)
```

### Templates

```ProgressiveImageLoaderScript``` will include ```ProgressiveImageLoaderStyle```
to provide some nice transitions for loading images.

You can provide your own ProgressiveImageLoaderStyle include in a theme if required.

## How it works

A thumbnail with 10% of the width/height of the requested size and a quality of 1 will be created. This will be the main image to load.
The final image will be created using the requested size and quality (420x280 @ 80% quality in the above example)

When the page loads, the tiny, low quality image will display by default, once the image scrolls or appears in the viewport, the larger image will load.

## Notes

Only ScaleWidth, Fill and Pad image thumbnailing is supported at the moment.

## Thanks

+ Some inspiration provided by: https://jmperezperez.com/medium-image-progressive-loading-placeholder/

## Licence

BSD-3 Clause
