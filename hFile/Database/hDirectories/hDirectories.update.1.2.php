<?php

class hDirectories_1to2 extends hPlugin {

    public function hConstructor()
    {
        $this->hDirectories->addFullTextIndex('hDirectoryPath');
    }
    
    public function undo()
    {
        $this->hDirectories->dropFullTextIndex('hDirectoryPath');
    }
}

?>