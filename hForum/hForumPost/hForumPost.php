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
# <h1>Forum Post Plugin</h1>
# <p>
#    This plugin provides a UI for posting to a forum.
# </p>
# @end

class hForumPost extends hPlugin {

    private $hForm;
    private $hForum;
    private $hForumDatabase;
    private $hSubscription;
    private $hTidy;
    private $hMail;

    public function hConstructor()
    {
        $this->getPluginCSS();

        if ($this->isLoggedIn())
        {
            $this->hForum = $this->library('hForum');
            $this->hForumDatabase = $this->database('hForum');

            $this->setBreadcrumbs();

            if ($this->hForumTopicId && !$this->hForumDatabase->topicIsLocked($this->hForumTopicId) || $this->hForumIsModerator)
            {
                if ($this->hForumPostId && !$this->hForumDatabase->postIsLocked($this->hForumPostId) || $this->hForumIsModerator)
                {
                    $this->hForm = $this->library('hForm');
                    $this->getForm();
                }
                else
                {
                    $this->hFileDocument = $this->getTemplate('Post Is Locked');
                }
            }
            else
            {
                $this->hFileDocument = $this->getTemplate('Topic Is Locked');
            }
        }
        else
        {
            // The -s switch preceding the value allows these messages to be overridden via a per-document variable.
            $this->hUserLoginMessage = $this->getTemplate('Login Message');
            $this->hUserRegisterMessage = $this->getTemplate('Registration Message');

            $this->notLoggedIn();
        }

        $this->hFileDocument = $this->getTemplate(
            'Wrapper',
            array(
                'hFileDocument' => $this->hFileDocument
            )
        );
    }

    private function postHandler()
    {
        if (isset($_POST['hForumPostForm']))
        {
            $success = false;
            $edit    = false;
            $newPost = false;

            $message = '';
            $message = hString::scrubHTML($_POST['hForumPost']);

            if (isset($_POST['hForumPost']) && $this->hTidyEnabled(false))
            {
                $this->hTidy = $this->library('hTidy');
                $message = $this->hTidy->getHTML($message);
            }

            switch (true)
            {
                case (isset($_GET['hForumEdit']) && !empty($_GET['hForumPostId']) && $this->hForum->hasEdit()):
                {
                    if ($this->hForum->hasEdit($this->hForumDatabase->getPostAuthor((int) $_GET['hForumPostId'])))
                    {
                        $columns = array(
                            'hForumPostId'           => (int) $_GET['hForumPostId'],
                            'hForumPostSubject'      => hString::scrubValue($_POST['hForumPostSubject']),
                            'hForumPost'             => $message,
                            'hForumPostLastModified' => time(),
                            'hForumPostLastModifiedBy' => (int) $_SESSION['hUserId']
                        );

                        $this->getAdminColumns($columns, $this->hForumIsModerator);

                        $success = true;
                        $edit    = true;
                    }
                    break;
                }
                case (isset($_GET['hForumReply'])):
                case (isset($_GET['hForumQuote'])):
                {
                    $forumPostParentId = (int) $_GET['hForumPostId'];
                    $forumPostRootId   = $this->hForumPostId;
                }
                default:
                {
                    if ($this->hForumTopics->hasPermission($this->hForumTopicId, 'r') || $this->hForumIsModerator)
                    {
                        $columns = array(
                            'hForumPostId'           => 0,
                            'hForumTopicId'          => (int) $this->hForumTopicId,
                            'hUserId'                => (int) $_SESSION['hUserId'],
                            'hForumPostSubject'      => hString::scrubValue($_POST['hForumPostSubject']),
                            'hForumPost'             => $message,
                            'hForumPostInputMethod'  => $this->hForumPostMethod('wysiwyg'),
                            'hForumPostParentId'     => (isset($forumPostParentId)? (int) $forumPostParentId : 0),
                            'hForumPostRootId'       => (isset($forumPostRootId)?   (int) $forumPostRootId   : 0),
                            'hForumPostDate'         => time(),
                            'hForumPostLastResponse' => time(),
                            'hForumPostLastModified' => 0,
                            'hForumPostLastModifiedBy' => 0
                        );

                        $this->getAdminColumns($columns, $this->hForumIsModerator);
                        $success = true;
                        $newPost = true;
                    }
                }
            }

            if (isset($columns) && count($columns))
            {
                $forumPostId = $this->hForumDatabase->savePost($columns);

                if ($newPost)
                {
                    if (isset($forumPostRootId))
                    {
                        $this->hForumDatabase->markLastResponse($forumPostRootId);
                    }
                    else
                    {
                        $this->hForumDatabase->markLastResponseToTopic($forumPostId);
                    }
                }
            }

            if (!$this->hForumPostId && empty($_GET['hForumPostId']))
            {
                $forumPostRootId = $forumPostId;
            }

            $forum = $this->hForumFileId.'/'.$this->hForumId.'/'.$this->hForumTopicId.'/'.$forumPostRootId;

            if ($success && !$edit)
            {
                $this->hSubscription = $this->library('hSubscription');
                $this->hSubscription->save('hForumPosts', $forumPostRootId);

                $userIds = $this->hSubscription->getMultipleSubscriptions(
                    array(
                        'hForums'      => $this->hForumId,
                        'hForumTopics' => $this->hForumTopicId,
                        'hForumPosts'  => (int) $forumPostRootId
                    )
                );

                $message = $this->hForumDatabase->getPostBody((int) $forumPostId);

                if ($this->hForumPostMethod('wysiwyg') == 'bbcode')
                {
                    $message = $this->hForum->formatBBCode($message);
                }

                $message = preg_replace('/\r/ms', '', $message);

                $topic = $this->hForumDatabase->getTopic($this->hForumTopicId);

                $mailer = array_merge(
                    $_POST,
                    array(
                        'hFrameworkName'          => $this->hFrameworkName,
                        'hFrameworkAdministrator' => $this->hFrameworkAdministrator,
                        'hForumTopic'             => $topic,
                        'hForumPath'              => 'http://'.$this->hServerHost.$this->href($this->hFilePath),
                        'hForumPostSubjectFilter' => $topic,
                        'hForumPost'              => $message,
                        'hForumPostText'          => strip_tags(hString::decodeHTML($message)),
                        'hUserName'               => $this->user->getUserName(),
                        'hForumPostReplySubject'  => $this->hForumDatabase->getPostSubject($forumPostId),
                        'hForumPostLink'          => 'http://'.$this->hServerHost.$this->href($this->hFilePath, array('hForum' => $forum)),
                        'hForumPostReplyLink'     => 'http://'.$this->hServerHost.
                            $this->href(
                                $this->hFilePath,
                                array(
                                    'hForum'       => $forum,
                                    'hForumPostId' => $forumPostId,
                                    'hForumReply'  => 1
                                )
                            ),
                        'hForumPostUserName' => $this->user->getUserName()
                    )
                );

                foreach ($userIds as $userId)
                {
                    $firstName = $this->user->getFirstName($userId);

                    $this->sendMail(
                        $this->hForumMailTemplate('hForumPost'),
                        array_merge(
                            array(
                                'hContactDisplayName'      => $this->user->getFullName($userId),
                                'hContactFirstName'        => $firstName,
                                'hUserEmail'               => $this->user->getUserEmail($userId),
                                'hForumRecipientFirstName' => $firstName
                            ),
                            $mailer
                        )
                    );
                }
            }

            header('Location: '.$this->href($this->hFilePath, array('hForum' => $forum)));
            exit;
        }
    }

