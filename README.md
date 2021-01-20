# Progressive Image Loader for Silverstripe

This module does progressive image loading, based partly on Medium image loading techniques, using IntersectionObserver (v1)

[Browser support for IntersectionObserver](https://caniuse.com/#search=intersectionobserver) (currently all the important ones).

## Usage

Call the thumbnail rendering methods directly within your template:

```ss
<% with $Image %>
$ProgressiveFill(420,280,90)
<% end_with %>
```

In place of
```ss
<% with $Image %>
$Fill(420,280)
<% end_with %>
```

## Requirements

When one of the `$Progressive*` methods is called, Requirements will automatically be added via the Requirements API, these are loaded via data: uris with SRI hashes supplied

### Templates

```
NSWDPC
  ProgressiveImage
    ProgressiveImage.ss -> template containing HTML loading the image
    Script.ss -> provides the JS to handle image replacement
    Style.ss -> provides CSS to assist with image replacement
```

## How it works

A thumbnail with 10% of the width/height of the requested size and a quality of 1 will be created. This will be the main image to load.

The final image will be created using the requested size and quality (420x280 @ 80% quality in the ProgressiveFill example above)

When the page loads, the tiny, low quality image will display by default, once the image scrolls or appears in the viewport, the larger image will load, thanks to IntersectionObserver.

## Notes

### Supported thumbnailing methods:

+ ProgressiveScaleWidth (ScaleWidth)
+ ProgressiveFill (Fill) (was ProgressiveCroppedImage)
+ ProgressivePad (Pad)

### Inline script and css removal

To support Content Security Policies (CSP), the controller extension loading inline scripts and css plus related templates have been removed.

## Thanks

+ Some inspiration provided by: https://jmperezperez.com/medium-image-progressive-loading-placeholder/

## Licence

BSD-3 Clause
