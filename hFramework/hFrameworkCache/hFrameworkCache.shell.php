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

class hFrameworkCacheShell extends hPlugin {

    private $hFrameworkCache;
    private $hFilePHPCompress;

    public function hConstructor()
    {
        switch (true)
        {
            case $this->shellArgumentExists('php', '--php'):
            {
                $this->hFilePHPCompress = $this->library('hFile/hFilePHPCompress');
                $this->hFilePHPCompress->all();
                break;
            }
            case $this->shellArgumentExists('delete', '--delete'):
            {
                $this->hFrameworkCache = $this->library('hFramework/hFrameworkCache');
                $this->hFrameworkCache->delete();
                break;
            }
            default:
            {
                $this->hFrameworkCache = $this->library('hFramework/hFrameworkCache');
                $this->hFrameworkCache->go();
            }
        }
    }
}

?>