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

class hFileIcon extends hPlugin {

    private $hImage;

    private $width;
    private $height;

    public function hConstructor()
    {
        $this->hTemplatePath = '';

        $flag = strstr($this->hFileWildcardPath, '/flags/');
        $file = basename($this->hFileWildcardPath);

        preg_match('/\d{1,}x\d{1,}/', $this->hFileWildcardPath, $res);

        if (isset($res[0]))
        {
            $res = $res[0];
        }

        list($width, $height) = explode('x', $res);

        $this->width = $width;
        $this->height = $height;

        $iconsFolder = $this->hFrameworkIconPath;

        $path = $iconsFolder.'/'.$res.'/'.($flag? 'flags/' : '').$file;

        # See if the icon exists...
        $pathExists = file_exists($path);

        $this->hImage = $this->library('hImage');

        $sourcePath_Apps = $iconsFolder.'/Applications/'.($flag? 'flags/' : '').$file;

        $iconUtilExists = false;

        if (file_exists('/usr/local/bin/icns2png') && is_executable('/usr/local/bin/icns2png') && !$flag)
        {
            # This is a hack to get real icns support until I implement better icon
            # management and association.

            # See if an icns file exists...
            $icnsFile = $this->hFileIcons->selectColumn(
                'hFileICNS',
                array(
                    'hFileName' => $file
                )
            );

            if (empty($icnsFile))
            {
                switch ($file)
                {
                    default:
                    {
                        $sourcePath_512 = $iconsFolder.'/512x512/'.($flag? 'flags/' : '').$file;
                        $sourcePath_128 = $iconsFolder.'/128x128/'.($flag? 'flags/' : '').$file;
                    }
                }
            }

            if (!empty($icnsFile))
            {
                $icnsPath = $iconsFolder.'/Source/'.$icnsFile;

                if (!file_exists($icnsPath))
                {
                    $this->notice("Missing ICNS file '{$icnsPath}'.", __FILE__, __LINE__);
                }

                $icnsBaseName = explode('.', $icnsFile);
                $icnsBaseName = array_shift($icnsBaseName);

                $savePath = dirname($icnsPath);

                $icnsPath = escapeshellarg($icnsPath);
                $savePath = escapeshellarg($savePath);

                $find2xIcon = (
                    ($this->userAgent->browser == 'ie' && $this->userAgent->browserVersion > 8 ||
                    $this->userAgent->browser != 'ie') &&
                    file_exists('/usr/bin/iconutil') && !$flag
                );

                if ($find2xIcon)
                {
                    $result = $this->pipeCommand(
                        '/usr/bin/iconutil',
                        "--convert iconset {$icnsPath}"
                    );

                    $iconUtilExists = true;

                    // iconutil -> iconset
                    // /Sources/Console.icons
                    // /Sources/Console.iconset
                    //              icon_16x16.png
                    //              icon_16x16@2x.png
                    //              icon_32x32.png
                    //              icon_32x32@2x.png
                    //              icon_128x128.png
                    //              icon_128x128@2x.png
                    //              icon_256x256.png
                    //              icon_256x256@2x.png
                    //              icon_512x512.png
                    //              icon_512x512@2x.png

                    $resolutions = array(
                        '16x16',
                        '32x32',
                        '48x48',
                        '128x128',
                        '512x512'
                    );

                    $iconSourceFolder = $iconsFolder.'/Source/'.$icnsBaseName.'.iconset/icon_';
                    $largestResolution = nil;
                    $largestIs2x = false;
                    $source = '';

                    foreach ($resolutions as $resolution)
                    {
                        if ($this->setIconData($iconSourceFolder, $resolution, true, $largestResolution, $largestIs2x, $source))
                        {
                            break;
                        }
                        else if ($this->setIconData($iconSourceFolder, $resolution, false, $largestResolution, $largestIs2x, $source))
                        {
                            break;
                        }
                    }

                    if (empty($source) && !empty($largestResolution))
                    {
                        $this->setIconData(
                            $iconSourceFolder,
                            $largestResolution,
                            $largestIs2x,
                            $largestResolution,
                            $largestIs2x,
                            $source
                        );
                    }
                }
                else
                {
                    $result = $this->pipeCommand(
                        '/usr/local/bin/icns2png',
                        "-x {$icnsPath} -o {$savePath}"
                    );

                    if ($width <= 16 && file_exists($iconsFolder.'/Source/'.$icnsBaseName.'_16x16x32.png'))
                    {
                        # Copy 16x16 to 16x16 folder
                        $source = $iconsFolder.'/Source/'.$icnsBaseName.'_16x16x32.png';
                    }
                    else if ($width <= 32 && file_exists($iconsFolder.'/Source/'.$icnsBaseName.'_32x32x32.png'))
                    {
                        $source = $iconsFolder.'/Source/'.$icnsBaseName.'_32x32x32.png';
                    }
                    else if ($width <= 48 && file_exists($iconsFolder.'/Source/'.$icnsBaseName.'_48x48x32.png'))
                    {
                        $source = $iconsFolder.'/Source/'.$icnsBaseName.'_48x48x32.png';
                    }
                    else if ($width <= 128 && file_exists($iconsFolder.'/Source/'.$icnsBaseName.'_128x128x32.png'))
                    {
                        $source = $iconsFolder.'/Source/'.$icnsBaseName.'_128x128x32.png';
                    }
                    else if ($width <= 256 && file_exists($iconsFolder.'/Source/'.$icnsBaseName.'_256x256x32.png'))
                    {
                        $source = $iconsFolder.'/Source/'.$icnsBaseName.'_256x256x32.png';
                    }
                    else
                    {
                        $source = $icnsPath;
                    }
                }
            }
        }
        else
        {
            $sourcePath_512  = $iconsFolder.'/512x512/'.($flag? 'flags/' : '').$file;
            $sourcePath_128  = $iconsFolder.'/128x128/'.($flag? 'flags/' : '').$file;
        }

        if (!$iconUtilExists || $flag)
        {
            if (isset($source) && file_exists($source) && (!$pathExists || $pathExists && filemtime($source) > filemtime($path)))
            {
                $this->hImage->resizeImage(
                    $source,
                    $path,
                    $width,
                    $height,
                    false
                );
            }
            else if (isset($sourcePath_512) && file_exists($sourcePath_512) && (!$pathExists || $pathExists && filemtime($sourcePath_512) > filemtime($path)) && $res != '512x512')
            {
                $this->hImage->resizeImage(
                    $sourcePath_512,
                    $path,
                    $width,
                    $height,
                    false
                );
            }
            else if (isset($sourcePath_128) && file_exists($sourcePath_128) && (!$pathExists || $pathExists && filemtime($sourcePath_128) > filemtime($path)) && $res != '128x128')
            {
                $this->hImage->resizeImage(
                    $sourcePath_128,
                    $path,
                    $width,
                    $height,
                    false
                );
            }
            else if (file_exists($sourcePath_Apps) && (!$pathExists || $pathExists && filemtime($sourcePath_Apps) > filemtime($path)))
            {
                $this->hImage->resizeImage(
                    $sourcePath_Apps,
                    $path,
                    $width,
                    $height,
                    false
                );
            }

            $this->setFileProperties(
                $path,
                $this->hFrameworkIconRoot.'/'.$res.'/'.($flag? 'flags/' : '').$file
            );
        }
        else
        {
            $this->setFileProperties(
                $source,
                $this->getEndOfPath(
                    $source,
                    $this->hFrameworkPath
                )
            );

            // iconutil -> iconset
            // /Sources/Console.icons
            // /Sources/Console.iconset
            //              icon_16x16.png
            //              icon_16x16@2x.png
            //              icon_32x32.png
            //              icon_32x32@2x.png
            //              icon_128x128.png
            //              icon_128x128@2x.png
            //              icon_256x256.png
            //              icon_256x256@2x.png
            //              icon_512x512.png
            //              icon_512x512@2x.png
        }
    }

