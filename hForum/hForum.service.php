<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Forum Listener
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
# <h1>Forum Services API</h1>
# <p>
#    This object provides URI access to forum APIs that are necessary to control the forum
#    experience, such as moderating a forum, and creating and managing forums, topics, and posts.
# </p>
# @end

class hForumService extends hService {

    private $hForum;
    private $hSubscription;
    private $hUserPermissions;
    private $hForumDatabase;

    private $hForumFileId;
    private $hForumId;
    private $hForumTopicId;
    private $hForumPostId;

    public function hConstructor()
    {
        $this->hForumDatabase = $this->database('hForum');
        $this->hForum = $this->library('hForum');
        $this->hSubscription = $this->library('hSubscription');
    }

    public function toggleTopicLock()
    {
        if ($this->validate('hForumTopicId'))
        {
            $this->hForumDatabase->toggleTopicLock((int) $_GET['hForumTopicId']);
            $this->JSON(1);
        }
    }

    public function toggleTopicModeration()
    {
        if ($this->validate('hForumTopicId'))
        {
            $this->hForumDatabase->toggleTopicModeration((int) $_GET['hForumTopicId']);
            $this->JSON(1);
        }
    }

    public function togglePostAttribute($attribute)
    {
        if ($this->validate('hForumPostId'))
        {
            $response = $this->hForumDatabase->togglePostAttribute(
                (int) $_GET['hForumPostId'],
                $attribute
            );

            if (isset($_GET['html']))
            {
                header(
                    'Location: '.
                    $this->href(
                        '/System/Applications/Forum/Response.html',
                        array(
                            'togglePostAttribute' => $attribute,
                            'is' => $response,
                            'hFrameworkResource' => 'hForumPosts',
                            'hFrameworkResourceKey' => (int) $_GET['hForumPostId']
                        )
                    )
                );
            }
            else
            {
                $this->JSON((int) $response);
            }
        }
    }

    public function togglePostLock()
    {
        $this->togglePostAttribute('hForumPostIsLocked');
    }

    public function togglePostApproval()
    {
        $this->togglePostAttribute('hForumPostIsApproved');
    }

    public function togglePostStickiness()
    {
        $this->togglePostAttribute('hForumPostIsSticky');
    }

    public function renameForum()
    {
        if ($this->validate(array('hForumId', 'hForum')))
        {
            $this->hForumDatabase->renameForum(
                (int) $_GET['hForumId'],
                $_GET['hForum']
            );

            $this->JSON(1);
        }
    }

    public function newForum()
    {
        if ($this->validate(array('hFileId', 'hForum')))
        {
            $forum = hString::scrubValue($_GET['hForum']);

            $this->hForumDatabase->newForum(
                (int) $_GET['hFileId'],
                $forum
            );

            $this->JSON(1);
        }
    }

    public function saveTopic()
    {
        if ($this->validate(array('hForumTopicId', 'hForumId', 'hForumTopic')))
        {
            $forumTopicId = $this->hForumDatabase->saveTopic($_GET);

            $this->JSON($forumTopicId);
        }
    }

    public function sortForums()
    {
        if ($this->validate('hForums', '_POST'))
        {
            $this->hForumDatabase->sortForums($_POST['hForums']);
            $this->JSON(1);
        }
    }

    public function sortTopics()
    {
        if ($this->validate('hForums', '_POST'))
        {
            $this->hForumDatabase->sortTopics($_POST['hForums']);
            $this->JSON(1);
        }
    }

    public function deleteForum()
    {
        if ($this->validate('hForumId'))
        {
            $this->hForumDatabase->deleteForum((int) $_GET['hForumId']);

            $this->sendDeletionResponse(
                'hForums',
                (int) $_GET['hForumId']
            );
        }
    }

    public function deleteTopic()
    {
        if ($this->validate('hForumTopicId'))
        {
            $this->hForumDatabase->deleteTopic((int) $_GET['hForumTopicId']);

            $this->sendDeletionResponse(
                'hForumTopics',
                (int) $_GET['hForumTopicId']
            );
        }
    }

    public function deletePost()
    {
        if ($this->validate('hForumPostId'))
        {
            $this->hForumDatabase->deletePost((int) $_GET['hForumPostId']);

            $this->sendDeletionResponse(
                'hForumPosts',
                (int) $_GET['hForumPostId']
            );
        }
    }

    private function sendDeletionResponse($frameworkResource, $frameworkResourceKey)
    {
        if (isset($_GET['html']))
        {
            header(
                'Location: '.
                $this->href(
                    '/System/Applications/Forum/Response.html',
                    array(
                        'delete' => 1,
                        'hFrameworkResource' => $frameworkResource,
                        'hFrameworkResourceKey' => $frameworkResourceKey
                    )
                )
            );
        }
        else
        {
            $this->JSON(1);
        }
    }

    public function getTopic()
    {
        if ($this->validate('hForumTopicId'))
        {
            $this->JSON(
                $this->hForumTopics->selectAssociative(
                    array(
                        'hForumTopic',
                        'hForumTopicDescription',
                        'hForumTopicIsLocked',
                        'hForumTopicIsModerated'
                    ),
                    (int) $_GET['hForumTopicId']
                )
            );
        }
    }

    public function toggleForumSubscription()
    {
        $this->toggleSubscription(
            'hForums',
            'hForumId'
        );
    }

    public function toggleTopicSubscription()
    {
        $this->toggleSubscription(
            'hForumTopics',
            'hForumTopicId'
        );
    }

    public function toggleThreadSubscription()
    {
        $this->toggleSubscription(
            'hForumPosts',
            'hForumPostId'
        );
    }

    private function toggleSubscription($frameworkResource, $frameworkResourcePrimaryKey)
    {
        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }

        if ($this->validate($frameworkResourcePrimaryKey, '_GET', false))
        {
            $frameworkResourceKey = (int) $_GET[$frameworkResourcePrimaryKey];

            $this->subscriptionResponse(
                (int) $this->hSubscription->toggleSubscription(
                    $frameworkResource,
                    $frameworkResourceKey
                ),
                $frameworkResource,
                $frameworkResourceKey
            );
        }
    }

    private function subscriptionResponse($response, $frameworkResource, $frameworkResourceKey)
    {
        if (isset($_GET['html']))
        {
            header(
                'Location: '.
                $this->href(
                    '/System/Applications/Forum/Response.html',
                    array(
                        'hSubscription' => 1,
                        'hFrameworkResource' => $frameworkResource,
                        'hFrameworkResourceKey' => $frameworkResourceKey,
                        'hSubscriptionStatus' => $response
                    )
                )
            );
        }
        else
        {
            $this->JSON($response);
        }
    }

    private function validate($variables = array(), $array = '_GET', $authentication = true)
    {
        if (!is_array($variables))
        {
            $variables = array($variables);
        }

        foreach ($variables as $key => $value)
        {
            switch ($array)
            {
                case '_GET':
                {
                    if (!isset($_GET[$value]))
                    {
                        $this->JSON(-5);
                        return false;
                    }
                    break;
                }
                case '_POST':
                {
                    if (!isset($_POST[$value]))
                    {
                        $this->JSON(-5);
                        return false;
                    }
                    break;
                }
            }
        }

        if ($authentication)
        {
            if (!$this->isLoggedIn())
            {
                $this->JSON(-6);
                return false;
            }

            if (!$this->hForum->isAdministrator())
            {
                $this->JSON(-1);
                return false;
            }
        }

        return true;
    }
}

?>