<?php

class hCalendarFiles_3to4 extends hPlugin {
    
    public function hConstructor()
    {
        $this->hCalendarFiles
            ->appendColumn('hCalendarFileCreated', hDatabase::time)
            ->appendColumn('hCalendarFileLastModified', hDatabase::time)
            ->appendColumn('hCalendarFileLastModifiedBy', hDatabase::id);
    }

    public function undo()
    {
        $this->hCalendarFiles->dropColumns(
            'hCalendarFileCreated', 
            'hCalendarFileLastModified', 
            'hCalendarFileLastModifiedBy'
        );
    }
}


?>