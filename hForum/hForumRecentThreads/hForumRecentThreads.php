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
# <h1>Forum Recent Threads Plugin</h1>
# <p>
#    This plugin provides a view for the user to see all recent threads posted across
#    all forums and topics.
# </p>
# @end

class hForumRecentThreads extends hPlugin {

    private $hSubscription;
    private $hForum;
    private $hForumPost;
    private $hSearch;
    private $hForumDatabase;

    private $posts;

    public function hConstructor()
    {
        hString::scrubArray($_GET);

        $this->hSubscription  = $this->library('hSubscription');
        $this->hForum         = $this->library('hForum');
        $this->hForumDatabase = $this->database('hForum');
        $this->hSearch        = $this->library('hSearch');

        $this->hForumThreads = true;

        $this->breadcrumbs();

        $this->hSearch->getCSS();

        $this->hSearchResultsPerPage = $this->hForumResultsPerPage(10);
        $this->hSearchPagesPerChapter = $this->hForumPagesPerChapter(7);

        $this->getPluginCSS();

        //$hFileId, $time, $approved = 0, $limit = null, $sort = 'DESC'
        $posts = $this->hForumDatabase->getRecentThreads(
            $this->hForumFileId,
            $this->hForumRecentPostsThreshold('3 months ago'),
            $this->hForumIsModerator,
            $this->hSearch->getLimit()
        );

        $count = $this->hDatabase->getResultCount();

        $this->hSearch->setParameters($count);

        $this->getThreads($posts);

        $this->hFileTitle = 'Recent Posts (from '.$this->hForumRecentPostsThreshold('3 months ago').' to now)';

        $html = '';

        foreach ($this->posts as $forumTopicId => $post)
        {
            $html .= $this->getTemplate(
                'Topic',
                array(
                    'hForumTopic' => $this->hForumDatabase->getTopic($forumTopicId),
                    'hForumTopicId' => (int) $forumTopicId,
                    'hForumPosts' => $this->hDatabase->getResultsForTemplate($post)
                )
            );
        }

        $this->hFileDocument = $this->getTemplate(
           'Recent',
           array(
               'hForumSearchPages' => $this->hSearch->hSearchPageCount > 1,
               'hForumSearchCount' => $count,
               'hForumSearchPage' => $this->hSearch->hSearchPage,
               'hForumSearchPageCount' => $this->hSearch->hSearchPageCount,
               'hForumSearchNavigation' => $this->hSearch->getNavigationHTML(
                   $this->hFilePath,
                   array(
                       'hForum' => $_GET['hForum']
                   )
               ),
               'hFilePath' => $this->hFilePath,
               'hForumTopics' => $html
           )
        );
    }

    private function getThreads($query)
    {
        $posts = array();

        foreach ($query as $data)
        {
            $forumTopicId = (int) $data['hForumTopicId'];

            if (!isset($posts[$forumTopicId]))
            {
                $posts[$forumTopicId] = array();
            }

            $post = array(
                'hForumPostId'          => (int) $data['hForumPostId'],
                'hForumPostLink'        => $this->hForum->getLink($data['hForumPostId']),
                'hForumPostSubject'     => $data['hForumPostSubject'],
                'hForumPostAuthor'      => $this->hForumDatabase->getPostAuthorUserName($data['hUserId']),
                'hForumPostReplyCount'  => (int) $data['hForumPostResponseCount'],
                'hForumPostReadCount'   => '', // Not Implemented
                'hForumIsAdministrator' => $this->hForumIsModerator,
                'hForumPostHasEdit'     => $this->hForum->hasEdit($data['hUserId']),
                'hForumPostEditLink'    => $this->hForum->getPostLink('hForumEdit', $data['hForumPostId']),
                'hForumPostIsSticky'    => (bool) $data['hForumPostIsSticky'],
                'hForumPostIsLocked'    => (bool) $data['hForumPostIsLocked'],
                'hForumPostReplyLink'   => $this->hForum->getPostLink('hForumReply', $data['hForumPostId'])
            );

            $post['hForumLastPostDate'] = $this->hForum->getDate(
                !empty($data['hForumPostLastResponse'])? $data['hForumPostLastResponse'] : $data['hForumPostDate'],
                !empty($data['hForumPostLastResponseBy'])? $data['hForumPostLastResponseBy'] : $data['hUserId']
            );

            array_push($posts[$forumTopicId], $post);
        }

        $this->posts = $posts;
    }

    private function breadcrumbs()
    {
        // Home -> Topics -> Threads -> Thread
        $this->makeBreadcrumbs(
            array(
                'self' => $this->hForum->getTopicName($this->hForumTopicId)
            ),
             true
        );
    }
}

?>