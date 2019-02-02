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
# <h1>File API</h1>
# <p>
#     This library provides methods which provide information about file or folder objects in the
#     Hot Toddy file system (HtFS), and file or folder objects in the server's file system, in addition
#     to items in file system extensions.
# </p>
# <h2>Member Properties</h2>
# <p>
#     Information about files is made available from the <var>hFileLibrary</var> object in a variety
#     of member properties.
# </p>
# <table id='properties'>
#    <thead>
#        <tr>
#            <th>Property</th>
#            <th>Description</th>
#        </tr>
#    </thead>
#    <tbody>
#        <tr id='baseName'>
#            <td class='code'>baseName</td>
#            <td>The file or folder name.</td>
#        </tr>
#        <tr id='categoryId'>
#            <td class='code'>categoryId</td>
#            <td>The categoryId of the folder, if the folder is a category.</td>
#        </tr>
#        <tr id='directoryId'>
#            <td class='code'>directoryId</td>
#            <td>The directoryId of the folder, if the folder is a directory.</td>
#        </tr>
#        <tr id='directoryPath'>
#            <td class='code'>directoryPath</td>
#            <td>
#                The file path to the file or folder.  In the case of <var>/www.example.com/test</var>, a folder,
#                the value would be <var>/www.example.com</var>.  In the case of <var>/www.example.com/test.html</var>,
#                a file, the value would be <var>/www.example.com</var>.
#            </td>
#        </tr>
#        <tr id='exists'>
#            <td class='code'>exists</td>
#            <td>Whether or not the file or folder or category or whatever exists.</td>
#        </tr>
#        <tr id='extension'>
#            <td class='code'>extension</td>
#            <td>If the object is a file, this property will contain the file's extension.</td>
#        </tr>
#        <tr id='file'>
#            <td class='code'>file</td>
#            <td>
#                Contains cached information about each file system object <var>hFileLibrary</var>
#                gathers information on.
#            </td>
#        </tr>
#        <tr id='fileExtension'>
#            <td class='code'>fileExtension</td>
#            <td>An alias for <a href='#extension' class='code'>extension</a></td>
#        </tr>
#        <tr id='fileId'>
#            <td class='code'>fileId</td>
#            <td>The file's Id, if the object is a file.</td>
#        </tr>
#        <tr id='fileName'>
#            <td class='code'>fileName</td>
#            <td>An alias for <a href='#baseName' class='code'>baseName</a></td>
#        </tr>
#        <tr id='filePath'>
#            <td class='code'>filePath</td>
#            <td>The full path to the file, folder, or category.</td>
#        </tr>
#        <tr id='fileSystemPath'>
#            <td class='code'>fileSystemPath</td>
#            <td>An alias for the framework variable <var>hFrameworkFileSystemPath</var>.</td>
#        </tr>
#        <tr id='fileTypes'>
#            <td class='code'>fileTypes</td>
#            <td>
#                May contain an array of file extensions or MIME types that the user is allowed to access.  If set, the
#                user will only be allowed to interact with files of the extensions or MIME types specified.
#            </td>
#        </tr>
#        <tr id='filterPaths'>
#            <td class='code'>filterPaths</td>
#            <td>May contain an array of paths that should not be revealed to the user.</td>
#        </tr>
#        <tr id='directoryId'>
#            <td class='code'>directoryId</td>
#            <td>The directory's Id, if the object is a directory.</td>
#        </tr>
#        <tr id='isElevatedUser'>
#            <td class='code'>isElevatedUser</td>
#            <td>Whether the user is in an elevated group of any kind.</td>
#        </tr>
#        <tr id='isFile'>
#            <td class='code'>isFile</td>
#            <td>Whether or not the file system object is a file.</td>
#        </tr>
#        <tr id='isFilePath'>
#            <td class='code'>isFilePath</td>
#            <td>An alias for <a href='#isFile' class='code'>isFile</a>.</td>
#        </tr>
#        <tr id='isDirectory'>
#            <td class='code'>isDirectory</td>
#            <td>Whether or not the file system object is a directory.</td>
#        </tr>
#        <tr id='isDirectoryPath'>
#            <td class='code'>isDirectoryPath</td>
#            <td>An alias for <a href='#isDirectory' class='code'>isDirectory</a></td>
#        </tr>
#        <tr id='isRootUser'>
#            <td class='code'>isRootUser</td>
#            <td>Whether or not the user is a member of the <i>root</i> user group.</td>
#        </tr>
#        <tr id='isServer'>
#            <td class='code'>isServer</td>
#            <td>Whether or not the file system object is a real file on the server's file system (rather than a virtual one in the Hot Toddy File System)</td>
#        </tr>
#        <tr id='isServerPath'>
#            <td class='code'>isServerPath</td>
#            <td>An alias for <a href='#isServer' class='code'>isServer</a></td>
#        </tr>
#        <tr id='isWebsiteAdministrator'>
#            <td class='code'>isWebsiteAdministrator</td>
#            <td>Whether or not the user is a member of the <i>Website Administrators</i> user group.</td>
#        </tr>
#        <tr id='parentDirectoryPath'>
#            <td class='code'>parentDirectoryPath</td>
#            <td>An alias for <a href='directoryPath' class='code'>directoryPath</a>.</td>
#        </tr>
#        <tr id='serverPath'>
#            <td class='code'>serverPath</td>
#            <td>
#                If a file is a real file system object, Hot Toddy uses specially prefixed paths to allow any
#                file or folder in the real file system to be referenced.  For example, the root server folder
#                on Mac OS X is <var>/</var>, in Hot Toddy, this folder is <var>/System/Server</var>.  The
#                <var>serverPath</var> property contains the real path, or in this case <var>/</var>.
#            </td>
#        </tr>
#        <tr id='userAuthenticationWasDone'>
#            <td class='code'>userAuthenticationWasDone</td>
#            <td>
#                Whether or not <var>hFileLibrary</var> has already gathered information about the user's
#                group affiliations, once group affiliations have been gathered (whether or not the user is
#                a <i>root</i> user, or in the <i>Website Administrators</i> group, and so on), since that information
#                is constant (for the duration of execution, at least), this property is set to <var>true</var> to
#                prevent that information from being gathered repeatedly.
#            </td>
#        </tr>
#        <tr id='userIsReadAuthorized'>
#            <td class='code'>userIsReadAuthorized</td>
#            <td>Whether or not the user has <i>read</i> access to the file system object.</td>
#        </tr>
#        <tr id='userIsWriteAuthorized'>
#            <td class='code'>userIsWriteAuthorized</td>
#            <td>Whether or not the user has <i>write</i> access to the file system object.</td>
#        </tr>
#    </tbody>
# </table>
# @end

class hFileLibrary extends hPlugin {

    public $file = array();
    private $counter = 0;

    public $filePath  = '';
    public $isElevatedUser = false;
    public $isWebsiteAdministrator = false;
    public $userAuthenticationWasDone = false;
    public $isRootUser;
    public $fileSystemPath = '';

    private $hFileDatabase;
    private $hUserPermissions;
    private $hSubscription;

    public $variables = array(
        // Variables
        'baseName',
        'categoryId',
        'directoryId',
        'directoryPath',
        'exists',
        'extension',
        'fileExtension',
        'fileId',
        'fileName',
        'filePath',
        'fileSystemPath',
        'directoryId',
        'isCategory',
        'isDirectory',
        'isElevatedUser',
        'isFile',
        'isFilePath',
        'isDirectory',
        'isDirectoryPath',
        'isRootUser',
        'isServer',
        'isServerPath',
        'isWebsiteAdministrator',
        'parentDirectoryId',
        'parentDirectoryPath',
        'serverPath',
        'userAuthenticationWasDone',
        'userIsReadAuthorized',
        'userIsWriteAuthorized'
    );

    public $methods = array(
        'duplicateFileExists',
        'exists',
        'hFileExists',
        'inFileSystem',
        'isRootServerDirectory',
        'getAllDirectories',
        'getAllDirectoriesInPath',
        'getAllFiles',
        'getDirectory',
        'getDirectoryPseudoMIME',
        'getDuplicatePath',
        'getFile',
        'getFileId',
        'getFileProperties',
        'getFileResults',
        'getName',
        'getPathVariables',
        'getServerPath',
        'getThumbnailPath',
        'listenerValidation',
        'makeServerPath',
        'mergeFileSystemObjects',
        'newSymbolicLink',
        'setBaseName',
        'setCategoryId',
        'setDirectoryId',
        'setExists',
        'setExtension',
        'setFileId',
        'setFilePath',
        'setFileTypes',
        'setFilterPaths',
        'setInterfacePlugins',
        'setIsDirectory',
        'setIsElevatedUser',
        'setIsFile',
        'setIsRootUser',
        'setIsServerPath',
        'setIsWebsiteAdministrator',
        'setIsCategory',
        'setLimit',
        'setParentDirectoryId',
        'setParentDirectoryPath',
        'setPath',
        'setPermissionsMethodToEverything',
        'setPermissionsMethodToWorld',
        'setServerPath',
        'setUserAuthentication',
        'setUserAuthenticationWasDone',
        'setUserIsReadAuthorized',
        'setUserIsWriteAuthorized',
        'sortByCreated',
        'sortByFileName',
        'sortByIndex',
        'sortByLastModified',
        'sortByRandom',
        'unsetPath',
        'query'
    );

