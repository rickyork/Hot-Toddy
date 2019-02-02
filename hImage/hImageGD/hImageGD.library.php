<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy GD Image Library
#//\\ @@@@  @@@@\\\\\|
#//\\\@@@@| @@@@\\\\\|
#//\\\ @@ |\\@@\\\\\\| http://www.hframework.com
#//\\\\  ||   \\\\\\\| © Copyright 2015 Richard York, All rights Reserved
#//\\\\  \\_   \\\\\\|
#//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#//\\\\\  ----  \@@@@| http://www.hframework.com/license
#//@@@@@\       \@@@@|
#//@@@@@@\     \@@@@@|
#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

class hImageGDLibrary extends hPlugin implements hImageInterface {

    # Because GD supports fuck all.
    private $formats = array(
        'jpg',
        'jpe',
        'jpeg',
        'gif',
        'png'
    );

    private $dimensions = array();

    public $hImage;

    public function hConstructor()
    {
        if (!function_exists('imagecreatetruecolor'))
        {
            $this->warning('PHP GD extension is not installed.', __FILE__, __LINE__);
        }
    }

    public function getSupportedFormats()
    {
        return $this->formats;
    }

    public function getDimensions($path)
    {
        if (file_exists($path))
        {
            if (!isset($this->dimensions[$path]))
            {
                $size = getImageSize($path);

                $this->dimensions[$path] = array(
                    'width' => $size[0],
                    'height' => $size[1]
                );
            }

            return $this->dimensions[$path];
        }
        else
        {
             $this->warning("Unable to retrieve image dimensions because path '{$path}' does not exist.", __FILE__, __LINE__);
        }
    }

    public function resizeImage($sourcePath, $destinationPath, $width, $height, $quality)
    {
        $sourceFormat      = $this->getExtension($sourcePath);
        $destinationFormat = $this->getExtension($destinationPath);

        $destination = imageCreateTrueColor($width, $height);

        $dimensions = $this->getDimensions($sourcePath);

        switch ($sourceFormat)
        {
            case 'jpeg':
            case 'jpe':
            case 'jpg':
            {
                $source = imageCreateFromJPEG($sourcePath);
                break;
            }
            case 'png':
            {
                $source = imageCreateFromPNG($sourcePath);
                break;
            }
            case 'gif':
            {
                $source = imageCreateFromGIF($sourcePath);
                break;
            }
        }

        # GD transparency with help from:
        # http://mediumexposure.com/techblog/smart-image-resizing-while-preserving-transparency-php-and-gd-library
        if ($sourceFormat == 'gif' || $sourceFormat == 'png')
        {
            $transparency = imageColorTransparent($source);

            # If we have a specific transparent color
            if ($transparency >= 0)
            {
                # Get the original image's transparent color's RGB values
                $transparentColor = imageColorsForIndex($source, $transparency);

                # Allocate the same color in the new image resource
                $transparentIndex = imageColorAllocate(
                    $destination,
                    $transparentColor['red'],
                    $transparentColor['green'],
                    $transparentColor['blue']
                );

                # Completely fill the background of the new image with allocated color.
                imageFill($destination, 0, 0, $transparentIndex);

                # Set the background color for new image to transparent
                imageColorTransparent($destination, $transparentIndex);
            }
            # Always make a transparent background color for PNGs that don't have one allocated already
            else if ($sourceFormat == 'png')
            {
                # Turn off transparency blending (temporarily)
                imageAlphaBlending($destination, false);

                # Create a new transparent color for image
                $color = imageColorAllocateAlpha($destination, 0, 0, 0, 127);

                # Completely fill the background of the new image with allocated color.
                imageFill($destination, 0, 0, $color);

                # Restore transparency blending
                imageSaveAlpha($destination, true);
            }
        }

        $this->copyResized($source, $destination, $dimensions, $width, $height);

        switch ($destinationFormat)
        {
            case 'jpeg':
            case 'jpe':
            case 'jpg':
            {
                imageJPEG($destination, $destinationPath, $quality);
                break;
            }
            case 'png':
            {
                imagePNG($destination, $destinationPath);
                break;
            }
            case 'gif':
            {
                imageGIF($destination, $destinationPath);
                break;
            }
        }

        @imageDestroy($source);
        @imageDestroy($destination);
    }

    private function copyResized(&$source, &$destination, $dimensions, $width, $height)
    {
         imageCopyResampled(
            $destination,
            $source,
            0, 0, 0, 0,
            $width,
            $height,
            $dimensions['width'],
            $dimensions['height']
        );
    }
}

?>