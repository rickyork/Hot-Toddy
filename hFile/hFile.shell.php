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
#
# This library provides methods which provide information about file objects in the
# Hot Toddy file system, or file objects in the real, server file system.
#

class hFileShell extends hShell {

    private $hFile;
    private $hUserPermissions;

    public function hConstructor()
    {
        $this->hFile = $this->library('hFile');

        switch (true)
        {
            case $this->shellArgumentExists('copy', '--copy'):
            {
                $this->copy();
                break;
            }
            case $this->shellArgumentExists('move', '--move'):
            {
                # $source = $this->getShellArgumentValue('');
                # hFile move /www.example.com/test.html to /Events
                $this->move();
                break;
            }
            case $this->shellArgumentExists('rename', '--rename'):
            {
                $this->rename();
                break;
            }
            case $this->shellArgumentExists('delete', '--delete'):
            {
                $this->delete();
                break;
            }
            case $this->shellArgumentExists('mkdir', '--mkdir'):
            {
                $this->mkdir();
                break;
            }
            case $this->shellArgumentExists('chmod', '--chmod'):
            {
                $this->chmod();
                break;
            }
            case $this->shellArgumentExists('chown', '--chown'):
            {
                $this->chown();
                break;
            }
            case $this->shellArgumentExists('permissions', '--permissions'):
            {
                $this->permissions();
                break;
            }
            case $this->shellArgumentExists('touch', '--touch'):
            {
                $this->touch();
                break;
            }
            case $this->shellArgumentExists('ls', '--ls'):
            {
                $this->ls();
                break;
            }
            case $this->shellArgumentExists('help', '--help'):
            {
                $this->console($this->getTemplateTXT('Help'));
                break;
            }
        }
    }

    private function copy()
    {
        $file = $this->getShellArgumentValue('copy', '--copy');

        if ($this->shellArgumentExists('to', '--to'))
        {
            $destination = $this->getShellArgumentValue('to', '--to');
        }

        $this->console("Attempting to copy file ".$file);

        if (!$this->hFile->exists($file))
        {
            $this->console("Unable to copy file {$file} because it does not exist");
        }
        else
        {
            if (isset($destination))
            {
                if ($this->hFile->exists($destination))
                {
                    $this->console("Unable to copy file {$file} because {$destination} already exists");
                }
                else
                {
                    $fileId = $this->hFile->copy($file, $destination);

                    $this->console("Copied file {$file} to {$destination}");
                    $this->console("Inserted hFileId: {$fileId}");
                }
            }
            else
            {
                $fileId = $this->hFile->copy($file);

                $this->console("Copied file {$file} to ".$this->getFilePathByFileId($fileId));
                $this->console("Inserted hFileId: {$fileId}");
            }
        }
    }

    private function move()
    {
        $file = $this->getShellArgumentValue('move', '--move');

        if ($this->shellArgumentExists('to', '--to'))
        {
            $destination = trim($this->getShellArgumentValue('to', '--to', nil, true));
        }

        if (empty($destination))
        {
            $this->fatal("Unable to move file {$file} because a required argument, to, was not provided.", __FILE__, __LINE__);
        }

        $replace = $this->shellArgumentExists('force', '--force');

        if ($this->hFile->exists($file))
        {
            $response = $this->hFile->move($destination, $file, $replace);

            switch ($response)
            {
                case -3:
                {
                    $this->console("Unable to move {$file} because it already exists in {$destination}");
                    break;
                }
                case -20:
                {
                    $this->console("Unable to move {$file} because {$destination} is not a directory");
                    break;
                }
                case -18:
                {
                    $this->console("Unable to move {$file} to itself");
                    break;
                }
                case -21:
                {
                    $this->console("Unable to move {$file} into itself");
                    break;
                }
                case 1:
                {
                    $this->console("Moved {$file} to {$destination}");
                    break;
                }
            }
        }
        else
        {
            $this->console("Unable to move file {$file} because it does not exist");
        }
    }

    private function rename()
    {
        $file = $this->getShellArgumentValue('rename', '--rename');

        if ($this->shellArgumentExists('to', '--to'))
        {
            $newName = trim($this->getShellArgumentValue('to', '--to', nil, true));
        }

        if (empty($newName))
        {
            $this->fatal(
                "Unable to rename file {$file} because a required argument, to, was not provided.",
                __FILE__,
                __LINE__
            );
        }

        $replace = $this->shellArgumentExists('force', '--force');

        $response = $this->hFile->rename($file, $newName, $replace);

        switch ($response)
        {
            case -3:
            {
                $this->console("Unable to rename {$file} because {$newName} already exists");
                break;
            }
            case 1:
            {
                $this->console("Renamed {$file} to {$newName}");
                break;
            }
        }
    }

    private function delete()
    {
        $file = $this->getShellArgumentValue('delete', '--delete');

        if ($this->hFile->exists($file))
        {
            $this->hFile->delete($file);
            $this->console("Deleted file {$file}");
        }
        else
        {
            $this->console("Unable to delete file {$file} because it does not exist");
        }
    }