    public $filterPaths = array();
    public $fileTypes = array();

    public function hConstructor()
    {
        # @return void

        # @description
        # <h2>Constructor</h2>
        # <p>
        #    The constructor sets the internal <var>fileSystemPath</var> to the value of the
        #    framework variable <var>hFrameworkFileSystemPath</var>.  Then the plugin
        #    <a href='/Hot Toddy/Documentation?hFile/hFileInterface'>hFileInterface</a> is
        #    included, which contains an <var>interface</var> for a file system API.
        # </p>
        # @end

        $this->fileSystemPath = $this->hFrameworkFileSystemPath;

        $this->includePlugin('hFile/hFileInterface');
    }

    public function &setPath($path)
    {
        # @return hFileLibrary

        # @description
        # <h2>Setting the Active File Path</h2>
        # <p>
        #    Calling this method sets the active file path.  Most other method calls
        #    are carried out in context to this path.  For example, if you call:
        # </p>
        # <code>
        #    $this-&gt;hFile-&gt;setPath('/www.example.com/index.html');
        # </code>
        # <p>
        #    Then the following call to <var>exists()</var> checks to see if
        #    <var>/www.example.com/index.html</var> exists.
        # </p>
        # <code>
        #    $this-&gt;hFile-&gt;exists();
        # </code>
        # <p>
        #    <var>setPath();</var> sets the file path so other method calls in
        #    relation to that file path can be carried out without having to
        #    specify the path again and again.
        # </p>
        # @end

        $this->filePath = $path;
        return $this;
    }

    public function &setFilePath($path)
    {
        # @return hFileLibrary

        # @description
        # <h2>Setting the Active File Path</h2>
        # <p>
        #    This is an alias for <a href='#setPath' class='code'>setPath()</a>
        # </p>
        # @end

        $this->filePath = $path;
    }

    public function &unsetPath($path)
    {
        # @return hFileLibrary

        # @description
        # <h2>Unsetting a File Path</h2>
        # <p>
        #    Removes cached data from the <var>file</var> property, which stores
        #    various information associated with a file or folder.  Sets the
        #    active path to nil.
        # </p>
        # @end

        if (isset($this->file[$path]))
        {
            unset($this->file[$path]);
        }

        $this->filePath = nil;

        return $this;
    }

    public function &query($path, $checkExists = false)
    {
        # @return hFileLibrary

        # @description
        # <h2>Querying a Path</h2>
        # <p>
        #    This method analyzes a file system object whether in the Hot Toddy File System (HtFS),
        #    the real file system, or in Hot Toddy's category file system.  This method begins by setting the
        #    active path in the <var>filePath</var> property.  Subsequent method calls will be carried out
        #    in the context of that path until it is changed.  Once the path is set, this method goes
        #    on to set a few dozen properties about the path.  Documentation of all of the member properties
        #    set in <var>hFileLibrary</var> appear <a href='#properties'>here</a>.
        # </p>
        # <h3>Querying an HtFS Path</h3>
        # <p>
        #    Typically and more often than not, when you're querying a file system object, you're dealing with
        #    a file system object that's located in Hot Toddy's database-driven file system or (HtFS).  These
        #    are the files stored in <var>hFiles</var> and <var>hDirectories</var> and associated database tables.
        # </p>
        # <h3>Querying a Server Path</h3>
        # <p>
        #    Although <var>query()</var>
        #    analyzes paths from many sources, the path provided to it must be a Hot Toddy File System
        #    path.  For example, a real folder on the server might be <var>/usr/local/bin</var>, to
        #    analyze this path it must be first transformed to its equivalent Hot Toddy File System
        #    path, which would be <var>/System/Server/usr/local/bin</var>.  Hot Toddy provides the following
        #    methods to assist with server paths:
        # </p>
        # <ul>
        #    <li>
        #        <a href='/Hot Toddy/Documentation?hFile/hFilePath#isServerPath'>isServerPath($path, $exactMatch = false)</a>
        #        is used to tell you whether or not the Hot Toddy path you're dealing with is in fact a server path.
        #        The <var>$exactMatch</var> argument is used to toggle how the path is matched, set to <var>true</var> to
        #        only match the root folder, but not subfolders.  Examples of server paths include:
        #        <ul>
        #            <li class='code'>/System/Server</li>
        #            <li class='code'>/System/Framework</li>
        #            <li class='code'>/System/Documents</li>
        #            <li class='code'>/Library</li>
        #            <li class='code'>/Template/Pictures</li>
        #        </ul>
        #    </li>
        #    <li>
        #        <a href='/Hot Toddy/Documentation?hFile/hFilePath#getVirtualFileSystemPath'>getVirtualFileSystemPath($path)</a>
        #        can be used to convert a real path to a virtual one.
        #    </li>
        #    <li>
        #        <a href='/Hot Toddy/Documentation?hFile/hFilePath#getServerFileSystemPath'>getServerFileSystemPath($path)</a>
        #        can be used to convert a virtual path to a real one.
        #    </li>
        # </ul>
        # <h3>Querying a Category Path</h3>
        # <p>
        #    Category paths allow you to browse categories using the file system, and categories, of course, allow
        #    you to organize and tag files in one or many different groupings, without duplicating the file.
        #    For example, think of Yahoo's directory-based search engine of old where a URL would exist in one
        #    or more human-maintained categories.  Hot Toddy also provides tools for converting categories into
        #    paths and paths into categories:
        # </p>
        # <ul>
        #    <li>
        #        <a href='/Hot Toddy/Documentation?hFile/hFilePath#isCategoryPath'>isCategoryPath($path)</a> will tell
        #        you if a path is a category path.
        #    </li>
        #    <li>
        #        <a href='/Hot Toddy/Documentation?hFile/hFilePath#isHomeCategoryPath'>isHomeCategoryPath($path)</a> will
        #        tell you if a path is a category path originating from a user's home folder.
        #    </li>
        #    <li>
        #        <a href='/Hot Toddy/Documentation?hFile/hFilePath#getCategoryPath'>getCategoryPath($categoryId)</a>
        #        converts a supplied <var>$categoryId</var> into a category path.
        #    </li>
        #    <li>
        #        <a href='/Hot Toddy/Documentation?hFile/hFilePath#getCategoryIdFromPath'>getCategoryIdFromPath($path)</a>
        #        takes a path and gives you a <var>$categoryId</var>.
        #    </li>
        #    <li>
        #        <a href='/Hot Toddy/Documentation?hFile/hFilePath#categoryExists'>categoryExists($path)</a> tells you
        #        if the provided path is a category path, and the category exists.
        #    </li>
        # </ul>
        # @end

        $this->setPath($path);

        # In order to force the use of cached data, the cached data needs to
        # be purged or updated when the object changes.
        if (!$this->userAuthenticationWasDone)
        {
            $this
                ->setIsRootUser(
                    $this->inGroup('root')
                )
                ->setIsElevatedUser(
                    $this->isInElevated()
                )
                ->setIsWebsiteAdministrator(
                    $this->inGroup('Website Administrators')
                )
                ->setUserAuthenticationWasDone();
        }

        $exists = false;

        if ($this->isServerPath($path))
        {
            $this
                ->setIsServerPath()
                ->setServerPath(
                    $this->getServerFileSystemPath($path)
                )
                ->setIsDirectory(
                    is_dir($this->serverPath)
                )
                ->setIsCategory(false);

            $exists = file_exists(
                $this->getServerFileSystemPath($path)
            );

            if ($exists)
            {
                if ($this->pathIsFolder)
                {
                    $this->setDirectoryId(
                        file_exists($this->serverPath)?
                            str_replace('=', '', base64_encode($this->serverPath)).'s' : 0
                    )
                    ->setIsFile(false);
                }
                else
                {
                    $this->setDirectoryId(
                        file_exists(dirname($this->serverPath))?
                            str_replace('=', '', base64_encode(dirname($this->serverPath))).'s' : 0
                    )
                    ->setFileId(
                        file_exists($this->serverPath)?
                            str_replace('=', '', base64_encode($this->serverPath)).'s' : 0
                    )
                    ->setIsFile();
                }
            }
        }
        else if ($this->isCategoryPath($path))
        {
            $this->setIsDirectory()
                 ->setIsCategory()
                 ->setIsServerPath(false)
                 ->setIsFile(false);

            $exists = $this->categoryExists($path);

            if ($exists)
            {
                $this->setCategoryId(
                    $this->getCategoryIdFromPath($path)
                );
            }
        }
        else
        {
            $directoryId = (int) $this->hDirectories->selectColumn(
                'hDirectoryId',
                array(
                    'hDirectoryPath' => $path
                )
            );

            $this->setIsCategory(false)
                 ->setIsServerPath(false);

            if (!$directoryId)
            {
                $directoryId = (int) $this->getDirectoryId(dirname($path));

                if ($directoryId)
                {
                    $this
                        ->setDirectoryId($directoryId)
                        ->setParentDirectoryId(
                            $this->getDirectoryId(
                                dirname($path)
                            )
                        );

                    $fileId = (int) $this->hFiles->selectColumn(
                        'hFileId',
                        array(
                            'hDirectoryId' => $directoryId,
                            'hFileName' => basename($path)
                        )
                    );

                    if ($fileId)
                    {
                        $this->setIsFile()
                             ->setIsDirectory(false)
                             ->setFileId($fileId);

                        $exists = true;
                    }
                    else
                    {
                        $this->setIsDirectory()
                             ->setIsFile(false);
                    }
                }
            }
            else
            {
                $this
                    ->setIsDirectory()
                    ->setIsFile(false)
                    ->setDirectoryId($directoryId)
                    ->setParentDirectoryId(
                        $this->getDirectoryId(
                            dirname($path)
                        )
                    );

                $exists = true;
            }
        }

        $this
            ->setExists($exists)
            ->setBaseName(
                basename($path)
            )
            ->setParentDirectoryPath(
                dirname($path)
            );

        if (!$this->isDirectory && strstr($this->baseName, '.'))
        {
            $this->setExtension(
                $this->getExtension($this->baseName)
            );
        }

        return $this;
    }

