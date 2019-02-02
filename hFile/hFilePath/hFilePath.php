<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File Path
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
# <h1>File Path API</h1>
# <p>
#   The <var>hFilePath</var> object provides methods that provides information about
#   paths, analyze paths, transform paths.
# </p>
# @end

class hFilePath extends hFrameworkVariables {

    private $modifiedTimes = array();

    public function setPath($path)
    {
        # @return hFilePath

        # @description
        # <h2>Setting the Path</h2>
        # <p>
        #   Set the path that hFramework is going to use for retrieving a document.
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td class='code'>hFileBasePath</td>
        #           <td>The base directory of the file</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileName</td>
        #           <td>The file name.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFilePath</td>
        #           <td>The absolute path (directory and file).</td>
        #       </tr>
        #   </tbody>
        # </table>
        # <p>
        #   If a file name cannot be determined from the path, the file name defaults to <var>index.html</var>.
        # </p>
        # <p class='hDocumentationNote'>
        #   A numeric path is assumed to be and is used as a shortcut for <var>fileId</var>.  So if you were
        #   to go to <var>http://www.example.com/1</var>, the '1' will refer to <var>fileId = 1</var>.
        # </p>
        # @end

        if (is_numeric($path))
        {
            $path = $this->getFilePathByFileId($path);
        }

        $baseName = basename($path);

        if (empty($baseName) || empty($path) || substr($path, -1) == '/')
        {
            $baseName = 'index.html';
            $path .= $baseName;
        }

        $this->hFileBasePath = dirname($path);
        $this->hFileName = $baseName;
        $this->hFilePath = $path;

        $this->fire->setFilePath($path);

        #return $this;
    }

    protected function inspectFilePath()
    {
        # @return hFilePath

        # @description
        # <h2>Inspecting the Path</h2>
        # <p>
        #   Inspects the current path and tries to determine what the path should be.
        # </p>
        # @end

        $this->getFileFromDatabase(
            $this->hFileBasePath,
            $this->hFileName
        );

        if (!$this->hFileId)
        {
            $this->getFileFromDatabase(
                $this->hFilePath,
                $this->hDirectoryIndex('index.html')
            );
        }

        if (!$this->hFileId)
        {
            $this->hDirectoryId = $this->getDirectoryId($this->hFilePath);

            if ($this->hDirectoryId)
            {
                $this->hFileDirectoryIndexPath = $this->hFilePath;
                $this->hFileDirectoryIndexId   = $this->hDirectoryId;

                $this->setPath(
                    $this->getFilePathByPlugin('hFile/hFileDirectoryIndex')
                );

                $this->hDirectoryId = $this->getDirectoryId('/System/Applications/');
            }
            else
            {
                $this->hFileStatusPath = $this->hFilePath;
                $this->hFileStatusCode = 404;
                $fileId = $this->getFileIdByFilePath('/System/Applications/Status Code.html');

                $this->setPath(
                    empty($fileId)?
                            $this->getFilePathByPlugin('hFile/hFileStatusCode')
                        :
                            '/System/Applications/Status Code.html'
                );

                $this->hDirectoryId = $this->getDirectoryId('/System/Applications/');
            }

            $this->getFileFromDatabase($this->hFileBasePath, $this->hFileName);
        }

        # Is the file stored on the server?
        if ($this->beginsPath($this->hFilePath, '/Volumes'))
        {
            $this->hFileSystemDocumentIsVolume = true;
            $this->hFileSystemDocument = true;
        }
        else
        {
            $this->hFileSystemDocument = $this->hFileServerPath || file_exists($this->hFileSystemPath.$this->hFilePath);
        }

        #return $this;
    }

    protected function getFileFromDatabase($path, $name)
    {
        # @return hFilePath

        # @description
        # <h2>Get the File From the Database</h2>
        # <p>
        #   Sets the <var>hFileId</var>, <var>hDirectoryId</var>, and <var>hDirectoryPath</var> for
        #   the current document.
        # </p>
        # @end

        if (empty($name))
        {
            $name = $this->hDirectoryIndex('index.html');
        }

        $file = $this->hDatabase->getAssociativeResults(
            $this->getTemplate(
                dirname(__FILE__).'/SQL/lookupFile.sql',
                array(
                    'hFileName' => $name,
                    'hDirectoryPath' => $path,
                    'hFrameworkSite' => $this->hFrameworkSite,
                    'hDirectoryPathIsRoot' => $path == '/'
                )
            )
        );

        if (count($file))
        {
            $this->setPath(
                $this->getConcatenatedPath($file['hDirectoryPath'], $name)
            );

            $this->setVariables($file);
        }

        #return $this;
    }

