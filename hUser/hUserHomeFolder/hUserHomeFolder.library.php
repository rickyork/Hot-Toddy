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

class hUserHomeFolderLibrary extends hPlugin {

    private $hFile;
    private $hUserPermissions;

    public function hConstructor()
    {
        $this->hFile = $this->library('hFile');
        $this->hUserPermissions = $this->library('hUser/hUserPermissions');
    }

    public function save($userId, $userName, $oldUserName = nil)
    {
        if ($this->groupExists('User Folders') && $this->groupExists('Employees'))
        {
            $special = $this->inGroup('User Folders', $userId) || $this->inGroup('Employees', $userId);
        }
        else if ($this->inGroup('root', $userId))
        {
            $special = true;
        }
        else
        {
            $special = false;
        }

        $homePath = '/Users/'.$userName;

        $this->hUserPermissions = $this->library('hUser/hUserPermissions');

        if (!$this->hFile->exists($homePath))
        {
            $homeId = $this->hFile->newDirectory(
                '/Users',
                $userName,
                $userId
            );

            if ($this->groupExists('Finder Administrators'))
            {
                $this->hUserPermissions->setGroup(
                    'Finder Administrators',
                    'rw'
                );
            }

            $this->hUserPermissions->save(
                'hDirectories',
                $homeId,
                $special? 'rw' : 'r'
            );
        }
        else
        {
            $this->hFile->rename(
                '/Users/'.$oldUserName,
                $userName
            );
        }

        if ($special)
        {
            $specials = array(
                'Documents',
                'Library',
                'Sites',
                'Pictures',
                'Music',
                'Movies',
                'Categories'
            );

            foreach ($specials as $special)
            {
                if (!$this->hFile->exists($homePath.'/'.$special))
                {
                    $sid = $this->hFile->newDirectory(
                        $homePath,
                        $special,
                        $userId
                    );

                    $this->hDirectoryProperties->insert(
                        array(
                            'hDirectoryId' => (int) $sid,
                            'hFileIconId'  => (int) $this->hFileIcons->selectColumn(
                                'hFileIconId',
                                array(
                                    'hFileMIME' => 'directory/'.strtolower($special)
                                )
                            ),
                            'hDirectoryIsApplication' => 0,
                            'hDirectoryLabel' => ''
                        )
                    );

                    $this->hUserPermissions = $this->library('hUser/hUserPermissions');

                    $this->hUserPermissions->save(
                        'hDirectories',
                        $sid,
                        'rw'
                    );
                }
            }
        }
    }
}

?>