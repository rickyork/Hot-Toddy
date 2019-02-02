<?php

class hFiles_1to2 extends hPlugin {

    public function hConstructor()
    {   
        $this->hFiles->addFullTextIndex('hFileName');
    }
    
    public function undo()
    {
        $this->hFiles->dropFullTextIndex('hFileName');
    }
}

?>