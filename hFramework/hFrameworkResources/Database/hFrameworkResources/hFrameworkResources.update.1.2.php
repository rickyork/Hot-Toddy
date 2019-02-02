<?php

class hFrameworkResources_1to2 extends hPlugin {

    public function hConstructor()
    {
        $this->hFrameworkResources->insert(
            array(
                'hFrameworkResourceId' => 20,
                'hFrameworkResourceTable' => 'hCategories',
                'hFrameworkResourcePrimaryKey' => 'hCategoryId',
                'hFrameworkResourceNameColumn' => 'hCategoryName'
            )
        );
    }
}

?>