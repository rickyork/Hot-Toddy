<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy User Import Shell
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