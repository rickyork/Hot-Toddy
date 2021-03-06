<?php

class hLocationCounties_1to2 extends hPlugin {
    
    public function hConstructor()
    {
        $this->hLocationCounties
            ->appendColumn('hLocationCountyCreated', hDatabase::time)
            ->appendColumn('hLocationCountyLastModified', hDatabase::time)
            ->appendColumn('hLocationCountyLastModifiedBy', hDatabase::id);
    }

    public function undo()
    {
        $this->hLocationCounties->dropColumns(
            'hLocationCountyCreated',
            'hLocationCountyLastModified',
            'hLocationCountyLastModifiedBy'
        );
    }
}


?>