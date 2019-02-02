<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework Cache Shell
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