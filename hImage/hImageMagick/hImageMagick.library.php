<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Image Magick Library
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

class hImageMagickLibrary extends hPlugin implements hImageInterface {

    private $formats = array(
        'a', 'ai', 'art', 'arw', 'avi', 'avs', 'b', 'bmp', 'bmp2', 'bmp3', 'c',
        'caption', 'cin', 'cip', 'clip', 'cmyk', 'cmyka', 'cr2', 'crw', 'cur',
        'cut', 'dcm', 'dcr', 'dcx', 'dfont', 'djvu', 'dng', 'dot', 'dps', 'dpx',
        'epdf', 'epi', 'eps', 'eps2', 'eps3', 'epsf', 'epsi', 'ept', 'ept2', 'ept3',
        'exr', 'fax', 'fits', 'fractal', 'fts', 'g', 'g3', 'gif', 'gif87', 'gradient',
        'gray', 'histogram', 'htm', 'html', 'icb', 'ico', 'icon', 'info', 'ipl', 'jng',
        'jp2', 'jpeg', 'jpg', 'jpx', 'k', 'k25', 'kdc', 'label', 'm', 'm2v', 'map',
        'map', 'matte', 'miff', 'mng', 'mono', 'mpc', 'mpeg', 'mpg', 'mrw', 'msl',
        'msvg', 'mtv', 'mvg', 'nef', 'null', 'o', 'orf', 'otb', 'otf', 'pal', 'palm',
        'pam', 'pattern', 'pbm', 'pcd', 'pcds', 'pcl', 'pct', 'pcx', 'pdb', 'pdf', 'pef',
        'pfa', 'pfb', 'pfm', 'pgm', 'pgx', 'picon', 'pict', 'pix', 'pjpeg', 'plasma',
        'png', 'png24', 'png32', 'png8', 'pnm', 'ppm', 'preview', 'ps', 'ps2', 'ps3',
        'psd', 'ptif', 'pwp', 'r', 'raf', 'ras', 'rgb', 'rgba', 'rgbo', 'rla', 'rle',
        'scr', 'sct', 'swf', 'sgi', 'shtml', 'sr2', 'srf', 'stegano', 'sun', 'svg',
        'svgz', 'text', 'tga', 'thumbnal', 'tiff', 'tif', 'tiff64', 'tile',
        'tim', 'ttc', 'ttf', 'txt', 'uil', 'uyvy', 'vda', 'vicar', 'vid', 'viff', 'vst',
        'wbmp', 'wmf', 'wmz', 'wpg', 'x', 'x3f', 'xbm', 'xc', 'xcf', 'xpm', 'xv', 'xwd',
        'y', 'ycbcr', 'ycbcra', 'yuv'
    );

    private $dimensions = array();

    public $hImage;

    public function hConstructor()
    {
        if (!class_exists('Imagick'))
        {
            $this->warning('PHP ImageMagick extension is not installed.', __FILE__, __LINE__);
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
                $image = new Imagick($path);

                $this->dimensions[$path] = array(
                    'width'  => $image->getImageWidth(),
                    'height' => $image->getImageHeight()
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
        $image = new Imagick($sourcePath);
        $image->resizeImage($width, $height, imagick::FILTER_LANCZOS, 1);
        $image->writeImage($destinationPath);
    }
}

?>