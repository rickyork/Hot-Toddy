<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework
#//\\ @@@@  @@@@\\\\\|
#//\\\@@@@| @@@@\\\\\|
#//\\\ @@ |\\@@\\\\\\| https://github.com/rickyork/Hot-Toddy
#//\\\\  ||   \\\\\\\| © Copyright 2019 Richard York, All rights Reserved
#//\\\\  \\_   \\\\\\|
#//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#//\\\\\  ----  \@@@@| https://github.com/rickyork/Hot-Toddy/blob/master/License
#//@@@@@\       \@@@@|
#//@@@@@@\     \@@@@@|
#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

interface hImageInterface {
    public function getSupportedFormats();
    // {}

    public function getDimensions($path);
    // {}

    public function resizeImage($sourcePath, $destinationPath, $width, $height, $quality);
    // {}
}

class hImageLibrary extends hPlugin {

    private $hImageInterface;
    private $formats;

    public function hConstructor()
    {
        if (!$this->hImageInterfaceLibrary(null))
        {
            switch ($this->hOS)
            {
                case 'Darwin':
                {
                    $this->hImageInterface = $this->library('hImage/hImageCI');
                    break;
                }
                default:
                {
                    if (class_exists('Imagick'))
                    {
                        $this->hImageInterface = $this->library('hImage/hImageMagick');
                    }
                    else if (function_exists('imagecreate'))
                    {
                        $this->hImageInterface = $this->library('hImage/hImageGD');
                    }
                }
            }
        }
        else
        {
            # Allow the library to be explicitly set, should someone on
            # Mac OS X want to use GD instead of CoreImage, or another
            # interface all-together.
            $this->hImageInterface = $this->library($this->hImageInterfaceLibrary);
        }
    }

    public function getImageInterface()
    {
        return get_class($this->hImageInterface);
    }

    public function getSupportedFormats()
    {
        return $this->hImageInterface->getSupportedFormats();
    }

    public function getDimensions($path)
    {
        return $this->hImageInterface->getDimensions($path);
    }

    public function getAspectRatioDimensions($sourceWidth, $sourceHeight, $resizeWidth, $resizeHeight)
    {
        switch (true)
        {
            case (($sourceWidth <= $resizeWidth) && ($sourceWidth <= $sourceHeight)):
            {
                return(
                    array(
                        'width' => $sourceWidth,
                        'height' => $sourceHeight
                    )
                );
            }
            case ((($resizeWidth / $sourceWidth) * $sourceHeight) < $resizeHeight):
            {
                return(
                    array(
                        'width'  => $resizeWidth,
                        'height' => ceil(($resizeWidth / $sourceWidth) * $sourceHeight)
                    )
                );
            }
            default:
            {
                return(
                    array(
                        'width'  => ceil(($resizeHeight / $sourceHeight) * $sourceWidth),
                        'height' => $resizeHeight
                    )
                );
            }
        }
    }

    public function supportedFormat($format)
    {
        return in_array($format, $this->hImageInterface->getSupportedFormats());
    }

    public function resizeImage($sourcePath, $destinationPath, $width, $height, $maintainAspectRatio = true, $jpegQuality = 100)
    {
        $sourceFormat = $this->getExtension($sourcePath);
        $destinationFormat = $this->getExtension($destinationPath);

        if (!$this->supportedFormat($sourceFormat))
        {
            $this->warning(
                "Source format '{$sourceFormat}' is not supported by the image interface.",
                __FILE__,
                __LINE__
            );
        }

        if (!$this->supportedFormat($destinationFormat))
        {
            $this->warning(
                "Destination format '{$destinationFormat}' is not supported by the image interface.",
                __FILE__,
                __LINE__
            );
        }

        if ($maintainAspectRatio)
        {
            $dimensions = $this->hImageInterface->getDimensions($sourcePath);

            $aspectRatio = $this->getAspectRatioDimensions($dimensions['width'], $dimensions['height'], $width, $height);

            $width  = $aspectRatio['width'];
            $height = $aspectRatio['height'];
        }

        $this->hImageInterface->resizeImage(
            $sourcePath,
            $destinationPath,
            $width,
            $height,
            $jpegQuality
        );
    }
}

?>