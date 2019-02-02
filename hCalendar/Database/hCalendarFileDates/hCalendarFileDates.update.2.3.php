<?php

class hCalendarFileDates_2to3 extends hPlugin {

    public function hConstructor()
    {
        $this->hCalendarFileDates
            ->appendColumn('hCalendarFileDateCreated', hDatabase::time)
            ->appendColumn('hCalendarFileDateLastModified', hDatabase::time)
            ->appendColumn('hCalendarFileDateLastModifiedBy', hDatabase::id);
    }

    public function undo()
    {
        $this->hCalendarFileDates->dropColumns(
            'hCalendarFileDateCreated',
            'hCalendarFileDateLastModified',
            'hCalendarFileDateLastModifiedBy'
        );
    }
}


?>