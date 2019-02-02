<?php

//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
//\\\       \\\\\\\\|
//\\\ @@    @@\\\\\\| Hot Toddy Database Structure Update
//\\ @@@@  @@@@\\\\\|
//\\\@@@@| @@@@\\\\\|
//\\\ @@ |\\@@\\\\\\| http://www.hframework.com
//\\\\  ||   \\\\\\\| © Copyright 2015 Richard York, All rights Reserved
//\\\\  \\_   \\\\\\|
//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
//\\\\\  ----  \@@@@| http://www.hframework.com/license
//@@@@@\       \@@@@|
//@@@@@@\     \@@@@@|
//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

class hFileDomains_1to2 extends hPlugin {

    public function hConstructor()
    {   
        $this->hFileDomains
            ->addColumn('hServerHost', hDatabase::name, 'hFileId')
            ->addColumn('hFileDomainIsDefault', hDatabase::is, 'hServerHost')
            ->addIndex('hFileDomain')
            ->addIndex('hFileDomainIsDefault');
    }
    
    public function undo()
    {
        $this->hFileDomains
            ->dropColumns('hServerHost', 'hFileDomainIsDefault')
            ->dropIndex('hFileDomain')
            ->dropIndex('hFileDomainIsDefault');
    }
}

?>