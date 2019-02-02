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
# A URL accessible API for retreiving, adding, modifying, and deleting access codes
# on Hot Toddy file system files.
#
# Access codes allow files to be password protected on a file by file basis.  This
# functionality is used to grant an outside party (who does not have a framework account)
# temporary access to a file, to permenently password protect a given file for added security.
#
# Access codes may be removed from a file automatically after 24, 48, or 72 hours have passed.
#
# Access code expirations can also trigger the deletion of the file they are placed on after
# 24, 48, or 72 hours have passed. (see access code daemon)
#
# (see hHTTP.js for a list of response codes)
#

class hFinderPasswordsService extends hService {

    private $methods = array(
        'get' => array(
            'authenticate' => 'rw'
        ),
        'save' => array(
            'authenticate' => 'rw',
            'isset' => array(
                '_POST' => array(
                    'hFilePassword',
                    'hFilePasswordCreated',
                    'hFilePasswordLifetime',
                    'hFilePasswordExpirationAction',
                    'hFilePasswordRequired'
                )
            )
        ),
        'delete' => array(
            'authenticate' => 'rw',
            'isset' => array(
                '_GET' => array(
                    'hFilePasswordCreated'
                )
            )
        )
    );

    private $hFile;
    private $hFinder;

    public function hConstructor()
    {
        hString::safelyDecodeURL($_GET['path']);
        hString::safelyDecodeURL($_POST['path']);

        $this->hFile = $this->library('hFile');
        $this->hFinder = $this->library('hFinder');

        $json = $this->hFile->listenerValidation($this->methods, $this->hListenerMethod);

        if ($json <= 0)
        {
            $this->JSON($json);
            return;
        }
    }

    public function get()
    {
        // Return all passwords for a path via XML
        if (!$this->hFile->isServer && !$this->hFile->isDirectory)
        {
            $this->JSON(
                $this->hFilePasswords->select(
                    array(
                        'hFilePassword',
                        'hFilePasswordLifetime',
                        'hFilePasswordExpirationAction',
                        'hFilePasswordRequired',
                        'hFilePasswordCreated',
                        'hFilePasswordExpires'
                    ),
                    (int) $this->hFile->fileId
                )
            );
            return;
        }

        $this->JSON(0);
    }

    public function save()
    {
        hString::scrubArray($_POST);

        $passwordExists = $this->hFilePasswords->selectExists(
            'hFilePasswordCreated',
            array(
                'hFileId' => (int) $this->hFile->fileId,
                'hFilePassword' => $_POST['hFilePassword']
            )
        );

        if (!$passwordExists && empty($_POST['hFilePasswordCreated']))
        {
            // Insert
            $created  = time();
            $lifetime = (int) $_POST['hFilePasswordLifetime'];
            $expires  = $lifetime? $this->getExpiration($lifetime, $created) : 0;

            $this->hFilePasswords->insert(
                array(
                    'hFileId'                       => (int) $this->hFile->fileId,
                    'hFilePassword'                 => $_POST['hFilePassword'],
                    'hFilePasswordLifetime'         => $lifetime,
                    'hFilePasswordExpirationAction' => (int) $_POST['hFilePasswordExpirationAction'],
                    'hFilePasswordRequired'         => (int) $_POST['hFilePasswordRequired'],
                    'hFilePasswordCreated'          => $created,
                    'hFilePasswordExpires'          => $expires
                )
            );
        }
        else
        {
            $query = $this->hFilePasswords->selectQuery(
                'hFilePasswordCreated',
                array(
                    'hFileId' => (int) $this->hFile->fileId,
                    'hFilePassword' => $_POST['hFilePassword']
                )
            );

            $passwordCount = $this->hDatabase->getResultCount($query);

            if ($passwordCount > 1)
            {
                $created = $this->hDatabase->getColumn($query);

                $this->hFilePasswords->delete(
                    array(
                        'hFileId'       => (int) $this->hFile->fileId,
                        'hFilePassword' => $hFilePassword
                    )
                );
            }
            else
            {
                $created = (int) $_POST['hFilePasswordCreated'];
            }

            $lifetime = (int) $_POST['hFilePasswordLifetime'];
            $expires  = $lifetime? $this->getExpiration($lifetime, $created) : 0;

            $this->hFilePasswords->update(
                array(
                    'hFilePassword'                 => $_POST['hFilePassword'],
                    'hFilePasswordLifetime'         => $lifetime,
                    'hFilePasswordExpirationAction' => (int) $_POST['hFilePasswordExpirationAction'],
                    'hFilePasswordRequired'         => (int) $_POST['hFilePasswordRequired'],
                    'hFilePasswordExpires'          => $expires
                ),
                array(
                    'hFileId' => (int) $this->hFile->fileId,
                    'hFilePasswordCreated' => $created
                )
            );
        }

        $this->JSON(1);
    }

    public function delete()
    {
        $this->hFilePasswords->delete(
            array(
                'hFileId'              => (int) $this->hFile->fileId,
                'hFilePasswordCreated' => (int) $_GET['hFilePasswordCreated']
            )
        );

        $this->JSON(1);
    }

    private function getExpiration($lifetime, $created)
    {
        return ($lifetime > 0)? (int) $created + (($lifetime * 60) * 60) : 0;
    }
}

?>