    public function getFilePathByFileId($fileId, $path = false)
    {
        # @return string, false

        # @description
        # <h2>Get File Path By File Id</h2>
        # <p>
        #   Return's the absolute path to a file in HtFS based on the supplied
        #   <var>fileId</var>.  If the optional <var>$path</var> argument is
        #   <var>true</var>, just the absolute path to the directory the file
        #   resides in is returned. For example, if the path to the file is
        #   <var>/www.example.com/test/this/folder/file.html</var>
        #   setting <var>$path</var> to <var>true</var> returns
        #   <var>/www.example.com/text/this/folder</var>
        # </p>
        # @end

        $filePath = $this->hFiles->selectColumn(
            array('hFilePath'), (int) $fileId
        );

        if (!empty($filePath))
        {
            return $path? dirname($filePath) : $filePath;
        }

        return false;
    }

    public function getFileName($fileId = 0)
    {
        # @return string

        # @description
        # <h2>Getting a File Name</h2>
        # <p>
        #   Returns the <var>fileName</var>, e.g., <var>index.html</var> for the
        #   supplied <var>fileId</var>.
        # </p>
        # @end

        if (empty($fileId))
        {
            return $this->hFileName;
        }

        return $this->hFiles->selectColumn(
            'hFileName',
            (int) $fileId
        );
    }

    public function getShortPath($fileId)
    {
        # @return string

        # @description
        # <h2>Short Path Shortcuts</h2>
        # <p>
        #   Short paths are tiny file paths that allow you to link to a file in
        #   HtFS using the shortest path possible. Short paths are created simply
        #   using a <var>fileId</var> instead of <var>filePath</var>. For example,
        #   the short path <var>www.example.com/1</var> links to
        #   <var>www.example.com/index.html</var>.
        # </p>
        # @end

        if (!is_numeric($fileId))
        {
            $fileId = $this->getFileIdByFilePath($fileId);
        }

        return '/'.$fileId;
    }

    public function getFilePathByPlugin($plugin)
    {
        return $this->getFilePathByFileId(
            $this->getFileIdByPlugin($plugin)
        );
    }

    public function getFileIdByPlugin($plugin)
    {
        return $this->hFiles->selectColumn(
            'hFileId',
            array(
                'hPlugin' => $plugin
            )
        );
    }

    public function getFileIdByFilePath($filePath)
    {
        # @return integer

        # @description
        # <h2>Get File Id By File Path</h2>
        # <p>
        #   Returns the <var>fileId</var> for the specified <var>filePath</var>.
        # </p>
        # @end

        $directoryId = $this->getDirectoryId(dirname($filePath));

        if ($directoryId > 0)
        {
            return (int) $this->hFiles->selectColumn(
                'hFileId',
                array(
                    'hDirectoryId' => $directoryId,
                    'hFileName' => basename($filePath)
                )
            );
        }

        return 0;
    }

    public function getDirectoryId($directoryPath)
    {
        # @return integer

        # @description
        # <h2>Get Directory Id</h2>
        # <p>
        #   Returns the corresponding <var>directoryId</var> for the supplied
        #   <var>directoryPath</var>.
        # </p>
        # @end

        if (strlen($directoryPath) > 1 && substr($directoryPath, -1, 1) == '/')
        {
            $directoryPath = substr($directoryPath, 0, -1);
        }

        return (int) $this->hDirectories->selectColumn(
            'hDirectoryId',
            array(
                'hDirectoryPath' => $directoryPath
            )
        );
    }

    public function getDirectoryPath($directoryId)
    {
        # @return string

        # @description
        # <h2>Get Directory Path</h2>
        # <p>
        #   Returns the <var>directoryPath</var> for the supplied <var>directoryId</var>.
        # </p>
        # @end

        return $this->hDirectories->selectColumn(
            'hDirectoryPath',
            (int) $directoryId
        );
    }

    public function getConcatenatedPath($path, $name)
    {
        # @return string

        # @description
        # <h2>Get Concatenated Path</h2>
        # <p>
        #   Returns a path with the supplied <var>$name</var> added to the end of the path.
        #   Using this method properly determines whether or not a forward slash should be
        #   added between <var>$path</var> and <var>$name</var>.
        # </p>
        # @end

        return (
            $path.
            (substr($name, 0, 1) !== '/' && substr($path, -1, 1) !== '/' ? '/' : '').
            ($name == '/'? '' : $name)
        );
    }

