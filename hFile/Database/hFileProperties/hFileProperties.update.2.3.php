<?php

class hFileProperties_2to3 extends hPlugin {

    public function hConstructor()
    {
        $this->hFileProperties->addColumn('hFileSystemPath', hDatabase::text, 'hFileIsSystem');
    }
    
    public function undo()
    {
        $this->hFileProperties->dropColumn('hFileSystemPath');
    }
}

?>