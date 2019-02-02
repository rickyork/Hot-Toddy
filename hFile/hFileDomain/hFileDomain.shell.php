<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File Domain Shell
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

class hFileDomainShell extends hShell {

    private $hFile;
    private $hFileIcon;

    public function hConstructor()
    {
        // ./hot plugin hFile/hFileDomain install mirror.example.com
        // ./hot plugin hFile/hFileDomain install mirror.example.com as www.example.com
        if ($this->shellArgumentExists('install', '--install'))
        {
            $domain = $this->getShellArgumentValue('install', '--install');

            $as = '';

            if ($this->shellArgumentExists('as', '--as'))
            {
                $as = $this->getShellArgumentValue('as', '--as');
            }

            if (!empty($domain))
            {


            }
        }
    }
}

?>