    public function getIncludePath($path)
    {
        # @return string, false

        # @description
        # <h2>Get Include Path</h2>
        # <p>
        #   When a file or folder can potentially reside in either the <var>Hot Toddy</var> or
        #   the <var>Plugins</var> folders, the <var>getIncludePath()</var> method is called to
        #   determine which folder the file or folder resides in, and once this is determined it
        #   returns an absolute path to the file or folder.
        # </p>
        # <p>
        #   This creates an order of precedence where it concerns a file of the same name residing
        #   simultaneously in both locations. In that situation, the file in the <var>Hot Toddy</var>
        #   folder takes precedence over the file in the <var>Plugins</var> folder.  This is,
        #   however, quite unlikely to occur, since a name space is used for both Hot Toddy plugins
        #   in the <var>Hot Toddy</var> folder and plugins installed in the <var>Plugins</var> folder.
        # </p>
        # <p>
        #   If the file exists in neither location, <var>getIncludePath()</var> returns <var>false</var>.
        # </p>
        # @end

        if (!file_exists($path))
        {
            $endOfPath = $this->getEndOfPath($path, $this->hServerDocumentRoot);

            if (file_exists($endOfPath))
            {
                return $endOfPath;
            }

            if ($this->beginsPath($endOfPath, '/Pictures'))
            {
                $endOfPath = $this->hFrameworkPluginRoot.$endOfPath;
            }

            $path = $this->hFrameworkPath.$endOfPath;

            if (!file_exists($path))
            {
                # If plugin root is customized, paths to things there will need to be
                # adjusted, so that /Plugins/Pictures/Some+Image.png can be automatically
                # rewritten to /Plugins/Some Extra Folder/Pictures/Some+Image.png
                if ($this->beginsPath($endOfPath, '/Plugins') && $this->hFrameworkPluginRoot != '/Plugins')
                {
                    $endOfPath = $this->getEndOfPath($endOfPath, '/Plugins');
                }

                $path = $this->hFrameworkPath.$this->hFrameworkPluginRoot('/Plugins').$endOfPath;

                if (!file_exists($path))
                {
                    $path = $this->hFrameworkPath.$this->hFrameworkApplicationRoot('/Applications').$endOfPath;

                    if (!file_exists($path))
                    {
                        return false;
                    }
                }
            }
        }

        return $path;
    }

    public function isListenerPath($uri)
    {
        # @return boolean

        # @description
        # <h2>Determine if this is a Listener Path</h2>
        # <p>
        #   Determines whether or not the path supplied in <var>$path</var> is a listener path.
        #   Listeners are plugins that allow URL-based access to methods of that plugin. For example,
        #   <var>http://www.example.com/hFile/rename</var> the path <var>/hFile/rename</var> points
        #   to the <var>hFile.listener.php</var> plugin in the hFile folder, and <var>rename</var>
        #   points to the <var>rename()</var> method defined in that plugin.
        # </p>
        # @end

        $path = dirname($uri);

        if ($path != '/')
        {
            $method = basename($uri);

            if (!empty($method))
            {
                $listener = $this->getListenerPath($uri);

                $this->hFrameworkListenerPlugin = $listener;
                $this->hFrameworkListenerMethod = $method;

                $pluginPath = $path.'/'.basename($path).'.listener.php';

                $fileExists = (
                    file_exists($this->hServerDocumentRoot.$pluginPath) ||
                    file_exists($this->hFrameworkPath.$this->hFrameworkPluginRoot('/Plugins').$pluginPath)
                );

                if ($fileExists)
                {
                    if (!$this->isListenerMethod($listener, $method))
                    {
                        $this->registerPlugin($listener);
                    }

                    return $this->isListenerMethod($listener, $method);
                }
            }
        }

        return false;
    }

    public function isServicePath($uri)
    {
        # @return boolean

        # @description
        # <h2>Determine if this is a Service Path</h2>
        # <p>
        #   Determines whether or not the path supplied in <var>$path</var> is a service path.
        #   Services are plugins that allow URL-based access to methods of that plugin. For example,
        #   <var>http://www.example.com/hFile/rename</var> the path <var>/hFile/rename</var> points
        #   to the <var>hFile.listener.php</var> plugin in the hFile folder, and <var>rename</var>
        #   points to the <var>rename()</var> method defined in that plugin.
        # </p>
        # @end

        $path = dirname($uri);

        if ($path != '/')
        {
            $method = basename($uri);

            if (!empty($method))
            {
                $service = $this->getServicePath($uri);

                $this->hFrameworkServicePlugin = $service;
                $this->hFrameworkServiceMethod = $method;

                $pluginPath = $path.'/'.basename($path).'.service.php';

                $fileExists = (
                    file_exists($this->hServerDocumentRoot.$pluginPath) ||
                    file_exists($this->hFrameworkPath.$this->hFrameworkPluginRoot('/Plugins').$pluginPath)
                );

                if ($fileExists)
                {
                    if (!$this->isServiceMethod($service, $method))
                    {
                        $this->registerPlugin($service);
                    }

                    return $this->isServiceMethod($service, $method);
                }
            }
        }

        return false;
    }

