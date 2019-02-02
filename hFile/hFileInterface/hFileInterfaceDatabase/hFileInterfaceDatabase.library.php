<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File Database Interface
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
# @description
# <h1>HtFS - Hot Toddy File System (Database) Interface API</h1>
# <p>
#   This plugin provides a file system interface API
# </p>
# <p>
#    All methods in this object called from <var>hFileLibrary</var> (the only way methods in this
#    object are called extenally) get one additional parameter at the beginning of the argument
#    list: $path.  Method calls made from one method to another within this same object do not
#    include the <var>$path</var> argument.
# </p>
# @end

class hFileInterfaceDatabaseLibrary extends hFileInterface {

    private $hSubscription;
    private $hFileDatabase;
    private $hFileSpotlightMD;
    private $hFileConvert;
    private $hPhotoDatabase;
    private $hMovieDatabase;
    private $hSmartyPants;

    public $filterPaths = array();
    public $fileTypes = array();
    public $methodsWereAdded = false;

    public function shouldBeCalled()
    {
        # @return boolean

        # @description
        # <h2>Should This Interface Be Used?</h2>
        # <p>
        #    This method is called by
        #    <a href='/Hot Toddy/Documentation?hFile/hFile.library.php'>hFileLibrary</a> and
        #    it exists in all file interfaces.  It answers the question.  Which file interface
        #    should be used?  In this case, the answer to that question is, if the file object is
        #    not a server path, and it is not a category, send it here.
        # </p>
        # @end

        return !$this->isServerPath && !$this->isCategory;
    }

    public function getMethods()
    {
        # @return array

        # @description
        # <h2>Getting the Methods Provided By This Object</h2>
        # <p>
        #    In order to properly implement overloaded methods that are shared between
        #    <a href='/Hot Toddy/Documentation?hFile/hFile.library.php'>hFileLibrary</a> and
        #    <var>hFileInterfaceDatabaseLibrary</var>, it is required that all overloaded methods
        #    be declared and known about so that the <var>__call()</var> method can differentiate
        #    between overloaded methods that point to a file system API, and overloaded methods
        #    that point to somewhere else in Hot Toddy.
        # </p>
        # @end

        // Can't use something nice and simple like get_class_methods(),
        // it returns all methods from all parent objects, and I only want
        // this to return the methods from this object only.
        return array(
            'shouldBeCalled',
            'getMIMEType',
            'getTitle',
            'import',
            'upload',
            'getMetaData',
            'deleteMetaData',
            'getSize',
            'getDescription',
            'getLastModified',
            'getCreated',
            'hasChildren',
            'getDirectories',
            'getLabel',
            'setLabel',
            'getProperty',
            'setProperty',
            'getFiles',
            'search',
            'searchByPreset',
            'rename',
            'delete',
            'newFolder',
            'newDirectory',
            'makePath',
            'move',
            'copy',
            'touch'
        );
    }

    public function getMIMEType()
    {
        # @return string

        # @description
        # <h2>Getting the MIME Type</h2>
        # <p>
        #    Returns the MIME type for the file or folder.  If the resource is a folder,
        #    then a <i>pseudo</i> MIME type is returned.  Pseudo MIME types are used
        #    to identify special folders.  See:
        #    <a href='/Hot Toddy/Documentation?hFile/hFile.library.php#getDirectoryPseudoMIME'>getDirectoryPseudoMIME()</a>
        # </p>
        # <p>
        #    If the resource is a file, and it doesn't have a specific MIME type assigned
        #    to it, Hot Toddy will query the file's extension in its <var>hFileIcons</var>
        #    table.
        # </p>
        # @end

        if ($this->isFolder)
        {
            return $this->getDirectoryPseudoMIME($this->filePath);
        }
        else
        {
            $mime = $this->hFileMIME('', $this->fileId);

            if (empty($mime))
            {
                $this->hDatabase->setDefaultResult('text/plain');

                return $this->hFileIcons->selectColumn(
                    'hFileMIME',
                    array(
                        'hFileExtension' => $this->hFileExtension
                    )
                );
            }

            return $mime;
        }
    }

    public function getTitle()
    {
        # @return string

        # @description
        # <h2>Getting a File's Title</h2>
        # <p>
        #    Returns the file's title stored in <var>hFileTitle</var>.  If the object is a directory,
        #    then nil is returned.
        # </p>
        # @end

        return $this->isDirectory? '' : $this->getFileTitle($this->hFileSymbolicLink? $this->hFileSymbolicLink : $this->fileId);
    }

    public function import($files)
    {
        # @return integer

        # @description
        # <h2>Importing/Uploading Files</h2>
        # <p>
        #   One or multiple files can be imported into HtFS using this method.
        # </p>
        # <h3>How to structure the <var>$files</var> argument</h3>
        # <p>
        #   <var>$files</var> should be provided as a numerically offset collection of
        #   associative arrays.  The structure of the array should look something like this:
        # </p>
        # <code>
        #   $files = array(
        #       array(
        #           'hDirectoryId'      =&gt; 1,
        #           'hFileName'         =&gt; 'File Name.pdf',
        #           'hFileReplace'      =&gt; true,
        #           'hFileMD5Checksum'  =&gt; '6c100fabaa9c3c44fe5ee5e9a76ed090',
        #           'hFileTempPath'     =&gt; '/tmp/asdfoiuasd',
        #           'hFileMIME'         =&gt; 'application/pdf'
        #       )
        #   );
        # </code>
        # <p>
        #   The above array can be repeated for as many files as you have.
        # </p>
        # <h3>Md5 Checksum Duplication Detection</h3>
        # <p>
        #   Duplicate files in Hot Toddy are prevented, by default.  This is enforced by
        #   using md5 checksums on files to detect the presence of an identical file elsewhere
        #   in the file system.  Duplicate file detection can be disabled one of two ways:
        # </p>
        # <ol>
        #   <li>By setting the framework variable <var>hFileSystemAllowDuplicates</var> to <var>true</var></li>
        #   <li>
        #       By supplying the GET argument <var>$_GET['hFileSystemAllowDuplicates']</var> to the Finder, e.g.,
        #       <var>/Applications/Finder?hFileSystemAllowDuplicates=1</var>
        #   </li>
        # </ol>
        # <h3>Response Codes</h3>
        # <p>
        #    The following response codes are returned by this method:
        # </p>
        # <table>
        #    <thead>
        #        <tr>
        #            <th>Response Code</th>
        #            <th>Description</th>
        #        </tr>
        #    </thead>
        #    <tbody>
        #        <tr>
        #            <td>1</td>
        #            <td>Import/Upload was successful</td>
        #        </tr>
        #        <tr>
        #            <td>-3</td>
        #            <td>The file already exists, and the replace parameter is set to <var>false</var></td>
        #        </tr>
        #        <tr>
        #            <td>-31</td>
        #            <td>
        #                The destination path located within the folder at: <var>{hFrameworkFileSystemPath}</var> does not exist.
        #            </td>
        #        </tr>
        #        <tr>
        #            <td>-32</td>
        #            <td>
        #                An identical file already exists elsewhere in the file system.
        #            </td>
        #        </tr>
        #    </tbody>
        # </table>
        # @end

        $basePath = $this->getConcatenatedPath($this->hFrameworkFileSystemPath, $this->filePath);

        $this->makeServerPath($basePath);

        $this->hFileDatabase = $this->database('hFile');
        $this->hFileConvert  = $this->library('hFile/hFileConvert');
        $this->hSubscription = $this->library('hSubscription');
        $this->hSmartyPants  = $this->library('hSmartyPants');

        $allowDuplicates = $this->hFileSystemAllowDuplicates(false);

        # Path is the directory we're uploading to....
        foreach ($files as $index => $file)
        {
            if (!isset($file['hDirectoryId']))
            {
                $file['hDirectoryId'] = $this->directoryId;
            }

            # Get rid of nefarious characters. backslash, forwardslash
            $file['hFileName'] = str_replace(array("/", '\\'), '', $file['hFileName']);

            $file['hFileName'] = hString::entitiesToUTF8($file['hFileName'], false);

            # Smartify quotes and whatnot in the filename so those don't interfere with anything.
            $file['hFileName'] = $this->hSmartyPants->get($file['hFileName']);

            $file['hFileName'] = hString::escapeAndEncode($file['hFileName']);

            $this->console("Imported directory path is: {$this->filePath}");

            $savePath = $this->getConcatenatedPath($this->filePath, $file['hFileName']);
            $fileSystemPath = $this->getConcatenatedPath($basePath, $file['hFileName']);

            $this->console("Importing file to '{$savePath}'");

            if ($this->exists($savePath))
            {
                $this->query($savePath);

                if ($file['hFileReplace'])
                {
                    $file['hFileMD5Checksum'] = md5_file($file['hFileTempPath']);

                    if (!$allowDuplicates && $this->duplicateFileExists($file['hFileMD5Checksum']))
                    {
                        $this->hFileDuplicatePath = $this->getDuplicatePath($file['hFileMD5Checksum']);

                        if ($savePath != $this->hFileDuplicatePath)
                        {
                            return -32;
                        }
                    }

                    $this->deleteMetaData($this->fileId);
                    $GLOBALS['hFramework']->move($file['hFileTempPath'], $fileSystemPath);

                    if (!file_exists($fileSystemPath))
                    {
                        return -31;
                    }

                    $file['hFileDocument'] = hString::escapeAndEncode(trim($this->hFileConvert->getPlainText($fileSystemPath)));

                    unset($file['hFileTempPath']);

                    $this->hFileDatabase->save($file);

                    if ($this->isImage($file['hFileName'], $file['hFileMIME']))
                    {
                        $this->hPhotoDatabase = $this->database('hPhoto');
                        $this->hPhotoDatabase->addPhoto($this->fileId);
                    }

                    if ($this->isVideo($file['hFileName'], $file['hFileMIME']))
                    {
                        $this->hMovieDatabase = $this->database('hMovie');
                        $this->hMovieDatabase->addMovie($this->fileId);
                    }

                    $this->hFiles->activity('Replaced File: '.$this->getFilePathByFileId($this->fileId));
                }
                else
                {
                    return -3;
                }
            }
            else
            {
                $file['hFileMD5Checksum'] = md5_file($file['hFileTempPath']);

                if (!$allowDuplicates && $this->duplicateFileExists($file['hFileMD5Checksum']))
                {
                    $this->hFileDuplicatePath = $this->getDuplicatePath($file['hFileMD5Checksum']);

                    if ($savePath != $this->hFileDuplicatePath)
                    {
                        return -32;
                    }
                }

                $file['hFileId'] = 0;

                $GLOBALS['hFramework']->move($file['hFileTempPath'], $fileSystemPath);

                if (!file_exists($fileSystemPath))
                {
                    return -31;
                }

                $file['hFileDocument'] = hString::escapeAndEncode(trim($this->hFileConvert->getPlainText($fileSystemPath)));

                unset($file['hFileTempPath']);

                $file['hFileId'] = $this->hFileDatabase->save($file);

                if ($this->isImage($file['hFileName'], $file['hFileMIME']))
                {
                    $this->hPhotoDatabase = $this->database('hPhoto');
                    $this->hPhotoDatabase->addPhoto($file['hFileId']);
                }

                if ($this->isVideo($file['hFileName'], $file['hFileMIME']))
                {
                    $this->hMovieDatabase = $this->database('hMovie');
                    $this->hMovieDatabase->addMovie($file['hFileId']);
                }

                $this->hFiles->activity('Uploaded File: '.$this->getFilePathByFileId($file['hFileId']));
            }
        }

        return 1;
    }

