<?php

class hCalendarFiles_2to3 extends hPlugin {

    public function hConstructor()
    {
        // Make sure that 'hCalendarFileDates' exists.
        $this->hDatabase->uses('hCalendarFileDates');

        $this->hCalendarFiles->addIndex(
            array(
                'hCalendarId',
                'hCalendarCategoryId',
                'hFileId',
                'hCalendarBegin',
                'hCalendarEnd'
            )
        );
        
        $this->hCalendarFiles->addIndex(
            array(
                'hCalendarId',
                'hCalendarCategoryId',
                'hCalendarBegin',
                'hCalendarEnd'
            )
        );
    }
    
    public function undo()
    {
        $this->hCalendarFiles->dropIndex(
            array(
                'hCalendarId',
                'hCalendarCategoryId',
                'hFileId',
                'hCalendarBegin',
                'hCalendarEnd'
            )
        );
        
        $this->hCalendarFiles->dropIndex(
            array(
                'hCalendarId',
                'hCalendarCategoryId',
                'hCalendarBegin',
                'hCalendarEnd'
            )
        );
        
    }
}

?>