    private function setIconData($iconSourceFolder, $resolution, $is2x, &$largestResolution, &$largestIs2x, &$source)
    {
        $icon = $iconSourceFolder.$resolution.($is2x? '@2x' : '').'.png';

        list($width, $height) = explode('x', $resolution);

        if (file_exists($icon))
        {
            $largestResolution = $resolution;
            $largestIs2x = $is2x;

            if ($this->width <= $width)
            {
                $source = $icon;
                return true;
            }
        }

        return false;
    }

    public function setFileProperties($path, $iconPath)
    {
        // /Websites/www.deadmarshes.com/Icons/96x96/folder.png
        // /Icons/96x96/folder.png

        if (!file_exists($path))
        {
            $this->notice("Icon '".basename($path)."' does not exist.", __FILE__, __LINE__);
        }

        if (isset($_GET['hFileLastModified']))
        {
            $this->hFileDisableCache = false;
            $this->hFileEnableCache  = true;
            $this->hFileCacheExpires = strtotime('+10 Years');
        }

        $this->hFileSize = filesize($path);
        $this->hFileDownload = false;
        $this->hFileSystemDocument = true;
        $this->hFileLastModified = filemtime($path);
        $this->hTemplatePath = '';
        $this->hFileName = basename($path);

        $this->hFileSystemPath = $this->hFrameworkPath;
        $this->hFilePath = $iconPath;
        $this->hFileMIME = 'image/png';
    }
}

?>