    public function upload($files)
    {
        # @return integer

        # @description
        # <h2>Uploading Files to HtFS</h2>
        # <p>
        #   This method is an alias for <a href='#import' class='code'>import()</a>
        # </p>
        # @end

        return $this->import($files);
    }

    public function getMetaData()
    {
        # @return array

        # @description
        # <h2>Getting File Meta Data</h2>
        # <p>
        #    Queries Mac OS X's Spotlight application for meta data.  In order to use Spotlight,
        #    all folders must be visible (the name cannot be preceded with a dot).
        # </p>
        # <p>
        #    See: <a href='/Hot Toddy/Documentation?hFile/hFileSpotlight/hFileSpotlightMD/hFileSpotlightMD.library.php'>hFileSpotlightMDLibrary</a>
        # </p>
        # @end

        $data = array();

        if ($this->isDirectory)
        {
            $directory = $this->getDirectories(true, false);

            $data = array(
                'DisplayName'         => $this->filePath == '/'? $this->hFinderDiskName($this->hServerHost) : $this->fileName,
                'ContentCreationDate' => $directory['hFileCreated'],
                'ContentModifiedDate' => $directory['hFileLastModified'],
                'FSInvisible'         => substr($this->fileName, 0, 1) == '.'? 1 : 0,
                'FSName'              => $this->filePath == '/'? $this->hFinderDiskName($this->hServerHost) : $this->fileName,
                'FSSize'              => !empty($directory['hFileSize'])? $directory['hFileSize'] : '--',
                'Kind'                => 'Folder',
                'kMDItemFSLabel'      => $directory['hFileLabel']
            );

            $data = array_merge(
                $data,
                $this->hDirectoryProperties->selectAssociative(
                    array(
                        'hDirectoryIsApplication',
                        'hDirectoryLabel',
                        'hFileIconId'
                    ),
                    $this->directoryId
                )
            );
        }
        else
        {
            if (file_exists($this->hFrameworkFileSystemPath.$this->filePath))
            {
                if ($this->hOS == 'Darwin')
                {
                    $this->hFileSpotlightMD = $this->library('hFile/hFileSpotlight/hFileSpotlightMD');
                    $data = $this->hFileSpotlightMD->get($this->hFrameworkFileSystemPath.$this->filePath);
                }
                else
                {

                }
            }
            else
            {
                $file = $this->getFiles(true, true, false);

                $data = array(
                    'DisplayName'         => $this->fileName,
                    'ContentCreationDate' => $file['hFileCreated'],
                    'ContentModifiedDate' => $file['hFileLastModified'],
                    'FSInvisible'         => substr($this->fileName, 0, 1) == '.'? 1 : 0,
                    'FSName'              => $this->fileName,
                    'FSSize'              => !empty($file['hFileSize'])? $this->bytes($file['hFileSize']) : '--',
                    'Kind'                => 'Website Document',
                    'kMDItemFSLabel'      => $file['hFileLabel']
                );
            }

            $data['ContentAccessedDate'] = $this->hDatabase->selectColumn(
                'hFileLastAccessed',
                'hFileStatistics',
                $this->fileId
            );

            $data = array_merge(
                $data,
                $this->hFileProperties->selectAssociative(
                    array(
                        'hFileIconId',
                        'hFileDownload',
                        'hFileIsSystem',
                        'hFileLabel'
                    ),
                    $this->fileId
                )
            );
        }

        return $data;
    }

    public function getSize()
    {
        # @return string

        # @description
        # <h2>Getting File and Folder Sizes</h2>
        # <p>
        #    This method returns a file or folder's size.  Note that getting folder sizes can be
        #    very time consuming.
        # </p>
        # @end

        if ($this->isDirectory)
        {
            return 0;

            # $bytes = 0;
            #
            # $query = $this->hDatabase->getResults(
            #     $this->getTemplateSQL(
            #         array(
            #             'path' => $this->filePath
            #         )
            #     )
            # );
            #
            # foreach ($query as $data)
            # {
            #     if (file_exists($this->hFrameworkFileSystemPath.$data['hFilePath']))
            #     {
            #         $bytes += filesize($this->hFrameworkFileSystemPath.$data['hFilePath']);
            #     }
            #     else
            #     {
            #         $bytes += $data['hFileSize'];
            #     }
            # }
            #
            # return $this->bytes($bytes);
        }
        else
        {
            if (file_exists($this->hFrameworkFileSystemPath.$this->filePath))
            {
                return $this->bytes(
                    filesize($this->hFrameworkFileSystemPath.$this->filePath)
                );
            }
            else
            {
                return $this->bytes(
                    strlen(
                        $this->hFileDocuments->selectColumn(
                            'hFileDocument',
                            array(
                                'hFileId' => (int) $this->fileId
                            )
                        )
                    )
                );
            }
        }
    }

