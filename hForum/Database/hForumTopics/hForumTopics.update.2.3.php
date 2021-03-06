<?php

class hForumTopics_2to3 extends hPlugin {

    public function hConstructor()
    {
        $this->hForumTopics
            ->appendColumn('hForumTopicCreated', hDatabase::time)
            ->appendColumn('hForumTopicLastModified', hDatabase::time)
            ->appendColumn('hForumTopicLastModifiedBy', hDatabase::id);
    }

    public function undo()
    {
        $this->hForumTopics->dropColumns(
            'hForumTopicCreated',
            'hForumTopicLastModified',
            'hForumTopicLastModifiedBy'
        );
    }
}

?>