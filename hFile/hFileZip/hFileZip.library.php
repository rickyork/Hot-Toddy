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
# <h1>File Zip API</h1>
# <p>
#
# </p>
# @end

class hFileZipLibrary extends hPlugin {

    private $hFileUtilities;
    private $hFile;

    private $temporary;
    private $unzip;

    public function hConstructor()
    {
        $this->unzip = $this->hFrameworkPathToUnzip('/usr/bin/unzip');

        $this->temporary = $this->hFrameworkTemporaryPath; // I need a temporary secretary!

        // Delete the temporary folder.
        //`rm -rf "{$temporary}"`;
        if (!file_exists($this->temporary))
        {
            if (is_writable($this->hFrameworkPath))
            {
                $this->mkdir($this->temporary);
                $this->console("Creating temporary directory, {$temporary}");

                $this->chmod($this->temporary, 775);
                $this->console("Chmod of temporary directory set to 775.");
            }
            else
            {
                $this->warning(
                    "Plugin installation failed, unable to make a temporary folder in: ".
                    "'{$this->hFrameworkPath}'.",
                    __FILE__,
                    __LINE__
                );
            }
        }

        if (!is_writable($this->temporary))
        {
            $this->warning(
                "Plugin installation failed, temporary folder '{$this->temporary}' ".
                "is not writable.",
                __FILE__,
                __LINE__
            );
        }

        $this->hFile = $this->library('hFile');
    }

    public function unzip($path, $name)
    {
        if (is_executable($this->unzip))
        {
            $command = '-o '.escapeshellarg($path).' -d '.escapeshellarg($this->temporary.'/'.$name);

            $this->console("Unzipping '{$path}' with command: unzip {$command}");

            $this->pipeCommand($this->unzip, $command);
        }
        else
        {
            $this->warning(
                "Unzip failed '{$this->unzip}' does not appear to be executable.",
                __FILE__,
                __LINE__
            );
        }
    }

    public function unzipToHtFSFromHtFS($path)
    {
        # Get the file plugin, so that I might begin importing the documents and folders
        # into the framework's file system.
        $this->unzipToHtFS(
            $path,
            $this->hFileSystemPath.$path
        );
    }

    public function unzipToHtFS($destinationHtFSFolder, $sourceZipFile)
    {
        $this->hFileSystemAllowDuplicates = true;

        $source = $sourceZipFile;
        $destination = $destinationHtFSFolder;

        $mime = $this->getMIMEType($source);

        if ($mime == 'application/zip')
        {
            $this->console("Unzipping to HtFS '{$source}'");

            # Get the file name without the .zip extension, this method will allow
            # the file name to have any extension, so long as the mime type is
            # application/zip
            $name = basename($source);
            $bits = explode('.', $name);
            array_pop($bits);
            $name = implode('.', $bits);

            $unzippedFolder = $this->temporary.'/'.$name;

            if (file_exists($unzippedFolder))
            {
                $this->rm($unzippedFolder);
            }

            # Files will be unzipped to /Temporary/{fileName without .zip}
            $this->unzip($source, $name);

            $folder = dirname($destination);

            if (file_exists($unzippedFolder))
            {
                # Load file utilities and prepare to scan the zip folder, empty the
                # default file and folder exclusions so everything in the zip archive
                # will be imported to the framework's file system.
                $this->hFileUtilities = $this->library(
                    'hFile/hFileUtilities',
                    array(
                        'excludeFolders' => array(),
                        'includeFileTypes' => array(),
                        'autoScanEnabled' => false,
                        'scanTextFiles' => false
                    )
                );

                # Scan the unzipped folder tree, absolute paths to all files and folders
                # will now be in the files variable.
                $this->hFileUtilities->scanFiles($unzippedFolder);

                $folders = $this->hFileUtilities->getFolders();
                $files = $this->hFileUtilities->getFiles();

                $destinationPath = $this->getConcatenatedPath($folder, $name);

                if ($this->hFile->exists($destinationPath))
                {
                    $this->hFile->delete($destinationPath);
                }

                $this->console("Creating new folder '{$name}' in path '{$folder}'");

                $this->hFile->makePath(
                    $this->getConcatenatedPath($folder, $name)
                );

                if (is_array($folders))
                {
                    foreach ($folders as $folder)
                    {
                        $currentPath = $this->getEndOfPath(
                            $folder,
                            $unzippedFolder
                        );

                        $importPath = $this->getConcatenatedPath(
                            $destinationPath,
                            $currentPath
                        );

                        $this->console("Importing from zip archive '{$importPath}'");

                        $this->hFile->makePath($importPath);
                    }

                    if (is_array($files))
                    {
                        foreach ($files as $file)
                        {
                            $currentPath = $this->getEndOfPath(
                                $file,
                                $unzippedFolder
                            );

                            $importPath = $this->getConcatenatedPath(
                                $destinationPath,
                                $currentPath
                            );

                            $this->console("Importing from zip archive '{$importPath}'");

                            $upload = $this->hFile->import(
                                dirname($importPath),
                                array(
                                    array(
                                        'hFileTempPath'           => $file,
                                        'hFileMIME'               => $this->getMIMEType($file),
                                        'hFileName'               => hString::escapeAndEncode(basename($file)),
                                        'hFileSize'               => filesize($file),
                                        'hFileTitle'              => '',
                                        'hFileDescription'        => '',
                                        'hUserPermissions'        => true,
                                        'hUserPermissionsWorld'   => '',
                                        'hUserPermissionsInherit' => true,
                                        'hFileReplace'            => false
                                    )
                                )
                            );
                        }
                    }

                    # Cleanup after yourself.
                    $this->rm($unzippedFolder);
                }
                else
                {
                    $this->warning(
                        "The folder hierarchy of the unzipped folder was not successfully retrieved ".
                        "from '{$unzippedFolder}'.",
                        __FILE__,
                        __LINE__
                    );
                }
            }
            else
            {
                $this->warning(
                    "The unzip operation failed, the unzipped folder structure does not exist at ".
                    "'{$unzippedFolder}'. ",
                    __FILE__,
                    __LINE__
                );
            }
        }
        else
        {
            $this->warning(
                "The path provided '{$path}' is not a ZIP file.",
                __FILE__,
                __LINE__
            );
        }
    }
}

?>