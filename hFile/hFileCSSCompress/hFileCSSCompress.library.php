<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File CSS Compress Library
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

class hFileCSSCompressLibrary extends hPlugin {

    public function get($path)
    {
        $file = $this->getTemplate($path);

        if ($this->hFrameworkCompressCSS(true) && !isset($_GET['noCompression']))
        {
            if (!isset($_GET['compression']))
            {
                # Strip whitespace
                $file = preg_replace('/\s{2,}|\n|\r/', '', $file);
            }

            if (!isset($_GET['comments']))
            {
                # Strip comments
                $file = preg_replace('/\/\*.*\*\//Ums', '', $file);
            }
        }

        # Find template paths and append the last modifed time to those
        return $this->parseDocument($file);
    }
}

?>