    private function mkdir()
    {
        $file = $this->getShellArgumentValue('mkdir', '--mkdir');

        if (!$this->hFile->exists($file))
        {
            $directoryId = $this->hFile->newDirectory(
                dirname($file),
                basename($file)
            );

            $this->console("Directory: {$file} created");
            $this->console("Inserted hDirectoryId: {$directoryId}");
        }
        else
        {
            $this->console("Unable to make directory {$file} because it already exists");
        }
    }

    private function chmod()
    {
        $file = $this->getShellArgumentValue('chmod', '--chmod');

        $this->hFile->query($file);

        if ($this->hFile->exists($file))
        {
            $owner  = $this->shellArgumentExists('owner', '--owner')? $this->getShellArgumentValue('owner', '--owner') : '';
            $world  = $this->shellArgumentExists('world', '--world')? $this->getShellArgumentValue('world', '--world') : '';

            // groups "Website Administrators=rw,Other=r"
            $groups = $this->shellArgumentExists('groups', '--groups')? $this->getShellArgumentValue('groups', '--groups') : '';

            if (!empty($groups))
            {
                $groups = explode(",", $groups);

                foreach ($groups as $group)
                {
                    list($user, $userPermissions) = explode('=', $group);

                    if ($this->hFile->isDirectory)
                    {
                        $this->hDirectories->addGroup($user, $userPermissions);
                    }
                    else
                    {
                        $this->hFiles->addGroup($user, $userPermissions);
                    }
                }
            }

            if ($this->hFile->isDirectory)
            {
                $this->hDirectories->savePermissions(
                    $this->hFile->directoryId,
                    $owner,
                    $world
                );
            }
            else
            {
                $this->hFiles->savePermissions(
                    $this->hFile->fileId,
                    $owner,
                    $world
                );
            }

            $this->console("Updated permissions on file {$file}");
        }
        else
        {
            $this->console("Unable to set permissions on file {$file} because it does not exist");
        }
    }

    private function chown()
    {
        $file = $this->getShellArgumentValue('chown', '--chown');

        $this->hFile->query($file);

        if ($this->hFile->exists($file))
        {
            $user = $this->getShellArgumentValue('to', '--to');

            if ($this->hFile->isDirectory)
            {
                $this->hDirectories->update(
                    array(
                        'hUserId' => $this->user->getUserId($user)
                    ),
                    array(
                        'hDirectoryId' => $this->hFile->directoryId
                    )
                );
            }
            else
            {
                $this->hFiles->update(
                    array(
                        'hUserId' => $this->user->getUserId($user)
                    ),
                    array(
                        'hFileId' => $this->hFile->fileId
                    )
                );
            }

            $this->console("File: {$file} owner updated to: {$user}");
        }
        else
        {
            $this->console("File: {$file} does not exist");
        }
    }

    private function touch()
    {
        $file = $this->getShellArgumentValue('touch', '--touch');

        if ($this->hFile->exists(dirname($file)))
        {
            if (!$this->hFile->exists($file))
            {
                $fileId = $this->hFile->touch($file);

                $this->console("Touched file {$file}");
                $this->console("Inserted hFileId: {$fileId}");
            }
            else
            {
                $this->console("Unable to touch file {$file} because it already exists");
            }
        }
        else
        {
            $this->console("Unable to touch file {$file} because the directory ".dirname($file)." does not exist");
        }
    }

    private function permissions()
    {
        $file = $this->getShellArgumentValue('permissions', '--permissions');

        $this->hFile->query($file);

        if ($this->hFile->exists($file))
        {
            if ($this->hFile->isFile)
            {
                $permissions = $this->hFiles->getPermissions($this->hFile->fileId);
            }
            else
            {
                $permissions = $this->hDirectories->getPermissions($this->hFile->directoryId);
            }

            $this->console("Owner: ".$permissions['hUserPermissionsOwner']);
            $this->console("World: ".$permissions['hUserPermissionsWorld']);

            if (is_array($permissions['hUserGroups']))
            {
                $this->console("\nGroups");

                foreach ($permissions['hUserGroups'] as $userGroupId => $userPermissionsGroup)
                {
                    $this->console($this->user->getUserName($userGroupId).': '.$userPermissionsGroup);
                }
            }

            if (is_array($permissions['hUsers']))
            {
                $this->console("\nUsers");

                foreach ($permissions['hUsers'] as $userId => $userPermissionsGroup)
                {
                    $this->console($this->user->getUserName($userId).': '.$UserPermissionsGroup);
                }
            }
        }
        else
        {
            $this->console("Unable to query permissions on file {$file} because it does not exist");
        }
    }

    private function ls()
    {
        $listFile = $this->getShellArgumentValue('ls', '--ls');

        $this->hFile->query($listFile);

        if ($this->hFile->exists($listFile))
        {
            if ($this->hFile->isDirectory)
            {
                $files = $this->hFile->getDirectories($listFile, false);

                foreach ($files as $file)
                {
                    $this->console($file['hFileName']);
                }

                $files = $this->hFile->getFiles($listFile, false);

                foreach ($files as $file)
                {
                    $this->console($file['hFileName']);
                }
            }
            else
            {
                $this->console("Unable to list directory {$listFile} because it is not a directory");
            }
        }
        else
        {
            $this->console("Unable to list directory {$listFile} because it does not exist");
        }
    }
}

?>