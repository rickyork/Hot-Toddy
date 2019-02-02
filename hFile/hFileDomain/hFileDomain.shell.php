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