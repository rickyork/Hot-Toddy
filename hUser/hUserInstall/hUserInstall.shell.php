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

class hUserInstallShell extends hShell {

    private $hUserLogin;
    private $hUserDatabase;
    private $hContactDatabase;

    public function hConstructor()
    {
        if ($this->shellArgumentExists('--hFrameworkConfiguration', 'hFrameworkConfiguration'))
        {
            $path = base64_decode($this->getShellArgumentValue('-c', '--hFrameworkConfiguration'));

            if (!empty($path) && file_exists($path))
            {
                $obj = unserialize(file_get_contents($path));

                $options = (object) '';

                foreach ($obj as $key => $value)
                {
                     $options->$key = $value;
                }
            }
            else
            {
                echo "Framework configuration could not be retrieved from: ".$path."\n";
            }
        }

        echo "Installing default users and groups...\n";

        $this->hUserLogin = $this->library('hUser/hUserLogin');

        $this->hUserDatabase = $this->database('hUser');
        $this->hContactDatabase = $this->database('hContact');

        $this->plugin('hUser', false, false, false, false);

        $this->hContactDatabase->saveAddressBook(
            array(
                'hContactAddressBookName' => 'Website Accounts',
                'hPlugin' => 'hUser'
            )
        );

        $hUserId = $this->hUserDatabase->save(
            0,
            !empty($options->hFrameworkUsername)? $options->hFrameworkUsername : 'administrator',
            !empty($options->hFrameworkEmailAddress)? $options->hFrameworkEmailAddress : 'administrator@localhost',
            !empty($options->hFrameworkPassword)? $options->hFrameworkPassword : 'password'
        );

        $hContactId = $this->hDatabase->selectColumn(
            'hContactId',
            'hContacts',
            array(
                'hContactAddressBookId' => 1,
                'hUserId' => (int) $hUserId
            )
        );

        $displayName = 'AdministratorUser';

        if (!empty($options->hFrameworkFirstName) && !empty($options->hFrameworkLastName))
        {
            $displayName = trim($options->hFrameworkFirstName).' '.trim($options->hFrameworkLastName);
        }

        $this->hContactDatabase->saveContact(
            array(
                'hContactFirstName'   => !empty($options->hFrameworkFirstName)? trim($options->hFrameworkFirstName) : 'Administrator',
                'hContactLastName'    => !empty($options->hFrameworkLastName)? trim($options->hFrameworkLastName) : 'User',
                'hContactCompany'     => 'Company',
                'hContactDisplayName' => $displayName
            ),
            1,
            $hUserId,
            $hContactId
        );

        echo "Added user Administrator.\n";

        $root = $this->hUserDatabase->save(0, 'root', 'root@localhost', '');
        $this->hUserDatabase->saveGroupProperties($root, 1, 1, '', 0);

        echo "Added group root.\n";

        $this->hUserDatabase->addUserToGroup($root, $hUserId);

        echo "Added user Administrator to group root.\n";

        // Need to write an update that converts group names to the following throughout
        // the framework and in the database:
        //
        // Root                                                 // Superuser, has all possible privileges
        // Website Administrators  = hFinderDocument/('Website Administrators')   // Gets most privileges to admin the website
        // Finder Administrators   = file-admin, FTP Admin      // Gets some privileges to admin and view user folders
        // Finder Folders          = hSites, User Folders       // Adds special folders to a user's home folder
        // User Administrators     = Users                      // Gives a user the ability to admin all users (in address book 1)
        // Contact Administrators  = hContactAll                // Gives a user the ability to admin any address book
        // Contact Address Book    = hContactMine               // Gives a user the ability to have his/her own address book.
        // Disabled User Accounts  = Inactive                   // Disables a user's ability to login.
        // Documents                                            // Reserved group name.

        // Group Permissions Explained
        //
        // Root should allow a user access to anything ordinarliy limited by access privileges.
        //   If being in a group determines whether or not you have access to a resource,
        //   a root user is always considered a group member.
        //
        //   If having read/write access is necessary to access/modify a resource, a root
        //   user always receives read/write.
        //
        // Website Administators have read/write to /www.example.com and sub-folders
        // Finder Administrators have read/write to /Users and sub-folders and are a member of User Administrators
        // User Administrators are able to modify/add users/groups that are not elevated
        // Contact Administrators are able to modify contact information in any address book
        // Contact Address Book allows a user to create/maintain his/her own address book
        // Diabled Users prevents a user from logging in and accessing his/her account.
        // Documents is a reserved group name.  This might be used to grant users access to a document repository.
        //

        $groups = array(
            'Administrators'          => 1,
            'Website Administrators'  => 1,
            'Finder Administrators'   => 1,
            'Calendar Administrators' => 1,
            'User Administrators'     => 1,
            'Employees'               => 0,
            'Disabled User Accounts'  => 0,
            'Contact Administrators'  => 1,
            'Contact Address Book'    => 0
        );

/*
        $groups = array(
            'hFinderDocument'       => 1,
            'file-admin'            => 1,
            'Finder Administrators' => 1,
            'Users'                 => 1,
            'Employees'             => 0,
            'User Folders'          => 1,
            'Inactive'              => 0,
            'FTP Admin'             => 1,
            'hContactAll'           => 0,
            'hContactMine'          => 0,
            'hSites'                => 0
        );
*/

        foreach ($groups as $group => $elevated)
        {
            $this->hUserDatabase->saveGroupProperties(
                $this->hUserDatabase->save(0, $group, str_replace(' ', '', $group).'@localhost', ''),
                1, $elevated, '', 0
            );

            echo "Created group {$group}.\n";
        }
    }
}

?>