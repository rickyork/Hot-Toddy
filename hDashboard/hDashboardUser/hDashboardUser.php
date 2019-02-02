<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Dashboard Plugin
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

class hDashboardUser extends hPlugin {

    private $hDashboard;
    private $hUserDatabase;
    private $hDialogue;
    private $hForm;
    private $hDashboardUser;
    private $hApplicationStatus;
    private $hContactDatabase;

    private $groups;

    public function hConstructor()
    {
        if ($this->isLoggedIn())
        {
            if ($this->inGroup('Website Administrators') || $this->inGroup('Contact Administrators'))
            {
                $this->getUserDashboard();
            }
            else
            {
                $this->notAuthorized();
            }
        }
        else
        {
            $this->notLoggedIn();
        }
    }

    private function getUserDashboard()
    {
        $this->hDashboard = $this->library('hDashboard');
        $this->hDialogue = $this->library('hDialogue');
        $this->hForm = $this->library('hForm');

        $this->hContactDatabase = $this->database('hContact');

        $this->plugin('hApplication/hApplicationStatus');

        $this->getPluginFiles();
        $this->getPluginCSS('hPagination');

        $this->hUserDatabase = $this->database('hUser');

        $this->HotToddySideBoxHeading = 'User Account Groups';

        $this->groups = $this->hUserDatabase->getGroups();

        $groups = array(
            'userGroupId' => array(),
            'userGroup' => array()
        );

        foreach ($this->groups as $userGroupId => $userGroup)
        {
            $groups['userGroupId'][] = $userGroupId;
            $groups['userGroup'][] = $userGroup;
        }

        $this->HotToddySideBoxContent = $this->getTemplate(
            'Groups',
            array(
                'groups' => $groups
            )
        );

        $this->hDashboardUser = $this->library('hDashboard/hDashboardUser');

        $this->hFileDocument = $this->getTemplate(
            'Dashboard Users',
            array(
                'users' => nil
            )
        );

        $this->HotToddyAppendBody =
            $this->getUserForm().
            $this->getGroupForm();
    }

    public function getUserForm()
    {
        $this->hForm
            ->addDiv(
                'HotToddyAdminUserAccount',
                'Account'
            )
            ->addFieldset(
                'Personal Information',
                '100%',
                '175px,'
            )
                ->addTextInput(
                    'hContactFirstName',
                    'First Name:',
                    '30,100'
                )
                ->addTextInput(
                    'hContactLastName',
                    'Last Name:',
                    '30,100'
                )
                ->addTextInput(
                    'hContactCompany',
                    'Company:',
                    '50,100'
                )
                ->addTextInput(
                    'hContactTitle',
                    'Title:',
                    '50,100'
                )
                ->addTextInput(
                    'hContactDepartment',
                    'Department:',
                    '40,100'
                )
                ->addTextInput(
                    'hContactWebsite',
                    'Website:',
                    '50,255'
                )
                ->addRadioInput(
                    'hContactGender',
                    'Gender:',
                    array(
                        -1 => 'No Response',
                        0  => 'Female',
                        1  => 'Male'
                    )
                )

            ->addFieldset(
                'Account Information',
                '100%',
                '175px,'
            )
                ->addTextInput(
                    'hUserName',
                    'Screen Name:',
                    '35,255'
                )
                ->addTextInput(
                    'hUserEmail',
                    'Email Address:',
                    '50,255'
                )
                ->addPasswordInput(
                    'hUserPassword',
                    'Password:',
                    40
                )
                ->addPasswordInput(
                    'hUserPasswordConfirm',
                    'Confirm Password:',
                    40
                )
                ->addHiddenInput('hUserId')

            ->addDiv(
                'HotToddyAdminUserAddress',
                'Address'
            )
            ->addFieldset(
                'Address',
                '100%',
                '175px,'
            )
                ->addSelectInput(
                    array(
                        'id' => 'hContactFieldId-0',
                        'class' => 'hContactFieldId',
                        'name' => 'hContactFieldId[]'
                    ),
                    'Address Location:',
                    $this->hContactAddresses->getFields()
                )
                ->addTextareaInput(
                    array(
                        'id' => 'hContactAddressStreet-0',
                        'class' => 'hContactAddressStreet',
                        'name' => 'hContactAddressStreet[]'
                    ),
                    'Street:', '35,2'
                )
                ->addTextInput(
                    array(
                        'id' => 'hContactAddressCity-0',
                        'class' => 'hContactAddressCity',
                        'name' => 'hContactAddressCity[]'
                    ),
                    array(
                        'label' => 'City:'
                    ),
                    '40,100'
                )
                ->addSelectState(
                    array(
                        'id' => 'hLocationStateId-0',
                        'class' => 'hLocationStateId',
                        'name' => 'hLocationStateId[]'
                    ),
                    'State:'
                )
                ->addTextInput(
                    array(
                        'id ' => 'hContactAddressPostalCode-0',
                        'class' => 'hContactAddressPostalCode',
                        'name' => 'hContactAddressPostalCode[]'
                    ),
                    'Postal Code:',
                    '25,15'
                )
                ->addSelectCountry(
                    array(
                        'id' => 'hLocationCountryId-0',
                        'class' => 'hLocationCountryId',
                        'name' => 'hLocationCountryId[]'
                    )
                )
            ->addFieldset('', '100%', '100%', 'HotToddyAdminUserAddressControls')
                ->addTableCell(
                    $this->getTemplate('Address Controls')
                )

            ->addDiv(
                'HotToddyAdminUserGroups',
                'Groups'
            )
            ->addFieldset(
                'Groups',
                '100%',
                '100%',
                'HotToddyAdminUserGroupsFieldset'
            )
                ->addSelectInput(
                    array(
                        'id ' => 'hUserGroups',
                        'name' => 'hUserGroups[]',
                        'multiple' => 'multiple'
                    ),
                    'Member of Groups:',
                    array(),
                    12
                )
                ->addTableCell(
                    $this->getTemplate('Group Controls'),
                    2
                )
                ->addSelectInput(
                    array(
                        'id' => 'hUserGroupsSource',
                        'name' => 'hUserGroupsSource[]',
                        'multiple' => 'multiple'
                    ),
                    nil,
                    $this->groups,
                    12
                );

        $dialogue = $this->hDialogue
            ->newDialogue('HotToddyAdminUser')
            ->setForm($this->hForm)
            ->addButtons('Save', 'Cancel')
            ->getDialogue(nil, 'Create/Modify User');

        $this->hForm->reset();

        return $dialogue;
    }

    public function getGroupForm()
    {
        $this->hForm
            ->addDiv(
                'HotToddyAdminGroupDiv',
                'Group'
            )
            ->addFieldset('Group Information', '100%', '175px,')
                ->addTextInput(
                    'hUserGroupName',
                    'Group Name:',
                    '35,255'
                )
                ->addTextInput(
                    'hUserGroupEmail',
                    'Group Email Address:',
                    '50,255'
                );
                //->addPasswordInput('hUserGroupPassword', 'Group Password:', 40)
                //->addPasswordInput('hUserGroupPasswordConfirm', 'Confirm Group Password:', 40);

        $dialogue = $this->hDialogue
            ->newDialogue('HotToddyAdminGroup')
            ->setForm($this->hForm)
            ->addButtons('Save', 'Cancel')
            ->getDialogue(nil, 'Create/Modify Group');

        $this->hForm->reset();

        return $dialogue;
    }
}

?>