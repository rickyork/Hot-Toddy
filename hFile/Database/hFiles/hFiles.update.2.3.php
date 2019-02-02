<?php

class hFiles_2to3 extends hPlugin {

    public function hConstructor()
    {        
        $this->hFiles->addColumn('hFileLastModifiedBy', hDatabase::id, 'hFileLastModified');
    }
    
    public function undo()
    {
        $this->hFiles->dropColumn('hFileLastModifiedBy');
    }
}

?>