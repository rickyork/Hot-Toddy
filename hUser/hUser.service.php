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
# <h1>User Services API</h1>
# <p>
#
# </p>
# @end

class hUserService extends hService {

    private $hUserDatabase;
    private $hSearchDatabase;
    private $hSearch;
    private $hUserLoginInformation;

    public function hConstructor()
    {
        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }

        if (!empty($_GET['hContactConf']))
        {
            $this->loadConfigurationFile(
                $this->hFrameworkConfigurationPath.'/hContact '.hString::scrubString($_GET['hContactConf'])
            );
        }

        $this->hUserDatabase = $this->database('hUser');
    }

    private function validatedRequest()
    {
        # @return boolean

        # @description
        # <h2>Validating a Request</h2>
        # <p>
        #   A request is considered valid if the user is a member of <i>Website Administrators</i>,
        #   <i>Contact Administrators</i>, or <i>User Administrators</i>, or if the user has read
        #   access to the address book, or the contact.
        # </p>
        # @end

        if ($this->inAnyOfTheFollowingGroups(array('Website Administrators', 'Contact Administrators', 'User Administrators')))
        {
            return true;
        }

        if (!$this->hContactAddressBooks->hasPermission(1, 'r'))
        {
            // If not, is there a specific contact specified, and does the user have permission to look
            // at that contact?
            if (isset($_GET['hContactId']))
            {
                if (!$this->hContacts->hasPermission((int) $_GET['hContactId'], 'r'))
                {
                   $this->JSON(-1);
                   return false;
                }
            }
            else
            {
                $this->JSON(-1);
                return false;
            }
        }

        return true;
    }

    public function getLoginInformation()
    {
        # @return JSON
        # @service getLoginInformation
        # @description
        # <h2>Retrieving Login Information</h2>
        # <p>
        #   Returns JSON formated data representing an individual user's login information and
        #   statistics (the user's encrypted password is not returned).
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td>$_GET['userId']</td>
        #       </tr>
        #       <tr>
        #           <td>$_GET['contactId']</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        if (!$this->validatedRequest())
        {
            return;
        }

        $userId = (int) $this->get('userId', 0);
        $contactId = (int) $this->get('contactId', 0);

        // Either can be set, one must be present.
        if (!$userId && !$contactId)
        {
            $this->JSON(-5);
            return;
        }

        $loginInformation = $this->hUserDatabase->getLoginInformation($userId, $contactId);

        $loginInformation['hUserHistory'] = $this->getTemplate(
            'History',
            array(
                'hFiles' => $loginInformation['hUserHistory']
            )
        );

        $loginInformation['hUserActivity'] = $this->getTemplate(
            'Recent Activity',
            array(
                'activities' => $loginInformation['hUserActivity']
            )
        );

        $this->JSON($loginInformation);
    }

    public function getActivity()
    {
        # @return JSON
        # @service getActivity
        # @description
        # <h2>Retrieving User Activity</h2>
        # <p>
        #   Returns paged user activity.
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td>$_GET['cursor']</td>
        #       </tr>
        #       <tr>
        #           <td>$_GET['userId']</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        if (!$this->validatedRequest())
        {
            return;
        }

        $cursor = $this->get('searchCursor', 'cursor', nil);
        $userId = (int) $this->get('userId', 0);

        if (!$cursor || !$userId)
        {
            $this->JSON(-5);
            return;
        }

        $data = $this->hUserDatabase->getActivities($userId);

        $this->JSON(
            array(
                'activities' => $this->getTemplate(
                    'Recent Activity',
                    array(
                        'activities' => $data['activity']
                    )
                ),
                'pagination' => $data['pagination']
            )
        );
    }

    public function getDocumentHistory()
    {
        # @return JSON
        # @service getDocumentHistory
        # @description
        # <h2>Retrieving User Document History</h2>
        # <p>
        # <p>
        #   Returns paged user document history.
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td>$_GET['cursor']</td>
        #       </tr>
        #       <tr>
        #           <td>$_GET['userId']</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        if (!$this->validatedRequest())
        {
            return;
        }

        $cursor = $this->get('searchCursor', 'cursor', nil);
        $userId = (int) $this->get('userId', 0);

        if (!$cursor || !$userId)
        {
            $this->JSON(-5);
            false;
        }

        $data = $this->hUserDatabase->getDocumentHistories($userId);

        $this->JSON(
            array(
                'history' => $this->getTemplate(
                    'History',
                    array(
                        'hFiles' => $data['history']
                    )
                ),
                'pagination' => $data['pagination']
            )
        );
    }

    public function addUserToGroup()
    {
        # @return JSON
        # @service addUserToGroup
        # @description
        # <h2>Add a User to a Group</h2>
        # <p>
        #   Adds a user to a group.
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td>$_GET['userGroupId']</td>
        #       </tr>
        #       <tr>
        #           <td>$_GET['userId']</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        if (!$this->validatedRequest())
        {
            return;
        }

        $userGroupId = (int) $this->get('userGroupId', 0);
        $userId = (int) $this->get('userId', 0);

        if (!$userGroupId || !$userId)
        {
            $this->JSON(-5);
            return;
        }

        $this->hUserDatabase->addUserToGroup($userGroupId, $userId);

        $this->JSON(1);
    }
}

?>