<?php

class hLocationZipCodes_2to3 extends hPlugin {
    
    public function hConstructor()
    {
        $this->hLocationZipCodes
            ->appendColumn('hLocationZipCodeCreated', hDatabase::time)
            ->appendColumn('hLocationZipCodeLastModified', hDatabase::time)
            ->appendColumn('hLocationZipCodeLastModifiedBy', hDatabase::id);
    }

    public function undo()
    {
        $this->hLocationZipCodes->dropColumns(
            'hLocationZipCodeCreated',
            'hLocationZipCodeLastModified',
            'hLocationZipCodeLastModifiedBy'
        );
    }
}


?>