<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Finder Icons
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

class hFinderIcons extends hPlugin {

    private $hFile;
    private $hFileIcon;

    public function hConstructor()
    {
        $this->library('hFile');
        $this->hFile = $this->library('hFile');

        $this->hFileIcon = $this->library('hFile/hFileIcon');

        $this->hFileMIME = 'text/css';
        $this->hTemplatePath = '';

        $query = $this->hDatabase->select(
            array(
                'hFileMIME',
                'hFileName',
                'hFileExtension'
            ),
            'hFileIcons'
        );

        $icons = array();

        foreach ($query as $data)
        {
            if (empty($data['hFileExtension']))
            {
                $data['hFileExtension'] = str_replace(array('.', '+', '/'), '-', $data['hFileMIME']);
            }

            $icons[$data['hFileExtension']] = $data['hFileName'];
        }

        $resolutions = array(
            '16x16',
            '32x32',
            '48x48'
        );

        $css = '';

        foreach ($icons as $type => $icon)
        {
            foreach ($resolutions as $resolution)
            {
                list($width, $height) = explode('x', $resolution);

                $css .=
                    "div.hFinderIcon-".$type.'-'.$resolution." {\n".
                    '    '.$this->getProperty($icon, $resolution)."\n".
                    "    background-size: {$width}px {$height}px;\n".
                    "    width: {$width}px;\n".
                    "    height: {$height}px;\n".
                    "}\n";
            }
        }

        $icons = array(
            'ie'      => 'ie_document.png',
            'gecko'   => 'firefox_document.png',
            'safari'  => 'safari_document.png',
            'opera'   => 'opera_document.png',
            'default' => 'safari_document.png'
        );

        foreach ($icons as $browser => $icon)
        {
            foreach ($resolutions as $resolution)
            {
                list($width, $height) = explode('x', $resolution);

                $css .=
                    "div.hFinderBrowserIcon-".$browser.'-'.$resolution." {\n".
                    '    '.$this->getProperty($icon, $resolution)."\n".
                    "    background-size: {$width}px {$height}px;\n".
                    "    width: {$width}px;\n".
                    "    height: {$height}px;\n".
                    "}\n";
            }
        }

        foreach ($resolutions as $resolution)
        {
            list($width, $height) = explode('x', $resolution);

            $css .=
                "div.hFinderDirectoryIcon-".$resolution." {\n".
                '    '.$this->getProperty('folder.png', $resolution)."\n".
                "    background-size: {$width}px {$height}px;\n".
                "    width: {$width}px;\n".
                "    height: {$height}px;\n".
                "}\n";
        }


        $this->hFileSize = strlen($css);
        $this->hFileDocument = $this->parseDocument($css);
    }

    private function getProperty($icon, $resolution)
    {
        if ($this->userAgent->isTridentLT8)
        {
            return "filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='/images/icons/{$resolution}/{$icon}', sizingMethod='scale');";
        }
        else
        {
            return "background: url('/images/icons/{$resolution}/{$icon}') no-repeat center;";
        }
    }
}

?>