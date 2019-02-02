<?php

class hForumPosts_2to3 extends hPlugin {
    
    public function hConstructor()
    {
        $this->hForumPosts
            ->addColumn('hForumPostCreated', hDatabase::time, 'hForumPostResponseCount')
            ->renameColumn('hForumPostLastToModify', 'hForumPostLastModifiedBy');
    }
    
    public function undo()
    {
        $this->hForumPosts
            ->dropColumn('hForumPostCreated')
            ->renameColumn('hForumPostLastModifiedBy', 'hForumPostLastToModify');
    }
}

?>