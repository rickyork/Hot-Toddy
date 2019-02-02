<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File Comments Library
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

class hFileCommentsLibrary extends hPlugin {

    private $hFileCommentsDatabase;
    private $hCalendarDatabase;
    private $hSearch;

    private $commentQuestionAnswer = true;
    private $hasWrite = false;

    public function hConstructor()
    {
        $this->getPluginFiles();

        $this->hFileCommentsDatabase = $this->database('hFile/hFileComments');
        $this->hCalendarDatabase = $this->database('hCalendar');

        $this->hasWrite();

        $this->hSearch = $this->library('hSearch');
    }

    public function hasWrite($fileId = 0)
    {
        if (empty($fileId))
        {
            $fileId = $this->hFileId;
        }

        $this->hasWrite = ($this->isLoggedIn() && $this->hFiles->hasPermission($fileId, 'rw'));

        return $this->hasWrite;
    }

    /**
    * This handles things in the legacy way: calling the "Comments" plugin brought
    * back a template that included the post, author, date, comments, and comment form.
    *
    */
    public function getCommentsAndDocument()
    {
        $variables['hFileDocument'] = $this->hFileDocument;

        $date = (int) $this->hCalendarDatabase->getFileDate($this->hFileId);

        if (!empty($date))
        {
            $filePosted = date(
                $this->hFileCommentsDateFormat('F j, Y'),
                $date
            )." by ";

            switch (true)
            {
                case $this->hCalendarBlogFullName(false):
                {
                    $filePosted .= $this->user->getFullName($this->hUserId);
                    break;
                }
                case $this->hCalendarBlogFirstName(false):
                {
                    $filePosted .= $this->user->getFirstName($this->hUserId);
                    break;
                }
                default:
                {
                    $filePosted .= $this->user->getUserName($this->hUserId);
                    break;
                }
            }

            $variables['hFilePosted'] = $filePosted;
        }
        else
        {
            $variables['hFilePosted'] = '';
        }

        return $this->getComments($variables);
    }

    public function getComments(array $variables = array())
    {
        if ($this->hFileCommentsEnabled(true))
        {
            $this->postComment();
        }

        $comments = $this->hFileCommentsDatabase->getComments($this->hFileId);

        if ($this->hFileCommentsPagation(false))
        {
            $count = $this->hFileCommentsDatabase->getResultCount();
            $this->hSearch->setParameters($count);
        }

        $this->hSearchNavigationAnchor = '#hFileComments';

        $html = '';

        foreach ($comments as $comment)
        {
            $html .= $this->getTemplate('Comment', $comment);
        }

        $variables = array_merge(
            array(
                'comments' => $html,
                'commentFormAction' => $this->hFilePath,
                'commentsWebsiteLinkURL' => $this->hFileCommentsWebsiteLinkURL(true),
                'commentQuestionAnswer' => $this->commentQuestionAnswer,
                'commentQuestion' => $this->hFileCommentQuestion('What Year is it?'),
                'name' => $this->getFieldValue(
                    'hFileCommentName',
                    $this->user->getFullName()
                ),
                'email' => $this->getFieldValue(
                    'hFileCommentEmail',
                    $this->user->getUserEmail()
                ),
                'website' => $this->getFieldValue('hFileCommentWebsite'),
                'comment' => $this->getFieldValue('hFileComment'),
                'commentError' => isset($_POST['hFileComment']) && empty($_POST['hFileComment']),
                'commentEmailError' => isset($_POST['hFileCommentEmail']) && empty($_POST['hFileCommentEmail']),
                'commentNameError' => isset($_POST['hFileCommentName']) && empty($_POST['hFileCommentName']),
                'commentQuestionError' => isset($_POST['hFileCommentQuestion']) && empty($_POST['hFileCommentQuestion']),
                'commentRememberMe' => isset($_COOKIE['hFileCommentName']) && !empty($_COOKIE['hFileCommentName']),
                'commentWebsiteLabel' => $this->hFileCommentWebsiteLabel('Website'),
                'commentPagation' => $this->hFileCommentsPagation(false)? $this->hSearch->getNavigationHTML() : ''
            ),
            $variables
        );

        $this->hSearchNavigationAnchor = '';

        if (!isset($variables['hFileDocument']))
        {
            $variables['hFileDocument'] = '';
        }

        if (!isset($variables['hFilePosted']))
        {
            $variables['hFilePosted'] = '';
        }

        return $this->getTemplate('Comments', $variables);
    }

