<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File Import Library
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
# <h1>File Import API</h1>
# <p>
#   This library provides a collection of methods that assist with importing into HtFS
#   from various outside sources.
# </p>
# @end

class hFileImportLibrary extends hPlugin {

    private $hFile;
    private $hFileDatabase;

    private $filePath;

    private $options;

    private $phpQuery;

    private $optionLabels = array(
        'importFrom',
        'saveFile',
        'removeHostName',
        'removeIP',
        'picturesFolder',
        'documentsFolder',
        'moviesFolder',
        'musicFolder',
        'chmod',
        'chown',
        'hUserId',
        'hUserPermissionsOwner',
        'hUserPermissionsWorld',
        'hUserPermissionsGroups'
    );

    public function hConstructor()
    {
        $this->hFile = $this->library('hFile');
        $this->hFileDatabase = $this->database('hFile');
    }

    public function reset()
    {
        $this->options = array();
    }

    public function setOptions(&$options)
    {
        # @return void

        # @description
        # <h2>Setting the Host Name to be Removed</h2>
        # <p>
        #   When importing HTML source, sometimes links or paths are prefixed with a
        #   hostname.  To import that HTML source, these links or paths will need to be
        #   rewritten to remove the host name so that they will function after import.
        #   The <var>$hostName</var> can be specified one of two ways, via <var>$options</var>
        #   as <var>$options['removeHostName']</var> or via this method <var>setRemoveHostName()</var>
        # </p>
        # <h2>Setting an IP to be Removed</h2>
        # <p>
        #   When importing HTML source, sometimes links or paths are prefixed with a
        #   hostname.  To import that HTML source, these links or paths will need to be
        #   rewritten to remove the host name so that they will function after import.
        #   The <var>$hostName</var> can be specified one of two ways, via <var>$options</var>
        #   as <var>$options['removeHostName']</var> or via this method <var>setRemoveHostName()</var>
        # </p>
        # <p>
        #   Options:
        # </p>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>Option</th>
        #           <th>Description</th>
        #           <th>Default</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td class='code'>importFrom</td>
        #           <td>
        #               What host name to import files found in HTML source documents from.
        #           </td>
        #           <td>
        #               The value of the <var>hFrameworkSite</var> framework variable.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>removeHostName</td>
        #           <td>
        #               The host name to remove from existing URLs found in the HTML source,
        #               that when removed will expose all internally pointing paths.
        #           </td>
        #           <td>
        #               The value of the <var>hFrameworkSite</var> framework variable.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>removeIP</td>
        #           <td>
        #               If an IP address is used to create paths, rather than a hostname,
        #               this setting will remove the IP address from paths,
        #               and once removed will expose even more internally pointing paths.
        #           </td>
        #           <td>
        #               None.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>chmod</td>
        #           <td>
        #               The mode to set both the imported files and the entire <var>HtFS</var>
        #               directory to using Hot Toddy's
        #               <a href='/Hot Toddy/Documentation?hFile/hFilePath#chmod'>chmod()</a> method.
        #               Requires import script to be run as root.
        #           </td>
        #           <td>
        #               None, no mode is set if no value is provided.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>chown</td>
        #           <td>
        #               The owner (and optionally group) to set the imported files and the entire
        #               HtFS directory to using Hot Toddy's
        #               <a href='/Hot Toddy/Documentation?hFile/hFilePath#chown'>chown()</a> method.
        #               To set owner and group use: <i>owner:group</i>.
        #               Requires import script to be run as root.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>picturesFolder</td>
        #           <td>
        #               The path to the folder to save files deemed to be 'pictures'.
        #           </td>
        #           <td>
        #               /{hFrameworkSite}/Pictures
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>moviesFolder</td>
        #           <td>
        #               The path to the folder to save files deemed to be 'movies'.
        #           </td>
        #           <td>
        #               /{hFrameworkSite}/Movies
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>moviesFolder</td>
        #           <td>
        #               The path to the folder to save files deemed to be 'music'.
        #           </td>
        #           <td>
        #               /{hFrameworkSite}/Music
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>documentsFolder</td>
        #           <td>
        #               The path to the folder to save files deemed to be 'documents'.
        #           </td>
        #           <td>
        #               /{hFrameworkSite}/Documents
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>userId</td>
        #           <td>
        #               The <var>hUserId</var> that will own imported files and folders created by <var>hFileImport</var>.
        #           </td>
        #           <td>
        #               1 (hUserId = 1)
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>userPermissionsOwner</td>
        #           <td>
        #               The HtFS permissions for imported files as applied to the owner of the document(s).
        #           </td>
        #           <td>
        #               rw (Read/Write)
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>userPermissionsWorld</td>
        #           <td>
        #               The HtFS permissions for imported files as applied to the world (public).
        #           </td>
        #           <td>
        #               r (Read-Only)
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>userPermissionsGroups</td>
        #           <td>
        #               An array of groups or users where the key of each entry is the group's or user's <var>hUserName</var>
        #               or <var>hUserId</var> and the corresponding value is the permissions level set for that
        #               group or user.
        #           </td>
        #           <td>
        #               <i>Website Administrators</i> gets permissions <var>rw</var>, by default.
        #           </td>
        #       </tr>
        #
        #   </tbody>
        # </table>
        # @end

        foreach ($this->optionLabels as $optionLabel)
        {
            if (isset($options[$optionLabel]))
            {
                $this->options[$optionLabel] = $options[$optionLabel];
                unset($options[$optionLabel]);
            }
        }

        if (empty($this->options['importFrom']))
        {
            $this->options['importFrom'] = 'http://'.$this->hFrameworkSite;
        }
        else if (!stristr($this->options['importFrom'], 'http://') && !stristr($this->options['importFrom'], 'https://'))
        {
            $this->options['importFrom'] = 'http://'.$this->options['importFrom'];
        }

        if (empty($this->options['removeHostName']))
        {
            $this->options['removeHostName'] = $this->hFrameworkSite;
        }

        if (!isset($this->options['hUserPermissionsGroups']))
        {
            $this->options['hUserPermissionsGroups'] = array(
                'Website Administrators' => 'rw'
            );
        }
    }

