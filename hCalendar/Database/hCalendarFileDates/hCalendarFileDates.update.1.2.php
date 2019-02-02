<?php

class hCalendarFileDates_1to2 extends hPlugin {
    
    public function hConstructor()
    {
        $this->hCalendarFileDates->prependColumn('hCalendarFileDateId', hDatabase::autoIncrement);
    }
    
    public function undo()
    {
        $this->hCalendarFileDate->dropColumn('hCalendarFileDateId');
    }
}

?>