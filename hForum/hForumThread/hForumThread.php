<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Forum Thread
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
# <h1>Forums Thread UI</h1>
# <p>
#    This plugin provides a UI for viewing an individual thread posted to a forum topic.
# </p>
# @end

class hForumThread extends hPlugin {

    private $hSubscription;
    private $hForum;
    private $hForumDatabase;
    private $hSearch;
    private $posts = array();
    private $isLocked = false;

    public function hConstructor()
    {
        $this->hSubscription = $this->library('hSubscription');
        $this->hForum = $this->library('hForum');
        $this->hForumDatabase = $this->database('hForum');
        $this->hSearch = $this->library('hSearch');

        $this->hSearch->getCSS();

        $this->hSearchResultsPerPage = $this->hForumResultsPerPage(10);
        $this->hSearchPagesPerChapter = $this->hForumPagesPerChapter(7);

        $this->getPluginCSS();

        if (!$this->hForumAppend(false))
        {
            $this->hFileDocument = '';
        }

        // Make breadcrumbs before changing the title.
        if (!$this->hForumAppend && class_exists('hFileBreadcrumbs'))
        {
            $this->addBreadcrumbs();
        }

        if ($this->hForumPostId)
        {
            $this->hForumPostLimit = $this->hSearch->getLimit();

            $query = $this->hForumDatabase->getThreads(
                $this->hForumTopicId,
                $this->hForum->hasPrivileges(),
                0,
                0,
                $this->hForumPostId
            );

            $count = $this->hDatabase->getResultCount();

            $this->getPosts($query);

            $this->hSearch->setParameters($count);

            $this->hFileTitle = $this->hForumDatabase->getPostSubject($this->hForumPostId);

            $this->hFileDocument .= $this->getTemplate(
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
                    'hForumTopicId' => $this->hForumTopicId,
                    'hForumPostIsLocked' => $this->isLocked,
                    'hForumPostId' => $this->hForumPostId,
                    'isLoggedIn' => $this->isLoggedIn(),
                    'hForumSubscriptionLabel' => ($this->hForumPosts->isSubscribed($this->hForumPostId)? 'Uns' : 'S').'ubscribe',
                    'hForumPosts' => $this->posts
                )
            );
        }
    }

    private function getPosts($query)
    {
        $this->hForumPostSort = 'ASC';

        $postCounter = 0;

        $posts = array();

        foreach ($query as $data)
        {
            if ($data['hForumPostId'] == $this->hForumPostId)
            {
                $this->isLocked = (bool) $data['hForumPostIsLocked'];
            }

            switch ($data['hForumPostInputMethod'])
            {
                case 'bbcode':
                {
                    $data['hForumPost'] = $this->hForum->bbCodeToHTML($data['hForumPost']);
                    break;
                }
                case 'wysiwyg':
                {
                    $data['hForumPost'] = hString::decodeHTML($data['hForumPost']);
                    break;
                }
            }

            $postCount = $this->hForumDatabase->getUserPostCount($data['hUserId']);

            $icon = $this->hForum->getUserRank($data['hUserId'], $postCount);

            $post = array(
                'hForumPostId' => $data['hForumPostId'],
                'hForumPostOdd' => ($postCounter & 1),
                'hForumPostDate' => date(
                    $this->hForumPostDateFormat('l, F j, Y h:i:s A T'),
                    $data['hForumPostDate']
                ),
                'hForumPostSubject' => $data['hForumPostSubject'],
                'hForumPostIsLocked' => (bool) $data['hForumPostIsLocked'],
                'hForumPostIsSticky' => (bool) $data['hForumPostIsSticky'],
                'hForumPostReplyLink' => $this->hForum->getPostLink(
                    'hForumReply',
                    $data['hForumPostId']
                ),
                'hForumPostReplyWithQuoteLink' => $this->hForum->getPostLink(
                    'hForumQuote',
                    $data['hForumPostId']
                ),
                'hForumPostHasEdit' => $this->hForum->hasEdit($data['hUserId']),
                'hForumPostEditLink' => $this->hForum->getPostLink(
                    'hForumEdit',
                    $data['hForumPostId']
                ),
                'hForumPostDisplayName' => $this->hForumPostDisplayName(false) || $this->inGroup('Employees', $data['hUserId']),
                'hForumPostAuthorRank' => $icon['html'],
                'hForumPostDisplayTitle' => $this->hForumPostDisplayTitle(false) || $this->inGroup('Employees', $data['hUserId']),
                'hUserName' => $this->user->getUserName($data['hUserId']),
                'hContactDisplayName' => $this->user->getFullName($data['hUserId']),
                'hContactTitle' => $this->user->getTitle($data['hUserId']),
                'hForumPostAuthorTitle' => $icon['title'],
                'hForumPostCount' => $postCount,
                'hForumPost' => $data['hForumPost']
            );

            array_push($posts, $post);
            $postCounter++;

/*
            if ($this->hForumDatabase->postHasChildren($data['hForumPostId']))
            {
                $postCounter++;
                $this->getPost($data['hForumPostId'], $postCounter, $posts);
            }
*/
        }

        $this->posts = $this->hDatabase->getResultsForTemplate($posts);
    }

    private function addBreadcrumbs()
    {
        // Home -> Topics -> Threads -> Thread
        $this->makeBreadcrumbs(
            array(
                "{$this->hFilePath}?hForum={$this->hForumFileId}/{$this->hForumId}/{$this->hForumTopicId}" => $this->hForumDatabase->getTopic($this->hForumTopicId),
                'self' => $this->hForumDatabase->getPostSubject($this->hForumPostId)
            ),
             true
        );
    }
}

?>