    public function fromSourceDOM($source, $selector, $filePath, array $file = array(), array $options = array())
    {


    }

    public function fromSource($source, $filePath = null, array $file = array(), array $options = array())
    {
        # @return void

        # @description
        # <h2>Importing From Source</h2>
        # <p>
        #   Importing from source takes an HTML source document, or HTML snippet
        #   (an incomplete HTML document) and analyzes that document's paths.
        #   The paths found in the document are imported to HtFS (saved to the
        #   local Hot Toddy File System), and are rewritten so that once the
        #   HTML is created as a Hot Toddy document, paths to documents, files, and
        #   images within it still function.  On the other hand, paths to HTML
        #   documents or dynamic documents will not continue to function.  These
        #   paths are left untouched, presently.  Additionally paths to external
        #   website are not touched.
        # </p>
        # <p>
        #   <var>$source</var> is an HTML document.
        # </p>
        # <p>
        #   <var>$filePath</var> is the path within HtFS you wish to store the HTML document.
        # </p>
        # <p>
        #   <var>$file</var> is an array of file settings that control meta data attached
        #   to the HTML file you're creating.  For settings see:
        #   <a href='/Hot Toddy/Documentation?hFile/hFileDatabase/hFileDatabase.library.php#save' class='code'>hFileDatabaseLibrary::save()</a>
        # </p>
        # <p>
        #   <var>$options</var> is an array of settings that controls how linked
        #   files are imported into HtFS.
        # </p>
        # @end

        if (!empty($source))
        {
            $this->setOptions($options);

            $source = preg_replace_callback(
                "/(action|src|background|poster)\=(\'|\")(.*)(\'|\")/iU",
                array($this, 'pathCallback'),
                $source
            );

            $source = preg_replace_callback(
                "/\<a(.*)href\=(\'|\")(.*)(\'|\")(.*)\>(.*)\<\/a\>/iU",
                array($this, 'hrefPathCallback'),
                $source
            );

            $source = hString::escapeAndEncode($source);

            $saveFile = !isset($this->options['saveFile']) || !empty($this->options['saveFile']);

            if ($saveFile)
            {
                $fileId = $this->hFileDatabase->save(
                    array_merge(
                        array(
                            'hFilePath' => $filePath,
                            'hFileDocument' => $source,
                        ),
                        $file
                    )
                );
            }

            if (!empty($this->options['chmod']))
            {
                $this->chmod(
                    $this->hFrameworkFileSystemPath,
                    $this->options['chmod'],
                    true
                );
            }

            if (!empty($this->options['chown']))
            {
                $this->chown(
                    $this->hFrameworkFileSystemPath,
                    $this->options['chown'],
                    true
                );
            }

            $this->options = array();

            return $saveFile? $fileId : $source;
        }
        else
        {
            $this->warning("Unable to import source HTML, no value was provided.");
        }

        return 0;
    }

