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

class hFileDomains_2to3 extends hPlugin {

    public function hConstructor()
    {
        $this->hFileDomains
            ->addColumn('hTemplateId', hDatabase::id, 'hServerHost')
            ->renameColumn('hServerHost', 'hFrameworkSite')
            ->modifyColumn('hFrameworkSite', hDatabase::name);
    }
    
    public function undo()
    {
        $this->hFileDomains
            ->dropColumn('hTemplateId')
            ->renameColumn('hFrameworkSite', 'hServerHost');
    }
}

?>