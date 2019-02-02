<?php

class hCalendarResources_3to4 extends hPlugin {

    public function hConstructor()
    {
        $this->hCalendarResources
            ->addColumn('hCalendarResourceName', hDatabase::name, 'hUserId')
            ->addColumn('hCalendarResourceLastModifiedBy', hDatabase::id, 'hCalendarResourceLastModified');
    }

    public function undo()
    {
        $this->hCalendarResources->removeColumn(
            'hCalendarResourceName',
            'hCalendarResourceLastModifiedBy'
        );
    }
}

?>