    public function fromSite($uri, $filePath, Array $file = array(), Array $options = array())
    {
        # @return void

        # @description
        # <h2>Importing From a Site</h2>
        # <p>
        #   This method imports from a URI, e.g., <var>http://www.example.com/index.html</var>.
        #   Once the HTML source is retrieved from the remote website, it is passed to the
        #   <a href='#getSource' class='code'>getSource()</a> method, where it is further
        #   analyzed for other files.  If other file paths are referenced in the document
        #   they are imported based on the settings provided in the <var>$options</var>
        #   arugment, or default values that are in effect in the absense of options.
        # </p>
        # <p>
        #   See <a href='#getSource' class='code'>getSource()</a>
        #   for an explanation of the <var>$filePath</var>, <var>$file</var> and
        #   <var>$options</var> arguments.
        # </p>
        # @end

        if (!empty($uri))
        {
            $source = file_get_contents($uri);

            return $this->fromSource(
                $source,
                $filePath,
                $file,
                $options
            );
        }
        else
        {
            $this->warning("Unable to import from site via URI, no value was provided.");
        }

        return 0;
    }

    public function fromDisk($path, $filePath, array $file = array(), array $options = array())
    {
        # @return void

        # @description
        # <h2>Importing From the Server</h2>
        # <p>
        #   This method imports from a location on the server outside of HtFS, this can be
        #   any path on the server, or even a mounted network share.
        # </p>
        # <p>
        #   Once the HTML source is retrieved from the file, it is passed to the
        #   <a href='#getSource' class='code'>getSource()</a> method, where it is further
        #   analyzed for other files.  If other file paths are referenced in the document
        #   they are imported based on the settings provided in the <var>$options</var>
        #   arugment, or default values that are in effect in the absense of options.
        # </p>
        # <p>
        #   See <a href='#getSource' class='code'>getSource()</a>
        #   for an explanation of the <var>$filePath</var>, <var>$file</var> and
        #   <var>$options</var> arguments.
        # </p>
        # @end

        if (!empty($path))
        {
            if (file_exists($path))
            {
                $source = file_get_contents($path);

                return $this->fromSource(
                    $source,
                    $filePath,
                    $file,
                    $options
                );
            }
            else
            {
                $this->warning("Unable to import HTML from disk, path '{$path}' does not exist.");
            }
        }
        else
        {
            $this->warning("Unable to import HTML from disk, no path was provided.");
        }

        return 0;
    }

    public function fromArray($array, array $options = array())
    {
        # @return void

        # @description
        # <h2>Importing Files From an Array</h2>
        # <p>
        #   This method allows you to import one or more files specified in an array
        #   where the key can be the file name you want to import that file as, or if
        #   the key is just a numeric value, the existing file name will be kept
        #   (and modified based on the rules laid out in
        #   <a href='#saveFileFrom' class='code'>saveFileFrom()</a>)
        # </p>
        # <p>
        #   File paths in the array can be files in the local file system, or files
        #   that exist on a remote server.
        # </p>
        # <p>
        #   Files are imported based on the settings provided in the <var>$options</var>
        #   arugment, or default values that are in effect in the absense of options.
        # </p>
        # <p>
        #   See <a href='#getSource' class='code'>getSource()</a>
        #   for an explanation of the <var>$filePath</var>, <var>$file</var> and
        #   <var>$options</var> arguments.
        # </p>
        # @end

        $this->setOptions($options);

        foreach ($array as $key => $path)
        {
            $saveTo = $this->getSaveFolder($path);

            if (!empty($saveTo))
            {
                if (is_numeric($key))
                {
                    $this->saveFileFrom($path, $saveTo);
                }
                else
                {
                    $extension = $this->getExtension($key);

                    if (empty($extension))
                    {
                        $key .= '.'.$this->getExtension($path);
                    }

                    $this->saveFileFrom($path, $saveTo, $key);
                }
            }
        }
    }

