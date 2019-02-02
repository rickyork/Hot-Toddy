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
# <h1>Console</h1>
# <p>
#   Hot Toddy's Console API provides error, statistical, and usage data.
# </p>
# @end

class hConsoleDatabase extends hPlugin {

    private $timestamps = array(
        'hErrorDate',
        'hUserActivityTime',
        'hFileLastAccessed',
        'hUserCreated',
        'hUserLastLogin',
        'hUserLastFailedLogin',
        'hUserLastModified'
    );

    private $users = array(
        'hUserId',
        'hUserLastModifiedBy',
        'hUserReferredBy'
    );

    public function getErrors($limit = '0,20')
    {
        # @return array

        # @description
        # <h2>Getting Framework Errors</h2>
        # <p>
        #   Returns framework error data from the
        #   <a href='/System/Framework/Hot Toddy/hFramework/hFrameworkError/Database/hFrameworkErrors/hFrameworkErrors.sql' class='code' target='_blank'>hFrameworkErrors</a>
        #   database table.
        # </p>
        # @end

        return $this->hFrameworkErrors->select(
            array(
                'SQL_CALC_FOUND_ROWS',
                'hUserId',
                'hFrameworkError',
                '@hFilePath',
                'hPluginPath',
                'hPluginLine',
                'hFrameworkBackTrace',
                'hFrameworkErrorDate',
                'hUserAgent',
                'hUserRemoteIP'
            ),
            array(

            ),
            'AND',
            array(
                'hFrameworkErrorDate',
                'DESC'
            ),
            $limit
        );
    }

    public function getStatusCodes($limit = '0,20')
    {
        # @return array

        # @description
        # <h2>Getting File Status Codes</h2>
        # <p>
        #   Returns file status codes from the
        #   <a href='/System/Framework/Hot Toddy/hFile/hFileStatistics/Database/hFileStatusLog/hFileStatusLog.sql' class='code' target='_blank'>hFileStatusLog</a>
        #   database table.
        # </p>
        # @end

        return $this->hFileStatusLog->select(
            array(
                'SQL_CALC_FOUND_ROWS',
                'hUserId',
                'hFileStatusPath',
                'hFileStatusCode',
                'hFileStatusReferrerPath',
                'hFileStatusAccessCount'
            ),
            array(

            ),
            'AND',
            'hFileStatusPath',
            $limit
        );
    }

    public function getActivity($limit = '0,20')
    {
        # @return array

        # @description
        # <h2>Getting User Activity</h2>
        # <p>
        #   Returns user activity from thes
        #   <a href='/System/Framework/Hot Toddy/hUser/Database/hUserActivityLog/hUserActivityLog.sql' class='code' target='_blank'>hUserActivityLog</a>
        #   database table.
        # </p>
        # @end

        return $this->hUserActivityLog->select(
            array(
                'SQL_CALC_FOUND_ROWS',
                'hUserId',
                'hUserActivity',
                'hUserActivityComponent',
                'hUserActivityTime',
                'hUserActivityIP'
            ),
            array(

            ),
            'AND',
            array(
                'hUserActivityTime',
                'DESC'
            ),
            $limit
        );
    }

    public function getDocumentHistory($limit = '0,20')
    {
        # @return array

        # @description
        # <h2>Getting User Document History</h2>
        # <p>
        #   Returns user document history from the
        #   <a href='/System/Framework/Hot Toddy/hFile/hFileStatistics/Database/hFileUserStatistics/hFileUserStatistics.sql' class='code' target='_blank'>hFileUserStatistics</a>
        #   database table.
        # </p>
        # @end

        return $this->hFileUserStatistics->select(
            array(
                'SQL_CALC_FOUND_ROWS',
                'hUserId',
                'hFileId',
                'hFileAccessCount',
                'hFileLastAccessed'
            ),
            array(

            ),
            'AND',
            array(
                'hFileLastAccessed',
                'DESC'
            ),
            $limit
        );
    }

    public function getUserLog($limit = '0,20', $sortBy = 'hUserLastLogin', $sortDirection = 'DESC')
    {
        # @return array

        # @description
        # <h2>Getting User Login History</h2>
        # <p>
        #   Returns user login history from the
        #   <a href='/System/Framework/Hot Toddy/hUser/Database/hUserLog/hUserLog.sql' class='code' target='_blank'>hUserLog</a>
        #   database table.
        # </p>
        # @end

        return $this->hUserLog->select(
            array(
                'SQL_CALC_FOUND_ROWS',
                'hUserId',
                'hUserLoginCount',
                'hUserFailedLoginCount',
                'hUserCreated',
                'hUserLastLogin',
                'hUserLastFailedLogin',
                'hUserLastModified',
                'hUserLastModifiedBy',
                'hUserReferredBy',
                'hUserRegistrationTrackingId',
                'hFileId'
            ),
            array(

            ),
            'AND',
            array(
                $sortBy,
                $sortDirection
            ),
            $limit
        );
    }

    public function getResultsForTemplate($query)
    {
        # @return array

        # @description
        # <h2>Getting Results For a Template</h2>
        # <p>
        #
        # </p>
        # @end

        $return = array();

        $i = 0;

        foreach ($query as $data)
        {
            foreach ($data as $column => $value)
            {
                if (in_array($column, $this->timestamps))
                {
                    if (!empty($value))
                    {
                        $value = date($this->hConsoleDateFormat('n/j/Y g:i:s a'), $value);
                    }
                    else
                    {
                        $value = '';
                    }
                }

                if (in_array($column, $this->users))
                {
                    if (!empty($value))
                    {
                        $return[$column.'_hUserFullName'][$i] = $this->user->getFullName($value);
                        $return[$column.'_hUserName'][$i] = $this->user->getUserName($value);
                    }
                    else
                    {
                        $value = '';
                        $return[$column.'_hUserFullName'][$i] = '';
                        $return[$column.'_hUserName'][$i] = '';
                    }
                }

                if ($column == 'hFileId')
                {
                    $return['hFilePath'][$i] = $this->getFilePathByFileId($value);
                }

                $return[$column][$i] = $value;
            }

            $i++;
        }

        return $return;
    }
}

?>