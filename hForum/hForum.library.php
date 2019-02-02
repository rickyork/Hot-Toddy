<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Forum Library
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
# <h1>Forum API</h1>
# <p>
#     This object provides some forum APIs necessary for implementing forums.
# </p>
# @end

class hForumLibrary extends hPlugin {

    private $hSubscription;
    private $hForumDatabase;

    public function hConstructor()
    {
        $this->hForumDatabase = $this->database('hForum');
        $this->hSubscription  = $this->library('hSubscription');
    }

    public function getUserRank($userId, $count)
    {
        # @return string

        # @description
        # <h2>Getting a User's Forum Ranking</h2>
        #
        # @end

        //getUserIcon($count, $threshold, $userTitle, $title, $icon, $iconCount)
        $users = array(
            array(
                50,
                'NewMember',
                'New Member',
                '',
                0
            ),
            array(
                100,
                'BeginningMember',
                'Beginning Member',
                'starBronze.gif',
                1
            ),
            array(
                200,
                'IntermediateMember',
                'Intermediate Member',
                'starOrange.gif',
                2
            ),
            array(
                500,
                'Member',
                'Member',
                'starRed.gif',
                3
            ),
            array(
                1000,
                'SeniorMember',
                'Senior Member',
                4
            ),
            array(
                1000000,
                'ExpertMember',
                'Expert Member',
                5
            )
        );

        foreach ($users as $user)
        {
            if (false !== ($icon = $this->getUserThresholdIcon($count, $user[0], $user[1], $user[2], $user[3], $user[4])))
            {
                break;
            }
        }

        $isEmployee = false;

        switch (true)
        {
            case $this->inGroup('Employees', $userId, false):
            {
                $icon = $this->getUserIcon('Employees', $this->hFrameworkName.' Employee', 'starSilver.gif', 5);
                $isEmployee = true;
                break;
            }
            case $this->isAdministrator($userId):
            {
                $icon = $this->getUserIcon('Administrator', 'Administrator', 'starGold.gif', 5);
                break;
            }
            case $this->isModerator($userId):
            {
                $icon = $this->getUserIcon('Moderator', 'Moderator', 'starSilver.gif', 5);
                break;
            }
        }

        $icon['html'] = '';

        if ($isEmployee && $this->hFileLogoSmall)
        {
            $icon['html'] .= $this->getTemplate(
                'Logo',
                array(
                    'hFileLogoSmall' => $this->hFileLogoSmall,
                    'hFrameworkName' => $this->hFrameworkName
                )
            );
        }

        return $icon;
    }

    private function getUserThresholdIcon($count, $threshold, $userTitle, $title, $icon, $count)
    {
        # @return string | boolean

        # @description
        # <h2>Getting </h2>
        #
        # @end

        if ($count < $this->{"hForum{$userTitle}Threshold"}($threshold, $this->hForumFileId))
        {
            return $this->getUserIcon($userTitle, $title, $icon, $count);
        }

        return false;
    }

    private function getUserIcon($userTitle, $title, $icon, $count)
    {
        # @return array

        # @description
        # <h2> </h2>
        #
        # @end

        return (
            array(
                'icon'  => $this->{"hForum{$userTitle}Icon"}($icon, $this->hForumFileId),
                'count' => $this->{"hForum{$userTitle}IconCount"}($count, $this->hForumFileId),
                'title' => $this->{"hForum{$userTitle}Title"}($title, $this->hForumFileId)
            )
        );
    }

    public function getDate($timestamp, $userId)
    {
        # @return string

        # @description
        # <h2>Getting Formatted Date HTML</h2>
        # <p>
        #
        # </p>
        # @end
        return (
            $this->getTemplate(
                'Date',
                array(
                    'Day' => date('m/d/y', $timestamp),
                    'Hour' => date('h:m:i A T', $timestamp),
                    'User' => $this->user->getUserName($userId)
                )
            )
        );
    }

    public function getModerators($forumId, $forumTopicId)
    {
        # @return string

        # @description
        # <h2>Fetching Forum Moderators</h2>
        # <p>
        #
        # </p>
        # @end

        $users = $this->hForumDatabase->getModerators($forumTopicId);

        $links = array();

        foreach ($users as $userId)
        {
            $links[] = $this->user->getUserName($userId);
        }

        return implode(', ', $links);
    }

