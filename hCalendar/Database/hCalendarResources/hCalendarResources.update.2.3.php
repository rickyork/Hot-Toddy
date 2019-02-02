<?php

class hCalendarResources_2to3 extends hPlugin {

    public function hConstructor()
    {
        $this->hCalendarResources->addColumn('hCalendarResourceCacheExpires', hDatabase::time, 'hCalendarResourceLastModified');
    }
    
    public function undo()
    {
        $this->hCalendarResources->removeColumn('hCalendarResourceCacheExpires');
    }
}

?>