    public function pathCallback($matches)
    {
        # @return string

        # @description
        # <h2>Path Callback</h2>
        # <p>
        #   When HTML source is analyzed, paths are found in the HTML file by using a regular
        #   expression that finds <var>action</var>, <var>src</var>, <var>background</var>,
        #   and <var>poster</var> attributes.  Paths found in these attributes are then passed
        #   to <a href='#saveFileFrom' class='code'>saveFileFrom()</a>
        # </p>
        # @end

        $attribute = $matches[1];
        $quote     = $matches[2];
        $path      = $matches[3];

        $this->stripHostName($path);

        if (substr($path, 0, 1) == '/')
        {
            $saveTo = $this->getSaveFolder($path);

            if (!empty($saveTo))
            {
                $path = $this->saveFileFrom($path, $saveTo);
            }
        }

        return $attribute.'='.$quote.$path.$quote;
    }

    public function hrefPathCallback($matches)
    {
        # @return string

        # @description
        # <h2>Href Path Callback</h2>
        # <p>
        #   When HTML source is analyzed, paths are found in the HTML file using a
        #   regular expression that identifies links and link labels, basically
        #   matching anything resembling the following:
        # </p>
        # <code>
        #   &lt;a href='/path/to/something.pdf'&gt;My Great File&lt;/a&gt;
        # </code>
        # <p>
        #   Similarly to <a href='#pathCallback' class='code'>pathCallback()</a>,
        #   <var>hrefPathCallback()</var> grabs the file specified in the <var>href</var>
        #   attribute of the <var>&lt;a&gt;</var> element.  The only thing <var>hrefPathCallback</var>
        #   does differently, is that it also captures the text used for the link label.  e.g.,
        #   "My Great File", in the example above, and it renames the file based on the
        #   content of the text label.  So <var>something.pdf</var> in the above example
        #   becomes <var>My Great File.pdf</var>.
        # </p>
        # @end

        $beforePath = $matches[1];
        $quote      = $matches[2];
        $path       = $matches[3];
        $afterPath  = $matches[5];
        $label      = $matches[6];

        $this->stripHostName($path);

        if (substr($path, 0, 1) == '/')
        {
            $extension = strtolower($this->getExtension($path));

            $saveTo = $this->getSaveFolder($path);

            $fileName = trim(strip_tags($label));

            $fileName = str_replace(
                array(
                    '/',
                    '\\',
                    ':',
                    ';',
                    '?',
                    '!',
                    '+',
                    '%',
                    '<',
                    '>',
                    '(',
                    ')',
                    '{',
                    '}',
                    '$',
                    '#',
                    '@',
                    '*',
                    '[',
                    ']',
                    "'",
                    '"',
                    '^',
                    '~',
                    '`',
                    '|'
                ),
                '',
                $fileName
            );

            $fileName = str_replace(
                array(
                    '&',
                    '&amp;',
                ),
                'and',
                $fileName
            );

            if (!empty($saveTo))
            {
                $path = $this->saveFileFrom(
                    $path,
                    $saveTo,
                    $fileName.'.'.strtolower($extension)
                );
            }
        }

        return '<a'.$beforePath.'href='.$quote.$path.$quote.$afterPath.'>'.$label.'</a>';
    }

