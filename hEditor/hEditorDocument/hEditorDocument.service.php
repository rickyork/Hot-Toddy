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

class hEditorDocumentService extends hService {

    private $hFile;
    private $hFileDatabase;

    public function hConstructor()
    {
        if (!isset($_GET['path']))
        {
            $this->JSON(-5);
            return;
        }

        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }

        hString::safelyDecodeURL($_GET['path']);

        if (isset($_POST['path']))
        {
            hString::safelyDecodeURL($_POST['path']);
        }

        if (isset($_POST['hDirectoryPath']))
        {
            hString::safelyDecodeURL($_POST['hDirectoryPath']);
        }

        $this->hFile = $this->library('hFile');
        $this->hFileDatabase = $this->database('hFile');

        $this->hFile->query($_GET['path']);

        if (!$this->hFile->userIsWriteAuthorized && !$this->inGroup('Website Administrators'))
        {
            $this->JSON(-1);
            return;
        }
    }

    public function getPluginConfiguration()
    {
        $xml = 1;
    }

    public function get()
    {

    }

    public function save()
    {
        if (isset($_POST['hEditorDocumentDebug']))
        {
            unset($_POST['hEditorDocumentDebug']);
            var_dump($_POST);
            return;
        }

        $isServer = (int) $_POST['hFileIsServer'];

        if (empty($isServer))
        {
            if (!empty($_POST['hFileReplaceExisting']))
            {
                // Delete the file/folder being replaced...
                $this->hFile->delete(
                    $this->getConcatenatedPath(
                        $_POST['hDirectoryPath'],
                        $_POST['hFileName']
                    )
                );
            }

            unset($_POST['hFileReplaceExisting']);

            if (isset($_POST['hDirectoryPath']))
            {
                $_POST['hDirectoryId'] = $this->getDirectoryId($_POST['hDirectoryPath']);
                unset($_POST['hDirectoryPath']);
            }

            $_POST['hFileId'] = (int) $_POST['hFileId'];

            $newDocument = false;

            if (empty($_POST['hFileId']))
            {
                $newDocument = true;
            }

            $_POST['hFileVariables'] = true;

            $fileId = $this->hFileDatabase->save($_POST);

            if ($newDocument)
            {
                $this->hFiles->savePermissions($fileId, 'rw', '');
            }

            $this->JSON($fileId);
        }
        else
        {
            if (!$this->inGroup('root'))
            {
                $this->JSON(-1);
                return;
            }

            $path = $this->getServerFileSystemPath(
                $this->getConcatenatedPath(
                    $_POST['hDirectoryPath'],
                    $_POST['hFileName']
                )
            );

            if (is_writable($path))
            {
                file_put_contents(
                    $path,
                    hString::decodeEntitiesAndUTF8($_POST['hFileDocument'])
                );

                $this->JSON(1);
            }
            else
            {
                $this->JSON(-33);
            }
        }
    }
}

?>