    public function getListenerPath($uri)
    {
        # @return string

        # @description
        # <h2>Get the Listener Path from the URL</h2>
        # <p>
        #   <var>getListenerPath()</var> gets the path to the <var>.listener.php</var> file from
        #   the supplied URI.  For example, <var>/hFile/rename</var> passed to the <var>$uri</var>
        #   argument returns the path the listener PHP file <var>/hFile/hFile.listener.php</var>
        # </p>
        # @end

        $bits = explode('/', $uri);

        array_shift($bits);

        $method = array_pop($bits);
        $bits   = array_reverse($bits);

        $bits   = array_reverse($bits);
        $path   = implode('/', $bits);
        $name   = strstr($path, '/')? basename($path) : $path;

        return $path.'/'.$name.'.listener.php';
    }

    public function getServicePath($uri)
    {
        # @return string

        # @description
        # <h2>Get the Service Path from the URL</h2>
        # <p>
        #   <var>getServicePath()</var> gets the path to the <var>.service.php</var> file from
        #   the supplied URI.  For example, <var>/hFile/rename</var> passed to the <var>$uri</var>
        #   argument returns the path the service PHP file <var>/hFile/hFile.service.php</var>
        # </p>
        # @end

        $bits = explode('/', $uri);

        array_shift($bits);

        $method = array_pop($bits);
        $bits   = array_reverse($bits);

        $bits   = array_reverse($bits);
        $path   = implode('/', $bits);
        $name   = strstr($path, '/')? basename($path) : $path;

        return $path.'/'.$name.'.service.php';
    }

    public function beginsPath($pathHaystack, $pathNeedles)
    {
        # @return boolean

        # @description
        # <h2>Determine if a Folder or Path Begins Another Path</h2>
        # <p>
        #   <var>beginsPath()</var> is used to determine when one path appears at the
        #   beginning of another path.  One example, you want to know if <var>/Clams</var>
        #   starts another path.  This can be useful for createng a wildcard catch-all
        #   path, where you want all traffic to <var>/Clams</var> and all subfolders to
        #   go to a particular script. With this method, you have just that tool.
        # </p>
        # <code>$this-&gt;beginsPath('/Clams/Pearls', '/Clams');</code>
        # <p>
        #   This example returns <var>true</var>.
        # </p>
        # <p>
        #   But the followng example won't match:
        # </p>
        # <code>$this-&gt;beginsPath('/Clams-a-lam-a-ding-dong/Pearls', '/Clams');</code>
        # <p>
        #   <var>beginsPath()</var> recognizes proper directory boundaries.
        # </p>
        # @end

        if (!is_array($pathNeedles))
        {
            return (
                substr($pathHaystack, 0, strlen($pathNeedles.'/')) == $pathNeedles.'/' ||
                $pathHaystack == $pathNeedles
            );
        }
        else
        {
            foreach ($pathNeedles as $pathNeedle)
            {
                $condition = (
                    substr($pathHaystack, 0, strlen($pathNeedle.'/')) == $pathNeedle.'/' ||
                    $pathHaystack == $pathNeedle
                );

                if ($condition)
                {
                    return true;
                }
            }

            return false;
        }
    }

    public function inPath($path, $file)
    {
        # @return boolean

        # @description
        # <h2>Determine If a File Is In a Path</h2>
        # <p>
        #   Returns <var>true</var> if the specified <var>$file</var> is the base name of <var>$path</var>.
        # </p>
        # @end

        return $file == basename($path);
    }

    public function getEndOfPath($path, $beginning)
    {
        # @return string

        # @description
        # <h2>Get End of Path</h2>
        # <p>
        #   This method allows you to extract the end of a path based on the string
        #   found at the beginning.  Take, for example,
        #   <var>/Websites/www.example.com/Hot Toddy/hFile/hFilePath</var>.  If you
        #   wanted to simply extract the portion of the path <var>/Hot Toddy/hFile/hFilePath</var>,
        #   you could call this:
        # </p>
        # <code>
        #   $this-&gt;getEndOfPath('/Websites/www.example.com/Hot Toddy/hFile/hFilePath', '/Websites/www.example.com');
        # </code>
        # <p>
        #   This function call returns just that, <var>/Hot Toddy/hFile/hFilePath</var>
        # </p>
        # @end

        if ($path == $beginning)
        {
            return nil;
        }

        return substr(
            $path,
            strlen($beginning)
        );
    }

