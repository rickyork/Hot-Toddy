<?php

class hContactFields_2to3 extends hPlugin {

    public function hConstructor()
    {
        $this->hContactFields->addColumn('hContactFieldName', hDatabase::varCharTemplate(50), 'hContactFieldSortIndex');    
        $this->hContactFields->truncateAndInsert();
    }
}

?>