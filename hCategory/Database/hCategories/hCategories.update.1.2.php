<?php

class hCategories_1to2 extends hPlugin {

    public function hConstructor()
    {
        $this->hCategories->addColumn('hUserId', hDatabase::id, 'hCategoryId');
    }
    
    public function undo()
    {
        $this->hCategories->dropColumn('hUserId');
    }
}

?>