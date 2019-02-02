<?php

class hForums_1to2 extends hPlugin {

    public function hConstructor()
    {
        $this->hForums
            ->appendColumn('hForumCreated', hDatabase::time)
            ->appendColumn('hForumLastModified', hDatabase::time)
            ->appendColumn('hForumLastModifiedBy', hDatabase::id);
        
    }

    public function undo()
    {
        $this->hForums->dropColumns(
            'hForumCreated',
            'hForumLastModified',
            'hForumLastModifiedBy'
        );
    }
}

?>