    public function getSaveFolder($path)
    {
        $extension = strtolower($this->getExtension($path));

        if (!empty($extension))
        {
            switch ($extension)
            {
                case 'php':
                case 'phtml':
                case 'aspx':
                case 'asp':
                case 'cfm':
                case 'cf':
                case 'rb':
                case 'html':
                case 'htm':
                case 'xhtml':
                case 'cgi':
                case 'py':
                {
                    return '';
                }
                default:
                {
                    switch ($extension)
                    {
                        case 'jpg':
                        case 'png':
                        case 'gif':
                        case 'jpe':
                        case 'jpeg':
                        case 'tif':
                        case 'psd':
                        case 'svg':
                        case 'ico':
                        case 'eps':
                        case 'ai':
                        {
                            if (!empty($this->options['picturesFolder']))
                            {
                                $saveTo = $this->options['picturesFolder'];
                            }
                            else
                            {
                                $saveTo = '/'.$this->hFrameworkSite.'/Pictures';
                            }

                            break;
                        }
                        case 'm4v':
                        case 'mp4':
                        case 'mov':
                        case 'wmv':
                        case 'rm':
                        case 'swf':
                        case 'flv':
                        case 'api':
                        case 'fla':
                        case 'mpeg':
                        {
                            if (!empty($this->options['moviesFolder']))
                            {
                                $saveTo = $this->options['moviesFolder'];
                            }
                            else
                            {
                                $saveTo = '/'.$this->hFrameworkSite.'/Movies';
                            }

                            break;
                        }
                        case 'mp3':
                        case 'wav':
                        case 'aac':
                        case 'band':
                        case 'm4a':
                        {
                            if (!empty($this->options['musicFolder']))
                            {
                                $saveTo = $this->options['musicFolder'];
                            }
                            else
                            {
                                $saveTo = '/'.$this->hFrameworkSite.'/Music';
                            }

                            break;
                        }
                        case 'pdf':
                        case 'doc':
                        case 'docx':
                        case 'xls':
                        case 'xlsx':
                        case 'ppt':
                        case 'pptx':
                        case 'zip':
                        case 'pages':
                        case 'numbers':
                        case 'keynote':
                        case 'txt':
                        case 'rtf':
                        case 'eml':
                        case 'msg':
                        case 'xml':
                        case 'css':
                        case 'js':
                        case 'dmg':
                        case 'iso':
                        case 'json':
                        default:
                        {
                            if (!empty($this->options['documentsFolder']))
                            {
                                $saveTo = $this->options['documentsFolder'];
                            }
                            else
                            {
                                $saveTo = '/'.$this->hFrameworkSite.'/Documents';
                            }
                        }
                    }

                    return $saveTo;
                }
            }
        }

        return '';
    }