    public function splitPath($path, $beginning)
    {
        # @return string

        # @description
        # <p>
        #   Alias of: <a href='#getEndOfPath' class='code'>getEndOfPath()</a>
        # </p>
        # @end
        return $this->getEndOfPath(
            $path,
            $beginning
        );
    }

    public function isDocumentRootPath($path)
    {
        # @return boolean

        # @description
        # <h2>Is Document Root Path</h2>
        # <p>
        #   Determines if a path or file lives in <var>hServerDocumentRoot</var>, i.e., the
        #   <var>Hot Toddy</var> folder.
        # </p>
        # @end

        return $this->beginsPath(
            $path,
            $this->hServerDocumentRoot
        );
    }

    public function isFrameworkRootPath($path)
    {
        # @return boolean

        # @description
        # <h2>Is Framework Root Path</h2>
        # <p>
        #   Determines if a path or file lives in <var>hFrameworkPath</var>, i.e.,
        #   <var>/Websites/www.example.com</var>, the folder the whole of Hot Toddy is
        #   installed in.
        # </p>
        # @end

        return $this->beginsPath(
            $path,
            $this->hFrameworkPath
        );
    }

    public function getFileSystemPath()
    {
        # @return string

        # @description
        # <h2>Get File System Path</h2>
        # <p>
        #   Gets the file system path when a file exists as an HtFS file, but the contents are
        #   stored in the server's file system (in the <var>HtFS</var> folder, typically,
        #   but the location can be customized).
        # </p>
        # @end

        if ($this->hFileSystemDocumentIsVolume)
        {
            $volumeName = explode('/', $this->hFilePath);

            if (isset($volumeName[2]))
            {
                return (
                    $this->hFileSystemPath.
                    $this->getEndOfPath($this->hFilePath, '/Volumes/'.$volumeName[2])
                );
            }
            else
            {
                $this->notice('Volume name is not in volume path '.$this->hFilePath.'.', __FILE__, __LINE__);
            }
        }

        if ($this->hFileSystemThumbnailPath)
        {
            return $this->hFileSystemThumbnailPath;
        }

        return $this->hFileSystemPath.$this->hFilePath;
    }

    public function expandDocumentIds($fileDocument)
    {
        # @return string

        # @description
        # <h2>Expand Document Ids</h2>
        # <p>
        #   When Hot Toddy stores files in the database, documents are analyzed for links
        #   pointing inward to other HtFS files.  When HtFS files are detected, the
        #   links are converted to template syntax.  For example, a link to <var>/www.example.com/index.html</var>
        #   would be convertd to <var>{/hFileId:1}</var>.  This conversion allows file names and locations
        #   to change, while links continue to function.
        # </p>
        # <p>
        #   <var>expandDocumentIds()</var> exists to convert template syntax like <var>{/hFileId:1}</var>
        #   back into links. This function exists as a means of convertng links without needng full
        #   template syntax parsing support.
        # </p>
        # @end

        return preg_replace_callback(
            '/\{[\$]?hFileId\:(\d*)\}/iUx',
            array(
                $this,
                'getFileIdPath'
            ),
            $fileDocument
        );
    }

    public function getFileIdPath($matches)
    {
        # @return string

        # @description
        # <h2>Get Path For FileId</h2>
        # <p>
        #   This is a callback function that works in conjuncton with
        #   <a href='#expandDocumentIds' class='code'>expandDocumentIds()</a>.
        #   It returns a path (minus the 'site' folder, see
        #   <a href='#cloakSitesPath' class='code'>cloakSitesPath()</a>),
        #   and appends the <var>hFileLastModified</var> argument to the url.
        # </p>
        # @end

        if ($matches[1] > 0)
        {
            $path = $this->getFilePathByFileId($matches[1]);

            if ($this->hFileDocumentExpandIdWithLastModified(true) && file_exists($this->hFileSystemPath.$path))
            {
                $path .= '?hFileLastModified='.filemtime($this->hFileSystemPath.$path);
            }

            return $this->cloakSitesPath($path);
        }
        else
        {
            return '#';
        }

        return $matches[0];
    }

