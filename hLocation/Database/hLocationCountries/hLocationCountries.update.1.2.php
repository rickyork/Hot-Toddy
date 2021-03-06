<?php

class hLocationCountries_1to2 extends hPlugin {
    
    public function hConstructor()
    {
        $this->hLocationCountries
            ->appendColumn('hLocationCountryCreated', hDatabase::time)
            ->appendColumn('hLocationCountryLastModified', hDatabase::time)
            ->appendColumn('hLocationCountryLastModifiedBy', hDatabase::id);
    }

    public function undo()
    {
        $this->hLocationCountries->dropColumns(
            'hLocationCountryCreated',
            'hLocationCountryLastModified',
            'hLocationCountryLastModifiedBy'
        );
    }
}

?>