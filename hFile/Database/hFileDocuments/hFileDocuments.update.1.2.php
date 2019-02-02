<?php

class hFileDocuments_1to2 extends hPlugin {

    public function hConstructor()
    {
        // Add two columns...
        $this->hFileDocuments->addColumn('hFileComments', hDatabase::text, 'hFileDocument');
    }
    
    public function undo()
    {
        $this->hFileDocuments->dropColumn('hFileComments');
    }
}

?>