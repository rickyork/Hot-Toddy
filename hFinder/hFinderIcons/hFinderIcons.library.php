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

class hFinderIconsLibrary extends hPlugin {

    public function getIconClassName($hFileMIME, $hFileIconResolution = '48x48', $hFileName = null)
    {
        if ($hFileMIME == 'directory')
        {
            return 'hFinderDirectoryIcon-'.$hFileIconResolution;
        }

        if (!empty($hFileName))
        {
            // Try and get the icon from the extension first,
            // since this is more reliable than MIME
            $hFileExtension = (strstr($hFileName, '.')? $this->getExtension($hFileName) : 'txt');

            $exists = $this->hFileIcons->selectExists(
                'hFileName',
                array(
                    'hFileExtension' => $hFileExtension
                ),
                null,
                null,
                1
            );

            if ($exists)
            {
                if ($this->isHTMLIcon($hFileExtension))
                {
                    return $this->getHTMLIconClassName($hFileIconResolution);
                }
                else
                {
                    return 'hFinderIcon-'.$this->scrubClassName($hFileExtension).'-'.$hFileIconResolution;
                }
            }
        }

        if (!empty($hFileMIME))
        {
            $exists = $this->hFileIcons->selectExists(
                'hFileName',
                 array(
                    'hFileMIME' => htmlspecialchars($hFileMIME, ENT_QUOTES)
                ),
                null,
                null,
                1
            );

            if ($exists)
            {
                if ($this->isHTMLIcon($hFileMIME))
                {
                    return $this->getHTMLIconClassName($hFileIconResolution);
                }
                else
                {
                    return 'hFinderIcon-'.$this->scrubClassName($hFileMIME).'-'.$hFileIconResolution;
                }
            }
        }

        return 'hFinderIcon-file-'.$hFileIconResolution;
    }

    private function scrubClassName($string, $x = null)
    {
        if (!empty($x))
        {
            $string = str_replace('x', '-', $string);
        }

        return str_replace(array('.', '+', '/', '_'), '-', $string);
    }

    public function getHTMLIconClassName($hFileIconResolution)
    {
        switch ($this->userAgent->browser)
        {
            case 'ie':
            case 'safari':
            case 'gecko':
            case 'opera':
            {
                return 'hFinderBrowserIcon-'.$this->userAgent->browser.'-'.$hFileIconResolution;
            }
            default:
            {
                return 'hFinderBrowserIcon-safari-'.$hFileIconResolution;
            }
        }
    }

    public function isHTMLIcon($type)
    {
        switch ($type)
        {
            case 'text/html':
            case 'html':
            case 'htm':
            {
                return true;
            }
            default:
            {
                return false;
            }
        }
    }
}

?>