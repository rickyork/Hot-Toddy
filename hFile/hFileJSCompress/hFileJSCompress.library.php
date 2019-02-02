<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File JS Compress Library
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

class hFileJSCompressLibrary extends hPlugin {

    private $packer;

    public function hConstructor()
    {
        # Include the JavaScript packer, which will compress the JavaScript into oblivion.
        include_once $this->hFrameworkLibraryPath.'/Packer/class.JavaScriptPacker.php';
    }

    public function get($path)
    {
        $dontCompress = false;

        $basePath = $this->getEndOfPath($path, $this->hFrameworkPath);

        # Some stuff doesn't handle compression well
        switch (true)
        {
            case $this->beginsPath($basePath, $this->hFrameworkLibraryRoot.'/fckeditor'):
            case $this->beginsPath($basePath, $this->hFrameworkLibraryRoot.'/Ace'):
            case $this->beginsPath($basePath, $this->hFrameworkLibraryRoot.'/dp.SyntaxHighlighter'):
            case $this->beginsPath($basePath, $this->hFrameworkLibraryRoot.'/SyntaxHighlighter'):
            case $this->beginsPath($basePath, $this->hFrameworkLibraryRoot.'/jQuery/Plugins/Threesixty'):
            case $this->beginsPath($basePath, $this->hFrameworkLibraryRoot.'/jQuery/Plugins/Reel'):
            {
                $dontCompress = true;
                break;
            }
        }

        if (basename($path) == 'jCrop.js')
        {
            $dontCompress = true;
        }

        $file = file_get_contents($path);

        if (strstr($path, '.template.js'))
        {
            $file = $this->parseTemplate($file);
        }

        if ($this->hFrameworkCompressJS(true) && !isset($_GET['noCompression']) && !$dontCompress)
        {
            # Pack it.
            $packer = new JavaScriptPacker($file, 'Normal', true, false);

            # Assign the packed version to the hFileDocument variable so it will be output.
            return $packer->pack();
        }

        return $file;
    }
}

?>