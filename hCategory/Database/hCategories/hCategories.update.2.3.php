<?php

class hCategories_2to3 extends hPlugin {

    public function hConstructor()
    {
        $this->hCategories->addColumn('hCategoryLastModifiedBy', hDatabase::id, 'hCategoryLastModified');
    }
    
    public function undo()
    {
        $this->hCategories->dropColumn('hCategoryLastModifiedBy');
    }
}

?>