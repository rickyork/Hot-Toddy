<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Core Image Library
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

class hImageCILibrary extends hPlugin implements hImageInterface {

    private $formats = array(
        'jpg',
        'jpe',
        'jp2',
        'jpeg',
        'bmp',
        'pdf',
        'qtif',
        'sgi',
        'tga',
        'gif',
        'png',
        'icns',
        'tiff',
        'tif',
        'pict',
        'psd',
        'ico',
        'ai',
        'eps',
        'pdf'
    );

    private $dimensions;
    public $hImage;

    private $ffmpeg = false;

    public function hConstructor()
    {
        if (file_exists('/usr/local/bin/ffmpeg'))
        {
            $this->ffmpeg = true;

            $this->formats = array_merge(
                $this->formats,
                array(
                    'mp4',
                    'm4v'
                )
            );
        }

        # On Mac OS X sips is installed in /usr/bin.
        if (!file_exists('/usr/bin/sips'))
        {
            $this->warning('SIPS is not installed at the path: /usr/bin/sips.', __FILE__, __LINE__);
        }
    }

    public function getSupportedFormats()
    {
        return $this->formats;
    }

    private function getNumericProperty($path, $property)
    {
        $result = $this->pipeCommand('/usr/bin/sips', "--getProperty {$property} ".escapeshellarg($path));

        preg_match('/pixel(Width|Height)\:\s(\d+)/m', $result, $matches);

        if (isset($matches[2]))
        {
            return (int) $matches[2];
        }
        else
        {
            $this->warning("Unable to retrieve property '{$property}' for '{$path}'.", __FILE__, __LINE__);
            return 0;
        }
    }

    public function getDimensions($path)
    {
        $extension = $this->getExtension($path);

        if (file_exists($path))
        {
            if (!isset($this->dimensions[$path]))
            {
                if ($this->ffmpeg && ($extension == 'mp4' || $extension == 'm4v'))
                {
                    $string = $this->pipeCommand('/usr/bin/mdls', '-name kMDItemPixelWidth '.escapeshellarg($path));
                    preg_match('/\d{1,}/', $string, $matches);

                    $width  = $matches[0];

                    $string = $this->pipeCommand('/usr/bin/mdls', '-name kMDItemPixelHeight '.escapeshellarg($path));
                    preg_match('/\d{1,}/', $string, $matches);

                    $height = $matches[0];

                    if ($width & 1)
                    {
                        $width--; # Value cannot be odd
                    }

                    if ($height & 1)
                    {
                        $height--; # Value cannot be odd
                    }

                    $this->dimensions[$path] = array(
                        'width'  => $width,
                        'height' => $height
                    );
                }
                else
                {
                    $this->dimensions[$path] = array(
                        'width'  => $this->getNumericProperty(
                            $path,
                            'pixelWidth'
                        ),
                        'height' => $this->getNumericProperty(
                            $path,
                            'pixelHeight'
                        )
                    );
                }
            }

            return $this->dimensions[$path];
        }
        else
        {
             $this->warning(
                'Unable to retrieve image dimensions, path, '.$path.', does not exist.',
                __FILE__,
                __LINE__
            );
        }
    }

    public function resizeImage($sourcePath, $destinationPath, $width, $height, $quality)
    {
        $sourceFormat = $this->getExtension($sourcePath);
        $destinationFormat = $this->getExtension($destinationPath);
        $dimensions  = $this->getDimensions($sourcePath);

        if ($sourceFormat == 'pdf')
        {
            # Can't get CoreImageTool to work with a PDF, but that's alright, because this one works!
            shell_exec("sips -Z {$width}x{$height} -s format {$destinationFormat} ".escapeshellarg($sourcePath).' --out '.escapeshellarg($destinationPath));
        }
        else if ($sourceFormat == 'mp4' || $sourceFormat == 'm4v')
        {
            $this->pipeCommand(
                '/usr/local/bin/ffmpeg',
                '-y -i '.escapeshellarg($sourcePath)." -vcodec mjpeg -vframes 1 -an -f rawvideo -ss 10 -s {$width}x{$height} ".escapeshellarg($destinationPath)
            );

            if (strstr($destinationPath, '.thumbnail.'))
            {
                $this->pipeCommand(
                    '/usr/local/bin/ffmpeg',
                    "-y -i ".escapeshellarg($sourcePath).' '.
                    "-vcodec mjpeg -vframes 1 -an -f rawvideo -ss 10 -s {$dimensions['width']}x{$dimensions['height']} ".
                    escapeshellarg(str_replace('.thumbnail', '', $destinationPath))
                );
            }
        }
        else
        {
            $uti = '';

            $scale = ((int) $width && (int) $dimensions['width'])? (int) $width / (int) $dimensions['width'] : 1;

            switch ($destinationFormat)
            {
                case 'jpeg':
                case 'jpe':
                case 'jpg':
                {
                    $uti = 'public.jpeg';
                    break;
                }
                case 'gif':
                {
                    $uti = 'com.compuserve.gif';
                    break;
                }
                case 'png':
                {
                    $uti = 'public.png';
                    break;
                }
                case 'psd':
                {
                    $uti = 'com.adobe.photoshop-image';
                    break;
                }
                case 'icns':
                {
                    $uti = 'com.apple.icns';
                    break;
                }
                case 'ico':
                {
                    $uti = 'com.microsoft.ico';
                    break;
                }
                case 'ai':
                {
                    $uti = 'com.adobe.illustrator.ai-image';
                    break;
                }
            }

            if ($scale > 1)
            {
                $scale = 1;
            }

            if (!is_executable($this->hFrameworkLibraryPath.'/CoreImageTool/CoreImageTool'))
            {
                $this->warning('CoreImageTool is not executable.', __FILE__, __LINE__);
            }

            shell_exec(
                escapeShellArg($this->hFrameworkLibraryPath.'/CoreImageTool/CoreImageTool').' '.
                " load   resizeImage ".escapeShellArg($sourcePath).
                " filter resizeImage CILanczosScaleTransform scale={$scale}".
                " store  resizeImage ".escapeShellArg($destinationPath)." {$uti}"
            );
        }

        #shell_exec("sips -Z {$width}x{$height} -s format {$destinationFormat} {$sourcePath} --out {$destinationPath}");
    }
}

?>