# ImageFactory

Based on the [Imagine](https://github.com/avalanche123/Imagine) library, ImageFactory provides wrappers allowing image manipulation in a simple way.

### Requirements

 * PHP 5.3+
 * Imagine's ones 

### Installation

```
composer require 
```

### Basic usage

AndroidBitmapGenerator is the wrapper that allows Android developers to generate bitmaps of every density (mdpi - hdpi - xhdpi - xxhdpi - xxxhdpi) on the fly just by providing the original image path.

```php
<?php

require 'vendor/autoload.php';

use ImageFactory\Android\Density\AndroidBitmapGenerator;

$generator = new AndroidBitmapGenerator('images/neptune.jpg');
$generator->execute();
```

This is as simple as that, the files will be generated in the same directory as the source image with the same name suffixed with the relative density. (e.g. neptune-mdpi.jpg).

A different name can be given to the generated files either via the constructor as the second argument or by calling the appropriate method
```php
<?php

$generator = new AndroidBitmapGenerator('images/neptune.jpg');
$generator->setOutputPath('output/saturn.jpg')
		  ->execute();

/* OR */

$generator = new AndroidBitmapGenerator('images/neptune.jpg', 'output/saturn.jpg');
$generator->execute(); 

```


The reference size (px width*height) which refers to the highest density can be set either via the constructor as the third and fourth arguments or by calling the appropriate setter. Otherwise it will be equivalent to the original image size by default.
```php
<?php

$generator = new AndroidBitmapGenerator('images/neptune.jpg', null, 400, 300);
$generator->execute(); 

/* OR */

$generator = new AndroidBitmapGenerator('images/neptune.jpg');
$generator->setReferenceSize(400, 300)
		  ->execute();
```


To change the reference size when a new source image is set, make sure to call *setReferenceSize* after *setImagePath* (which calls it internally) so that the default size can be overriden).

```php
<?php

$generator = new AndroidBitmapGenerator('images/neptune.jpg');
$generator->setImagePath(__DIR__.'/images/mars.jpg')
		  ->setReferenceSize(400, 300)
		  ->execute();
```

Two compression types are supported : 
- png (default one)
- jpeg

It only has to be set when the output format is jpeg given that the default compression type is png. The *setCompression* method takes two arguments, the first one is the type (png or jpeg) and the second one is its value.
The default compression level for png files is 7 (a value from 0 to 9 is required) whereas 75 is the default one for jpeg files (its value should be between 0 and 100).

```php
<?php

use ImageFactory\Image\ImageGeneratorInterface;

$generator = new AndroidBitmapGenerator('/images/neptune.jpg');
	$generator
	          ->setCompression(ImageGeneratorInterface::COMPRESSION_JPEG, ImageGeneratorInterface::COMPRESSION_JPEG_DEFAULT_LEVEL)
  			  ->execute();
```
### Roadmap

 * [markdown-it](https://github.com/markdown-it/markdown-it) for Markdown parsing
 * [CodeMirror](http://codemirror.net/) for the awesome syntax-highlighted editor
 * [highlight.js](http://softwaremaniacs.org/soft/highlight/en/) for syntax highlighting in output code blocks
 * [js-deflate](https://github.com/dankogai/js-deflate) for gzipping of data to make it fit in URLs

### License

