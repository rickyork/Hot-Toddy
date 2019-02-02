<?php

class hFrameworkResources_2to3 extends hPlugin {

    public function hConstructor()
    {
        $this->hFrameworkResources
            ->setAutoIncrement(1000)
            ->insert(
                array(
                    'hFrameworkResourceId' => 21,
                    'hFrameworkResourceTable' => 'hCalendarFileDates',
                    'hFrameworkResourcePrimaryKey' => 'hCalendarFileDateId',
                    'hFrameworkResourceNameColumn' => 'hCalendarDate'
                )
            );
    }
}

?>