    private function &setUserAuthenticationWasDone($userAuthenticationWasDone = true)
    {
        # @return hFileLibrary

        # @description
        # <h2>Setting User Authentication Variables</h2>
        # <p>
        #    The member property <var>userAuthenticationWasDone</var> is set so that certain
        #    constant and unchanging information about a user's authentication is retrieved and
        #    set only once.  That information is the following:
        # </p>
        # <ul>
        #    <li>Whether or not the user is in the <i>root</i> user group (aka Developers)</li>
        #    <li>Whether or not the user is in the <i>Website Administrators</i> user group</li>
        #    <li>Whether or not the user is in an "elevated" user group of any kind</li>
        # </ul>
        # <p>
        #    Once this information is retrieved and set, then <var>userAuthenticationWasDone</var> is
        #    set to <var>true</var> to prevent this information from being retrieved and set again.
        # </p>
        # @end

        $this->userAuthenticationWasDone = $userAuthenticationWasDone;
        return $this;
    }

    private function &setIsRootUser($isRootUser)
    {
        # @return hFileLibrary

        # @description
        # <h2>Caching Whether or Not the User is Root</h2>
        # <p>
        #    Whether or not the user is in the <i>root</i> (aka Developers) user group
        #    is retrieved and stored in the property <var>isRootUser</var>.
        # </p>
        # @end

        $this->isRootUser = $isRootUser;
        return $this;
    }

    private function &setIsElevatedUser($isElevatedUser)
    {
        # @return hFileLibrary

        # @description
        # <h2>Caching Whether or Not the User is Elevated</h2>
        # <p>
        #    Whether or not the user is in an <i>Elevated</i> user group
        #    is retrieved and stored in the property <var>isElevatedUser</var>.
        # </p>
        # @end

        $this->isElevatedUser = $isElevatedUser;
        return $this;
    }

    private function &setIsWebsiteAdministrator($isWebsiteAdministrator)
    {
        # @return hFileLibrary

        # @description
        # <h2>Caching Whether or Not the User is a Website Administrator</h2>
        # <p>
        #    Whether or not the user is in the <i>Website Administrators</i> user group
        #    is retrieved and stored in the property <var>isWebsiteAdministrator</var>.
        # </p>
        # @end

        $this->isWebsiteAdministrator = $isWebsiteAdministrator;
        return $this;
    }

    private function &setIsServerPath($isServerPath = true)
    {
        # @return hFileLibrary

        # @description
        # <h2>Caching Whether or Not the Path is a Server Path</h2>
        # <p>
        #    Whether or not the path is a server path
        #    is retrieved and stored in the properties <var>isServer</var> and
        #    <var>isServerPath</var>.
        # </p>
        # @end

        $this->isServer = $isServerPath;
        $this->isServerPath = $isServerPath;
        return $this;
    }

    private function &setServerPath($serverPath)
    {
        # @return hFileLibrary

        # @description
        # <h2>Caching the Server Path</h2>
        # <p>
        #    If a path is a server path, the server path
        #    is retrieved and stored in the property <var>serverPath</var>.
        # </p>
        # @end

        $this->serverPath = $serverPath;
        return $this;
    }

    private function &setIsCategory($isCategory = true)
    {
        # @return hFileLibrary

        # @description
        # <h2>Caching Whether or Not the Path is a Category Path</h2>
        # <p>
        #    Whether or not a path is a category path is retrieved and stored in
        #    the property <var>isCategory</var>.
        # </p>
        # @end

        $this->isCategory = $isCategory;
        return $this;
    }

    private function &setCategoryId($categoryId)
    {
        # @return hFileLibrary

        # @description
        # <h2>Caching the Category Id</h2>
        # <p>
        #    If a path is a category path then the associated category Id is retrieved
        #    and stored in the property <var>categoryId</var>.
        # </p>
        # @end

        $this->categoryId = $categoryId;
        return $this;
    }

    private function &setIsDirectory($isDirectory = true)
    {
        # @return hFileLibrary

        # @description
        # <h2>Caching Whether or Not the Path is a Directory (or Folder)</h2>
        # <p>
        #    Whether or not a path is a directory (or folder) is retrieved
        #    and stored in the properties <var>isDirectory</var> and <var>isDirectoryPath</var>.
        # </p>
        # @end

        $this->isDirectory = $isDirectory;
        $this->isDirectoryPath = $isDirectory;
        return $this;
    }

    private function &setDirectoryId($directoryId)
    {
        # @return hFileLibrary

        # @description
        # <h2>Caching the Directory Id</h2>
        # <p>
        #    If a path is a directory then the associated directory Id is retrieved
        #    and stored in the property <var>directoryId</var>.
        # </p>
        # @end

        $this->directoryId = $directoryId;
        return $this;
    }

    private function &setExists($exists)
    {
        # @return hFileLibrary

        # @description
        # <h2>Caching the Existence of a Path</h2>
        # <p>
        #    Whether or not a path exists is collected
        #    and stored in the property <var>exists</var>.
        # </p>
        # @end

        $this->exists = $exists;
        return $this;
    }

    private function &setParentDirectoryPath($parentDirectoryPath)
    {
        # @return hFileLibrary

        # @description
        # <h2>Caching the Parent Directory Path</h2>
        # <p>
        #    The parent directory path of a directory is retrieved and stored in the
        #    properties <var>parentDirectoryPath</var> and <var>directoryPath</var>.
        # </p>
        # @end

        $this->parentDirectoryPath = $parentDirectoryPath;
        $this->directoryPath = $parentDirectoryPath;
        return $this;
    }

    private function &setParentDirectoryId($parentDirectoryId)
    {
        # @return hFileLibrary

        # @description
        # <h2>Caching a Directory's Parent Id</h2>
        # <p>
        #    The parent directory path's id of a directory is retrieved and stored in the
        #    <var>parentDirectoryId</var> property.
        # </p>
        # @end

        $this->parentDirectoryId = (int) $parentDirectoryId;
        return $this;
    }

    private function &setExtension($extension)
    {
        # @return hFileLibrary

        # @description
        # <h2>Caching a File's Extension</h2>
        # <p>
        #    If the path references a file and the file has an extension, the extension
        #    is retrieved and stored in the properties <var>extension</var> and <var>fileExtension</var>.
        # </p>
        # @end

        $this->extension = $extension;
        $this->fileExtension  = $extension;
        return $this;
    }

