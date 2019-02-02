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

class hUserImportShell extends hShell {

    private $hUserImport;

    public function hConstructor()
    {
        $this->hUserImport = $this->library('hUser/hUserImport');

        if (!function_exists('json_decode'))
        {
           // PHP4, yuk!
            $this->setToPHP4();
            include_once 'Services/JSON.php';
        }

        if (!$this->shellArgumentExists('from', '--from'))
        {
            $this->fatal(
                'Import users failed because the from path was not specified.',
                __FILE__,
                __LINE__
            );
        }

        $from = $this->getShellArgumentValue('from', '--from');

        if (file_exists($from))
        {
            $this->fatal(
                'Import users failed because the save path already exists.',
                __FILE__,
                __LINE__
            );
        }
    }
}

?>