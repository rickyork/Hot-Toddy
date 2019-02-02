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

class hFileCommentsDatabase extends hPlugin {

    private $hSearch;
    private $count;

    public function hConstructor()
    {
        $this->hSearch = $this->library('hSearch');
    }

    public function modifyCalendarResource($hFileId)
    {
        $query = $this->hCalendarFiles->select(
            array(
                'hCalendarId',
                'hCalendarCategoryId'
            ),
            array(
                'hFileId' => (int) $hFileId
            )
        );

        if (count($query))
        {
            foreach ($query as $data)
            {
                $this->hCalendarResources->update(
                    array(
                        'hCalendarResourceLastModified' => time()
                    ),
                    array(
                        'hCalendarId' => (int) $data['hCalendarId'],
                        'hCalendarCategoryId' => (int) $data['hCalendarCategoryId']
                    )
                );
            }
        }
    }

    public function getCount($hFileId)
    {
        $where = array(
            'hFileId' => (int) $hFileId
        );

        if ($this->hFileCommentModeration(false))
        {
            $where['hFileCommentIsApproved'] = 1;
        }

        return $this->hFileComments->selectCount(
            'hFileId',
            $where
        );
    }

    public function insertComment($columns)
    {
        if (!empty($columns['hFileCommentWebsite']) && strtolower(substr($columns['hFileCommentWebsite'], 0, 7)) != 'http://' && strtolower(substr($columns['hFileCommentWebsite'], 0, 8) != 'https://'))
        {
            $columns['hFileCommentWebsite'] = 'http://'.$columns['hFileCommentWebsite'];
        }

        $hasWrite = $this->hasWrite($columns['hFileId']);

        $columns = array_merge(
            array(
                'hFileCommentId' => 0,
                'hFileCommentPosted' => time(),
                'hFileCommentIsApproved' => $hasWrite? 1 : 0
            ),
            $columns
        );

        if ($this->hFileComments->columnExists('hFileCommentIsAuthor'))
        {
            $columns = array_merge(
                array(
                    'hFileCommentIsAuthor' => $this->isLoggedIn() && (int) $_SESSION['hUserId'] == $this->hUserId
                ),
                $columns
            );
        }

        $fileCommentId = $this->hFileComments->save($columns);

        $this->modifyCalendarResource((int) $columns['hFileId']);

        return $fileCommentId;
    }

    public function getFileId($fileCommentId)
    {
        return $this->hFileComments->selectColumn(
            'hFileId',
            (int) $fileCommentId
        );
    }

    public function deleteComment($fileCommentId)
    {
        $this->hFileComments->delete(
            'hFileCommentId',
            (int) $fileCommentId
        );

        $this->modifyCalendarResource(
            $this->getFileId($fileCommentId)
        );
    }

    public function approveComment($fileCommentId)
    {
        $this->hFileComments->update(
            array(
                'hFileCommentIsApproved' => 1
            ),
            (int) $fileCommentId
        );

        $this->modifyCalendarResource(
            $this->getFileId($fileCommentId)
        );
    }

    public function denyComment($fileCommentId)
    {
        $this->hFileComments->update(
            array(
                'hFileCommentIsApproved' => 0
            ),
            (int) $fileCommentId
        );

        $this->modifyCalendarResource(
            $this->getFileId($fileCommentId)
        );
    }

    public function getResultCount()
    {
        return $this->count;
    }

    public function hasWrite($fileId)
    {
        return ($this->isLoggedIn() && $this->hFiles->hasPermission($fileId, 'rw'));
    }

