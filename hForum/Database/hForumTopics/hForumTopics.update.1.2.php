<?php

class hForumTopics_1to2 extends hPlugin {

    public function hConstructor()
    {
        $this->hForumTopics
            ->addColumn('hForumTopicLastResponse', hDatabase::time, 'hForumTopicIsModerated')
            ->addColumn('hForumTopicLastResponseBy', hDatabase::id, 'hForumTopicLastResponse')
            ->addColumn('hForumTopicResponseCount', hDatabase::intTemplate(5), 'hForumTopicLastResponseBy');
    }
    
    public function undo()
    {
        $this->hForumTopics->dropColumns(
            'hForumTopicLastResponse',
            'hForumTopicLastResponseBy',
            'hForumTopicResponseCount'
        );
    }
}

?>