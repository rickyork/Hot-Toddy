<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File Icon Library
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

class hFileIconLibrary extends hPlugin {

    private $htmlTypes = array(
        'text/html',
        'application/xml+xhtml'
    );

    private $htmlExtensions = array(
        'xhtml',
        'html',
        'htm'
    );

    private $hImage;

    public function hConstructor()
    {

    }

    public function getHTMLIcon()
    {
        switch ($this->userAgent->browser)
        {
            case 'ie':
            {
                return 'ie_document.png';
            }
            case 'opera':
            {
                return 'opera_document.png';
            }
            case 'gecko':
            {
                return 'firefox_document.png';
            }
            case 'webkit':
            {
                return 'safari_document.png';
            }
            default:
            {
                return 'generic_document.png';
            }
        }
    }

    public function getFileProperties($fileId)
    {
        return array_merge(
            $this->hFiles->selectAssociative(
                'hFileName',
                (int) $fileId
            ),
            $this->hFileProperties->selectAssociative(
                array(
                    'hFileIconId',
                    'hFileMIME'
                ),
                (int) $fileId
            )
        );
    }

    public function getFileIconPath($fileId, $mime = nil, $fileName = nil, $iconResolution = '32x32')
    {
        $properties = $this->getFileProperties($fileId);

        if (empty($mime) && isset($properties['hFileMIME']))
        {
            $mime = $properties['hFileMIME'];
        }

        if (empty($fileName) && isset($properties['hFileName']))
        {
            $fileName = $properties['hFileName'];
        }

        if (isset($properties['hFileIconId']))
        {
            $fileIconId = (int) $properties['hFileIconId'];
        }

        if (!empty($fileIconId))
        {
            return(
                '/images/icons/'.$iconResolution.'/'.
                $this->hFileIcons->selectColumn(
                    'hFileName',
                    (int) $fileIconId
                )
            );
        }
        else
        {
            return $this->getIconPath(
                $mime,
                $fileName,
                $iconResolution
            );
        }
    }

    public function getIconPath($mime, $fileName, $iconResolution = '32x32')
    {
        return '/images/icons/'.$iconResolution.'/'.$this->getIconFileName($mime, $fileName);
    }

    public function getIconFileName($mime, $fileName = nil)
    {
        $fileExtension = (strstr($fileName, '.')? $this->getExtension($fileName) : '');

        if ($this->isHTMLIcon($fileExtension))
        {
            $iconFileName = $this->getHTMLIcon();
        }

        if (empty($iconFileName) && !empty($fileExtension))
        {
            $iconFileName = $this->hFileIcons->selectColumn(
                'hFileName',
                array(
                    'hFileExtension' => $fileExtension
                ),
                nil,
                nil,
                1
            );
        }

        if (empty($iconFileName) && !empty($mime))
        {
            $iconFileName = $this->hFileIcons->selectColumn(
                'hFileName',
                array(
                    'hFileMIME' => $mime
                ),
                nil,
                nil,
                1
            );
        }

        return (empty($iconFileName)? 'generic_document.png' : $iconFileName);
    }

    public function getIconPathById($fileIconId, $iconResolution = '32x32')
    {
        return(
            '/images/icons/'.$iconResolution.'/'.
            $this->hFileIcons->selectColumn(
                'hFileName',
                (int) $fileIconId
            )
        );
    }

    public function isHTMLIcon($mime)
    {
        return (in_array($mime, $this->htmlTypes) || in_array($mime, $this->htmlExtensions));
    }

    public function addHTMLExtension($fileExtension)
    {
        array_push(
            $this->htmlExtensions,
            $fileExtension
        );
    }

    public function addHTMLType($mime)
    {
        array_push(
            $this->htmlTypes,
            $mime
        );
    }

    private function osXIcons()
    {
        // /System/Library/CoreServices/Finder.app/Contents/Resources
    }
}

?>