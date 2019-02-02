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

class hFileStatistics extends hPlugin {

    public function hConstructor()
    {
        // Don't run stats when called from the command line.
        if (!isset($GLOBALS['argv']))
        {
            if ($this->hFileStatistics->selectExists('hFileId', (int) $this->hFileId))
            {
                $this->hFileStatistics->update(
                    array(
                        'hFileAccessCount'  => 'hFileAccessCount + 1',
                        'hFileLastAccessed' => time()
                    ),
                    (int) $this->hFileId
                );
            }
            else
            {
                $this->hFileStatistics->insert(
                    array(
                        'hFileId' => (int) $this->hFileId,
                        'hFileAccessCount' => 1,
                        'hFileLastAccessed' => time()
                    )
                );
            }

            $host = '';//isset($_SERVER['REMOTE_ADDR'])?     db::escapeString(gethostbyaddr($_SERVER['REMOTE_ADDR'])) : '';
            $address = isset($_SERVER['REMOTE_ADDR'])?         hString::escapeAndEncode($_SERVER['REMOTE_ADDR'])     : '';
            $user_agent = isset($_SERVER['HTTP_USER_AGENT'])?  hString::escapeAndEncode($_SERVER['HTTP_USER_AGENT']) : '';
            $query_string = isset($_SERVER['QUERY_STRING'])?   hString::escapeAndEncode($_SERVER['QUERY_STRING'])    : '';

            if ($this->isLoggedIn())
            {
                $exists = $this->hFileUserStatistics->selectExists(
                    'hUserId',
                    array(
                        'hUserId' => (int) $_SESSION['hUserId'],
                        'hFileId' => (int) $this->hFileId
                    )
                );

                if ($exists)
                {
                    $this->hFileUserStatistics->update(
                        array(
                            'hFileAccessCount'  => 'hFileAccessCount + 1',
                            'hFileLastAccessed' => time()
                        ),
                        array(
                            'hUserId' => (int) $_SESSION['hUserId'],
                            'hFileId' => (int) $this->hFileId
                        )
                    );
                }
                else
                {
                    $this->hFileUserStatistics->insert(
                        array(
                            'hUserId' => (int) $_SESSION['hUserId'],
                            'hFileId' => (int) $this->hFileId,
                            'hFileAccessCount' => 1,
                            'hFileLastAccessed' => time()
                        )
                    );
                }
            }

            if ($this->hFileStatusCode(null) == 404)
            {
                $this->logStatusCode(404, $this->hFileStatusPath);
            }

            if (!$this->hFileAuthorized)
            {
                $this->logStatusCode(401, $this->hFilePath);
            }
        }
    }

    public function logStatusCode($statusCode, $path)
    {
        $userId = $this->isLoggedIn()? (int) $_SESSION['hUserId'] : 0;
        $referrer = isset($_SERVER['HTTP_REFERER'])? hString::escapeAndEncode($_SERVER['HTTP_REFERER']) : '';
        $path = hString::escapeAndEncode($path);

        // The user is not authorized, log the activity.
        // Write the path to the 401 log
        $exists = $this->hFileStatusLog->selectExists(
            'hFileStatusLogId',
            array(
                'hFileStatusPath' => $path,
                'hFileStatusCode' => $statusCode
            )
        );

        if ($exists)
        {
            $this->hFileStatusLog->update(
                array(
                    'hFileStatusAccessCount' => 'hFileStatusAccessCount + 1',
                    'hUserId' => $userId
                ),
                array(
                    'hFileStatusPath' => $path,
                    'hFileStatusCode' => $statusCode
                )
            );
        }
        else
        {
            $this->hFileStatusLog->insert(
                array(
                    'hFileStatusLogId'        => nil,
                    'hUserId'                 => $userId,
                    'hFileStatusPath'         => $path,
                    'hFileStatusCode'         => $statusCode,
                    'hFileStatusReferrerPath' => $referrer,
                    'hFileStatusAccessCount'  => 1
                )
            );
        }
    }
}

?>