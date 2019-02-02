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
# <h1>Retrieving File Thumbnails</h1>
# <p>
#   This plugin creates thumbnails of supported file formats.  Generally the
#    supported format is determined by the hImage plugin prior to making a
#   request to this plugin based on the capabilities of the chosen image
#   processing API, e.g., CoreImage, ImageMagik, PHP GD.
# </p>
# <p>
#   This plugin generally lives at the path:<br />
#   www.example.com/System/Applications/Thumbnail.plugin
# </p>
# <p>
#   Or perhaps:<br />
#   www.example.com/System/Applications/File/Thumbnail.plugin
# </p>
# <p>
#   The location of the Thumbnail.plugin file can be moved anywhere
#   you like and be referenced dynamically by calling:
# </p>
# <code>
#   $this->getFilePathByPlugin('hFile/hFileThumbnail');
# </code>
# <p>
#   This will output the path to wherever the file this plugin
#   is attached to presently resides.
# </p>
# @end

class hFileThumbnail extends hPlugin {

    protected $hFinder;
    protected $hFile;
    private $hImage;

    public function hConstructor()
    {
        hString::scrubArray($_GET);

        if (isset($_GET['path']))
        {
            $this->hFinder = $this->library('hFinder');
            $this->hFile   = $this->library('hFile');

            // This probably seems excessive, but it's needed to support non-ascii characters
            // in file paths.
            $path = urldecode($_GET['path']);
            $path = hString::safelyDecodeURLPath($path);

            $path = mb_convert_encoding($path, 'UTF-8', 'HTML-ENTITIES');
            $path = mb_convert_encoding($path, 'UTF-8', 'HTML-ENTITIES');
            $path = htmlspecialchars($path, ENT_QUOTES);
            $path = mb_convert_encoding($path, 'HTML-ENTITIES', 'UTF-8');

            // Query the document in the framework's file system to pull up some information
            // about the document.
            $this->hFile->query($path);

            // Turn on the hFileSystemDocument toggle.  This controls how the file is handled
            // further down the execution chain.
            //
            // For example, this indicates to the framework that the file won't be in a template
            // and that it will be retrieved directly from the server's file system.  Since it
            // is retrieved directly, it will be output incrementally and efficiently, avoiding
            // memory limits and the proper headers will go out with it.
            $this->hFileSystemDocument = true;

            // Explicitly turn off framework templates by setting the hTemplatePath to null.
            $this->hTemplatePath = '';

            // Does the file exist in the framework's file system?
            if ($this->hFile->exists())
            {
                // If the file is server document, that is to say, a file that is contained on the
                // same server as the framework, but has nothing to do with the framework, then the
                // user should be logged into the framework and a root user to see a thumbnail of
                // that document.
                //
                // Otherwise, if the file is not a 'server' document, but is just a framework document,
                // then the user should merely be authorized to see it by virtue of framework file
                // system permissions (read or read/write).
                //
                // Obviously, then, the framework allows any path on the server to be referenced or
                // retrieved if you have the right credentials.  See hFilePath for more information.
                if ($this->hFile->isServer && $this->hFile->isRootUser || $this->hFile->userIsReadAuthorized)
                {
                    // Split the file name on the dot...
                    $fileName = explode('.', $this->hFile->fileName);

                    // Get the extension of the file.
                    $this->hFileSystemImageExtension = array_pop($fileName);

                    // Get the 'real' path of the document, the absolute server path.
                    $this->hFileSystemImagePath = $this->hFile->isServer? $this->hFile->serverPath : $this->hFileSystemPath.$this->hFile->filePath;

                    // Does the path reference a file that exists?
                    $imageExists = file_exists($this->hFileSystemImagePath);

                    if ($imageExists)
                    {
                        // Create a thumbnail path.  All thumbnails presently are stored in the framework's
                        // HtFS folder.  This keeps the thumbnails in the framework's file system.  If the
                        // path references a document elsewhere on the server, the thumbnail is stored using
                        // the framework's path reference to the server document.
                        //
                        // e.g., www.example.com/System/Server will be stored in HtFS/System/Server
                        //
                        // All thumbnails are created with the suffix '.thumbnail.png'.  For the sake of
                        // consistency, transparency, and quality, all thumbnails are PNG images.

                        $this->hFileSystemThumbnailPath =
                            $this->hFileSystemPath.
                            $this->hFile->getConcatenatedPath(
                                $this->hFile->parentDirectoryPath,
                                // Implode glues back together the part of the file name not containing
                                // the extension.
                                implode('.', $fileName).'.'.(isset($_GET['hMovie'])? '' : 'thumbnail.').'png'
                            );

                        // This call ensures that the path exists in the HtFS folder,
                        // So if HtFS/System/Server does not already exists, these folders
                        // are all created.
                        $this->hFile->makeServerPath(dirname($this->hFileSystemThumbnailPath));

                        // Has a thumbnail already been created?
                        $thumbExists = file_exists($this->hFileSystemThumbnailPath);

                        // If the thumbnail does not exist, create it.
                        //
                        // If the thumbnail does already exist, make sure that it is up-to-date
                        // by comparing the modified time of the thumbnail with the modified time
                        // of the original image.
                        if (!$thumbExists || $thumbExists && @filemtime($this->hFileSystemImagePath) > @filemtime($this->hFileSystemThumbnailPath))
                        {
                            $this->generateThumbnail();
                        }

                        // Set the outgoing MIME type.
                        $this->hFileMIME    = 'image/png';

                        // Define the directory for the image.
                        $this->hDirectoryId = $this->hFile->directoryId;

                        // Define the file name.
                        $this->hFileName = $this->hFile->fileName;
                    }
                    else
                    {
                        // If the image does not exist, send out a default image.
                        $this->noImage();

                        // And log that the image did not exist in the error log.
                        $this->warning(
                            "Thumbnail creation failed: The path {$this->hFileSystemImagePath} does not exist ".
                            "in the server's file system.",
                            __FILE__,
                            __LINE__
                        );
                    }
                }
                else
                {
                    // Go away.
                    $this->notAuthorized();
                }
            }
            else
            {
                // If no file exists in the framework's file system, send out a
                // default image.
                $this->noImage();

                // And log that the file did not exist in the error log.
                $this->warning(
                    "Thumbnail creation failed: The path {$path} does not exist in the framework's file system.",
                    __FILE__,
                    __LINE__
                );
            }
        }
        else
        {
            // Can't generate a thumbnail without a path.
            $this->notice(
                "Unable to generate a thumbnail, no path was provided.",
                __FILE__,
                __LINE__
            );
        }
    }

    private function noImage()
    {
        // Redirect to the default image.
        header('Location: /hFile/hFileThumbnail/Pictures/No Image.png');
        exit;
    }

    private function generateThumbnail()
    {
        // Pass off creation of the thumbnail to the hImage library, which
        // is better equipped for this sort of thing.
        $this->hImage = $this->library('hImage');

        $this->hImage->resizeImage(
            $this->hFileSystemImagePath,
            $this->hFileSystemThumbnailPath,
            110,
            110
        );
    }
}

?>