    public function postComment($fileId = 0)
    {
        if (empty($fileId))
        {
            $fileId = $this->hFileId;
        }

        hString::scrubArray($_COOKIE, 75);
        hString::scrubArray($_GET);

        if (isset($_POST['hFileCommentName']) && !empty($_POST['hFileComment']))
        {
            if ($this->hFileCommentQuestion('What Year is it?') || $this->hFileCommentAnswer(date('Y')))
            {
                if (empty($_POST['hFileCommentQuestion']))
                {
                    $this->commentQuestionAnswer = false;
                    return -34;
                }

                if ($this->hFileCommentQuestion('What Year is it?') == 'What year is it?')
                {
                    if ($_POST['hFileCommentQuestion'] != date('Y') && $_POST['hFileCommentQuestion'] != date('y'))
                    {
                        $this->commentQuestionAnswer = false;
                        return -33;
                    }
                }
                else
                {
                    if (!stristr($this->hFileCommentAnswer(date('Y')), trim($_POST['hFileCommentQuestion'])))
                    {
                        $this->commentQuestionAnswer = false;
                        return -34;
                    }
                }
            }

            if ($this->commentQuestionAnswer)
            {
                $fileCommentId = $this->hFileCommentsDatabase->insertComment(
                    array(
                        'hFileId'             => (int) $fileId,
                        'hUserId'             => $this->isLoggedIn()? (int) $_SESSION['hUserId'] : 0,
                        'hFileCommentName'    => hString::scrubValue($_POST['hFileCommentName']),
                        'hFileCommentEmail'   => hString::scrubValue($_POST['hFileCommentEmail']),
                        'hFileCommentWebsite' => hString::scrubValue($_POST['hFileCommentWebsite']),
                        'hFileComment'        => hString::scrubHTML($_POST['hFileComment'])
                    )
                );

                $this->sendMail(
                    'hFileComments',
                    array(
                        'hFileCommentId'          => (int) $fileCommentId,
                        'hFileTitle'              => $this->getFileTitle($fileId),
                        'hFileComments'           => nl2br($_POST['hFileComment']),
                        'hFileCommentName'        => $_POST['hFileCommentName'],
                        'hFileCommentEmail'       => $_POST['hFileCommentEmail'],
                        'hFileCommentWebsite'     => $_POST['hFileCommentWebsite'],
                        'hUserEmail'              => $this->user->getUserEmail($this->getFileOwner($fileId)),
                        'hContactDisplayName'     => $this->user->getFullName($this->getFileOwner($fileId)),
                        'hFilePath'               => $this->getURLByFileId($fileId),
                        'hFrameworkName'          => $this->hFrameworkName,
                        'hFrameworkAdministrator' => $this->hFrameworkAdministrator
                    )
                );

                if (isset($_POST['hFileCommentRemember']) && !empty($_POST['hFileCommentRemember']))
                {
                    $this->setCookie('hFileCommentName', $_POST['hFileCommentName']);
                    $this->setCookie('hFileCommentEmail', $_POST['hFileCommentEmail']);
                    $this->setCookie('hFileCommentWebsite', $_POST['hFileCommentWebsite']);
                }
                else
                {
                    $this->setCookie('hFileCommentName', '', time() - 1000);
                    $this->setCookie('hFileCommentEmail', '', time() - 1000);
                    $this->setCookie('hFileCommentWebsite', '', time() - 1000);
                }

                if ($fileId == $this->hFileId)
                {
                    header('Location: '.$this->href());
                }
            }
        }

        if ($this->hasWrite && isset($_GET['delete']) && !empty($_GET['fileCommentId']))
        {
            $this->hFileCommentsDatabase->deleteComment((int) $_GET['fileCommentId']);
        }

        if ($this->hasWrite && isset($_GET['approve']) && !empty($_GET['fileCommentId']))
        {
            $this->hFileCommentsDatabase->approveComment((int) $_GET['fileCommentId']);
        }

        if ($this->hasWrite && isset($_GET['deny']) && !empty($_GET['fileCommentId']))
        {
            $this->hFileCommentsDatabase->denyComment((int) $_GET['fileCommentId']);
        }

        if (isset($fileCommentId))
        {
            return $fileCommentId;
        }
        else if (isset($_GET['fileCommentId']))
        {
            return (int) $_GET['fileCommentId'];
        }
        else
        {
            return 0;
        }
    }

    private function getFieldValue($field, $default = '')
    {
        if (isset($_POST[$field]))
        {
            return $_POST[$field];
        }
        else if (isset($_COOKIE[$field]))
        {
            return $_COOKIE[$field];
        }
        else
        {
            if ($this->isLoggedIn())
            {
                return $default;
            }
            else
            {
                return '';
            }
        }
    }
}

?>