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
# <h1>Forum Threads UI</h1>
# <p>
#    This plugin provides UI for viewing all threads posted to a forum topic.
# </p>
# @end

class hForumThreads extends hPlugin {

    private $hSubscription;
    private $hForum;
    private $hForumDatabase;
    private $hSearch;
    private $posts = array();

    public function hConstructor()
    {
        hString::scrubArray($_GET);

        $this->hSubscription = $this->library('hSubscription');
        $this->hForum = $this->library('hForum');
        $this->hForumDatabase = $this->database('hForum');
        $this->hSearch = $this->library('hSearch');

        $this->hForumThreads = true;

        $this->breadcrumbs();

        $this->hSearch->getCSS();

        $this->hSearchResultsPerPage = $this->hForumResultsPerPage(10);
        $this->hSearchPagesPerChapter = $this->hForumPagesPerChapter(7);

        $this->getPluginCSS();

        $this->hForumPostLimit = $this->hSearch->getLimit();

        $posts = $this->hForumDatabase->getThreads(
            $this->hForumTopicId,
            $this->hForumIsModerator,
            false
        );

        $count = $this->hDatabase->getResultCount();

        $this->hSearch->setParameters($count);

        // Get all sticky posts
        $sticky = $this->hForumDatabase->getThreads(
            $this->hForumTopicId,
            $this->hForumIsModerator,
            true
        );

        if ($this->hSearch->hSearchPage === 1)
        {
            $posts = array_merge(
                $this->hForumDatabase->getThreads(
                    $this->hForumTopicId,
                    $this->hForumIsModerator,
                    true
                ),
                $posts
            );
        }

        $this->getThreads($posts, false);

        $this->hFileTitle = $this->hForumDatabase->getTopic($this->hForumTopicId);

        $this->hFileDocument = $this->getTemplate(
           'Threads',
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
               'hFileId' => $this->hForumFileId,
               'hForumId' => $this->hForumId,
               'hForumTopicId' => $this->hForumTopicId,
               'isLoggedIn' => $this->isLoggedIn(),
               'hForumSubscriptionLabel' => ($this->hForumTopics->isSubscribed($this->hForumTopicId)? 'Uns' : 'S').'ubscribe',
               'hForumPosts' => $this->posts
           )
        );
    }

    private function getThreads($query)
    {
        $posts = array();

        foreach ($query as $data)
        {
            $post = array(
                'hForumPostId' => (int) $data['hForumPostId'],
                'hForumPostLink' => $this->hForum->getLink($data['hForumPostId']),
                'hForumPostSubject' => $data['hForumPostSubject'],
                'hForumPostAuthor' => $this->hForumDatabase->getPostAuthorUserName($data['hUserId']),
                'hForumPostReplyCount'  => (int) $data['hForumPostResponseCount'],
                'hForumPostReadCount' => '', // Not Implemented
                'hForumIsAdministrator' => $this->hForumIsModerator,
                'hForumPostHasEdit' => $this->hForum->hasEdit($data['hUserId']),
                'hForumPostEditLink' => $this->hForum->getPostLink(
                    'hForumEdit',
                    $data['hForumPostId']
                ),
                'hForumPostIsSticky' => (bool) $data['hForumPostIsSticky'],
                'hForumPostIsLocked' => (bool) $data['hForumPostIsLocked'],
                'hForumPostReplyLink' => $this->hForum->getPostLink(
                    'hForumReply',
                    $data['hForumPostId']
                )
            );

            $post['hForumLastPostDate'] = $this->hForum->getDate(
                !empty($data['hForumPostLastResponse'])? $data['hForumPostLastResponse'] : $data['hForumPostDate'],
                !empty($data['hForumPostLastResponseBy'])? $data['hForumPostLastResponseBy'] : $data['hUserId']
            );

            array_push($posts, $post);
        }

        $this->posts = $this->hDatabase->getResultsForTemplate($posts);
    }

    private function breadcrumbs()
    {
        // Home -> Topics -> Threads -> Thread
        $this->makeBreadcrumbs(
            array(
                'self' => $this->hForumDatabase->getTopic($this->hForumTopicId)
            ),
             true
        );
    }
}

?>