    public function cloakSitesPath($path)
    {
        # @return string

        # @description
        # <h2>Cloaking the Site Path</h2>
        # <p>
        #   Hot Toddy supports multiple sites, allowing each to live in its own, separate folder in the
        #   root of HtFS.  For the site <var>www.example.com</var>, for example, that site might live
        #   in Hot Toddy at <var>/www.example.com</var>.  Additional sites may be added, and each would, in
        #   turn have its own folder.  To make URLs cleaner and shorter, this method removes the base
        #   folder.  For the URL <var>http://www.example.com/www.example.com/index.html</var> becomes
        #   <var>http://www.example.com/index.html</var>.  'Cloaking' the sites folder removes the
        #   base folder named for the site's hostname.
        # </p>
        # @end

        if ($this->beginsPath($path, '/'.$this->hFrameworkSite))
        {
            return $this->getEndOfPath($path, '/'.$this->hFrameworkSite);
        }

        if ($this->beginsPath($path, 'http://'.$this->hServerHost.'/'.$this->hFrameworkSite))
        {
            return 'http://'.$this->hServerHost.$this->getEndOfPath($path, 'http://'.$this->hServerHost.'/'.$this->hFrameworkSite);
        }

        if ($this->beginsPath($path, 'https://'.$this->hServerHost.'/'.$this->hFrameworkSite))
        {
            return 'https://'.$this->hServerHost.$this->getEndOfPath($path, 'https://'.$this->hServerHost.'/'.$this->hFrameworkSite);
        }

        return $path;
    }

    public function getExtension($path)
    {
        # @return string

        # @description
        # <h2>Get File Extension</h2>
        # <p>
        #   Gets the file extension from the supplied file name or path.  If the file
        #   name is <var>index.html</var> this method returns the string <var>html</var>.
        #   The supplied string can also be a path.  Take this path, for example,
        #   <var>/www.example.com/test/folder/index.jpg</var>.
        #   This would return the string <var>jpg</var>.
        # </p>
        # @end

        if (!strstr($path, '.'))
        {
            return '';
        }

        return strtolower(substr($path, strrpos($path, '.') + 1));
    }

    public function insertSubExtension($path, $subExtension, $condition = true)
    {
        # @return string

        # @description
        # <h2>Inserting a Sub Extension</h2>
        # <p>
        #   Inserts a sub extension into the file name of a path. For example, test.css with a desired
        #   sub extension of <i>mobile</i> becomes test.mobile.css.
        # </p>
        # @end

        if ($condition && !stristr($path, '.'.$subExtension.'.') && strstr($path, '.'))
        {
            $pathWithSubExtension = substr($path, 0, strrpos($path, '.') + 1).$subExtension.'.'.substr($path, strrpos($path, '.') + 1);

            if (file_exists($pathWithSubExtension))
            {
                return $pathWithSubExtension;
            }
            else if (file_exists($this->getConcatenatedPath($this->hFrameworPath.'/Hot Toddy', $pathWithSubExtension)))
            {
                return $pathWithSubExtension;
            }
            else if (file_exists($this->getConcatenatedPath($this->hFrameworkPluginPath, $pathWithSubExtension)))
            {
                return $pathWithSubExtension;
            }
            else
            {
                return $path;
            }
        }

        return $path;
    }

    public function href($path = nil, $arguments = array(), $sessionId = true)
    {
        # @return string

        # @description
        # <h2>Including Paths in a Redirection</h2>
        # <p>
        #   This function should be called when using an HTTP redirect and the redirect
        #   is pointed to another framework document.  It should NOT be used for paths
        #   to be included within an HTML document, that use of this function is deprecated.
        # </p>
        # @end

        if (empty($path))
        {
            $path = $this->hFilePath;
        }

        if ($this->hPath != '/')
        {
            $path = $this->hPath.$path;
        }

        return(
            $this->cloakSitesPath($path).
            (count($arguments)? (strstr($path, '?')? '&' : '?') : '').
            $this->getQueryString($arguments).
            ($sessionId? $this->getSessionId(!empty($arguments)) : '')
        );
    }

    public function image($path)
    {
        # @return string

        # @description
        # <h2>Including Image Paths</h2>
        # <p>
        #   This function is deprecated, it has been supplanted by the
        #   automatic parsing of documents.
        # </p>
        # @end

        if ($this->hPath != '/')
        {
            $path = $this->hPath.$path;
        }

        if ($this->beginsPath($path, $this->hFrameworkPicturesRoot))
        {
            $sourcePath = $this->hDirectoryTemplatePictures.$this->getEndOfPath($path, $this->hFrameworkPicturesRoot);

            return $path.'?hFileLastModified='.filemtime($sourcePath);
        }

        return $path;
    }