    public function getDescription()
    {
        # @return string

        # @description
        # <h2>Getting File and Folder Descriptions</h2>
        # <p>
        #    Returns a short description of the file or folder (not the same as the <var>hFileDescription</var>
        #    field in the <var>hFileDocuments</var> database table.  If the file system object is a folder,
        #    the returned description looks something like this "Folder containing x folder(s) and x file(s)".  If the file
        #    system object is a file, the returned description will look something like this:
        #    "ISO Media, MPEG v4 system, iTunes AAC-LC" (this is an AAC file).
        # </p>
        # @end

        if ($this->isDirectory)
        {
            $description = 'Folder containing '.
                $this->hDirectories->selectCount(
                    'hDirectoryId',
                    array(
                        'hDirectoryParentId' => $this->directoryId
                    )
                ).' folder(s) and '.
                $this->hFiles->selectCount(
                    'hFileId',
                    array(
                        'hDirectoryId' => $this->directoryId
                    )
                ).' file(s)';
        }
        else
        {
            $description = $this->getFileDescription($this->hFileSymbolicLink? $this->hFileSymbolicLink : $this->fileId);

            if (empty($description) && file_exists($this->hFrameworkFileSystemPath.$this->filePath))
            {
                $description = $this->command('file -b '.escapeshellarg($this->hFrameworkFileSystemPath.$this->filePath));
            }
        }

        return $description;
    }

    public function getLastModified()
    {
        # @return integer

        # @description
        # <h2>Getting the Last Modified Time</h2>
        # <p>
        #    If the file system object is a folder, the <var>hDirectoryLastModified</var> Unix Timestamp is returned
        #    from the <var>hDirectories</var> table.  If the file system object is a file, the
        #    <var>hFileLastModified</var> Unix Timestamp is returned from the <var>hFiles</var> table.
        # </p>
        # @end

        if ($this->isDirectory)
        {
            return $this->hDirectories->selectColumn(
                'hDirectoryLastModified',
                (int) $this->directoryId
            );
        }
        else
        {
            return $this->hFiles->selectColumn(
                'hFileLastModified',
                (int) $this->fileId
            );
        }
    }

    public function getCreated()
    {
        # @return integer

        # @description
        # <h2>Getting the Created Time</h2>
        # <p>
        #    If the file system object is a folder, the <var>hDirectoryCreated</var> Unix Timestamp is returned
        #    from the <var>hDirectories</var> database table.  If the file system object is a file, the <var>hFileCreated</var>
        #    Unix Timestamp is returned from the <var>hFiles</var> database table.
        # </p>
        # @end

        if ($this->isDirectory)
        {
            return $this->hDirectories->selectColumn(
                'hDirectoryCreated',
                (int) $this->directoryId
            );
        }
        else
        {
            return $this->hFiles->selectColumn(
                'hFileCreated',
                (int) $this->fileId
            );
        }
    }

    public function hasChildren($countFiles = false)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Folder Has Children</h2>
        # <p>
        #    This method reports whether a folder has children within it.  If the <var>$countFiles</var>
        #    parameter is <var>false</var> (it is, by default), then this function only returns <var>true</var>
        #    if this folder contains other folders.  If <var>$countFiles</var> is <var>true</var>, then
        #    this function will return <var>true</var> if this folder contains either files or folders.
        # </p>
        # @end

