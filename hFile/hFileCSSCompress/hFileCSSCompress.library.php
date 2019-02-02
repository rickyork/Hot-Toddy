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