    public function saveFileFrom($path, $saveTo, $fileName = null)
    {
        # @return string

        # @description
        # <h2>Save Remote File</h2>
        # <p>
        #   Creates a new file in HtFS by grabbing the file specified in
        #   <var>$path</var>.  Since <var>$path</var> can be either a local
        #   file or a remote file on a completely different server.
        #   Nothing happens to verify that <var>$path</var> exists.  The
        #   file is saved to the HtFS path specified in <var>$saveTo</var>,
        #   and if <var>$fileName</var> is not <var>null</var>, the file is
        #   renamed to <var>$fileName</var>, if <var>$fileName</var> is
        #   <var>null</var>, on the other hand, the file keeps its present
        #   name, with a few potential modifications:
        # </p>
        # <ul>
        #   <li>Underscores are converted to spaces</li>
        #   <li>Extensions with uppercase letters are made all lowercase letters</li>
        #   <li>
        # </ul>
        # <h3>Fixing Permissions</h3>
        # <p>
        #   The imported file's permissions (on the server's hard disk) are modified
        #   to the <var>chmod</var>
        #   and <var>chown</var>.  Import script must be run as <i>root</i> for
        #   <var>chmod()</var> and <var>chown()</var> methods to do their deeds.
        # </p>
        # <p>
        #   The imported file's permissions (in HtFS) are modified to match
        #   the options <var>userPermissionsGroups</var>, <var>userPermissionsOwner</var>,
        #   <var>userPermissionsWorld</var>, and <var>hUserId</var>.
        # @end

        $extension = strtolower($this->getExtension($path));

        if (!$this->hFile->exists($saveTo))
        {
            $permissions = array();

            if (isset($this->options['hUserId']))
            {
                $permissions['hUserId'] = $this->options['hUserId'];
            }

            if (isset($this->options['hUserPermissionsOwner']))
            {
                $permissions['hUserPermissionsOwner'] = $this->options['hUserPermissionsOwner'];
            }

            if (isset($this->options['hUserPermissionsWorld']))
            {
                $permissions['hUserPermissionsWorld'] = $this->options['hUserPermissionsWorld'];
            }

            if (isset($this->options['hUserPermissionsGroups']) && is_array($this->options['hUserPermissionsGroups']))
            {
                $permissions['hUserPermissionsGroups'] = $this->options['hUserPermissionsGroups'];
            }

            $directoryId = $this->hFile->makePath(
                $saveTo,
                $permissions
            );
        }

        if (empty($fileName))
        {
            $fileName = basename($path);
            $fileName = str_replace('_', ' ', $fileName);

            if (strstr($fileName, '.'.strtoupper($extension)))
            {
                $fileName = str_replace(
                    '.'.strtoupper($extension),
                    '.'.strtolower($extension),
                    $fileName
                );
            }
        }

        $oldPath = $path;

        $newPath = $this->getConcatenatedPath($saveTo, $fileName);
        $directoryId = $this->getDirectoryId($saveTo);

        $temporaryFileName = $this->getRandomString(15);
        $temporaryPath = $this->hFrameworkPath.'/Temporary/'.$temporaryFileName;

        file_put_contents(
            $temporaryPath,
            file_get_contents($this->options['importFrom'].$path)
        );

        if (!empty($this->options['chmod']))
        {
            $this->chmod(
                $temporaryPath,
                $this->options['chmod']
            );
        }

        if (!empty($this->options['chown']))
        {
            $this->chown(
                $temporaryPath,
                $this->chown
            );
        }

        $this->hFileSystemAllowDuplicates = true;

        $mimeType = $this->hFileIcons->selectColumn(
            'hFileMIME',
            array(
                'hFileExtension' => $extension
            )
        );

        $this->hFile->import(
            $saveTo,
            array(
                array(
                    'hFileName'         => $fileName,
                    'hFileReplace'      => true,
                    'hFileMD5Checksum'  => md5_file($temporaryPath),
                    'hFileTempPath'     => $temporaryPath,
                    'hFileMIME'         => $mimeType
                )
            )
        );

        $this->hFileSystemAllowDuplicates = false;

        $fileId = $this->getFileIdByFilePath($newPath);

        if (is_array($this->options['hUserPermissionsGroups']))
        {
            foreach ($this->options['hUserPermissionsGroups'] as $group => $level)
            {
                $this->hFiles->setGroup($group, $level);
            }
        }

        $this->hFiles->savePermissions(
            $fileId,
            isset($this->options['hUserPermissionsOwner'])? $this->options['hUserPermissionsOwner'] : 'rw',
            isset($this->options['hUserPermissionsWorld'])? $this->options['hUserPermissionsWorld'] : 'r'
        );

        $userId = isset($this->options['hUserId'])? (int) $this->options['hUserId'] : 1;

        $this->hFiles->chown($fileId, $userId);

        $aliasExists = $this->hFileAliases->selectExists(
            'hFileAliasId',
            array(
                'hFileAliasPath' => $oldPath
            )
        );

        if (!$aliasExists)
        {
            $this->hFileAliases->insert(
                array(
                    'hFileId'            => $fileId,
                    'hFileAliasPath'     => $oldPath,
                    'hFileAliasRedirect' => 1,
                    'hFileAliasCreated'  => time(),
                    'hFileAliasExpires'  => 0
                )
            );
        }

        $this->rm($temporaryPath, true);

        return '{fileId:'.$fileId.'}';
    }

    private function stripHostName(&$path)
    {
        # @return void

        # @description
        # <h2>Stripping a Host Name From a URI</h2>
        # <p>
        #   <var>stripHostName</var> removes the host name of the site from the
        #   paths specified in HTML attributes. This is done to identify paths
        #   that point internally within the same website.
        # </p>
        # @end

        if (!empty($this->options['removeHostName']))
        {
            if (substr($path, 0, strlen('http://'.$this->removeHostName.'/')) == 'http://'.$this->removeHostName.'/')
            {
                $path = substr(
                    $path,
                    strlen('http://'.$this->removeHostName)
                );
            }

            if (substr($path, 0, strlen('https://'.$this->removeHostName.'/')) == 'https://'.$this->removeHostName.'/')
            {
                $path = substr(
                    $path,
                    strlen('https://'.$this->removeHostName)
                );
            }
        }

        if (!empty($this->removeIP))
        {
            if (substr($path, 0, strlen('http://'.$this->removeIP.'/')) == 'http://'.$this->removeIP.'/')
            {
                $path = substr(
                    $path,
                    strlen('http://'.$this->removeIP)
                );
            }

            if (substr($path, 0, strlen('https://'.$this->removeIP.'/')) == 'https://'.$this->removeIP.'/')
            {
                $path = substr(
                    $path,
                    strlen('https://'.$this->removeIP)
                );
            }
        }
    }

}

?>