    public function getQueryString($arguments)
    {
        # @return string

        # @description
        # <h2>Constructing a Query String</h2>
        # <p>
        #   Takes an array of arguments and constructs a GET query string
        #   from those arguments.
        # </p>
        # @end

        if (!empty($arguments) && is_array($arguments))
        {
            $parameters = array();

            foreach ($arguments as $argument => $value)
            {
                $parameters[] = $argument.'='.$value;
            }

            return implode('&', $parameters);
        }
    }

    public function makeFrameworkPath($path, $encodeAmpersands = false)
    {
        # @return string

        # @description
        # <h2>Creating Framework Paths</h2>
        # <p>
        #
        # </p>
        # @end

        # Support installing Hot Toddy in a directory other than DocumentRoot
        if ($this->hPath('/') != '/')
        {
            $path = $this->hPath.$path;
        }

        $http = false;
        $https = false;

        if ($this->beginsPath($path, 'http://'.$this->hServerHost))
        {
            $http = true;
            $path = $this->getEndOfPath($path, 'http://'.$this->hServerHost);
        }

        if ($this->beginsPath($path, 'https://'.$this->hServerHost))
        {
            $https = true;
            $path = $this->getEndOfPath($path, 'https://'.$this->hServerHost);
        }

        switch (true)
        {
            case $this->beginsPath($path, $this->hFrameworkLibraryRoot):
            {
                $serverPath = $this->hFrameworkLibraryPath.$this->getEndOfPath($path, $this->hFrameworkLibraryRoot);
                break;
            }
            case $this->beginsPath($path, ''):
            {
                $ext = $this->getExtension($path);

                #if ($ext == 'js' || $ext == 'css')
                #{
                    $serverPath = $this->getIncludePath($this->hServerDocumentRoot.$path);
                #}
                break;
            }
            case $this->beginsPath($path, $this->hFrameworkPicturesRoot):
            {
                $serverPath = $this->hFrameworkPicturesPath.$this->getEndOfPath($path, $this->hFrameworkPicturesRoot);
                break;
            }
            case $this->beginsPath($path, '/images/icons'):
            {
                $serverPath = $this->hFrameworkPath.$this->getEndOfPath($path, '/images');
                break;
            }
            default:
            {
                $ext = $this->getExtension($path);

                switch ($ext)
                {
                    case 'jpg':
                    case 'jpeg':
                    case 'jpe':
                    case 'gif':
                    case 'png':
                    case 'mp4':
                    case 'swf':
                    case 'flv':
                    case 'pdf':
                    case 'xls':
                    case 'doc':
                    {
                        $serverPath = $this->hFileSystemPath.$path;

                        if (!file_exists($serverPath))
                        {
                            $serverPath = $this->hFileSystemPath.'/'.$this->hFrameworkSite.$path;
                        }
                        break;
                    }
                }
            }
        }

        # Append last modified time in a query string, which is used to control caching.
        if (!empty($serverPath) && file_exists($serverPath))
        {
            $mTime = filemtime($serverPath);

            array_push($this->modifiedTimes, $mTime);

            $path .= '?hFileLastModified='.$mTime;
        }

        if ($this->beginsPath($path, '/'.$this->hFrameworkSite))
        {
            $path = $this->getEndOfPath($path, '/'.$this->hFrameworkSite);
        }

        if ($encodeAmpersands && !strstr($path, '&amp;'))
        {
            #$path = str_replace('&', '&amp;', $path);
        }

        if (!strstr($path, '%20') && !strstr($path, '+') && !strstr($path, 'mailto:'))
        {
            $matches = array();

            $path = preg_replace_callback('/\{[\$]?hFileId\:(\d*)\}/iUx', array($this, 'getFileIdPath'), $path);

            if ($path == '#')
            {
                return '#';
            }

            $path = hString::entitiesToUTF8($path, false);

            $fragment = '';

            if (strstr($path, '#'))
            {
                $bits = explode('#', $path);
                $path = $bits[0];
                $fragment = $bits[1];
            }

            $queryString = '';

            if (strstr($path, '?'))
            {
                $bits = explode('?', $path);
                $path = $bits[0];
                $queryString = $bits[1];
            }

            $pathBits = explode('/', $path);

            $fileName = array_pop($pathBits);

            foreach ($pathBits as $i => $directory)
            {
                $pathBits[$i] = urlencode($directory);
            }

            $path = implode('/', $pathBits).'/'.urlencode($fileName);

            if (!empty($queryString))
            {
                $path .= '?'.$queryString;
            }

            if (!empty($fragment))
            {
                $path .= '#'.$fragment;
            }
        }

        if ($http)
        {
            $path = 'http://'.$this->hServerHost.$path;
        }

        if ($https)
        {
            $path = 'https://'.$this->hServerHost.$path;
        }

        return str_replace(' ', '+', $path);
    }

