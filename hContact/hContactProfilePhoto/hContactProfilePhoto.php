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
# <h1>Contact Profile Photo Plugin</h1>
# <p>
#   This plugin provides a profile photo for address book contacts, users, and
#   groups.  It can retrieve a custom profile photo via associating a photo
#   in HtFS, or it can retrieve a profile photo from a network open directory
#   server.
# </p>
# <p>
#   This functionality has not been tested with Active Directory servers, and
#   may or may not work.  To find out for sure run the following command from
#   OS X server:
# </p>
# <code>
#   dscl '<i>/Active Directory/All Domains</i>' -read /Users/<i>username</i> JPEGPhoto | tail -1 | xxd -r -p
# </code>
# <p>
#   Replace <b>/Active Directory/All Domains</b> and <b>username</b> with the relevant
#   values.  To access a specfic Active Directory domain, for example,
#   you might want to use something like <var>/Active Directory/All Domains/EXAMPLE.COM</var>.
#   <b>userName</b> should be the short username.
# </p>
# <p>
#   If supported, and if there is a photo attached to the account, you'll see
#   raw JPEG binary source output to the console, i.e., what you would see if
#   you opened a JPEG file with a text editor.  If there is no photo, output will
#   be NULL.
# </p>
# <p>
#   The above command will ALWAYS work on OS X server, whether working with Open
#   Directory or Active Directory or local user accounts, however, what isn't known
#   to me is whether or not Active Directory supports attaching photos, and whether
#   or not photos attached are mapped correctly using OS X's <var>dscl</var> command.
# </p>
# @end

class hContactProfilePhoto extends hPlugin {

    public function hConstructor()
    {
        $this->hTemplatePath = '';

        if (isset($_GET['hContactId']))
        {
            if (!$this->getContactFileId($_GET['hContactId']))
            {
                $this->defaultImage();
            }
        }
        else if (isset($_GET['hUserId']))
        {
            if (!$this->getContactFileId($this->user->getContactId((int) $_GET['hUserId'])))
            {
                $this->getNetworkProfilePhoto($userId);
            }
        }
        else if (isset($_GET['hUserName']))
        {
            $userName = $_GET['hUserName'];
            //$this->hFileMIME = 'image/jpeg';

            $userId = $this->user->getUserId($userName);

            if (!empty($userId))
            {
                if (!$this->getContactFileId($this->user->getContactId($userId)))
                {
                    $this->getNetworkProfilePhoto($userId, $userName);
                }
            }
            else
            {
                $this->defaultImage();
            }
        }
        else
        {
            $this->defaultImage();
        }
    }

    private function getNetworkProfilePhoto($userId, $userName = null)
    {
        # @return void

        # @description
        # <h2>Getting a Network Profile Photo</h2>
        # <p>
        #   Mac OS X's Open Directory provides for a profile photo as part of a user's
        #   account data.  If a photo is specified, it will be stored under the <var>JPEGPhoto</var>,
        #   and it can be retrieved from the open directory controller, and output.  This
        #   functionality is enabled by default.  Specifying a profile photo in the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hContactFiles/hContactFiles.sql'>hContactFiles</a>
        #   database table takes precedence over the user's network profile photo.
        # </p>
        # <p>
        #   To retrieve a network profile photo, send the arguments <var>$_GET['hUserName']</var> or <var>$_GET['hUserId']</var>
        #   to <var>/System/Applications/Profile.plugin</var>.  Again, if a photo is specified for
        #   the user in
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hContactFiles/hContactFiles.sql'>hContactFiles</a>
        #   it takes precedence.  i.e., a profile photo is specified for the <var>hContactId</var> associated with the
        #   supplied <var>hUserId</var> or <var>hUserName</var>.
        # </p>
        # @end

        if (empty($userName))
        {
            $userName = $this->user->getUserName($userId);
        }

        if (!empty($userName))
        {
            $exists = $this->hUserUnixProperties->selectExists(
                'hUserId',
                array(
                    'hUserId' => $userId
                )
            );

            if ($exists)
            {
                $command = $this->pipeCommand(
                    '/usr/bin/dscl',
                    escapeshellarg($this->hContactDirectoryPath('.')).' '.
                    '-read '.escapeshellarg('/Users/'.$userName).' JPEGPhoto | tail -1 | xxd -r -p'
                );

                if ($command)
                {
                    $this->hFileMIME = 'image/jpeg';
                    echo $command;
                }
                else
                {
                    $this->defaultImage();
                }
            }
            else
            {
                $this->defaultImage();
            }
        }
        else
        {
            $this->defaultImage();
        }
    }

    private function getContactFileId($contactId)
    {
        # @return void

        # @description
        # <h2>Getting a Contact's Profile Photo</h2>
        # <p>
        #   To specify a profile photo for a contact, an entry is required in the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hContactFiles/hContactFiles.sql'>hContactFiles</a>
        #   database table.  This requires the <var>hContactId</var> of the rolodex
        #   entry, the <var>hFileId</var> of the file in HtFS to be used as a photo.
        #   The <var>hContactFileCategoryId</var>, which for this application would be
        #   <var>1</var>, which is for photos.  Then the flag <var>hContactIsProfilePhoto</var>
        #   must be set to <var>1</var>, indicating it this is a profile photo.
        #   And finally, if this is the default profile photo
        #   (the one you want displayed in the Contacts application when clicking on
        #   a user, group, or contact), then the flag <var>hContactIsDefaultProfilePhoto</var>
        #   must also be set to <var>1</var>.  If all of those conditions are met, the
        #   photo will be displayed whenever sending <var>$_GET['hContactId']</var> or for
        #   the <var>hContactId</var> associated with the supplied <var>$_GET['hUserId']</var>
        #   or <var>$_GET['hUserName']</var> arguments.
        # </p>
        # @end

        $fileId = $this->hContactFiles->selectColumn(
            'hFileId',
            array(
                'hContactId' => (int) $contactId,
                'hContactIsProfilePhoto' => 1,
                'hContactIsDefaultProfilePhoto' => 1
            )
        );

        if (!empty($fileId))
        {
            header('Location: '.$this->getFilePathByFileId($fileId));
            exit;
        }
        else
        {
            return false;
        }
    }

    private function defaultImage()
    {
        # @return void

        # @description
        # <h2>Default Profile Image</h2>
        # <p>
        #   The default profile image is an Apple "user" icon, atuomatically extracted from
        #   the host Mac OS X installation at 48x48.
        # </p>
        # <p>
        #   Hot Toddy does not ship OS X icons, OS X icons (icns files) are automatically
        #   harvested from the host OS X installation, if they are present.
        # </p>
        # @end

        $this->hFileWildcardPath = '/images/icons/48x48/user.png';
        $this->plugin('hFile/hFileIcon');
    }
}

?>