    public function getComments($fileId, $fileCommentId = 0)
    {
        $hasWrite = $this->hasWrite($fileId);

        if (empty($fileCommentId))
        {
            if ($this->hFileCommentModeration(false) && !$hasWrite)
            {
                $where = array(
                    'hFileId' => (int) $fileId,
                    'hFileCommentIsApproved' => 1
                );
            }
            else
            {
                $where = array(
                    'hFileId' => (int) $fileId
                );
            }
        }
        else
        {
            $where = array(
                'hFileCommentId' => (int) $fileCommentId
            );
        }

        $columns = array(
            'COUNT',
            'hFileCommentId',
            'hFileCommentName',
            'hFileCommentWebsite',
            'hFileComment',
            'hFileCommentPosted',
            'hFileCommentIsApproved'
        );

        if ($this->hFileComments->columnExists('hFileCommentIsAuthor'))
        {
            array_unshift(
                $columns,
                'hFileCommentIsAuthor'
            );
        }

        $query = $this->hFileComments->select(
            $columns,
            $where,
            'AND',
            array(
                'hFileCommentPosted',
                $this->hFileCommentsSortOrder('ASC')
            ),
            $this->hFileCommentsPagation(false)? $this->hSearch->getLimit() : 0
        );

        //echo $this->hDatabase->getLastQuery();

        $this->count = $this->hDatabase->getResultCount();

        $filePath = $this->getFilePathByFileId($fileId);

        if (isset($query) && is_array($query))
        {
            foreach ($query as &$data)
            {
                $data['hFileCommentPostedFormatted'] = date(
                    $this->hFileCommentsDateFormat('F j, Y'),
                    $data['hFileCommentPosted']
                );

                $data['hFileComment'] = str_replace(
                    array(
                        '&lt;code&gt;',
                        '&lt;/code&gt;',
                        '&lt;b&gt;',
                        '&lt;/b&gt;',
                        '&lt;i&gt;',
                        '&lt;/i&gt;',
                        '&lt;u&gt;',
                        '&lt;/u&gt;',
                        '&lt;var&gt;',
                        '&lt;/var&gt;',
                        '&lt;strong&gt;',
                        '&lt;/strong&gt;',
                        '&lt;em&gt;',
                        '&lt;/em&gt;',
                        '&lt;blockquote&gt;',
                        '&lt;/blockquote&gt;',
                        '&lt;ul&gt;',
                        '&lt;/ul&gt;',
                        '&lt;ol&gt;',
                        '&lt;/ol&gt;',
                        '&lt;li&gt;',
                        '&lt;/li&gt;',
                        '&lt;p&gt;',
                        '&lt;/p&gt;'
                    ),
                    array(
                        '<div class="hFileCommentCodeWrapper"><code class="hFileCommentCode">',
                        '</code></div>',
                        '<b>',
                        '</b>',
                        '<i>',
                        '</i>',
                        '<u>',
                        '</u>',
                        '<var>',
                        '</var>',
                        '<b>',
                        '</b>',
                        '<i>',
                        '</i>',
                        '<blockquote><span>&ldquo;</span>',
                        '<span>&rdquo;</span></blockquote>',
                        '<ul>',
                        '</ul>',
                        '<ol>',
                        '</ol>',
                        '<li>',
                        '</li>',
                        '<p>',
                        '</p>'
                    ),
                    $data['hFileComment']
                );

                if (!empty($data['hFileCommentWebsite']))
                {
                    if (substr($data['hFileCommentWebsite'], 0, strlen('http://')) != 'http://')
                    {
                        $data['hFileCommentWebsite'] = 'http://'.$data['hFileCommentWebsite'];
                    }
                }

                # Arrays of variables to be included in templates must have the same length
                # and must be set upon every iteration.
                $data['hFileCommentApprovePath'] = '';
                $data['hFileCommentDenyPath'] = '';
                $data['hFileCommentDeletePath'] = '';

                if ($hasWrite)
                {
                    $data['hFileCommentDeletePath'] = $filePath.'?delete&fileCommentId='.$fileCommentId;

                    if (isset($_GET['hSearchCursor']))
                    {
                        $data['hFileCommentDeletePath'] .= '&hSearchCursor='.$_GET['hSearchCursor'];
                    }

                    if (isset($_GET['cursor']))
                    {
                        $data['hFileCommentDeletePath'] .= '&cursor='.$_GET['cursor'];
                    }

                    if ($this->hFileCommentModeration(false))
                    {
                        $data['hFileCommentApprovePath'] = $filePath.'?approve&fileCommentId='.$fileCommentId;
                        $data['hFileCommentDenyPath'] = $filePath.'?deny&fileCommentId='.$fileCommentId;
                    }
                }
            }
        }

        return $query;
    }
}

?>