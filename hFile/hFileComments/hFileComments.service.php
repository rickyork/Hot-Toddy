<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File Comments Service
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

class hFileCommentsService extends hService {

    private $hFileComments;
    private $hFileCommentsDatabase;

    public function hConstructor()
    {
        $this->hFileComments = $this->library('hFile/hFileComments');
        $this->hFileCommentsDatabase = $this->database('hFile/hFileComments');
    }

    private function hasWrite()
    {
        $fileId = (int) $this->get('fileId', 0);

        if (empty($fileId))
        {
            $this->JSON(-5);
            return false;
        }

        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return false;
        }

        if (!$this->inGroup('Website Administrators') && $this->hFileComments->hasWrite($fileId))
        {
            $this->JSON(-1);
            return false;
        }

        return true;
    }

    public function delete()
    {
        if (!$this->hasWrite())
        {
            return;
        }

        $fileCommentId = (int) $this->get('fileCommentId');

        if (empty($fileCommentId))
        {
            $this->JSON(-5);
            return;
        }

        $this->hFileCommentsDatabase->deleteComment($fileCommentId);

        $this->JSON(1);
    }

    public function approve()
    {
        if (!$this->hasWrite())
        {
            return;
        }

        $fileCommentId = (int) $this->get('fileCommentId');

        if (empty($fileCommentId))
        {
            $this->JSON(-5);
            return;
        }

        $this->hFileCommentsDatabase->approveComment($fileCommentId);

        $this->JSON(1);
    }

    public function deny()
    {
        if (!$this->hasWrite())
        {
            return;
        }

        $fileCommentId = (int) $this->get('fileCommentId');

        if (empty($fileCommentId))
        {
            $this->JSON(-5);
            return;
        }

        $this->hFileCommentsDatabase->denyComment($fileCommentId);

        $this->JSON(1);
    }

    public function postComment()
    {
        $fileId = (int) $this->post('fileId');

        if (empty($fileId))
        {
            $this->JSON(-5);
            return;
        }

        $fileCommentId = $this->hFileComments->postComment($fileId);

        if ($fileCommentId <= 0)
        {
            $this->JSON($fileCommentId);
            return;
        }
        else
        {
            $comments = $this->hFileCommentsDatabase->getComments(
                $fileId,
                $fileCommentId
            );

            $html = '';

            foreach ($comments as $comment)
            {
                $html .= $this->getTemplate(
                    'Comment',
                    $comment
                );
            }

            $this->JSON(
                array(
                    'comments' => $html
                )
            );
        }
    }
}

?>