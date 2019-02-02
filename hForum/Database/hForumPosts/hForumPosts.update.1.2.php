<?php

class hForumPosts_1to2 extends hPlugin {

    public function hConstructor()
    {
        $this->hForumPosts
            ->addColumn('hForumPostLastResponse', hDatabase::time, 'hForumPostDate')
            ->addColumn('hForumPostLastResponseBy', hDatabase::id, 'hForumPostLastResponse')
            ->addColumn('hForumPostResponseCount', hDatabase::intTemplate(5), 'hForumPostLastResponseBy');
    }

    public function undo()
    {
        $this->hForumPosts->dropColumns('hForumPostLastResponse', 'hForumPostLastResponseBy', 'hForumPostResponseCount');
    }
}

?>