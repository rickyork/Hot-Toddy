<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Forum Topics
#//\\ @@@@  @@@@\\\\\|
#//\\\@@@@| @@@@\\\\\|
#//\\\ @@ |\\@@\\\\\\| http://www.hframework.com
#//\\\\  ||   \\\\\\\| © Copyright 2015 Richard York, All rights Reserved
#//\\\\  \\_   \\\\\\|
#//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#//\\\\\  ----  \@@@@| http://www.hframework.com/license
#//@@@@@\       \@@@@|
#//@@@@@@\     \@@@@@|
#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
# @description
# <h1>Forum Topics UI</h1>
# <p>
#    This plugin provides UI for viewing all forum topics posted to a forum.  All forums,
#    and then all topics in each forum are displayed.
# </p>
# @end

class hForumTopics extends hPlugin {

    private $forum;
    private $forumDatabase;
    private $hSubscription;
    private $isAdministrator;
    private $hForm;
    private $hDialogue;

    public function hConstructor()
    {
        $this->hForum = $this->library('hForum');
        $this->hForumDatabase = $this->database('hForum');
        $this->hSubscription = $this->library('hSubscription');

        $fileId = (int) $this->hFileSymbolicLinkTo($this->hFileId);

        $this->hFileDocument = $this->getTemplate(
            'Forums',
            array(
                'hFilePath'       => $this->hFilePath,
                'hFileId'         => $fileId,
                'hFileDocument'   => $this->hFileDocument,
                'isLoggedIn'      => $this->isLoggedIn(),
                'isAdministrator' => $this->hForumIsAdministrator(false),
                'hForums'         => $this->getForumsForTemplate($fileId)
            )
        );

        if ($this->hForumIsAdministrator(false))
        {
            $this->hFileDocumentAppend = $this->getTopicForm();
        }
    }

    public function getForumsForTemplate($fileId)
    {
        $query = $this->hForumDatabase->getForums($fileId);

        $isLoggedIn = $this->isLoggedIn();

        $forums = array();

        foreach ($query as $forumId => $forum)
        {
            $forum = array(
                'hForumId'        => $forumId,
                'hForum'          => $forum,
                'isLoggedIn'      => $isLoggedIn,
                'isAdministrator' => $this->hForumIsAdministrator(false),
                'hForumTopics'    => $this->getTemplate(
                    'Topics',
                    array(
                        'hForumTopics' => $this->getTopics($forumId, $fileId)
                    )
                )
            );

            array_push($forums, $forum);
        }

        $forums = $this->hDatabase->getResultsForTemplate($forums);

        return $forums;
    }

    public function getTopics($forumId, $fileId)
    {
        $query = $this->hForumDatabase->getTopics($forumId);

        $topics = array();

        if (count($query))
        {
            $isLoggedIn = $this->isLoggedIn();

            foreach ($query as $data)
            {
                $topic = array(
                    'isLoggedIn'             => $isLoggedIn,
                    'hForumTopicId'          => $data['hForumTopicId'],
                    'hForumTopic'            => $data['hForumTopic'],
                    'hForumTopicDescription' => $data['hForumTopicDescription'],
                    'hForumTopicIsModerated' => (int) $data['hForumTopicIsModerated'],
                    'hForumTopicIsLocked'    => (int) $data['hForumTopicIsLocked'],
                    'hForumPostCount'        => $data['hForumTopicResponseCount'],
                    'hForumThreadCount'      => $this->hForumDatabase->getThreadCount($data['hForumTopicId'], true),
                    'hForum'                 => $fileId.'/'.$forumId.'/'.$data['hForumTopicId'],
                    'hForumLastPost'         => $this->hForum->getDate($data['hForumTopicLastResponse'], $data['hForumTopicLastResponseBy']),
                    'hForumModerators'       => $this->hForum->getModerators($forumId, $data['hForumTopicId']),
                    'isAdministrator'        => $this->hForumIsAdministrator(false)
                );

                if ($isLoggedIn)
                {
                    if ($this->hSubscription->isSubscribed('hForumTopics', $data['hForumTopicId'], (int) $_SESSION['hUserId']))
                    {
                        $topic['hForumTopicSubscribeLabel'] = 'Unsubscribe';
                    }
                    else
                    {
                        $topic['hForumTopicSubscribeLabel'] = 'Subscribe';
                    }
                }

                array_push($topics, $topic);
            }
        }

        return $this->hDatabase->getResultsForTemplate($topics);
    }

    public function getTopicForm()
    {
        $this->hForm = $this->library('hForm');
        $this->hDialogue = $this->library('hDialogue');

        $this->hDialogue->setForm($this->hForm);
        $this->hDialogue->newDialogue('hForumTopic');

        $this->hDialogueAction = $this->hFilePath;

        $this->hForm->addDiv('hForumTopicDiv');
        $this->hForm->addFieldset('Topic Properties', '100%', '100px,');

        $this->hForm->addTextInput('hForumTopic', 'Topic:');

        $this->hForm->addTextareaInput('hForumTopicDescription', 'Description: -L', '50,5');

        $this->hForm->addTableCell('');
        $this->hForm->addCheckboxInput('hForumTopicIsLocked', 'Lock Topic?');

        $this->hForm->addTableCell('');
        $this->hForm->addCheckboxInput('hForumTopicIsModerated', 'Moderate Topic?');

        $this->hDialogue->addButtons('Save', 'Cancel');

        $dialogue = $this->hDialogue->getDialogue(null, 'Forum Topic');

        $this->hForm->reset();

        return $dialogue;
    }
}

?>