    public function getPostTopic($forumPostId, $action = nil)
    {
        # @return string

        # @description
        # <h2>Returns Forum Topic</h2>
        # <p>
        #
        # </p>
        # @end

        $topic = $this->hForumDatabase->getThreadTopic($forumPostId);

        if ($action == 'reply' && !stristr($topic, 'Re:'))
        {
            $topic = 'Re: '.$topic;
        }

        return $topic;
    }

    public function hasEdit($userId = 0)
    {
        # @return string

        # @description
        # <h2>Determining if the User Has Edit Privileges</h2>
        # <p>
        #
        # </p>
        # @end

        // Is the user viewing a moderator or admin?
        // Or is the user viewing logged in and the author of the post
        //
        // If either of those are satisfied, the user has edit privileges.
        return (
            $this->hasPrivileges() || $
            this->isLoggedIn() && !empty($userId) && (int) $this->hUserId === (int) $_SESSION['hUserId']
        );
    }

    public function hasPrivileges($userId = 0)
    {
        # @return boolean

        # @description
        # <h2>Determining if the User Has Elevated Privileges</h2>
        # <p>
        #
        # </p>
        # @end

        $this->user->whichUserId($userId);

        return (
            $this->hFiles->hasPermission($this->hForumFileId, 'rw', $userId) ||
            $this->hForums->hasPermission($this->hForumId, 'rw', $userId) ||
            $this->hForumTopics->hasPermission($this->hForumTopicId, 'rw', $userId)
        );
    }

    public function isModerator($userId = 0)
    {
        # @return boolean

        # @description
        # <h2>Determining if the User Has Moderator Privileges</h2>
        # <p>
        #
        # </p>
        # @end

        $this->user->whichUserId($userId);
        return $this->hForumTopics->hasPermission($this->hForumTopicId, 'rw', $userId);
    }

    public function isAdministrator($userId = 0)
    {
        # @return boolean

        # @description
        # <h2>Determining if the User is An Administrator</h2>
        # <p>
        #
        # </p>
        # @end

        $this->user->whichUserId($userId);

        if ($this->hForumId)
        {
            return (
                $this->hFiles->hasPermission($this->hForumFileId, 'rw', $userId) ||
                $this->hForums->hasPermission($this->hForumId, 'rw', $userId)
            );
        }
        else
        {
            return (
                $this->hFiles->hasPermission($this->hForumFileId, 'rw', $userId)
            );
        }
    }

    public function bbCodeToHTML($text)
    {
        # @return boolean

        # @description
        # <h2>Converting BB Code to HTML</h2>
        # <p>
        #
        # </p>
        # @end

        if (!class_exists('HTML_BBCoseParser'))
        {
            require_once 'PEAR.php';
            require_once 'HTML/BBCodeParser.php';
        }

        $text = htmlspecialchars($text);

        /* get options from the ini file */
        $config  = parse_ini_file($this->hFilePathToPEAR.'/HTML/BBCodeParser/example/BBCodeParser.ini', true);
        $options = PEAR::getStaticProperty('HTML_BBCodeParser', '_options');
        $options = $config['HTML_BBCodeParser'];
        unset($options);

        $parser = new HTML_BBCodeParser();

        /* do yer stuff! */
        $parser->setText($text);
        $parser->parse();

        return nl2br($parser->getParsed());
    }

    public function getLink($forumPostId = 0, $forumTopicId = 0, $forumId = 0, $postLink = false, $extraParameter = '')
    {
        # @return string

        # @description
        # <h2>Getting a Forum Link</h2>
        # <p>
        #
        # </p>
        # @end

        if (empty($forumPostId) && !$this->hForumThreads || $this->hForumThreads)
        {
            $forumPostId = $this->hForumPostId(0);
        }

        if (empty($forumTopicId))
        {
            $forumTopicId = $this->hForumTopicId(0);
        }

        if (empty($forumId))
        {
            $forumId = $this->hForumId(0);
        }

        $parameters = array(
            'hForum' => $this->hForumFileId.'/'.$forumId.'/'.$forumTopicId.'/'.$forumPostId
        );

        if ($postLink)
        {
            $parameters['hForumPostId'] = $forumPostId;
        }

        if (!empty($extraParameter))
        {
            $parameters[$extraParameter] = 1;
        }

        return $this->hFilePath.'?'.$this->getQueryString($parameters);
    }

    public function getPostLink($parameter = '', $postId = 0)
    {
        # @return string

        # @description
        # <h2>Getting a Forum Post Link</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->getLink($postId, 0, 0, true, $parameter);
    }
}

?>