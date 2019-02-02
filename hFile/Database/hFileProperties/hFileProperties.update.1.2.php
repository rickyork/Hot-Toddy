<?php

class hFileProperties_1to2 extends hPlugin {

    public function hConstructor()
    {
        $this->hFileProperties->addColumn('hFileLabel', hDatabase::varCharTemplate(50), 'hFileIsSystem');
    }
    
    public function undo()
    {
        $this->hFileProperties->dropColumn('hFileLabel');
    }
}

?>