    public function getModifiedTimes()
    {
        # @return array

        # @description
        # <h2>Diagnostics: Gettings All Modified Times</h2>
        # <p>
        #   Returns a list of all modified times.
        # </p>
        # @end

        return $this->modifiedTimes;
    }

    public function redirectIfSecureIsEnabled()
    {
        # @return void

        # @description
        # <h2>Redirecting to a Secure Site</h2>
        # <p>
        #   This method automatically redirects to HTTPS if SSL is enabled within Hot Toddy.
        # </p>
        # @end

        if (!$this->isSSLEnabled())
        {
            header('Location: '.$this->href('https://'.$this->hFrameworkSite.$this->hFilePath, $_GET));
            exit;
        }
    }

    public function isFrameworkPath($path)
    {
        # @return boolean

        # @description
        # <h2>Determining Framework Paths</h2>
        # <p>
        #   This function analyzes a path and determines whether or not it should be
        #   considered a framework path.
        # </p>
        # @end

        if ($this->beginsPath($path, 'http://'.$this->hServerHost))
        {
            return true;
        }

        if ($this->beginsPath($path, 'https://'.$this->hServerHost))
        {
            return true;
        }

        if (substr($path, 0, 9) == '{hFileId:')
        {
            return true;
        }

        # Match paths that begin with the following:
        # http://
        # https://
        # ftp://
        # *://  (any protocol)
        # javascript:
        # about:
        # //
        # #
        #
        # These are not framework paths or URIs and should not be altered.
        $matches = array();

        preg_match(
            '/^(.*\:\/\/|javascript\:|about\:|\/\/|\#)(.*)$/iU',
            $path,
            $matches
        );

        return empty($matches[1]);
    }

    private function getSessionId($separator = true)
    {
        # @return string

        # @description
        # <h2>Getting the Session Id for a URL</h2>
        # <p>
        #   Returns the session Id properly formatted as a GET query string.
        # </p>

        return
          ($this->isLoggedIn()? ($this->hSessionIncludeURLId(false)? ($separator? '&' : '?')
          .session_name().'='.session_id() : '') : '');
    }

    public function getPathMIME($path, &$mime)
    {
        if (file_exists($path) && empty($mime))
        {
            $mime = $this->getMIMEType($path);
        }

        return $mime;
    }

    public function isImage($file, $mime = nil)
    {
        # @return boolean

        # @description
        # <h2>Determining If a File Is an Image</h2>
        # <p>
        #   This function determines whether or not a file is an image,
        #   based on its extension or MIME type.
        # </p>
        # @end

        $extension = $this->getExtension($file);

        if (!empty($mime) && substr($mime, 0, 6) == 'image/')
        {
            return true;
        }

        return in_array(
            $extension,
            array(
                'png',
                'jpg',
                'jpeg',
                'jpe',
                'gif',
                'ai',
                'psd',
                'bmp',
                'tif',
                'tiff',
                'svg'
            )
        );
    }

    public function isAudio($file, $mime = nil)
    {
        # @return boolean

        # @description
        # <h2>Determining If a File Is Audio</h2>
        # <p>
        #   This function determines whether or not a file is audio,
        #   based on its extension or MIME type.
        # </p>
        # @end

        $this->getPathMIME($file, $mime);

        $extension = $this->getExtension($file);

        if (!empty($mime) && substr($mime, 0, 6) == 'audio/')
        {
            return true;
        }

        return in_array(
            $extension,
            array(
                'mp3',
                'wav',
                'aac',
                'aif',
                'm4a',
                'mpa',
                'ogg',
                'ra',
                'wma'
            )
        );
    }

    public function isVideo($file, $mime = nil)
    {
        # @return boolean

        # @description
        # <h2>Determining If a File Is Video</h2>
        # <p>
        #   This function determines whether or not a file is video,
        #   based on its extension or MIME type.
        # </p>
        # @end

        $this->getPathMIME($file, $mime);

        $extension = $this->getExtension($file);

        if (!empty($mime) && substr($mime, 0, 6) == 'video/')
        {
            return true;
        }

        return in_array(
            $extension,
            array(
                'mov',
                'qt',
                'movie',
                'flv',
                'f4v',
                'f4p',
                'swf',
                'mpa',
                'mpeg',
                'mpg',
                'mpe',
                'mp2',
                'mp4',
                'm4v',
                'mpv2',
                'avi',
                'wmv',
                'asf',
                'asx',
                'asr',
                'rm'
            )
        );
    }
}

?>