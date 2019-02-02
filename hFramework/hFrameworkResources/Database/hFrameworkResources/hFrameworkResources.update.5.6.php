<?php

class hFrameworkResources_5to6 extends hPlugin {
    
    public function hConstructor()
    {
        $this->hFrameworkResources->insert(
            array(
                'hFrameworkResourceId' => 25,
                'hFrameworkResourceTable' => 'hLocationCities',
                'hFrameworkResourcePrimaryKey' => 'hLocationCityId',
                'hFrameworkResourceNameColumn' => 'hLocationCity',
                'hFrameworkResourceLastModifiedColumn' => 'hLocationCityLastModified',
                'hFrameworkResourceLastModifiedByColumn' => 'hLocationCityLastModifiedBy'
            )
        );
    }
}

?>