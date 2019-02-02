<?php

class hFileAliases_1to2 extends hPlugin {

    public function hConstructor()
    {   
        $this->hFileAliases->addColumn('hFileAliasDestination', hDatabase::name, 'hFileAliasPath');
    }

    public function undo()
    {
        $this->hFileComments->dropColumn('hFileAliasDestination');
    }
}

?>