    private function getAdminColumns(&$columns, $admin)
    {
        if ($admin)
        {
            $columns['hForumPostIsSticky']   = (isset($_POST['hForumPostIsSticky'])? 1 : 0);
            $columns['hForumPostIsLocked']   = (isset($_POST['hForumPostIsLocked'])? 1 : 0);
            $columns['hForumPostIsApproved'] = (isset($_POST['hForumPostIsApproved'])? 1 : 0);
        }
        else
        {
            $columns['hForumPostIsApproved'] = ($this->hForumDatabase->topicIsModerated($this->hForumTopicId)? 0 : 1);
        }
    }

    private function getForm()
    {
        $this->postHandler();

        $data = $this->getPostAttributes(!empty($_GET['hForumPostId']));

        switch (true)
        {
            case (isset($_GET['hForumQuote'])):
            case (isset($_GET['hForumReply']) && !empty($_GET['hForumPostId'])):
            {
                $topic = $this->hForum->getPostTopic($_GET['hForumPostId'], 'reply');
                $title = 'Post a Reply';
                break;
            }
            case (isset($_GET['hForumEdit'])):
            {
                $topic = $this->hForum->getPostTopic($_GET['hForumPostId']);
                $title = 'Edit a Post';
                break;
            }
            case (empty($_GET['hForumPostId'])):
            default:
            {
                $topic = '';
                $title = 'New Post';
                break;
            }
        }

        $this->hForm->addDiv('hForumPostDiv');
        $this->hForm->addFieldset($title, '100%', '15%,85%');

        $this->hForm->addRequiredField('Please enter a subject for your post.');
        $this->hForm->addTextInput('hForumPostSubject', 'S:Subject:', 25, $topic);

        $this->getPrivateForm();

        $this->hForm->addRequiredField('Please enter a post.');

        switch ($this->hForumPostMethod('wysiwyg'))
        {
            case 'bbcode':
            {
//                    $this->form->field(
//                        array(
//                            'type' => 'bbcode',
//                            'value' => $this->getMessage($edit),
//                            'attributes' => array(
//                                'name' => 'hForum[message]',
//                                'id' => 'forum_post_message',
//                                'rows' => 20,
//                                'style' => 'width: 98%; height: 350px;'
//                            ),
//                            'cell' => array(
//                                'colspan' => 2,
//                                'style' => 'text-align: center;'
//                            )
//                        )
//                    );

                break;
            }
            case 'wysiwyg':
            {
                $this->hForm->addWYSIWYGInput(
                    'hForumPost',
                    'M:Message: -L',
                    $this->getMessage(),
                    '60,20',
                    '100%,350px',
                    array(
                        'CharacterMap',
                        'SpellChecker'
                    ),
                    'BasicSmiley'
                );

                break;
            }
            case 'text':
            {
                $this->hForm->addTextareaInput(
                    'hForumPost',
                    'M:Message: -L',
                    '60,15',
                    $this->getMessage()
                );

                break;
            }
        }

        if ($this->hForum->hasPrivileges())
        {
            $this->hForm->addTableCell('');
            $this->hForm->addCheckboxInput(
                'hForumPostIsSticky',
                'Sticky?',
                isset($_GET['hForumEdit'])? (int) $this->hForumDatabase->postIsSticky((int) $_GET['hForumPostId']) : 0
            );

            $this->hForm->addTableCell('');
            $this->hForm->addCheckboxInput(
                'hForumPostIsLocked',
                'Locked?',
                isset($_GET['hForumEdit'])? (int) $this->hForumDatabase->postIsLocked((int) $_GET['hForumPostId']) : 0
            );

            $this->hForm->addTableCell('');
            $this->hForm->addCheckboxInput(
                'hForumPostIsApproved',
                'Approved?',
                isset($_GET['hForumEdit'])? (int) $this->hForumDatabase->postIsApproved((int) $_GET['hForumPostId']) : 1
            );
        }

        $this->hForm->addSubmitButton('hForumSubmit', (empty($_GET['hForumEdit'])? 'Post' : 'Save'), 2);

        $this->hForm->setFormAttribute('action', $this->hFilePath.'?'.$this->getQueryString($_GET));

        $this->hFileDocument = $this->hForm->getForm('hForumPostForm');

        if (isset($_GET['hForumEdit']) || isset($_REQUEST['hForumReply']) || isset($_REQUEST['hForumQuote']))
        {
            $this->hForumAppend = true;
            $this->plugin('hForum/hForumThread');
        }
    }

