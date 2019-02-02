<?php

class hCalendars_1to2 extends hPlugin {
    
    public function hConstructor()
    {
        $this->hCalendars
            ->appendColumn('hCalendarCreated', hDatabase::time)
            ->appendColumn('hCalendarLastModified', hDatabase::time)
            ->appendColumn('hCalendarLastModifiedBy', hDatabase::id);
    }

    public function undo()
    {
        $this->hCalendars->dropColumns('hCalendarCreated', 'hCalendarLastModified', 'hCalendarLastModifiedBy');
    }
}


?>