    private function &setBaseName($fileName)
    {
        # @return hFileLibrary

        # @description
        # <h2>Caching the File Name</h2>
        # <p>
        #    If the path references a file, the file's name is retrieved and stored in
        #    the properties <var>fileName</var> and <var>baseName</var>.
        # </p>
        # @end

        $this->fileName = $fileName;
        $this->baseName = $fileName;
        return $this;
    }

    private function &setIsFile($isFile = true)
    {
        # @return hFileLibrary

        # @description
        # <h2>Caching Whether or Not the Path is a File</h2>
        # <p>
        #    Whether or not a path is a file is retrieved
        #    and stored in the properties <var>isFile</var> and <var>isFilePath</var>.
        # </p>
        # @end

        $this->isFile = $isFile;
        $this->isFilePath = $isFile;
        return $this;
    }

    private function &setFileId($fileId)
    {
        # @return hFileLibrary

        # @description
        # <h2>Caching the File Id</h2>
        # <p>
        #    If the path references a file, the file's Id is retrieved and stored in
        #    the property <var>fileId</var>.
        # </p>
        # @end

        $this->fileId = $fileId;
        return $this;
    }

    private function &setUserIsReadAuthorized($userIsReadAuthorized = true)
    {
        # @return hFileLibrary

        # @description
        # <h2>Caching Whether or Not the User is Read-Authorized to Access the Path</h2>
        # <p>
        #    Whether or not the user is read-authorized to access the path is
        #    retrieved and stored in the property <var>userIsReadAuthorized</var>
        # </p>
        # @end

        $this->userIsReadAuthorized = $userIsReadAuthorized;
        return $this;
    }

    private function &setUserIsWriteAuthorized($userIsWriteAuthorized = true)
    {
        # @return hFileLibrary

        # @description
        # <h2>Caching Whether or Not the User is Write-Authorized to Access the Path</h2>
        # <p>
        #    Whether or not the user is write-authorized to access the path is
        #    retrieved and stored in the property <var>userIsWriteAuthorized</var>
        # </p>
        # @end

        $this->userIsWriteAuthorized  = $userIsWriteAuthorized;
        return $this;
    }

    public function &setUserAuthentication()
    {
        # @return hFileLibrary

        # @description
        # <h2>Setting a User's Authorization for a Path</h2>
        # <p>
        #    Sets read / write information about the current user for the specified path, this information
        #    can be accessed in the <var>userIsReadAuthorized</var> and <var>userIsWriteAuthorized</var> properties,
        #    after querying the path and calling this method.
        # </p>
        # @end

        if ($this->exists)
        {
            if ($this->isServerPath)
            {
                if ($this->isDocumentRootPath($this->serverPath))
                {
                    $this->setUserIsReadAuthorized()->setUserIsWriteAuthorized(false);
                }
                else
                {
                    $this
                        ->setUserIsReadAuthorized($this->isRootUser)
                        ->setUserIsWriteAuthorized($this->isRootUser);
                }
            }
            else if ($this->isCategory)
            {
                $this
                    ->setUserIsReadAuthorized(
                        $this->hCategories->hasReadPermission($this->categoryId)
                    )
                    ->setUserIsWriteAuthorized(
                        $this->hCategories->hasWritePermission($this->categoryId)
                    );
            }
            else
            {
                if ($this->isDirectory)
                {
                    $this
                        ->setUserIsReadAuthorized(
                            $this->hDirectories->hasReadPermission($this->directoryId)
                        )
                        ->setUserIsWriteAuthorized(
                            $this->hDirectories->hasWritePermission($this->directoryId)
                        );
                }
                else
                {
                    $this
                        ->setUserIsReadAuthorized(
                            $this->hFiles->hasReadPermission($this->fileId)
                        )
                        ->setUserIsWriteAuthorized(
                            $this->hFiles->hasWritePermission($this->fileId)
                        );
                }
            }
        }

        return $this;
    }

    public function exists($path = nil)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Path Exists</h2>
        # <p>
        #    Determines if the supplied <var>$path</var> exists in the Hot Toddy
        #    File System.
        # </p>
        # @end

        if (empty($path))
        {
            $path = $this->filePath;
        }
        else
        {
            $presentPath = $this->filePath;
        }

        $this->query($path, true);
        $exists = $this->exists;

        if (isset($presentPath) && !empty($presentPath))
        {
            $this->filePath = $presentPath;
        }