        if ($this->isDirectory)
        {
            $hasDirectories = $this->hDirectories->selectExists(
                'hDirectoryId',
                array(
                    'hDirectoryParentId' => (int) $this->directoryId
                )
            );

            $hasFiles = $this->hFiles->selectExists(
                'hFileId',
                array(
                    'hDirectoryId' => (int) $this->directoryId
                )
            );

            return ($hasDirectories || ($countFiles && $hasFiles));
        }
        else
        {
            return $this->hFiles->selectExists(
                'hFileId',
                array(
                    'hFileParentId' => (int) $this->fileId
                )
            );
        }
    }

    public function getDirectories($checkPermissions = true, $queryParent = true)
    {
        # @return array

        # @description
        # <h2>Getting Child Directories</h2>
        # <p>
        #    This method returns all immediate child folders of the specified directory.
        #    If <var>$checkPermissions</var> is <var>true</var>, then permissions are
        #    checked as part of the query and only folders the user is allowed to access
        #    are returned in the query.
        # </p>
        # <p>
        #    The <var>$queryParent</var> parameter changes the query so that it matches
        #    the <var>hDirectoryParentId</var> instead of <var>hDirectoryId</var>
        # </p>
        # <h3>Returned Data</h3>
        # <table>
        #    <tbody>
        #        <tr>
        #            <td class='code'>hFileInterfaceObjectId</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFileName</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hDirectoryName</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFilePath</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hDirectoryPath</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFileIsServer</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hDirectoryId</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hDirectoryIsApplication</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFileIconId</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFileCreated</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFileLastModified</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFileMIME</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFileLabel</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFileSize</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hDirectoryCount</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFileCount</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hCategoryFileSortIndex</td>
        #        </tr>
        #    </tbody>
        # </table>
        # @end

        $directories = array();

        $query = $this->hDatabase->getResults(
            $this->getTemplateSQL(
                dirname(__FILE__).'/SQL/getDirectories',
                array_merge(
                    $this->getPermissionsVariablesForTemplate($checkPermissions, false),
                    array(
                        'directoryId' => (int) $this->directoryId,
                        'queryParent' => $queryParent
                    )
                )
            )
        );

        foreach ($query as $data)
        {
            $name = basename($data['hDirectoryPath']);

            if (!in_array($data['hDirectoryPath'], $this->filterPaths))
            {
                if ($data['hDirectoryPath'] == '/Categories')
                {
                    $data['hDirectoryCount'] = $this->hCategories->selectCount('hCategoryId', 0);
                    $data['hFileCount'] = $this->hCategoryFiles->selectCount('hCategoryId', 0);
                }

                $directories[$name] = array(
                    'hFileInterfaceObjectId'  => $data['hDirectoryId'],
                    'hFileName'               => $name,
                    'hDirectoryName'          => $name,
                    'hFilePath'               => $data['hDirectoryPath'],
                    'hDirectoryPath'          => $data['hDirectoryPath'],
                    'hFileIsServer'           => false,
                    'hDirectoryId'            => (int)  $data['hDirectoryId'],
                    'hDirectoryIsApplication' => (bool) $data['hDirectoryIsApplication'],
                    'hFileIconId'             => (int)  $data['hFileIconId'],
                    'hFileCreated'            => (int)  $data['hDirectoryCreated'],
                    'hFileLastModified'       => (int)  $data['hDirectoryLastModified'],
                    'hFileMIME'               => 'directory',
                    'hFileLabel'              => $data['hDirectoryLabel'],
                    'hFileSize'               => 0,
                    'hDirectoryCount'         => $data['hDirectoryCount'],
                    'hFileCount'              => $data['hFileCount'],
                    'hCategoryFileSortIndex'  => 0
                );
            }
        }

        return $queryParent? $directories : array_pop($directories);
    }

    public function getLabel()
    {
        # @return string

        # @description
        # <h2>Getting the File's Label</h2>
        # <p>
        #    Returns the file or folder's color label, if one is assigned.  The color
        #    labels are: orange, red, yellow, green, blue, purple, and gray.
        # </p>
        # @end

        return $this->getProperty('h'.($this->isDirectory? 'Directory' : 'File').'Label');
    }

    public function setLabel($label)
    {
        # @return void

        # @description
        # <h2>Setting a File's Label</h2>
        # <p>
        #    Sets the file's color label to one of: orange, red, yellow, green, blue, purple, or gray.
        #    To assign "none" the value passed must be nil or empty.
        # </p>
        # @end

        $this->setProperty('h'.($this->isDirectory? 'Directory' : 'File').'Label', $label);
    }

    public function getProperty($field)
    {
        # @return mixed

        # @description
        # <p>
        #    Returns the specified <var>$field</var> from either the <var>hDirectoryProperties</var> or
        #    <var>hFileProperties</var> tables.
        # </p>
        # <h3>File Properties</h3>
        # <p>
        #    File properties include the following:
        # </p>
        # <table>
        #    <tbody>
        #        <tr>
        #            <td>hFileIconId</td>
        #        </tr>
        #        <tr>
        #            <td>hFileMIME</td>
        #        </tr>
        #        <tr>
        #            <td>hFileSize</td>
        #        </tr>
        #        <tr>
        #            <td>hFileDownload</td>
        #        </tr>
        #        <tr>
        #            <td>hFileIsSystem</td>
        #        </tr>
        #        <tr>
        #            <td>hFileSystemPath</td>
        #        </tr>
        #        <tr>
        #            <td>hFileMD5Checksum</td>
        #        </tr>
        #        <tr>
        #            <td>hFileLabel</td>
        #        </tr>
        #    </tbody>
        # </table>
        # <h3>Directory Properties</h3>
        # <p>
        #    Directory properties include the following:
        # </p>
        # <table>
        #    <tbody>
        #        <tr>
        #            <td>hFileIconId</td>
        #        </tr>
        #        <tr>
        #            <td>hDirectoryIsApplication</td>
        #        </tr>
        #        <tr>
        #            <td>hDirectoryLabel</td>
        #        </tr>
        #    </tbody>
        # </table>
        # @end

        if ($this->isDirectory)
        {
            return $this->hDirectoryProperties->selectColumn(
                $field,
                array(
                    'hDirectoryId' => (int) $this->directoryId
                )
            );
        }
        else
        {
            return $this->hFileProperties->selectColumn(
                $field,
                array(
                    'hFileId' => (int) $this->fileId
                )
            );
        }
    }

    public function setProperty($field, $value)
    {
        # @return void

        # @description
        # <h2>Setting File / Directory Properties</h2>
        # <p>
        #    This method sets the relevant properties in either the <var>hFileProperties</var> or
        #    <var>hDirectoryProperties</var> database tables.
        # </p>
        # <p>
        #    See: <a href='#getProperty'>getProperty()</a> for a complete list of properties.
        # </p>
        # @end

        $columns[$field] = $value;

        if ($this->isDirectory)
        {
            $this->hDirectories->modifyResource($this->directoryId);
            $columns['hDirectoryId'] = (int) $this->directoryId;
            $this->hDirectoryProperties->save($columns);
        }
        else
        {
            $this->hFiles->modifyResource($this->fileId);
            $columns['hFileId'] = (int) $this->fileId;
            $this->hFileProperties->save($columns);
        }
    }

    public function getFiles($includeMetaData = true, $checkPermissions = true, $queryByDirectory = true)
    {
        # @return array

        # @description
        # <h2>Getting Files</h2>
        # <p>
        #    This method returns the immediate files contained within the specified folder.
        # </p>
        # <p>
        #    File sorting can be controlled with the framework variables <var>hFileOrderBy</var>
        #    and <var>hFileOrderByDirection</var>
        # </p>
        # <p>
        #    Returned files can also be limited using the framework variable <var>hFileLimit</var>
        # </p>
        # <p>
        #    <var>$includeMetaData</var> controls whether additional data is gathered about each file.
        #    Making this parameter <var>false</var> makes this method slightly faster.
        # </p>
        # <p>
        #    <var>$checkPermissions</var> controls whether permissions are consulted as part of the
        #    query.  If the user does not have access to one or more items in the query, those items
        #    will not appear in the results.
        # </p>
        # <p>
        #    <var>$queryByDirectory</var> if <var>true</var>, the <var>hDirectoryId</var> of the <var>hFiles</var>
        #    table is queried for a match, but if <var>false</var>, the <var>hFileId</var> of the <var>hFiles</var>
        #    table is quiered for a match.
        # </p>
        # <h3>Returned Data</h3>
        # <table>
        #    <tbody>
        #        <tr>
        #            <td class='code'>hFileId</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFileName</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFileCreated</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFileLastModified</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFilePath</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFileTitle</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFileIsServer</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hDirectoryId</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFileDescription</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFileIconId</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFileMIME</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFileLabel</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFileSize</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFileDownload</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFileDocumentSize</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hCategoryFileSortIndex</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFileInterfaceObjectId</td>
        #        </tr>
        #    </tbody>
        # </table>

        # @end

        $files = array();

        if ($this->isDirectory || !$queryByDirectory)
        {
            $orderBy = $this->hFileOrderBy('hFileName');

            if (!is_array($orderBy) && strstr($orderBy, '`') || strstr($orderBy, '('))
            {
                switch (true)
                {
                    case strstr($orderBy, '`hFileName`'):
                    {
                        $orderBy = 'hFileName';
                        break;
                    }
                    case strstr($orderBy, '`hFileSortIndex`'):
                    {
                        $orderBy = 'hFileSortIndex';
                        break;
                    }
                    case stristr($orderBy, 'RAND'):
                    {
                        $orderBy = 'RAND()';
                        break;
                    }
                }
            }

            $sql = $this->getTemplateSQL(
                dirname(__FILE__).'/SQL/getFiles',
                array_merge(
                    $this->getPermissionsVariablesForTemplate($checkPermissions, false),
                    array(
                        'fileId'                 => (int) $this->fileId,
                        'directoryId'            => (int) $this->directoryId,
                        'queryByDirectory'       => $queryByDirectory,
                        'orderBy'                => $orderBy,
                        'limit'                  => $this->hFileLimit(nil),
                        'sortRandom'             => $orderBy == 'RAND()',
                        'categoryFileSortIndex' => 0,
                    )
                )
            );

            $query = $this->hDatabase->getResults($sql);

            $files = $this->getFileResults($query);
        }

        return $queryByDirectory? $files : array_pop($files);
    }

    public function search($searchTerms, $checkPermissions = true)
    {
        # @return array

        # @description
        # <h2>Searching for Files</h2>
        # <p>
        #    The method is similar to <a href='#getFiles'>getFiles()</a>, except it
        #    accepts search terms and searches the entire file system.  It returns
        #    the same data as <a href='#getFiles'>getFiles()</a>.
        # </p>
        # @end

        $sql = $this->getTemplateSQL(
            dirname(__FILE__).'/SQL/search',
            array_merge(
                $this->getPermissionsVariablesForTemplate($checkPermissions, false),
                array(
                    // Quotes can be used to modify a boolean query, so make sure they
                    // get through unharmed.
                    'searchTerms' => str_replace('&quot;', '"', $searchTerms)
                )
            )
        );

        return $this->getFileResults(
            $this->hDatabase->getResults($sql)
        );
    }

    public function searchByPreset($preset, $time = nil, $checkPermissions = true)
    {
        # @return array

        # @description
        # <h2>Searching By Preset</h2>
        # <p>
        #    Preset searches search the entire file system for a type of file.  Built-in
        #    presets include the following:
        # </p>
        # <ul>
        #    <li><var>Images</var> &amp; <var>Pictures</var> - Searches are carried out by matching MIME types with <var>image/*</var></li>
        #    <li><var>Movies</var> &amp; <var>Video</var> - Searches are carried out by matching MIME types with <var>video/*</var></li>
        #    <li><var>Audio</var> &amp; <var>Music</var> - Searches are carried out by matching MIME types with <var>audio/*</var></li>
        #    <li><var>Documents</var> - Searches match any of the following MIME types:
        #        <ul>
        #            <li>application/pdf</li>
        #            <li>application/msword</li>
        #            <li>applicaiton/mspowerpoint</li>
        #            <li>application/vnd.ms-powerpoint</li>
        #            <li>applicatoin/msexcel</li>
        #            <li>application/x-excel</li>
        #            <li>application/excel</li>
        #            <li>application/x-msexcel</li>
        #            <li>application/vnd.ms-excel</li>
        #        </ul>
        #    </li>
        #    <li><var>Time</var> - Searches can be carried out within the context of one of three options available
        #        using the <var>$time</var> parameter:
        #        <ul>
        #            <li>Today</li>
        #            <li>Yesterday</li>
        #            <li>Past Week</li>
        #        </ul>
        #        Items matched include any file created, accessed, or last modifed within the specified time period.
        #     </li>
        # </ul>
        # <p>
        #    The <var>$checkPermissions</var> parameter controls whether permissions are queried as part of the query,
        #    if permissions are checked, then files the user does not have permission to access are not included in
        #    the query.
        # </p>
        # @end

        $startTime = 0;
        $stopTime = 0;

        $sql = '';

        if (!is_array($preset))
        {
            // MIME presents...
            switch ($preset)
            {
                case 'Pictures':
                case 'Images':
                {
                    $sql = "`hFileProperties`.`hFileMIME` LIKE 'image/%'";
                    break;
                }
                case 'Video':
                case 'Movies':
                {
                    $sql = "`hFileProperties`.`hFileMIME` LIKE 'video/%'";
                    break;
                }
                case 'Music':
                case 'Audio':
                {
                    $sql = "`hFileProperties`.`hFileMIME` LIKE 'audio/%'";
                    break;
                }
                case 'Documents':
                {
                    $mimes = array(
                        'application/pdf',
                        'application/msword',
                        'applicaiton/mspowerpoint',
                        'application/vnd.ms-powerpoint',
                        'applicatoin/msexcel',
                        'application/x-excel',
                        'application/excel',
                        'application/x-msexcel',
                        'application/vnd.ms-excel'
                    );

                    $sql = array();

                    foreach ($mimes as $mime)
                    {
                        $sql[] = "`hFileProperties`.`hFileMIME` = '{$mime}'";
                    }
                    break;
                }
                case 'Time':
                {
                    // Stuff I created, accessed, or last modified, in these time ranges...
                    switch (trim($time))
                    {
                        case 'Today':
                        {
                            $startTime = strtotime('-1 day');
                            break;
                        }
                        case 'Yesterday':
                        {
                            $startTime = strtotime('-2 days');
                            $stopTime  = strtotime('-1 day');
                            break;
                        }
                        case 'Past Week':
                        {
                            $startTime = strtotime('-7 days');
                            $stopTime  = strtotime('-5 days');
                            break;
                        }
                    }
                    break;
                }
            }
        }
        else
        {
            $sql = array();

            foreach ($preset as $mime)
            {
                $sql[] = "`hFileProperties`.`hFileMIME` = '{$mime}'";
            }
        }

        $sql = $this->getTemplateSQL(
            dirname(__FILE__).'/SQL/searchByPreset',
            array_merge(
                $this->getPermissionsVariablesForTemplate($checkPermissions, false),
                array(
                    // Quotes can be used to modify a boolean query, so make sure they
                    // get through unharmed.
                    'fileMIME' => is_array($sql)? implode(' OR ', $sql) : $sql,
                    'startTime' => $startTime,
                    'stopTime' => $stopTime
                )
            )
        );

        return $this->getFileResults(
            $this->hDatabase->getResults(
                $sql
            )
        );
    }

    public function rename($newName, $replace = false)
    {
        # @return integer

        # @description
        # <h2>Renaming Files or Folders</h2>
        # <p>
        #    Renames the specified file or folder to the name specified in <var>$newName</var>.
        #    If the file with the new name already exists, then the <var>$replace</var> parameter
        #    decides if that file should be deleted and replaced with the renamed one.  If the
        #    file or folder already exists and the <var>$replace</var> parameter is <var>false</var>
        #    then this method will return response code <var>-3</var>.  If rename was successful,
        #    then this method returns <var>1</var>.
        # </p>
        # @end

        // Remember the old path
        $newName = str_replace("/", '', $newName);

        $newPath = $this->getConcatenatedPath($this->parentDirectoryPath, $newName);

        $this->hFiles->activity('Renamed File: '.$this->filePath.' to '.$newName);

        // Next step is see if the "renamed" direcory or file already exists.
        // Circumvent namespace clashing in HtFS
        // See if a directory exists, if directory
        // See if a file exists, if file
        if ($this->exists($newPath) && strToLower($newPath) != strToLower($this->filePath))
        {
            if (!$replace)
            {
                return -3;
            }
            else
            {
                $this->hFile->delete($newPath);
            }
        }

        if ($this->isDirectory)
        {
            if (file_exists($this->hFrameworkFileSystemPath.$this->filePath))
            {
                // Don't forget to escape shell commands, stupid! :-)
                // The shell cannot handle spaces, thus was it written, and so it is!
                $GLOBALS['hFramework']->rename(
                    $this->hFrameworkFileSystemPath.$this->filePath,
                    $this->hFrameworkFileSystemPath.$newPath
                );
            }

            $query = $this->hDirectories->select(
                array(
                    'hDirectoryId',
                    'hDirectoryPath'
                ),
                array(
                    'hDirectoryPath' => array(
                        array('=', $this->filePath),
                        array('LIKE', $this->filePath.'/%')
                    )
                ),
                'OR',
                'hDirectoryPath'
            );

            foreach ($query as $data)
            {
                if ($data['hDirectoryPath'] == $this->filePath)
                {
                    $path = $newPath;
                }
                else
                {
                    $path = $this->getConcatenatedPath(
                        $newPath,
                        substr(
                            $data['hDirectoryPath'],
                            strlen($this->filePath)
                        )
                    );
                }

                // Replace the name of the directory
                $this->hDirectories->update(
                    array(
                        'hDirectoryPath' => $path
                    ),
                    $data['hDirectoryId']
                );

                $this->hDirectories->modifyResource($data['hDirectoryId']);
            }
        }
        else
        {
            $this->hFiles->update(
                array(
                    'hFileName' => $newName,
                    'hFileLastModifiedBy' => isset($_SESSION['hUserId'])? (int) $_SESSION['hUserId'] : 1
                ),
                (int) $this->fileId
            );

            $this->hFiles->modifyResource($this->fileId);

            $thumbnailPath = $this->hFile->getThumbnailPath($this->filePath);

            if (file_exists($thumbnailPath))
            {
                $this->rm($thumbnailPath);
            }

            if (file_exists($this->hFrameworkFileSystemPath.$this->filePath))
            {
                $GLOBALS['hFramework']->rename(
                    $this->hFrameworkFileSystemPath.$this->filePath,
                    $this->hFrameworkFileSystemPath.$newPath
                );
            }
        }

        $this->unsetPath($this->filePath);
        $this->unsetPath($newPath);
        return 1;
    }

    public function deleteMetaData($fileId)
    {
        # @return boolean

        # @description
        # <h2>Deleting Meta Data</h2>
        # <p>
        #    Permanently deletes all meta data associated with a file.  All
        #    information is deleted, except the file itself and its permissions.
        # </p>
        # <p>
        #    One situation where only meta data is deleted, but permissions are kept is
        #    when the 'Edit File' functionality of hFinder is used or the 'Edit Properties'
        #    GUI used in <a href='/Hot Toddy/Documentation?hEditor/hEditorProperties'>hEditorProperties</a>.
        #    See: <a href='/Hot Toddy/Documentation?hFile/hFile.listener.php#saveFinderProperties'>hFileListener::saveFinderProperties()</a>.
        #    In that scenario, an uploaded file's contents can be updated, while keeping
        #    permissions and other data.
        # </p>
        # @end

        $this->hDatabase->delete(
            array(
                'hFileVariables',
                'hFileStatistics',
                'hFileUserStatistics',
                'hListFiles',
                'hCalendarFiles',
                'hCategoryFiles',
                'hFilePasswords',
                'hFileAliases',
                'hFileComments',
                'hFileDomains',
                'hFileDocuments',
                'hFileHeaders',
                'hFileProperties',
                //'hFileStatusLog',
                'hFileUserStatistics',
                'hFilePathWildcards'
            ),
            'hFileId',
            $fileId
        );

        $this->hListFiles->delete('hListFileId', $fileId);

        // Delete forums
        $forumQuery = $this->hForums->selectQuery(
            'hForumId',
            array(
                'hFileId' => (int) $fileId
            )
        );

        if ($this->hDatabase->resultsExist($forumQuery))
        {
            while ($forumData = $this->hDatabase->getAssociativeResults($forumQuery))
            {
                $topicQuery = $this->hForumTopics->selectQuery(
                    'hForumTopicId',
                    array(
                        'hForumId' => (int) $forumData['hForumId']
                    )
                );

                if ($this->hDatabase->resultsExist($topicQuery))
                {
                    while ($topicData = $this->hDatabase->getAssociativeResults($topicQuery))
                    {
                        $this->hSubscription->delete(
                            'hForumTopics',
                            $topicData['hForumTopicId']
                        );

                        $postQuery = $this->hForumPosts->selectQuery(
                            'hForumPostId',
                            array(
                                'hForumTopicId' => $topicData['hForumTopicId']
                            )
                        );

                        while ($postData = $this->hDatabase->getAssociativeResults($postQuery))
                        {
                            $this->hSubscription->delete(
                                'hForumPosts',
                                $postData['hForumPostId']
                            );
                        }

                        $this->hDatabase->closeResults($postQuery);

                        $this->hForumPosts->delete(
                            'hForumTopicId',
                            $topicData['hForumTopicId']
                        );
                    }

                    $this->hDatabase->closeResults($topicQuery);
                }
            }

            $this->hDatabase->closeResults($forumQuery);
        }

        $this->hSubscription->delete(
            'hForums',
            $fileId
        );

        $this->hForums->delete(
            'hFileId',
            $fileId
        );

        $this->deleteCachedDocuments($fileId);

        return true;
    }

    public function delete()
    {
        # @return boolean

        # @description
        # <h2>Deleting a File or Folder</h2>
        # <p>
        #    Permanently deletes the specified path.  If the path is a folder, the path
        #    is deleted recursively.
        # </p>
        # @end

        $this->hSubscription = $this->library('hSubscription');

        $this->hFiles->activity('Deleted File: '.$this->filePath);

        $files = array();

        if ($this->isDirectory)
        {
            $directories = $this->getAllDirectoriesInPath($this->filePath);

            $directoryCounter = 0;

            foreach ($directories as $name => $data)
            {
                $directory = $this->hFile->getFiles($data['hFilePath']);

                if (count($directory))
                {
                    $files[$directoryCounter] = $directory;
                    $directoryCounter++;
                }
            }
        }
        else
        {
            $files[0] = array(
                $this->fileName => array(
                    'hFileId'   => $this->fileId,
                    'hFilePath' => $this->filePath
                )
            );
        }

        if (isset($files) && is_array($files))
        {
            // Next, time to delete registry entries from related tables.
            foreach ($files as $file)
            {
                foreach ($file as $name => $data)
                {
                    $this->deleteMetaData($data['hFileId']);

                    // Delete privileges
                    $this->hFiles->deletePermissions($data['hFileId']);

                    if (file_exists($this->hFrameworkFileSystemPath.$data['hFilePath']))
                    {
                        $this->rm($this->hFrameworkFileSystemPath.$data['hFilePath'], true);
                    }

                    $thumbnailPath = $this->hFile->getThumbnailPath($data['hFilePath']);

                    if (file_exists($thumbnailPath))
                    {
                        $this->rm($thumbnailPath);
                    }

                    // At last, delete the registry document
                    $this->hFiles->delete('hFileId', $data['hFileId']);
                }
            }

            $this->hFiles->modifyResource();
        }

        if ($this->isDirectory)
        {
            foreach ($directories as $name => $data)
            {
                $this->hDirectories->deletePermissions($data['hDirectoryId']);

                $this->hTemplateDirectories->delete(
                    'hDirectoryId',
                    (int) $data['hDirectoryId']
                );

                if ($data['hFilePath'] != '/')
                {
                    if (file_exists($this->hFrameworkFileSystemPath.$data['hFilePath']))
                    {
                        $this->rm($this->hFrameworkFileSystemPath.$data['hFilePath']);
                    }
                }

                $this->hDirectories->delete(
                    'hDirectoryId',
                    (int) $data['hDirectoryId']
                );
            }

            $this->hDirectories->modifyResource();
        }

        $this->unsetPath($this->filePath);

        return true;
    }

    public function newFolder($newFolderName, $userId = 0)
    {
        # @return integer

        # @description
        # <h2>Creating a New Folder</h2>
        # <p>
        #    This method is deprecated use: <a href='#makePath'>makePath()</a>, instead.
        #    Alias of: <a href='#makePath'>makePath()</a>.
        # </p>
        # @end

        $permissions = 0;

        if (!empty($userId))
        {
            $permissions['hUserId'] = (int) $userId;
        }

        return $this->hFile->makePath(
            $this->getConcatenatedPath(
                $this->filePath,
                $newFolderName
            ),
            $permissions
        );
    }

    public function newDirectory($newDirectoryName, $userId = 0)
    {
        # @return integer

        # @description
        # <h2>Creating a New Directory</h2>
        # <p>
        #    This method is deprecated use: <a href='#makePath'>makePath()</a>, instead.
        #    Alias of: <a href='#makePath'>makePath()</a>.
        # </p>
        # @end

        $permissions = array();

        if (!empty($userId))
        {
            $permissions['hUserId'] = (int) $userId;
        }

        return $this->hFile->makePath(
            $this->getConcatenatedPath(
                $this->filePath,
                $newDirectoryName
            ),
            $permissions
        );
    }

    public function makePath($permissions = array())
    {
        # @return integer

        # @description
        # <h2>Creating a Folder</h2>
        # <p>
        #    When called from <a href='/Hot Toddy/Documentation?hFile/hFile.library.php'>hFileLibrary</a>
        #    this method signature is used:
        # </p>
        # <code>public function makePath($path, array $permissions = array())</code>
        # <p>
        #    <var>$path</var> is the path that is to be created, all folders that do
        #    not exist in <var>$path</var> are created.
        # </p>
        # <p>
        #    Permissions can be set on each folder, or if no permissions are provided,
        #    permissions will be inherited from the first folder in the path that exists.
        # </p>
        # <p>
        #    If permissions are provided, they should be specified as follows in the
        #    <var>$permissions</var> argument:
        # </p>
        # <code>
        #   array(
        #       'hUserPermissionsGroups' => array(
        #           'Website Administrators' => 'rw'
        #       ),
        #       'hUserPermissionsOwner' => 'rw',
        #       'hUserPermissionsWorld' => 'r'
        #   )
        # </code>
        # <p>
        #    All permissions are optional.  Permissions not explicitly provided will be
        #    set to no access at all (with the exception of 'owner', which always defaults
        #    to read &amp; write), unless the default empty array is provided, in
        #    which case, permissions will be inherited from each parent folder for each
        #    folder that does not exist.
        # </p>
        # @end

        $directoryId = 0;

        $path = $this->filePath;

        $folders = explode('/', $path);

        $currentPath = '/';

        foreach ($folders as $folder)
        {
            if (!empty($folder))
            {
                $currentPath .= ($currentPath == '/')? $folder : '/'.$folder;

                if (!$this->exists($currentPath))
                {
                    $parentDirectoryId = $this->getDirectoryId(dirname($currentPath));

                    $directoryId = $this->hDirectories->insert(
                        array(
                            'hDirectoryId'           => nil,
                            'hDirectoryParentId'     => (int) $parentDirectoryId,
                            'hUserId'                => isset($permissions['hUserId'])? (int) $permissions['hUserId'] : 1,
                            'hDirectoryPath'         => $currentPath,
                            'hDirectoryCreated'      => time(),
                            'hDirectoryLastModified' => 0
                        )
                    );

                    if (!empty($parentDirectoryId) && (empty($permissions) || is_array($permissions) && !count($permissions) || !is_array($permissions)))
                    {
                        $this->hDirectories->inheritPermissionsFrom($parentDirectoryId);
                        $this->hDirectories->savePermissions($directoryId);
                    }
                    else if (!empty($directoryId))
                    {
                        if (isset($permissions['hUserPermissionsGroups']) && is_array($permissions['hUserPermissionsGroups']))
                        {
                            foreach ($permissions['hUserPermissionsGroups'] as $group => $level)
                            {
                                $this->hDirectories->addGroup($group, $level);
                            }
                        }

                        $this->hDirectories->savePermissions(
                            $directoryId,
                            isset($permissions['hUserPermissionsOwner'])? $permissions['hUserPermissionsOwner'] : 'rw',
                            isset($permissions['hUserPermissionsWorld'])? $permissions['hUserPermissionsWorld'] : ''
                        );
                    }
                }
            }
        }

        $this->hDirectories->modifyResource();

        # Last directory created is returned.
        return $directoryId;
    }

    public function move($sourcePath, $replace = false)
    {
         # @return integer

         # @description
         # <h2>Moving a Folder or File From One Folder to Another</h2>
         # <p>
         #    When called via <a href='/Hot Toddy/Documentation?hFile/hFile.library.php'>hFileLibrary</a>
         #    this method signature is used:
         # </p>
         # <code>public function move($destinationPath, $sourcePath, $replace = false)</code>
         # <p>
         #    <var>$sourcePath</var> specifies the file or folder to be moved, and <var>$destinationPath</var>
         #    specifies the folder that <var>$sourcePath</var> will be moved to.  In the event that
         #    <var>$sourcePath</var> is a folder, it will be moved recursively.
         # </p>
         # <p>
         #    This method takes care of moving thumbnails, physical files existing in the <var>HtFS</var>
         #    folder, and all necessary database modifications.
         # </p>
         # <h3>Response Codes</h3>
         # <p>
         #    The following response codes are returned by this method:
         # </p>
         # <table>
         #    <thead>
         #        <tr>
         #            <th>Response Code</th>
         #            <th>Description</th>
         #        </tr>
         #    </thead>
         #    <tbody>
         #        <tr>
         #            <td>1</td>
         #            <td>Move was successful</td>
         #        </tr>
         #        <tr>
         #            <td>-3</td>
         #            <td>
         #                A file or folder already exists by the same name as the source file or folder in
         #                the destination folder, and the <var>$replace</var> flag is
         #                set to <var>false</var>.  To override an existing file or folder at the
         #                destination by the same name, <var>$replace</var> must be set to <var>true</var>.
         #            </td>
         #        </tr>
         #        <tr>
         #            <td>-18</td>
         #            <td>
         #                <var>$sourcePath</var> and <var>$destinationPath</var> are the same path.  A file or folder
         #                cannot be moved to where it already is.
         #            </td>
         #        </tr>
         #        <tr>
         #            <td>-20</td>
         #            <td><var>$destinationPath</var> is not a folder, and therefore not a valid destination path.</td>
         #        </tr>
         #        <tr>
         #            <td>-21</td>
         #            <td>
         #                <var>$destinationPath</var> is a location within <var>$sourcePath</var>, a folder
         #                cannot be moved to a location within itself.
         #            </td>
         #        </tr>
         #    </tbody>
         # </table>
         # @end

        if (!$this->isDirectory)
        {
            return -20; // Destination is not a directory
        }
        else
        {
            $destination  = $this->filePath;
            $directoryId = $this->directoryId;

            $path = $this->getConcatenatedPath(
                $this->filePath,
                basename($sourcePath)
            );

            $exists = $this->exists($path);

            if ($exists && !$replace)
            {
                return -3; // File or Directory already exists
            }

            if (!$exists || $exists && $replace)
            {
                $this->hFile = $this->library('hFile');
                $this->hFile->query($sourcePath);

                if ($exists)
                {
                    $this->hFile->delete($path);
                }

                if ($this->isDirectory)
                {
                    if ($destination == $this->filePath)
                    {
                        return -18;
                    }

                    // Make sure a directory isn't being moved into itself...
                    if ($this->beginsPath($destination, $this->filePath))
                    {
                        return -21;
                    }

                    $this->hDirectories->update(
                        array(
                            'hDirectoryParentId' => (int) $directoryId
                        ),
                        array(
                            'hDirectoryPath' => $this->filePath
                        )
                    );

                    $query = $this->hDirectories->selectQuery(
                        array(
                            'hDirectoryId',
                            'hDirectoryPath'
                        ),
                        array(
                            'hDirectoryPath' => array(
                                array(
                                    '=',
                                    $this->filePath
                                ),
                                array(
                                    'LIKE',
                                    $this->filePath.'/%'
                                )
                            )
                        ),
                        'OR',
                        'hDirectoryPath'
                    );

                    // Find the position of the last slash in the path,
                    // this is where the path will have to be spliced, so that
                    // a new path can be grafted on
                    $sliceStart = strrpos($this->filePath, '/');

                    for ($pathCounter = 0; $data = $this->hDatabase->getAssociativeResults($query); $pathCounter++)
                    {
                        $slice = substr($data['hDirectoryPath'], $sliceStart);

                        $newPath = $this->getConcatenatedPath($destination, $slice);
                        $newPath = str_replace('//', '/', $newPath);

                        if (!$pathCounter && file_exists($this->hFrameworkFileSystemPath.$sourcePath))
                        {
                            $destinationDirectory = dirname($this->hFrameworkFileSystemPath.$newPath);

                            if (!file_exists($destinationDirectory))
                            {
                                $this->hFile->makeServerPath($destinationDirectory);
                            }

                            $GLOBALS['hFramework']->rename(
                                $this->hFrameworkFileSystemPath.$sourcePath,
                                $this->hFrameworkFileSystemPath.$newPath
                            );
                        }

                        $this->hDirectories->update(
                            array(
                                'hDirectoryPath' => $newPath
                            ),
                            (int) $data['hDirectoryId']
                        );

                        $this->hDirectories->modifyResource($data['hDirectoryId']);
                    }

                    $this->hDatabase->closeResults($query);
                }
                else
                {
                    // First move along any files in the server file system
                    $this->hFiles->update(
                        array(
                            'hDirectoryId' => (int) $directoryId,
                            'hFileLastModifiedBy' => isset($_SESSION['hUserId'])? (int) $_SESSION['hUserId'] : 1
                        ),
                        (int) $this->fileId
                    );

                    $this->hFiles->modifyResource($data['hFileId']);

                    $thumbnailPath = $this->hFile->getThumbnailPath($this->filePath);

                    if (file_exists($thumbnailPath))
                    {
                        $this->rm($thumbnailPath, true);
                    }

                    if (file_exists($this->hFrameworkFileSystemPath.$this->filePath))
                    {
                        $newPath = $this->getFilePathByFileId($this->fileId);
                        $destinationDirectory = dirname($this->hFrameworkFileSystemPath.$newPath);

                        if (!file_exists($destinationDirectory))
                        {
                            $this->hFile->makeServerPath($destinationDirectory);
                        }

                        $GLOBALS['hFramework']->rename(
                            $this->hFrameworkFileSystemPath.$this->filePath,
                            $this->hFrameworkFileSystemPath.$newPath
                        );
                    }
                }
            }
        }

        $this->unsetPath($sourcePath);
        $this->unsetPath($this->filePath);
        return 1;
    }

    public function copy($destination = nil)
    {
        # @return integer

        # @description
        # <h2>Copying a File</h2>
        # <p>
        #    This method allows you to copy any file in the Hot Toddy File System.
        #    In hFinder, this functionality is labeled 'Duplicate' in the UI.  The
        #    GUI presently limits duplication to files, the functionality to duplicate
        #    a folder does not yet exist.
        # </p>
        # <p>

        if (!$this->isDirectory)
        {
            return 0;
        }

        $this->hFileDatabase = $this->database('hFile');

        $file = $this->hFiles->selectAssociative(
            array(
                'hLanguageId',
                'hDirectoryId',
                'hUserId',
                'hFileParentId',
                'hFileName',
                'hPlugin',
                'hFileSortIndex'
            ),
            $this->fileId
        );

        $file['hFileId'] = 0;

        $this->hFiles->modifyResource();
        $this->hDirectories->modifyResource($file['hDirectoryId']);

        $directoryPath = $this->getDirectoryPath($file['hDirectoryId']);

        // Add "Copy" to file name
        // File the position of the last "." in the file name
        // split the string on the position of that ".", add " Copy"
        // Check to see if the file name exists.
        // If it exists do Copy 1...  Copy 2, and so on.
        if (empty($destination))
        {
            $fileExtension = $this->getExtension($file['hFileName']);

            $name = $file['hFileName'];

            $pathCounter = 0;

            do {
                $file['hFileName'] = substr_replace(
                    $name,
                    ' Copy'.($pathCounter > 0? " {$pathCounter}" : '').'.'.$fileExtension, -(strlen($fileExtension) + 1)
                );

                $path = $this->getConcatenatedPath($directoryPath, $file['hFileName']);

                $pathCounter++;

            } while($this->exists($path));
        }
        else
        {
            $file['hDirectoryId'] = $this->getDirectoryId(dirname($destination));
            $file['hFileName']    = basename($destination);

            $path = $destination;
        }

        $fileDocument = $this->hFileDocuments->selectAssociative(
            array(
                'hFileDescription',
                'hFileKeywords',
                'hFileTitle',
                'hFileDocument'
            ),
            array(
                'hFileId' => $this->fileId
            )
        );

        $fileHeaders = $this->hFileHeaders->selectAssociative(
            array(
                'hFileCSS',
                'hFileJavaScript'
            ),
            $this->fileId
        );

        $fileProperties = $this->hFileProperties->selectAssociative(
            array(
                'hFileIconId',
                'hFileMIME',
                'hFileSize',
                'hFileDownload',
                'hFileIsSystem',
                'hFileLabel'
            ),
            $this->fileId
        );

        // hFileVariables
        $query = $this->hFileVariables->select(
            array(
                'hFileVariable',
                'hFileValue'
            ),
            $this->fileId
        );

        foreach ($query as $data)
        {
            $file[$data['hFileVariable']] = $data['hFileValue'];
        }

        $calendarFiles = array();

        $query = $this->hDatabase->select(
            array(
                'hCalendarFiles' => array(
                    'hCalendarId',
                    'hCalendarCategoryId',
                    'hCalendarBegin',
                    'hCalendarEnd',
                    'hCalendarRange'
                ),
                'hCalendarFileDates' => array(
                    'hCalendarDate',
                    'hCalendarBeginTime',
                    'hCalendarEndTime',
                    'hCalendarAllDay'
                )
            ),
            array(
                'hCalendarFiles',
                'hCalendarFileDates'
            ),
            array(
                'hCalendarFiles.hCalendarFileId' => 'hCalendarFileDates.hCalendarFileId',
                'hCalendarFiles.hFileId' => $this->fileId
            )
        );

        foreach ($query as $data)
        {
            $calendarFiles['hFileCalendarId'][]         = $data['hCalendarId'];
            $calendarFiles['hFileCalendarCategoryId']   = $data['hCalendarCategoryId'];
            $calendarFiles['hFileCalendarBegin']        = $data['hCalendarBegin'];
            $calendarFiles['hFileCalendarEnd']          = $data['hCalendarEnd'];
            $calendarFiles['hFileCalendarRange']        = $data['hCalendarRange'];
            $calendarFiles['hFileCalendarDate'][]       = $data['hCalendarDate'];
            $calendarFiles['hFileCalendarBeginTime'][]  = $data['hCalendarBeginTime'];
            $calendarFiles['hFileCalendarEndTime'][]    = $data['hCalendarEndTime'];
            $calendarFiles['hFileCalendarAllDay'][]     = $data['hCalendarAllDay'];
        }

        $userPermissions = array();

        $permissions = $this->hFiles->getPermissions($this->fileId);

        $userPermissions['hUserPermissions']      = true;
        $userPermissions['hUserPermissionsWorld'] = $permissions['hUserPermissionsWorld'];
        $userPermissions['hUserPermissionsOwner'] = $permissions['hUserPermissionsOwner'];

        $userPermissions['hUserPermissionsGroups'] = array();

        if (isset($permissions['hUserGroups']) && is_array($permissions['hUserGroups']))
        {
            foreach ($permissions['hUserGroups'] as $userGroupId => $userPermissionsGroup)
            {
                $userPermissions['hUserPermissionsGroups'][$userGroupId] = $userPermissionsGroup;
            }
        }

        if (isset($permissions['hUsers']) && is_array($permissions['hUsers']))
        {
            foreach ($permissions['hUsers'] as $userId => $userPermissionsGroup)
            {
                $userPermissions['hUserPermissionsGroups'][$userId] = $userPermissionsGroup;
            }
        }

        $fileId = $this->hFileDatabase->save(
            array_merge(
                $file,
                $fileDocument,
                $fileHeaders,
                $fileProperties,
                $calendarFiles,
                $userPermissions
            )
        );

        // Duplicate other items, if necessary
        // hFileComments
        // hFilePasswords

        // Copy source file binary...
        if (file_exists($this->hFrameworkFileSystemPath.$this->filePath))
        {
            $GLOBALS['hFramework']->copy(
                $this->hFrameworkFileSystemPath.$this->filePath,
                $this->hFrameworkFileSystemPath.$path
            );
        }

        return $fileId;
    }

    public function touch($permissions = array())
    {
        # @return integer

        # @description
        # <h2>Touching (Creating) a File</h2>
        # <p>
        #    This method creates a new blank file.
        # </p>
        # <p>
        #    When called via <a href='/Hot Toddy/Documentation?hFile/hFile.library.php'>hFileLibrary</a>
        #    this method signature is used:
        # </p>
        # <code>public function touch($path)</code>
        # <p>
        #    <var>$path</var> specified the full path to the file to be created.
        # </p>
        # <p>
        #    If the file already exists, or if the parent path does not exist,
        #    this method will return <var>0</var>.
        # </p>
        # <p>
        #    Permissions can be set on the file, or if no permissions are provided,
        #    permissions will be inherited from the parent folder.
        # </p>
        # <p>
        #    If permissions are provided, they should be specified as follows in the
        #    <var>$permissions</var> argument:
        # </p>
        # <code>
        #   array(
        #       'hUserPermissionsGroups' => array(
        #           'Website Administrators' => 'rw'
        #       ),
        #       'hUserPermissionsOwner' => 'rw',
        #       'hUserPermissionsWorld' => 'r'
        #   )
        # </code>
        # <p>
        #    All permissions are optional.  Permissions not explicitly provided will be
        #    set to no access at all (with the exception of 'owner', which always defaults
        #    to read &amp; write), unless the default empty array is provided, in which
        #    case, permissions will be inherited from the parent folder.
        # </p>
        # @end

        if ($this->exists($this->filePath) || !$this->exists($this->parentDirectoryPath))
        {
            return 0;
        }

        $this->hFileDatabase = $this->database('hFile');

        $directoryId = $this->getDirectoryId($this->parentDirectoryPath);

        $fileId = $this->hFileDatabase->save(
            array(
                'hFileId' => 0,
                'hDirectoryId' => $directoryId,
                'hFileName' => $this->fileName
            )
        );

        $this->hDirectories->modifyResource($directoryId);
        $this->hFiles->modifyResource();

        if (empty($permissions) || is_array($permissions) && !count($permissions) || !is_array($permissions))
        {
            $this->hDirectories->inheritPermissionsFrom($this->directoryId);
            $this->hFiles->savePermissions($fileId);
        }
        else
        {
            if (isset($permissions['hUserPermissionsGroups']) && is_array($permissions['hUserPermissionsGroups']))
            {
                foreach ($permissions['hUserPermissionsGroups'] as $group => $level)
                {
                    $this->hFiles->addGroup($group, $level);
                }
            }

            $this->hFiles->savePermissions(
                $fileId,
                isset($permissions['hUserPermissionsOwner'])? $permissions['hUserPermissionsOwner'] : 'rw',
                isset($permissions['hUserPermissionsWorld'])? $permissions['hUserPermissionsWorld'] : ''
            );
        }

        $this->unsetPath($this->filePath);

        return $fileId;
    }
}

?>