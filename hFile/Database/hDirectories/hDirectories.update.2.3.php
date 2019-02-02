<?php

class hDirectories_2to3 extends hPlugin {

    public function hConstructor()
    {
        $this->hDirectories->appendColumn(
            'hDirectoryLastModifiedBy',
            hDatabase::id
        );
    }

    public function undo()
    {
        $this->hDirectories->dropColumn(
            'hDirectoryLastModifiedBy'
        );
    }
}

?>