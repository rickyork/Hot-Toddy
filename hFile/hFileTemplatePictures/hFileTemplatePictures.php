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

class hFileTemplatePictures extends hPlugin {

    private $hImage;

    public function hConstructor()
    {
        $this->hFileSystemPath = $this->hFrameworkPicturesPath;

        $this->hFilePath = urldecode(
            $this->getEndOfPath(
                $this->hFileWildcardPath,
                $this->hFrameworkPicturesRoot
            )
        );

        if (file_exists($this->hFileSystemPath))
        {
            //$this->hFileMIME = exec('file -ib '.$this->hFileSystemPath);

            // The shell doesn't always get the right MIME, so
            // let's fix it for the files we know about.
            //
            // It's not a proper byte sniffing MIME determination,
            // but it'll do.
            $hFileMIME = $this->hFileIcons->selectColumn(
                'hFileMIME',
                array(
                    'hFileExtension' => $this->getExtension($this->hFilePath)
                )
            );

            $path = $this->hFileSystemPath.$this->hFilePath;

            if (!file_exists($path))
            {
                $this->notice(
                    "Retrieval of template picture failed because the path '{$path}' does not exist.",
                    __FILE__,
                    __LINE__
                );

                exit;
            }

            if (!empty($hFileMIME))
            {
                $this->hFileMIME = $hFileMIME;
            }

            if (isset($_GET['hFileLastModified']))
            {
                $this->hFileDisableCache = false;
                $this->hFileEnableCache  = true;
                $this->hFileCacheExpires = strtotime('+10 Years');
            }

            $this->hFileSize = filesize($path);
            $this->hFileDownload = false;
            $this->hFileSystemDocument = true;
            $this->hFileLastModified = filemtime($path);
            $this->hTemplatePath = '';
            $this->hFileName = basename($path);
        }
        else
        {
            $this->notice(
                "File path: '{$this->hFileSystemPath}' does not exist.",
                __FILE__,
                __LINE__
            );
        }
    }

    private function generateImage($sourcePath, $destinationPath, $dimensions)
    {
        $this->hImage = $this->library('hImage');

        $dimensions = explode('x', $dimensions);

        $this->hImage->resizeImage(
            $sourcePath,
            $destinationPath,
            $dimensions[0],
            $dimensions[1]
        );
    }
}

?>