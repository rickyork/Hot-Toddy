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

class hFrameworkCleanupLibrary extends hPlugin {

    public function hConstructor()
    {

    }

    public function all()
    {
        $this->files();
        $this->users();
        $this->forums();
        $this->calendars();
        $this->contacts();
        $this->directories();
        $this->documentation();
    }

    public function files()
    {
        $this->hDatabase->query(
            $this->getTemplateSQL('deleteLostFiles')
        );

        $tables = array(
            'hFileActivity',
            'hFileAliases',
            'hFileComments',
            'hFileDocuments',
            'hFileHeaders',
            'hFileLog',
            'hFilePasswords',
            'hFilePathWildcards',
            'hFileProperties',
            'hFileStatistics',
            'hFileUserStatistics',
            'hFileVariables',
            'hListFiles',
            'hCategoryFiles',
            'hContactFiles'
        );

        foreach ($tables as $table)
        {
            $this->console("Cleaning up 'hFileId' in '{$table}'");

            $this->hDatabase->query(
                $this->getTemplateSQL(
                    'files',
                    array(
                        'table' => $table,
                        'column' => 'hFileId'
                    )
                )
            );
        }

        $tables = array(
            'hListFiles' => 'hListFileId'
        );

        foreach ($tables as $table => $column)
        {
            $this->console("Cleaning up '{$column}' in '{$table}'");

            $this->hDatabase->query(
                $this->getTemplateSQL(
                    'files',
                    array(
                        'table' => $table,
                        'column' => $column
                    )
                )
            );
        }
    }

    public function users()
    {
        $tables = array(
            'hUserActivityLog',
            'hUserAliases',
            'hUserAuthenticationLog',
            'hUserDirectory',
            'hUserGroupProperties',
            'hUserGroups',
            'hUserLog',
            'hUserNewsletter',
            'hUserPermissionsCache',
            'hUserUnixProperties',
            'hUserVariables',
            'hContactUsers'
        );

        foreach ($tables as $table)
        {
            $this->console("Cleaning up 'hUserId' in '{$table}'");

            $this->hDatabase->query(
                $this->getTemplateSQL(
                    array(
                        'table' => $table,
                        'column' => 'hUserId'
                    )
                )
            );
        }

        $tables = array(
            'hUserGroups' => 'hUserGroupId',
            'hUserPermissionsGroups' => 'hUserGroupId'
        );

        foreach ($tables as $table => $column)
        {
            $this->console("Cleaning up '{$column}' in '{$table}'");

            $this->hDatabase->query(
                $this->getTemplateSQL(
                    array(
                        'table' => $table,
                        'column' => $column
                    )
                )
            );
        }
    }

    public function forums()
    {
        $this->hDatabase->query(
            $this->getTemplateSQL(
                'files',
                array(
                    'table' => 'hForums',
                    'column' => 'hFileId'
                )
            )
        );

        $this->hDatabase->query($this->getTemplateSQL('topics'));
        $this->hDatabase->query($this->getTemplateSQL('posts'));
    }

    public function calendars()
    {
        $this->hDatabase->query(
            $this->getTemplateSQL(
                'files',
                array(
                    'table' => 'hCalendarFiles',
                    'column' => 'hFileId'
                )
            )
        );

        $this->console("Cleaning up 'hFileId' in 'hCalendarFiles'");

        $this->hDatabase->query($this->getTemplateSQL('calendarFiles'));
        $this->console("Cleaning up 'hCalendarId' and 'hCalendarCategoryId' in 'hCalendarFiles'");

        $this->hDatabase->query($this->getTemplateSQL('calendarResources'));
        $this->console("Cleaning up 'hCalendarId' and 'hCalendarCategoryId' in 'hCalendarResources'");

        $this->hDatabase->query($this->getTemplateSQL('calendarFileDates'));
        $this->console("Cleaning up 'hCalendarId' and 'hCalendarFileId' in 'hCalendarFileDates'");
    }

    public function contacts()
    {
        $tables = array(
            'hContactFiles',
            'hContactAddresses',
            'hContactEmailAddresses',
            'hContactInternetAccounts',
            'hContactPhoneNumbers',
            'hContactUsers',
            'hContactVariables'
        );

        foreach ($tables as $table)
        {
            $this->hDatabase->query(
                $this->getTemplateSQL(
                    array(
                        'table' => $table,
                        'column' => 'hContactId'
                    )
                )
            );

            $this->console("Cleaning up 'hContactId' in '{$table}'");
        }

        $this->hContactEmailAddresses->delete('hContactEmailAddress', '');
        $this->hContactPhoneNumbers->delete('hContactPhoneNumber', '');
        $this->hContactInternetAccounts->delete('hContactInternetAccount', '');

        $this->hDatabase->query($this->getTemplateSQL('contactAddressBooks'));
        $this->console("Cleaning up 'hContactAddressBookId' in 'hContacts'");
    }

    public function directories()
    {
        $tables = array(
            'hFiles',
            'hDirectoryProperties',
            'hTemplateDirectories'
        );

        foreach ($tables as $table)
        {
            $this->hDatabase->query(
                $this->getTemplateSQL(
                    array(
                        'table' => $table,
                        'column' => 'hDirectoryId'
                    )
                )
            );

            $this->console("Cleaning up 'hDirectoryId' in '{$table}'");
        }
    }

    public function documentation()
    {
        $this->hDatabase->query(
            $this->getTemplateSQL('documentationMethods')
        );

        $this->hDatabase->query(
            $this->getTemplateSQL('documentationMethodArguments')
        );
    }
}

?>