<?php

//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
//\\\       \\\\\\\\|
//\\\ @@    @@\\\\\\| Hot Toddy Documentation
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

class hDocumentationMethods_1to2 extends hPlugin {

    public function hConstructor()
    {
        $this->hDocumentationMethods
            ->addColumn('hDocumentationMethodBody', hDatabase::mediumText, 'hDocumentationMethodSignature')
            ->modifyColumn('hDocumentationMethodSignature', hDatabase::text)
            ->addColumn('hDocumentationMethodReturnDescription', hDatabase::text);
    }
    
    public function undo()
    {
        $this->hDocumentationMethods
            ->dropColumn('hDocumentationMethodBody')
            ->modifyColumn('hDocumentationMethodSignature', hDatabase::name)
            ->dropColumn('hDocumentationMethodReturnDescription');
    }
}

?>