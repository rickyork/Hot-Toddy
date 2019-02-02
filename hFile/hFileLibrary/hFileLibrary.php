<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File Library
#//\\ @@@@  @@@@\\\\\|
#//\\\@@@@| @@@@\\\\\|
#//\\\ @@ |\\@@\\\\\\| http://www.hframework.com
#//\\\\  ||   \\\\\\\| © Copyright 2015 Richard York, All rights Reserved
#//\\\\  \\_   \\\\\\|
#//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#//\\\\\  ----  \@@@@| http://www.hframework.com/license
#//@@@@@\       \@@@@|
#//@@@@@@\     \@@@@@|
#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#
# This plugin presently provides a method of accessing supporting framework files
# stored in the following locations:
#
# /Hot Toddy
# /Plugins
# /Library
#
# The supporting files are generally passed-through by setting framework variables
# that point the framework to the file and tell it how the file should be provided.
#
# This plugin takes extra measures with regards to CSS and JS files, CSS files are
# stripped of comments and excessive white space to compress the files for output.
#
# Most JS files are compressed using the 3rd-party Packer script.
# See: /Library/Packer
#
# Some exceptions apply, some JS files don't play well with the packer compression,
# so they are manually exempt from compression.  FCKEditor is one example of this.
#
# This plugin lives at the following path in the framework's file system:
# www.example.com/System/Applications/Library.plugin
#
# Or perhaps:
# www.example.com/System/Applications/File/Library.plugin
#
# (In theory) the location of the Library.plugin file can be moved anywhere
# you like and be referenced dynamically by calling:
#
# $this->getFilePathByPlugin('hFile/hFileLibrary');
#
# This will output the path to wherever the file this plugin
# is attached to presently resides.  (Regarding this particular plugin, this
# may or may not actually work, but the technique usually works with other
# plugins).

class hFileLibrary extends hPlugin {

    private $hFileJSCompress;
    private $hFileCSSCompress;