        return $exists;
    }

    public function hFileExists($path = nil)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Path Exists</h2>
        # <p>
        #    Determines if the provided <var>$path</var> exists.
        #    Deprecated. Use <a href='#exists' class='code'>exists()</a> instead.
        # </p>
        # @end

        return $this->exists($path);
    }

    public function __set($key, $value)
    {
        # @return void

        # @description
        # <h2>Setting Overloaded Properties</h2>
        # <p>
        #    This method sets overloaded file properties such as <var>fileId</var>,
        #    <var>isFile</var>, and so on.
        # </p>
        # @end

        if (in_array($key, $this->variables))
        {
            if (!isset($this->file[$this->filePath]))
            {
                $this->file[$this->filePath] = array();
            }

            if (!isset($this->file[$this->filePath]['filePath']))
            {
                $this->file[$this->filePath]['filePath'] = $this->filePath;
            }

            $this->file[$this->filePath][$key] = $value;
        }
        else
        {
            parent::__set($key, $value);
        }
    }

    public function &__get($key)
    {
        # @return mixed

        # @description
        # <h2>Retrieving Overloaded Properties</h2>
        # <p>
        #    This method returns overloaded file properties such as <var>fileId</var>,
        #    <var>isFile</var>, and so on.
        # </p>
        # @end

        if (in_array($key, $this->variables))
        {
            if (strstr($key, 'hUser') || stristr($key, 'user'))
            {
                $this->setUserAuthentication();
            }

            $rtn = '';

            if (isset($this->file[$this->filePath][$key]))
            {
                $rtn = $this->file[$this->filePath][$key];
            }

            return $rtn;
        }
        else
        {
            return parent::__get($key);
        }
    }

    public function getPathVariables()
    {
        # @return array

        # @description
        # <h2>Getting Cached Properties</h2>
        # <p>
        #    Returns cached properties for the active file path.
        # </p>
        # @end

        return $this->file[$this->filePath];
    }

    public function getDirectory($path, $checkPermissions = true)
    {
        # @return array

        # @description
        # <h2>Getting a Directory's Files</h2>
        # <p>
        #    This method provides a slightly simplified procedure for getting a directory's
        #    files (from HtFS).  This method gets a directory's files without also getting a lot of
        #    extra meta data, optimizing for speed.  This is the ideal method to
        #    use when getting a directory's files for a web page
        #    (for navigation, or a side box, for example).
        # </p>
        # @end

        if (empty($this->hFileDatabase))
        {
            $this->hFileDatabase = $this->library('hFile/hFileInterface/hFileInterfaceDatabase');
        }

        $directoryId = $this->getDirectoryId($path);

        if ($directoryId > 0)
        {
            $this->setPath($path);
            $this->setIsDirectory();
            $this->setDirectoryId($directoryId);

            return $this->hFileDatabase->getFiles(
                false,
                $checkPermissions
            );
        }
        else
        {
            $this->warning(
                "Directory '{$path}' does not exist.",
                __FILE__,
                __LINE__
            );
        }

        return array();
    }

    public function __call($method, $arguments)
    {
        # @return mixed

        # @description
        # <h2>Overloaded Method Calls</h2>
        # <p>
        #    This method provides a common API for overloaded methods.  In addition, it
        #    provides a gateway for file system interfaces.  This is done by analyzing the
        #    path, then once it is determined where the path resides, if necessary, the
        #    method call is forwarded to the interface for that path.  Paths that reside in
        #    Hot Toddy's database-driven file system (HtFS) are forwarded to the database
        #    interface:
        #    <a href='/Hot Toddy/Documentation?hFile/hFileInterface/hFileInterfaceDatabase/hFileInterfaceDatabase.library.php'>hFileInterfaceDatabaseLibrary</a>.
        #    Methods specific to category paths are forwarded to the category interface:
        #    <a href='/Hot Toddy/Documentation?hFile/hFileInterface/hFileInterfaceCategory/hFileInterfaceCategory.library.php'>hFileInterfaceCategoryLibrary</a>.
        #    Methods specific to the Unix file system (Mac OS X, specifically) are forwarded to
        #    the Unix interface:
        #    <a href='/Hot Toddy/Documentation?hFile/hFileInterface/hFileInterfaceUnix/hFileInterfaceUnix.library.php'>hFileInterfaceUnixLibrary</a>.
        # </p>
        # <p>
        #    Methods required in each interface are defined in:
        #    <a href='/Hot Toddy/Documentation?hFile/hFileInterface'>hFileInterface</a>.
        # </p>

        $this->counter++;

        if (empty($this->hFileInterfacePlugins) || !is_array($this->hFileInterfacePlugins))
        {
            $this->setInterfacePlugins();
        }

        foreach ($this->hFileInterfacePlugins as $i => $plugin)
        {
            if (method_exists($plugin, 'getMethods'))
            {
                if (!$plugin->methodsWereAdded)
                {
                    $plugin->methodsWereAdded = true;

                    $pluginMethods = $plugin->getMethods();

                    foreach ($pluginMethods as $pluginMethod)
                    {
                        if (!in_array($pluginMethod, $this->methods))
                        {
                            array_push(
                                $this->methods,
                                $pluginMethod
                            );
                        }
                    }
                }
            }
            else
            {
                $this->warning(
                    "Unable to use file interface '".get_class($plugin)."', the object does not have a ".
                    "method called getMethods() which supplies the names of all methods defined within it.  ",
                    __FILE__,
                    __LINE__
                );

                unset($this->hFileInterfacePlugins[$i]);
            }
        }

        if (!in_array($method, $this->methods, true))
        {
            return parent::__call($method, $arguments);
        }
        else
        {
            $pathReceptical = $this->filePath;

            if (empty($arguments[0]))
            {
                // I might modify this to default to the current path,
                // though this is dangerous if the method in question takes
                // multiple arguments.  Best to just supply the path.
                $arguments[0] = $this->filePath;
            }

            $this->filePath = $arguments[0];

            if (!empty($this->filePath))
            {
                $this->query($this->filePath);

                switch ($method)
                {
                    case 'getServerPath':
                    {
                        # <div id='getServerPath'>
                        #     <h3>Getting the Server Path</h3>
                        #     <code>string public function getServerPath($path = nil)</code>
                        #     <p>
                        #        Returns the <var>serverPath</var>
                        #     </p>
                        # </div>

                        $rtn = $this->serverPath;
                        break;
                    }
                    case 'getName':
                    {
                        # <div id='getName'>
                        #     <h3>Getting the File Name</h3>
                        #     <code>string public function getName($path = nil)</code>
                        #     <p>
                        #        Returns the <var>fileName</var>
                        #     </p>
                        # </div>

                        $rtn = $this->fileName;
                        break;
                    }
                    case 'isRootServerDirectory':
                    {
                        # <div id='isRootServerDirectory'>
                        #     <h3>Determining if the Path is a Root Server Directory</h3>
                        #     <code>string public function isRootServerDirectory($path = nil)</code>
                        #     <p>
                        #        Determines if the path is a root server path, i.e., it is one of
                        #        the following:
                        #     </p>
                        #     <ul>
                        #        <li class='code'>/System/Server</li>
                        #        <li class='code'>/System/Documents</li>
                        #        <li class='code'>/System/Framework</li>
                        #        <li class='code'>/Template/Pictures</li>
                        #        <li class='code'>/System/Server</li>
                        #     </ul>
                        # </div>

                        switch ($this->filePath)
                        {
                            case '/System/Server':
                            case '/System/Documents':
                            case '/System/Framework':
                            case '/Template/Pictures':
                            case $this->hFrameworkLibraryRoot:
                            {
                                $rtn = true;
                                break;
                            }
                            default:
                            {
                                $rtn = false;
                                break;
                            }
                        }

                        break;
                    }
                    case 'getAllDirectories':
                    {
                        # <div id='getAllDirectories'>
                        #     <h3>Getting a Unified List of Directories</h3>
                        #     <code>string public function getAllDirectories($path = nil)</code>
                        #     <p>
                        #        Returns a view of the directory where the list returned is a merged view of
                        #        the database file system and the real Unix file system, where applicable.
                        #     </p>
                        # </div>

                        if (!$this->isServer)
                        {
                            $directories = $this->getDirectories($this->filePath);

                            if ($this->hFileMergeFileSystems(false)  && ($this->isRootUser || $this->hFileDisablePermissionsCheck(false) || $this->isFrameworkRootPath($this->serverPath)))
                            {
                                $this->mergeFileSystemObjects(
                                    '/System/Framework'.$this->filePath,
                                    $directories,
                                    'getDirectories'
                                );
                            }
                        }
                        else if ($this->isRootUser || $this->isDocumentRootPath($this->serverPath))
                        {
                            $directories = $this->getDirectories($this->filePath);
                        }

                        if (is_array($directories))
                        {
                            ksort($directories, SORT_STRING);
                        }

                        $rtn = $directories;
                        break;
                    }
                    case 'getAllDirectoriesInPath':
                    {
                        # <div id='getAllDirectoriesInPath'>
                        #     <h3>Getting All Directories in the Path</h3>
                        #     <code>string public function getAllDirectoriesInPath($path = nil)</code>
                        #     <p>
                        #
                        #     </p>
                        # </div>

                        $path = ($this->filePath === '/'? '' : $this->filePath);

                         // Query the directory, and it's chillens
                        $query = $this->hDirectories->select(
                            array(
                                'hDirectoryId',
                                'hDirectoryPath'
                            ),
                            array(
                                'hDirectoryPath' => array(
                                    array('=', $path),
                                    array('LIKE', $path.'/%')
                                )
                            ),
                            'OR',
                            array(
                                'DESC',
                                'hDirectoryPath'
                            )
                        );

                        $directories = array();

                        foreach ($query as $data)
                        {
                            $name = basename($data['hDirectoryPath']);

                            $directories[$data['hDirectoryPath']] = array(
                                'hFileName'     => $name,
                                'hFilePath'     => $data['hDirectoryPath'],
                                'hFileIsServer' => false,
                                'hDirectoryId'  => $data['hDirectoryId']
                            );
                        }

                        $rtn = $directories;
                        break;
                    }
                    case 'getFileProperties':
                    {
                        # <div id='getFileProperties'>
                        #    <h3>Getting File Properties</h3>
                        #    <code>array public function getFileProperties($path = nil)</code>
                        #    <p>
                        #        Returns an array of file properties in the context of the provided <var>$path</var>,
                        #        or if the <var>$path</var> is not provided, the method is carried out within the
                        #        context of the path last assigned to <a href='#filePath'>filePath</a>
                        #    </p>
                        #    <h4>Returned Properties</h4>
                        #    <table>
                        #        <thead>
                        #        </thead>
                        #        <tbody>
                        #            <tr>
                        #                <td class='code'>hFileId</td>
                        #            </tr>
                        #            <tr>
                        #                <td class='code'>hFileName</td>
                        #            </tr>
                        #            <tr>
                        #                <td class='code'>hFilePath</td>
                        #            </tr>
                        #            <tr>
                        #                <td class='code'>hFileIsServer</td>
                        #            </tr>
                        #            <tr>
                        #                <td class='code'>hDirectoryId</td>
                        #            </tr>
                        #            <tr>
                        #                <td class='code'>hFileSize</td>
                        #            </tr>
                        #            <tr>
                        #                <td class='code'>hFileCreated</td>
                        #            </tr>
                        #            <tr>
                        #                <td class='code'>hFileLastModified</td>
                        #            </tr>
                        #            <tr>
                        #                <td class='code'>hFileDescription</td>
                        #            </tr>
                        #            <tr>
                        #                <td class='code'>hFileTitle</td>
                        #            </tr>
                        #            <tr>
                        #                <td class='code'>hFileMIME</td>
                        #            </tr>
                        #            <tr>
                        #                <td class='code'>hCategoryFileSortIndex</td>
                        #            </tr>
                        #        </tbody>
                        #    </table>
                        # </div>

                        $rtn = array(
                            'hFileId'                => $this->fileId,
                            'hFileName'              => $this->fileName,
                            'hFilePath'              => $this->filePath,
                            'hFileIsServer'          => true,
                            'hDirectoryId'           => $this->directoryId,
                            'hFileSize'              => $this->getSize(),
                            'hFileCreated'           => $this->getCreated(),
                            'hFileLastModified'      => $this->getLastModified(),
                            'hFileDescription'       => $this->getDescription(),
                            'hFileTitle'             => $this->getTitle(),
                            'hFileMIME'              => $this->getMIMEType(),
                            'hCategoryFileSortIndex' => 0
                        );

                        break;
                    }
                    case 'getFile':
                    {
                        # <div id='getFile'>
                        #     <h3>Getting a File's Contents</h3>
                        #     <code>string public function getFile($path = nil)</code>
                        #     <p>
                        #         Returns a file's contents (the body of the file).
                        #     </p>
                        # </div>

                        if ($this->isServer)
                        {
                            // Using this method of retrieval, limit the file size  to 2MB
                            if (filesize($this->serverPath) <= $this->hFileSizeLimit(2097152))
                            {
                                $rtn = file_get_contents($this->serverPath);
                            }
                            else
                            {
                                // Get all fields associated with a document, including the document itself.

                            }
                        }

                        break;
                    }
                    case 'getAllFiles':
                    {
                        # <div id='getAllFiles'>
                        #     <h3>Getting a Unified List of Files</h3>
                        #     <code>array public function getAllFiles($path = nil)</code>
                        #     <p>
                        #        Returns a view of the files where the list returned is a merged view of
                        #        the database file system and the real Unix file system, where applicable.
                        #     </p>
                        # </div>

                        if (!$this->isServer)
                        {
                            $files = $this->getFiles($this->filePath);

                            if ($this->hFileMergeFileSystems(false)  && ($this->isRootUser || $this->isFrameworkRootPath($this->serverPath)))
                            {
                                $this->mergeFileSystemObjects('/System/Framework'.$this->filePath, $files, 'getFiles');
                            }
                        }
                        else if ($this->isRootUser || $this->isFrameworkRootPath($this->serverPath))
                        {
                            $files = $this->getFiles($this->filePath);
                        }
                        else if ($this->isRootUser || $this->isDocumentRootPath($this->serverPath))
                        {
                            $files = $this->getFiles($this->filePath);
                        }

                        if (is_array($files) && !$this->hFileOrderBy)
                        {
                            ksort($files);
                        }

                        $rtn = $files;
                        break;
                    }
                    case 'newSymbolicLink':
                    {
                        # <div id='newSymbolicLink'>
                        #    <h3>Creating a Symbolic Link</h3>
                        #    <code>integer public function newSymbolicLink($path = nil)</code>
                        #    <p>
                        #        This method is presently not used.
                        #    </p>
                        # </div>

                        if ($this->exists)
                        {
                            if ($this->isServer)
                            {
                                $rtn = 1;
                            }
                            else
                            {
                                if ($this->isDirectory)
                                {
                                    $rtn = 1;
                                }
                                else
                                {
                                    $fileId = $this->hDatabase->insert(
                                        array(
                                            'hFileId' => nil,
                                            'hDirectoryId' => $this->directoryId,
                                            0,
                                            $arguments[2],
                                            time(),
                                            time()
                                        ),
                                        'hFiles'
                                    );

                                    $this->hFileSymbolicLinkTo($arguments[1], $fileId, true);

                                    $rtn = $fileId;
                                }
                            }
                        }
                        else
                        {
                            $rtn = -3;
                        }

                        $rtn = 0;
                        break;
                    }
                    default:
                    {
                        unset($arguments[0]);

                        foreach ($this->hFileInterfacePlugins as $plugin)
                        {
                            if (method_exists($plugin, 'shouldBeCalled'))
                            {
                                if ($plugin->shouldBeCalled())
                                {
                                    $plugin->filterPaths = $this->filterPaths;
                                    $plugin->fileTypes   = $this->fileTypes;

                                    if (method_exists($plugin, $method))
                                    {
                                        $rtn = call_user_func_array(
                                            array(
                                                $plugin,
                                                $method
                                            ),
                                            $arguments
                                        );
                                    }
                                }
                            }
                        }
                    }
                }

                $this->filePath = $pathReceptical;

                return isset($rtn)? $rtn : nil;
            }
            else
            {
                $this->warning('The path is empty.', __FILE__, __LINE__);
            }
        }
    }

    public function &setInterfacePlugins()
    {
        # @return hFileLibrary

        # @description
        # <h2>Setting Interface Plugins</h2>
        # <p>
        #    Interface plugins provide you with the freedom and the flexibility to
        #    extend Hot Toddy's file system in whatever way you see fit.  Default
        #    interface plugins provide file system access to the Hot Toddy File System
        #    (the Database interface), the Unix file system (on Mac OS X Server), and
        #    to Hot Toddy Categories, which allow you to organize and tag files in one or
        #    many categories.
        # </p>
        # @end

        $this->hFileInterfacePlugins = array(
            $this->library('hFile/hFileInterface/hFileInterfaceUnix'),
            $this->library('hFile/hFileInterface/hFileInterfaceCategory'),
            $this->library('hFile/hFileInterface/hFileInterfaceDatabase')
        );

        return $this;
    }

    public function &setFilterPaths(array $paths)
    {
        # @return hFileLibrary

        # @description
        # <h2>Filtering Allowed Paths</h2>
        # <p>
        #    This method allows you to pass an array of paths of files and folders your
        #    users are not allowed to see. Any path that appears in the provided array
        #    will be hidden from your users.
        # </p>
        # @end

        $this->filterPaths = $paths;
        return $this;
    }

    public function &setFileTypes(array $fileTypes)
    {
        # @return hFileLibrary

        # @description
        # <h2>Filtering Allowed File Types</h2>
        # <p>
        #    This method allows you to pass an array of file extensions, MIME types, or both
        #    of file types that your users are allowed to interact with. Any extension or MIME type
        #    not in the array are hidden from your users.
        # </p>
        # @end

        $this->fileTypes = $fileTypes;
        return $this;
    }

    public function &setPermissionsMethodToEverything()
    {
        # @return hFileLibrary

        # @description
        # <h2>Setting Permissions to <i>Everything</i></h2>
        # <p>
        #    This method sets the framework variable <var>hFilePermissionsMethod</var> to
        #    <var>'Everything'</var>, which causes calls to <var>getFiles()</var> and <var>getDirectories()</var>
        #    to examine all applicable permissions to determine if the user has permission to access the
        #    relevant resources.
        # </p>
        # @end

        $this->hFilePermissionsMethod = 'Everything';
        return $this;
    }

    public function &setPermissionsMethodToWorld()
    {
        # @return hFileLibrary

        # @description
        # <h2>Setting Permissions to <i>World</i></h2>
        # <p>
        #    This method sets the framework variable <var>hFilePermissionsMethod</var> to
        #    <var>'World'</var>, which causes calls to <var>getFiles()</var> and <var>getDirectories()</var>
        #    to examine only the World-Read permission to determine if the user has permission to access the
        #    relevant resources.  <b>Owner, User, and Group permissions are ignored</b>.
        # </p>
        # @end

        $this->hFilePermissionsMethod = 'World';
        return $this;
    }

    public function &sortByCreated($desc = false)
    {
        # @return hFileLibrary

        # @description
        # <h2>Sorting Files By Created Timestamp</h2>
        # <p>
        #    Files retrieved using the <var>getFiles()</var> method can be sorted by the created timestamp
        #    by calling this method.  This method simply sets the framework variables <var>hFileOrderBy</var>
        #    (to <var>'hFileCreated'</var>) and <var>hFileOrderByDirection</var> (to <var>'ASC'</var> or if
        #    <var>$desc</var> is <var>true</var>, then <var>'DESC'</var>).
        # </p>

        $this->hFileOrderBy = 'hFileCreated';
        $this->hFileOrderByDirection = $desc? 'DESC' : 'ASC';
        return $this;
    }

    public function &sortByLastModified($desc = false)
    {
        # @return hFileLibrary

        # @description
        # <h2>Sorting Files By Last Modified Timestamp</h2>
        # <p>
        #    Files retrieved using the <var>getFiles()</var> method can be sorted by the last modified timestamp
        #    (only files that have been modified have a modified timestamp, files never modified have a value of <var>0</var>)
        #    by calling this method. This method simply sets the framework variables <var>hFileOrderBy</var>
        #    (to <var>'hFileLastModified'</var>) and <var>hFileOrderByDirection</var> (to <var>'ASC'</var> or if
        #    <var>$desc</var> is <var>true</var>, then <var>'DESC'</var>).
        # </p>

        $this->hFileOrderBy = 'hFileLastModified';
        $this->hFileOrderByDirection = $desc? 'DESC' : 'ASC';
        return $this;
    }

    public function &sortByIndex($desc = false)
    {
        # @return hFileLibrary

        # @description
        # <h2>Sorting Files By Index</h2>
        # <p>
        #    Files retrieved using the <var>getFiles()</var> method can be sorted by index (a field that
        #    exists explictly for custom sorting)
        #    by calling this method. This method simply sets the framework variables <var>hFileOrderBy</var>
        #    (to <var>'hFileSortIndex'</var>) and <var>hFileOrderByDirection</var> (to <var>'ASC'</var> or if
        #    <var>$desc</var> is <var>true</var>, then <var>'DESC'</var>).
        # </p>
        # @end

        $this->hFileOrderBy = 'hFileSortIndex';
        $this->hFileOrderByDirection = $desc? 'DESC' : 'ASC';
        return $this;
    }

    public function &sortByFileName($desc = false)
    {
        # @return hFileLibrary

        # @description
        # <h2>Sorting Files By File Name</h2>
        # <p>
        #    Files retrieved using the <var>getFiles()</var> method can be sorted by file name
        #    by calling this method. This method simply sets the framework variables <var>hFileOrderBy</var>
        #    (to <var>'hFileName'</var>) and <var>hFileOrderByDirection</var> (to <var>'ASC'</var> or if
        #    <var>$desc</var> is <var>true</var>, then <var>'DESC'</var>).
        # </p>
        # @end

        $this->hFileOrderBy = 'hFileName';
        $this->hFileOrderByDirection = $desc? 'DESC' : 'ASC';
        return $this;
    }

    public function &sortByRandom()
    {
        # @return hFileLibrary

        # @description
        # <h2>Sorting Files Randomly</h2>
        # <p>
        #    Files retrieved using the <var>getFiles()</var> method can be sorted randomly
        #    by calling this method. This method simply sets the framework variable <var>hFileOrderBy</var>
        #    to <var>RAND()</var>.
        # </p>
        # @end

        $this->hFileOrderBy = 'RAND()';
        return $this;
    }

    public function &setLimit($limit)
    {
        # @return hFileLibrary

        # @description
        # <h2>Setting the LIMIT Clause for File Queries</h2>
        # <p>
        #    Retrieving files using the <var>getFiles()</var> method can be limited using the
        #    SQL <var>LIMIT</var> clause that you pass to this method.
        # </p>
        # <p>
        #    This method simply sets the framework variable <var>hFileLimit</var>.
        # </p>
        # @end

        $this->hFileLimit($limit);

        return $this;
    }

    public function &mergeFileSystemObjects($path, &$objects, $method)
    {
        # @return hFileLibrary

        # @description
        # <h2>Merging File Systems</h2>
        # <p>
        #    This method is used to merge files that originate from different file
        #    system sources.  This is used to present a unified file system view.
        #    For example, the root folder of Hot Toddy originates from many different
        #    sources.  Files that compose the final view of the root file system
        #    come from the following locations:
        # </p>
        # <ul>
        #   <li>
        #       The root HtFS folder <var>/</var>, this exists in the database-driven HtFS
        #   </li>
        #   <li>
        #       The root website folder for the <var>hFrameworkSite</var>, for
        #       example this might be <var>/www.example.com</var>, this exists in the
        #       database-driven HtFS
        #   </li>
        #   <li>
        #       The Hot Toddy folder, located at <var>{hFrameworkPath}/Hot Toddy</var>
        #   </li>
        #   <li>
        #       The Plugins folder, located at <var>{hFrameworkPath}/Plugins</var>
        #   </li>
        #   <li>
        #       The <var>DOCUMENT_ROOT</var> folder, located at <var>{hFrameworkPath}/www
        #   </li>
        #   <li>
        #       Aliases created in the <var>hFileAliases</var> database table
        #   </li>
        #   <li>
        #       Wildcard paths that exist in the <var>hFilePathWildcards</var> database table
        #   </li>
        # </ul>
        # <p>
        #    As you can see, in Hot Toddy, certain folders can originate from multiple
        #    sources.  If a file exists in multiple locations, there is an order of
        #    precedence that determines which file wins out over the one or more potential
        #    duplicates that can exist in other locations.
        # </p>
        # <p>
        #    Despite the possibility of conflicts, in reality, conflicts are made very rare
        #    because of framework programming standards:
        # </p>
        # <ol>
        #    <li>
        #       Files should not be uploaded directly to <var>DOCUMENT_ROOT</var>
        #       (<var>{hFrameworkPath}/www</var>), instead the Finder or another Framework-aware
        #       file management tool should be used to upload the file to HtFS
        #   </li>
        #   <li>
        #       Files located in <var>{hFrameworkPath}/Hot Toddy</var> or
        #       <var>{hFrameworkPath}/Plugins</var> should only be kept in the namespaced subfolders
        #       (with few exceptions). The namesspaced subfolders ensure that files existing in
        #       multiple locations remains an unlikely possibility.
        #   </li>
        # </ol>
        # <p>
        #    Where it conserns <var>hFileAliases</var> and <var>hFileWildcardPaths</var>, Hot Toddy
        #    will evolve eventually to expose the pseudo-files stored in these locations to the
        #    framework's file system, and thus prevent duplicates from immerging.
        # </p>
        # <p>
        #    Given this overview, <var>mergeFileSystemObjects()</var> can be used to merge files
        #    originating from different sources, typically, the real server file system and the
        #    virtual Hot Toddy database-driven file system.
        # </p>
        # @end

        $objects = array_merge($this->$method($path), $objects);

        return $this;
    }

    public function duplicateFileExists($md5Checksum)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Duplicate File Exists</h2>
        # <p>
        #    Takes the provided <var>$md5Checksum</var> string and determines
        #    if a file already exists in the Hot Toddy File System with that MD5
        #    checksum.
        # </p>
        # @end

        if (empty($md5Checksum))
        {
            return false;
        }

        return $this->hFileProperties->selectExists(
            'hFileId',
            array(
                'hFileMD5Checksum' => $md5Checksum
            )
        );
    }

    public function getDuplicatePath($md5Checksum)
    {
        # @return string

        # @description
        # <h2>Getting a Duplicate Path</h2>
        # <p>
        #   When a duplicate import/upload is detected, this is done using that
        #   file's MD5 checksum string.  After a duplicate is detected, the path
        #   to the duplicate file can be returned by providing the
        #   <var>$md5Checksum</var> to this method.
        # </p>
        # @end

        return $this->getFilePathByFileId(
            $this->hFileProperties->selectColumn(
                'hFileId',
                array(
                    'hFileMD5Checksum' => $md5Checksum
                )
            )
        );
    }

    public function getFileId($directoryId, $fileName)
    {
        # @return integer

        # @description
        # <h2>Getting a FileId For a File Name and Directory Id</h2>
        # <p>
        #   Returns the <var>fileId</var> for the provided <var>$directoryId</var> and
        #   <var>$fileName</var>
        # </p>
        # @end

        return $this->hFiles->selectColumn(
            'hFileId',
            array(
                'hFileName' => $fileName,
                'hDirectoryId' => (int) $directoryId
            )
        );
    }

    public function &makeServerPath($path)
    {
        # @return hFileLibrary

        # @description
        # <h2>Creating a Server Path</h2>
        # <p>
        #   Walks through the supplied path and creates every folder in the path
        #   starting from the root folder.
        # </p>
        # @end

        $this->console("Making server path: '{$path}'");

        if (!file_exists($path))
        {
            $directories  = explode('/', $path);
            $currentPath = '';

            for ($i = 0, $d = count($directories); $i < $d; $i++)
            {
                if (empty($directories[$i]))
                {
                    $currentPath = '/';
                    continue;
                }
                else
                {
                    if (substr($currentPath, -1, 1) != '/')
                    {
                        $currentPath .= '/';
                    }

                    $currentPath .= $directories[$i];
                }

                if (!empty($openBaseDir) && $this->beginsPath($openBaseDir, $currentPath) && $openBaseDir != $currentPath)
                {
                    continue;
                }

                if (!file_exists($currentPath))
                {
                    $this->mkdir($currentPath);
                }
            }
        }

        return $this;
    }

    public function getDirectoryPseudoMIME($path, $fileIconId = 0)
    {
        # @return string

        # @description
        # <h2>Getting a Directory's Pseudo-MIME String</h2>
        # <p>
        #   This method is deprecated. Its need is superceded by custom directory icon
        #   support in <var>hDirectoryProperties</var>. To assign a directory a custom
        #   icon, you must create a record in the <var>hDirectoryProperties</var> table
        #   with the correct hDirectoryId and a corresponding <var>hFileIconId</var>
        #   from the icon record in the <var>hFileIcons</var> table.
        # </p>
        # <h3>Directory Pseudo-MIME Types</h3>
        # <table>
        #    <tbody>
        #        <tr>
        #            <td class='code'>directory</td>
        #            <td>Regular, generic folder</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>directory/applications</td>
        #            <td>The <var>/Applications</var> folder</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>directory/categories</td>
        #            <td>
        #               The <var>/Categories</var> folder or the
        #               <var>/Users/{/userName}/Categories</var> folder
        #           </td>
        #        </tr>
        #        <tr>
        #            <td class='code'>directory/documents</td>
        #            <td>
        #               <var>/Documents</var>,
        #               <var>/www.example.com/Documents</var>, or
        #               <var>/Users/{/userName}/Documents</var> folders
        #           </td>
        #        </tr>
        #        <tr>
        #            <td class='code'>directory/home</td>
        #            <td>The <var>/Users/{/userName}</var> folder</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>directory/images</td>
        #            <td>Not used</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>directory/library</td>
        #            <td>
        #               The <var>/Library</var> or
        #               <var>/Users/{/userName}/Library</var> folders
        #           </td>
        #        </tr>
        #        <tr>
        #            <td class='code'>directory/media</td>
        #            <td>Not used.</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>directory/movies</td>
        #            <td>
        #               The <var>/Movies</var>,
        #               <var>/www.example.com/Movies</var> or
        #               <var>/Users/{/userName}/Movies</var> folders
        #           </td>
        #        </tr>
        #        <tr>
        #            <td class='code'>directory/music</td>
        #            <td>
        #               The <var>/Music</var>,
        #               <var>/www.example.com/Music</var> or
        #               <var>/Users/{/userName}/Music</var> folders
        #           </td>
        #        </tr>
        #        <tr>
        #            <td class='code'>directory/network</td>
        #            <td>Not used.</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>directory/pictures</td>
        #            <td>
        #               The <var>/Pictures</var>,
        #               <var>/www.example.com/Pictures</var> or
        #               <var>/Users/{/userName}/Pictures</var> folders
        #           </td>
        #        </tr>
        #        <tr>
        #            <td class='code'>directory/plugins</td>
        #            <td>Not used.</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>directory/private</td>
        #            <td>Not used.</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>directory/products</td>
        #            <td>Not used.</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>directory/sharepoint</td>
        #            <td>Not used.</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>directory/sites</td>
        #            <td>The <var>/www.example.com</var> folder</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>directory/system</td>
        #            <td>The <var>/System</var> folder</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>directory/users</td>
        #            <td>The <var>/Users</var> folder</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>directory/volumes</td>
        #            <td>The <var>/Volumes</var> folder</td>
        #        </tr>
        #    </tbody>
        # </table>
        # @end

        if (!empty($fileIconId))
        {
            return $this->hFileIcons->selectColumn('hFileMIME', $fileIconId);
        }

        return 'directory';
    }

    public function inFileSystem($fileId)
    {
        # @return boolean

        # @description
        # <h2>Determining if a File is Stored in the HtFS Folder</h2>
        # <p>
        #    Returns whether or not a physical file exists in the <var>HtFS</var> folder,
        #    if no physical file exists, then the file is purely a virtual file existing
        #    only in the Hot Toddy File System.
        # </p>
        # @end

        return file_exists($this->hFrameworkFileSystemPath.$this->getFilePathByFileId($fileId));
    }

    public function listenerValidation($methods, $method)
    {
        # @return integer

        # @description
        # <h2>Performing Listener Validation</h2>
        # <p>
        #    This method performs validation, and implements some patterns in validation for
        #    data supplied to AJAX (listener) requests.  It ensures that desired variables
        #    are set and propulated.  It ensures that the user has authentication to read or write
        #    the specified resource, as appropriate.  See:
        #    <a href='/Hot Toddy/Documentation?hFile/hFile.listener.php'>hFileListener</a> or
        #    <a href='/Hot Toddy/Documentation?hFinder/hFinder.listener.php'>hFinderListener</a>
        # </p>
        # @end

        $response = 1;

        hString::scrubArray($_GET);

        if (empty($_GET['path']))
        {
            if (empty($_POST['path']))
            {
                $response = -5;
            }
            else
            {
                $this->query($_POST['path']);
            }
        }
        else
        {
            $this->query($_GET['path']);
        }

        if (!$this->isLoggedIn())
        {
            $response = -6;
        }

        if ($response > 0)
        {
            switch (true)
            {
                case (!$this->exists()):
                {
                    $response = -404;
                    break;
                }
                case (isset($methods[$method]['authenticate']) && $methods[$method]['authenticate'] == 'r' && !$this->userIsReadAuthorized):
                {
                    $response = -1;
                    break;
                }
                case (isset($methods[$method]['authenticate']) && $methods[$method]['authenticate'] == 'rw' && !$this->userIsWriteAuthorized):
                {
                    $response = -1;
                    break;
                }
                case !isset($methods[$method]['authenticate']) && !$this->userIsWriteAuthorized:
                {
                    $response = -1;
                    break;
                }
                case isset($methods[$method]['isset']):
                {
                    $variables = array('_GET', '_POST', '_COOKIE');

                    foreach ($variables as $variable)
                    {
                        if (isset($methods[$method]['isset'][$variable]) && is_array($methods[$method]['isset'][$variable]))
                        {
                            foreach ($methods[$method]['isset'][$variable] as $key)
                            {
                                switch ($variable)
                                {
                                    case '_GET':
                                    {
                                        if (!isset($_GET[$key]))
                                        {
                                            $response = -5;
                                        }
                                        break;
                                    }
                                    case '_POST':
                                    {
                                        if (!isset($_POST[$key]))
                                        {
                                            $response = -5;
                                        }
                                        break;
                                    }
                                    case '_COOKIE':
                                    {
                                        if (!isset($_COOKIE[$key]))
                                        {
                                            $response = -5;
                                        }
                                        break;
                                    }
                                    case '_FILES':
                                    {
                                        if (!isset($_FILES[$key]))
                                        {
                                            $response = -5;
                                        }
                                        break;
                                    }
                                    default:
                                    {
                                        $this->warning("Unsupported superglobal '{$variable}'.", __FILE__, __LINE__);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $response;
    }

    public function getThumbnailPath($path)
    {
        # @return string

        # @description
        # <h2>Getting the Path to an Image's Thumbnail</h2>
        # <p>
        #   Returns the path to any image or document's thumbnail stored in the
        #   <var>HtFS</var> folder.
        # </p>
        # @end

        $filename  = basename($path);
        $directory = dirname($path);

        $filenameBits = explode('.', $filename);

        $extension = array_pop($filenameBits);

        array_push($filenameBits, 'thumbnail', 'png');

        return $this->fileSystemPath.$directory.'/'.implode('.', $filenameBits);
    }

    public function getFileResults($query)
    {
        # @return array

        # @description
        # <h2>Getting File Results</h2>
        # <p>
        #   Reconciles file results returned by the <var>getFiles()</var> method of various file
        #   system interfaces. The only argument accepted is the <var>$query</var> returned by
        #   <var>getFiles()</var>. The following reconciliations and/or adjustments are performed
        #   to the returned data:
        # </p>
        # <ul>
        #   <li>
        #       If a file has no file name, a file name is assigned in the format of
        #       "No Name {unix timestamp} {counter}"
        #   </li>
        #   <li>
        #       If a no name file is automatically given a name, its <var>hFileLastModified</var>
        #       timestamp is updated along with the new file name.
        #   </li>
        #   <li>
        #       If a file has no MIME type, a "text/html" MIME type is assigned to it. This
        #       modification is not permanent, its just for this result set.
        #   </li>
        #   <li>
        #       A <var>hCategoryFileSortIndex</var> is assigned, whether applicable or not,
        #       to made the results consistent. If not applicable, the value is <var>0</var>.
        #   </li>
        #   <li>
        #       Files are filtered out if settings in the <a href='#filterPaths'>filterPaths</a>
        #       or <a href='#fileTypes'>fileTypes</a> properties require this.
        #   </li>
        # </ul>
        # @end

        $files = array();

        $i = 0;

        foreach ($query as $data)
        {
            $fileName = $data['hFileName'];

            if (empty($fileName))
            {
                $data[$fileName] = 'No Name '.time().' '.$i;

                $this->hFiles->update(
                    array(
                        'hFileName' => $fileName,
                        'hFileLastModified' => time()
                    ),
                    (int) $data['hFileId']
                );

                $i++;
            }

            if (empty($data['hFileMIME']))
            {
                $data['hFileMIME'] = 'text/html';
            }

            $data['hCategoryFileSortIndex'] = !empty($data['hCategoryFileSortIndex'])? $data['hCategoryFileSortIndex'] : 0;

            $extension = $this->getExtension($data['hFileName']);

            $allowed =
                !in_array($data['hFilePath'], $this->filterPaths) && (
                    !count($this->fileTypes) || (
                        count($this->fileTypes) && (in_array($extension, $this->fileTypes) || in_array($data['hFileMIME'], $this->fileTypes))
                    )
                );

            if ($allowed)
            {
                $files[$fileName] = $data;

                if (!isset($files[$fileName]['hFileInterfaceObjectId']) && !isset($data['hFileInterfaceObjectId']))
                {
                    $files[$fileName]['hFileInterfaceObjectId'] = $data['hFileId'];
                }
            }
        }

        return $files;
    }
}

?>