    private function getQuoteWrapper($open = true)
    {
        if (isset($_GET['hForumQuote']))
        {
            switch ($this->hForumPostMethod('wysiwyg'))
            {
                case 'bbcode':  return "[".($open? '' : '/')."quote]\n";
                case 'text':    return '&'.($open? 'l' : 'r').'dquo;';
            }
        }
        else
        {
            return '';
        }
    }

    private function getMessage()
    {
        if (isset($_GET['hForumQuote']) || isset($_GET['hForumEdit']))
        {
            return(
                trim($this->getTemplate(
                    'Reply',
                    array(
                        'hForumQuote' => isset($_GET['hForumQuote']) && $this->hForumPostMethod('wysiwyg') == 'wysiwyg',
                        'hUserName'   => $this->hForumDatabase->getPostAuthorUserName((int) $_GET['hForumPostId']),
                        'hForumPost'  => $this->hForumDatabase->getPostBody((int) $_GET['hForumPostId'])
                    )
                ))
            );
        }

        return '';
    }

    private function setBreadcrumbs()
    {
        $links = array();

        switch (true)
        {
            case (isset($_GET['hForumQuote'])):
            case (isset($_GET['hForumReply'])):
            case (isset($_GET['hForumEdit'])):
            {
                $links = array(
                    "{$this->hFilePath}?hForum={$_GET['hForum']}" =>$this->hForumDatabase->getPostSubject($this->hForumPostId),
                    'self' => isset($_GET['hForumEdit'])? 'Edit Post' : 'Post Reply'
                );
                break;
            }
            default:
            {
                $links = array(
                    'self' => 'New Post'
                );
                break;
            }
        }

        $this->makeBreadcrumbs(
            array_merge(
                array(
                    "{$this->hFilePath}?hForum={$this->hForumFileId}/{$this->hForumId}/{$this->hForumTopicId}" => $this->hForumDatabase->getTopic($this->hForumTopicId),
                ), $links
            ),
             true
        );
    }

    private function getPostAttributes($edit = false)
    {
        if ($edit)
        {
            return $this->hForumPosts->selectAssociative(
                array(
                    'hForumPostIsSticky',
                    'hForumPostIsLocked',
                    'hForumPostIsApproved'
                ),
                $this->hForumPostId
            );
        }

        if ($this->hasPrivileges())
        {
            return (
                array(
                    'hForumPostIsSticky'   => (isset($_POST['hForum']['hForumPostIsSticky'])?   (int) $_POST['hForum']['hForumPostIsSticky']   : 0),
                    'hForumPostIsLocked'   => (isset($_POST['hForum']['hForumPostIsLocked'])?   (int) $_POST['hForum']['hForumPostIsLocked']   : 0),
                    'hForumPostIsApproved' => (isset($_POST['hForum']['hForumPostIsApproved'])? (int) $_POST['hForum']['hForumPostIsApproved'] : 1)
                )
            );
        }
        else
        {
            return (
                array(
                    'hForumPostIsSticky'   => 0,
                    'hForumPostIsLocked'   => 0,
                    'hForumPostIsApproved' => ($this->hForumDatabase->topicIsModerated($this->hForumTopicId)? 0 : 1)
                )
            );
        }
    }
}

?>