    # Third-party applications, scripts, css, etc, live outside of the document root in
    # a directory called "Library".  The framework automatically detects requests to
    # the /Library directory, and diverts those requests to this plugin.
    #
    # Additionally, a plugin's supporting files may be stored with it.  To make this
    # possible requests for files residing in /Hot Toddy and /Plugins are
    # also diverted to this plugin.  Both directories are referened in a common URI
    # www.example.com.  Whether or not this plugin looks in /Hot Toddy or
    # /Plugins is determined by the method getIncludePath(), which looks in both locations
    # for the file.
    #
    # A request to www.example.com/hFile/Pictures/something.png goes like this:
    #
    #  1. Look at /Hot Toddy/hFile/Pictures/something.png
    #
    #  2. Look at /Plugins/hFile/Pictures/something.png
    #
    #  3. Look at /private/hFile/Pictures/something.png (this is a legacy folder, don't use it)
    #
    # Whichever path actually exists first, wins.
    #
    # If a file will not load, it's possible the framework hasn't been told about the
    # file.  New types of files should be registered in the database table hFileIcons.
    # hFileIcons stores the MIME type, the extension, a reference to a PNG icon, and
    # a reference to a macintosh ICNS icon.  See hFile/hFileIcon for more information.
    #
    # hFileIcons is used to retrieve MIME types for files, which the framework uses to
    # set the Content-Type header.
    public function hConstructor()
    {
        # hFrameworkFilePath is a boolean variable that is set true when a request comes in
        # to www.example.com
        if ($this->hFrameworkFilePath)
        {
            $path = $this->getIncludePath($this->hServerDocumentRoot.$this->hFileWildcardPath);

            if ($this->beginsPath($path, $this->hServerDocumentRoot))
            {
                $this->hFileSystemPath = $this->hServerDocumentRoot;
            }
            else
            {
                $this->hFileSystemPath = $this->hFrameworkPath;
            }

            #$this->hFilePath = $this->hFileWildcardPath;

            #$this->hFilePath = substr($path, -strlen($this->hFileWildcardPath));

            $this->hFilePath = $this->getEndOfPath($path, $this->hFileSystemPath);

            $name = basename($this->hFilePath);

            if (strstr($this->hFilePath, '.svn'))
            {
                $this->fatal(
                    'Attempted web access to '.$path.'. SVN files cannot be directly accessed from a browser',
                    __FILE__,
                    __LINE__
                );
            }

            # Do not allow access to certain framework files...
            if (strstr($name, '.mail.json'))
            {
                $this->fatal(
                    'Attempted web access to '.$path.'. Mail configuration files cannot be directly accessed through a browser.',
                    __FILE__,
                    __LINE__
                );
            }

            if (substr($name, -4) == '.sql')
            {
                $this->fatal(
                    'Framework SQL documents cannot be directly accessed through a browser.',
                    __FILE__,
                    __LINE__
                );
            }
        }
        else
        {
            # Otherwise the request came in for www.example.com/Library
            $this->hFileSystemPath = $this->hFrameworkLibraryPath;

            $this->hFilePath = $this->getEndOfPath(
                $this->hFileWildcardPath,
                $this->hFrameworkLibraryRoot
            );
        }

        if (file_exists($this->hFileSystemPath))
        {
            # Return the extension portion of the file name.
            $extension = $this->getExtension($this->hFilePath);

            # The shell doesn't always get the right MIME, so
            # let's fix it for the files we know about.
            #
            # It's not a proper byte sniffing MIME determination, but it'll do
            switch ($extension)
            {
                case 'cgi':
                case 'pl':
                case 'cfc':
                case 'cfm':
                case 'py':
                case 'asp':
                case 'lasso':
                case 'afp':
                case 'rb':
                {
                    # Accessing these probably doesn't matter, but I'll prevent it anyway.
                    $this->notice(
                        'Document: '.$this->hFilePath.' is restricted.',
                        __FILE__,
                        __LINE__
                    );

                    break;
                }
                case 'php':
                {
                    # Are you in the /Library folder?
                    if (!$this->hFrameworkFilePath)
                    {
                        # What a coincidence, you're a PHP script you say?  Get outta here, I'm a PHP script!
                        include $this->hFileSystemPath.$this->hFilePath;

                        # Since you're here, I'm bailing.
                        exit;
                    }
                    else
                    {
                        # Framework PHP scripts won't be included this way.  Something tells me it wouldn't be good.
                        $this->notice(
                            'Framework plugin: '.$this->hFilePath.' cannot be accessed directly.',
                            __FILE__,
                            __LINE__
                        );

                        exit;
                    }
                }
            }

            $path = $this->hFileSystemPath.$this->hFilePath;

            if (file_exists($path))
            {
                # Is the MIME registered in the database?
                $hFileMIME = $this->hFileIcons->selectColumn(
                    'hFileMIME',
                    array(
                        'hFileExtension' => $extension
                    )
                );

                if (empty($hFileMIME))
                {
                    # Try to fall back if the MIME isn't explicitly registered with
                    # the database.
                    $hFileMIME = $this->getMIMEType($path);
                }

                # If the hFileLastModified argument is present, turn on caching of
                # the document and cache it for oh say, 10 years.  If the file is
                # updated, the hFileLastModified argument will update too, thus
                # forcing the cached version to be updated.
                if (isset($_GET['hFileLastModified']))
                {
                    $this->hFileDisableCache = false;
                    $this->hFileEnableCache  = true;
                    $this->hFileCacheExpires = strtotime('+10 Years');
                }

                # What's the MIME Type
                $this->hFileMIME = $hFileMIME;

                # File size in bytes
                $this->hFileSize = filesize($path);

                # Is this a file that should be forced to download?  No.
                $this->hFileDownload = false;

                # Is this a file system document?  Yes.
                $this->hFileSystemDocument = true;

                # When was it last modified?
                $this->hFileLastModified = filemtime($path);

                # No template path, sucka.
                $this->hTemplatePath = '';

                # The file name for the HTTP header, so we're not saying the name
                # of the file is "Library.plugin", which is what this would be
                # if this variable were not set here.
                $this->hFileName = basename($path);

                # Additionally, do extra special stuff to JS and CSS documents, like
                # compress the fuck out of them so they download really quickly.
                switch ($extension)
                {
                    case 'js':
                    {
                        # Turn off the output buffer, if it's on.
                        $this->hServerOutputBuffer = true;

                        # Override document output (when hFileSystemDocument is true) tells the framework
                        # I'm going to supply the contents of the document to it, rather than have it
                        # get the document's content from elsewhere on the server.
                        $this->hFrameworkOverrideDocumentOutput = true;

                        $this->hFileJSCompress = $this->library('hFile/hFileJSCompress');

                        $this->hFileDocument = $this->hFileJSCompress->get($this->hFileSystemPath.$this->hFilePath);
                        break;
                    }
                    case 'css':
                    {
                        # Turn off the output buffer, and again override document output.
                        $this->hServerOutputBuffer = true;
                        $this->hFrameworkOverrideDocumentOutput = true;

                        $this->hFileCSSCompress = $this->library('hFile/hFileCSSCompress');

                        # Assign the compressed version to hFileDocument so it will be output.
                        $this->hFileDocument = $this->hFileCSSCompress->get($this->hFileSystemPath.$this->hFilePath);
                        break;
                    }
                }
            }
            else
            {
                $this->warning(
                    "Failed to include library file: Path, {$path}, does not exist.",
                    __FILE__,
                    __LINE__
                );
            }
        }
        else
        {
            $this->warning(
                'File path: '.$this->hFileSystemPath.' does not exist.',
                __FILE__,
                __LINE__
            );
        }
    }

    # Compress a stylesheet using my very crude code that would most
    # certainly fall down upon more heavy scrutinization.
    public function CSS(&$file)
    {
        if (!isset($_GET['compression']))
        {
            # Strip whitespace
            $file = preg_replace('/\s{2,}|\n|\r/', '', $file);
        }

        if (!isset($_GET['comments']))
        {
            # Strip comments
            $file = preg_replace('/\/\*.*\*\//Ums', '', $file);
        }

        # Find template paths and append the last modifed time to those
        $file = $this->parseDocument($file);
    }
}

?>