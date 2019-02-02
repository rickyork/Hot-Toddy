<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework
#//\\ @@@@  @@@@\\\\\|
#//\\\@@@@| @@@@\\\\\|
#//\\\ @@ |\\@@\\\\\\| https://github.com/rickyork/Hot-Toddy
#//\\\\  ||   \\\\\\\| © Copyright 2019 Richard York, All rights Reserved
#//\\\\  \\_   \\\\\\|
#//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#//\\\\\  ----  \@@@@| https://github.com/rickyork/Hot-Toddy/blob/master/License
#//@@@@@\       \@@@@|
#//@@@@@@\     \@@@@@|
#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
# @description
# <h1>Forum UI</h1>
# <p>
#    This plugin provides forums to Hot Toddy.  Simple attach the <var>hForum</var> plugin
#    to a page, then navigate to that page to set-up a forum.
# </p>
# @end

class hForum extends hPlugin {

    private $hForum;

    function hConstructor()
    {
        $this->hEditorTemplateEnabled = false;

        if (isset($_GET['hForum']))
        {
            /**
            *  forum.html?hForum=3/2/4
            *
            *  hFileId/hForumId/hForumTopicId/hForumPostId
            *  0/1/2/3
            *
            */
            $forum = explode('/', $_GET['hForum']);
            $count = count($forum);

            if ($count)
            {
                $id = $this->hFileSymbolicLinkTo(0, (int) $forum[0]);

                if (empty($id))
                {
                    $id = (int) $forum[0];
                }

                $this->hForumFileId = (int) $id;
            }
            else
            {
                $this->hForumFileId = $this->hFileId;
            }

            if ($count > 1)
            {
                $this->hForumId = (int) $forum[1];
            }

            if ($count > 2)
            {
                $this->hForumTopicId = (int) $forum[2];
            }

            if ($count > 3)
            {
                $this->hForumPostId = (int) $forum[3];
            }
        }

        $this->hForum = $this->library('hForum');

        $this->hForumIsAdministrator = $this->hForum->isAdministrator();
        $this->hForumIsModerator = $this->hForum->isModerator() || $this->hForumIsAdministrator;

        if ($this->hForumIsAdministrator)
        {
           $this->jQuery('Sortable');
        }

        $this->getPluginFiles();
        $this->getPluginCSS('ie6');

        $this->plugin('hApplication/hApplicationStatus');

        switch (true)
        {
            case isset($_GET['hForumReply']):  // Reply
            case isset($_GET['hForumEdit']):   // Edit Post
            case isset($_GET['hForumQuote']):  // Reply with quote
            case isset($_GET['hForumPostId']) && empty($_GET['hForumPostId']): // New Post
            {
                $this->plugin('hForum/hForumPost');
                break;
            }
            case $this->hForumPostId(null):
            {
                $this->plugin('hForum/hForumThread');
                break;
            }
            case isset($_GET['hForumRecent']):
            {
                $this->plugin('hForum/hForumRecentThreads');
                break;
            }
            case $this->hForumTopicId(null):
            {
                $this->plugin('hForum/hForumThreads');
                break;
            }
            default:
            {
                $this->plugin('hForum/hForumTopics');
                break;
            }
        }

        $this->hFileDocument = $this->getTemplate(
            'Forum',
            array(
                'hFileDocument' => $this->hFileDocument,
                'hFilePath'     => $this->cloakSitesPath($this->hFilePath)
            )
        );
    }
}

?>