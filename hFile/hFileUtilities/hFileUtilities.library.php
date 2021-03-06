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
# <h1>File Utilities</h1>
# <p>
#   This plugin provides an API for listing and/or manipulating framework files en masse,
#   for things like find and replace, deleteing, tidying up framework files.
# </p>
# @end

class hFileUtilitiesLibrary extends hPlugin {

    private $files = array();
    private $folders = array();

    private $excludeFileTypes = array();

    private $includeFileTypes = array(
        'php',
        'js',
        'css',
        'xml',
        'html',
        'json'
    );

    private $excludeFolders = array(
        'HTML',
        'XML',
        'JS',
        'CSS',
        'SQL',
        'XHTML',
        'TXT',
        'PHP',
        'Database'
    );

    private $excludeFiles = array(
        '.DS_Store',
        'Thumbs.db',
        'install',
        'hShell',
        'hot'
    );

    private $includeFolders = array();
    private $includeFolderMatches = array();
    private $includeFilesInFolders = array();

    private $matchFileNameType;
    private $matchFileName;

    private $replaceFiles = array();

    private $found = array();

    private $type = 'strstr';
    private $regexp = nil;

    private $scanTextFiles = true;

    private $textTypes = array(
        'PHP script text',
        'ASCII English text',
        'HTML document text',
        'ASCII text',
        'ASCII text, with very long lines',
        'ASCII text, with no line terminators',
        'ASCII text, with CRLF line terminators',
        'ASCII C++ program text, with CRLF line terminators',
        'ASCII C++ program text, with very long lines',
        'ASCII C++ program text',
        'ASCII c program text',
        'ASCII c program text, with CRLF line terminators',
        'ASCII c program text, with CRLF, LF line terminators',
        'ASCII text, with CRLF line terminators',
        'a /usr/bin/php -q script text executable',
        'empty',
        'XML  document text',
        'troff or preprocessor input text'
    );

    public function hConstructor($arguments)
    {
        ini_set('MAX_EXECUTION_TIME', 0);

        if (isset($arguments['fileTypes']) && is_array($arguments['fileTypes']))
        {
            $this->includeFileTypes = $arguments['fileTypes'];
        }

        if (isset($arguments['includeFileTypes']) && is_array($arguments['includeFileTypes']))
        {
            $this->includeFileTypes = $arguments['includeFileTypes'];
        }

        if (isset($arguments['excludeFileTypes']) && is_array($arguments['excludeFileTypes']))
        {
            $this->excludeFileTypes = $arguments['excludeFileTypes'];
        }

        if (isset($arguments['excludeFolders']) && is_array($arguments['excludeFolders']))
        {
            $this->excludeFolders = $arguments['excludeFolders'];
        }

        if (isset($arguments['excludeFiles']) && is_array($arguments['excludeFiles']))
        {
            $this->excludeFiles = $arguments['excludeFiles'];
        }

        if (isset($arguments['includeFolders']) && is_array($arguments['includeFolders']))
        {
            $this->includeFolders = $arguments['includeFolders'];
        }

        if (isset($arguments['includeFilesInFolders']) && is_array($arguments['includeFilesInFolders']))
        {
            $this->includeFilesInFolders = $arguments['includeFilesInFolders'];
        }

        if (isset($arguments['matchFileName']))
        {
            $this->matchFileName = $arguments['matchFileName'];
        }

        if (isset($arguments['scanTextFiles']))
        {
            $this->scanTextFiles = $arguments['scanTextFiles'];
        }

        if (isset($arguments['matchFileNameType']))
        {
            switch ($arguments['matchFileNameType'])
            {
                case 'exactly':
                case 'strstr':
                case 'stristr':
                case 'regexp':
                {
                    $this->matchFileNameType = $arguments['matchFileNameType'];
                    break;
                }
                default:
                {
                    $this->warning(
                        'Invalid matchFileNameType.',
                        __FILE__,
                        __LINE__
                    );
                }
            }
        }

        if (isset($arguments['replaceFiles']))
        {
            $this->replaceFiles = $arguments['replaceFiles'];
        }

        if (isset($arguments['scanFolder']))
        {
            $this->scanFiles($arguments['scanFolder']);
        }

        if (isset($arguments['scanFolders']) && is_array($arguments['scanFolders']))
        {
            $this->scanFolders($arguments['scanFolders']);
        }

        if (!empty($arguments['autoScanEnabled']) || !isset($arguments['autoScanEnabled']))
        {
            $this->scanFiles(
                $this->hServerDocumentRoot
            );

            $this->scanFiles(
                $this->hFrameworkPath.
                $this->hFrameworkPluginRoot('/Plugins')
            );

            $this->scanFiles(
                $this->hFrameworkPath.
                $this->hFrameworkApplicationRoot('/Applications')
            );
        }
    }

    public function setFileTypes(array $types)
    {
        # @return void

        # @description
        # <p>
        #   Deprecated: Use <a href='#setIncludeFileTypes' class='code'>setIncludeFileTypes()</a> instead.
        # </p>
        # @end

        $this->fileTypes = $types;
    }

    public function addFileType($type)
    {
        # @return void
        # @description
        # <p>
        #   Deprecated: Use <a href='#addIncludeFileType' class='code'>addIncludeFileType()</a> instead.
        # </p>
        # @end

        array_push(
            $this->fileTypes,
            $type
        );
    }

    public function setIncludeFilesTypes(array $types)
    {
        # @return void
        # @description
        # <h2>Setting Inclusion File Types</h2>
        # <p>
        #   Sets an array of extensions to include in a framework file scan, which
        #   includes the folders <var>/Hot Toddy</var> and <var>/Plugins</var>.
        # </p>
        # @end

        $this->includeFileTypes = $types;
    }

    public function addIncludeFileType($type)
    {
        # @return void
        # @description
        # <h2>Adding an Inclusion File Type</h2>
        # <p>
        #   Adds the specified file extension to the internal <var>$includeFileTypes</var> property,
        #   which will include the file extension in subsequent framework file scans.
        # </p>
        # @end

        array_push(
            $this->includeFileTypes,
            $type
        );
    }

    public function setExcludeFileTypes(array $types)
    {
        # @return void
        # @description
        # <h2>Setting Exclusion File Types</h2>
        # <p>
        #   Sets an array of file extensions to be excluded from a framework
        #   file scan.
        # </p>
        # @end

        $this->excludeFileTypes = $types;
    }

    public function addExcludeFileType($type)
    {
        # @return void

        # @description
        # <h2>Adding a File Type for Exclusion</h2>
        # <p>
        #   Adds the specified file extension to the internal <var>$excludeFileTypes</var> property,
        #   which will exclude the file extension from subsequent framework file scans.
        # </p>
        # @end

        array_push(
            $this->excludeFileTypes,
            $type
        );
    }

    public function setExcludeFolders(array $folders)
    {
        # @return void

        # @description
        # <h2>Setting Exclusion Folders</h2>
        # <p>
        #   Sets an array of folder names to be excluded from a framework file scan.
        # </p>
        # @end

        $this->excludeFolders = $folders;
    }

    public function addExcludeFolder($folder)
    {
        # @return void

        # @description
        # <h2>Adding an Exclusion Folder</h2>
        # <p>
        #   Adds the specified folder to the internal <var>$excludeFolders</var> property,
        #   which excludes all folders of that name from subsequent framework file scans.
        # </p>
        # @end

        array_push(
            $this->excludeFolders,
            $folder
        );
    }

    public function setExcludeFiles(array $files)
    {
        # @return void

        # @description
        # <h2>Setting Exclusion Files</h2>
        # <p>
        #   Sets an array of file names to be excluded from a framework file scan.
        # </p>
        # @end

        $this->excludeFiles = $files;
    }

    public function addExcludeFile($file)
    {
        # @return void

        # @description
        # <h2>Adding an Exclusion File</h2>
        # <p>
        #   Adds the specified file to the internal <var>$excludeFiles</var> property,
        #   which excludes all files of that name from subsequent framework file scans.
        # </p>
        # @end

        array_push(
            $this->excludeFiles,
            $file
        );
    }

    public function setMatchFileName($fileName)
    {
        # @return void

        # @description
        # <h2>Matching File Names</h2>
        # <p>
        #   Sets the internal <var>$matchFileName</var> property. This feature matches
        #   file names for inclusion using the include file extensions feature. How
        #   matching occurs depends on the value of the <var>$matchFileNameType</var>
        #   property, which can be one of: exactly, strstr, stristr, or regexp.
        # </p>
        # @end

        $this->matchFileName = $fileName;
    }

    public function setMatchFileNameType($type)
    {
        # @return void

        # @description
        # <h2>Matching File Name Type</h2>
        # <p>
        #
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td>exactly</td>
        #       </tr>
        #       <tr>
        #           <td>strstr</td>
        #       </tr>
        #       <tr>
        #           <td>stristr</td>
        #       </tr>
        #       <tr>
        #           <td>regexp</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        $this->matchFileNameType = $type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setRegExp($regexp)
    {
        $this->regexp = $regexp;
    }

    public function setScanTextFiles($scanTextFiles)
    {
        $this->scanTextFiles = (bool) $scanTextFiles;
    }

    public function findAndReplace($find, $replace = nil, $options = array())
    {
        if (!isset($options['dryRun']))
        {
            $options['dryRun'] = true;
        }

        if (isset($options['type']))
        {
            $this->type = $options['type'];
        }

        $this->found = array();

        foreach ($this->files as $file)
        {
            if (!$options['dryRun'] && is_writable($file) || $options['dryRun'] && is_readable($file))
            {
                $this->found[$file] = array();

                $fp = fopen($file, 'r');

                if ($fp)
                {
                    $content = '';

                    for ($i = 1; !feof($fp); $i++)
                    {
                        $line = fgets($fp);

                        if (is_array($find))
                        {
                            foreach ($find as $needle => $replace)
                            {
                                if ($this->scanLine($file, $line, $i, $needle, $replace) && !$options['dryRun'])
                                {
                                    $line = $this->replace(
                                        $needle,
                                        $replace,
                                        $line
                                    );
                                }
                            }
                        }
                        else
                        {
                            if ($this->scanLine($file, $line, $i, $find, $replace) && !$options['dryRun'])
                            {
                                $line = $this->replace(
                                    $find,
                                    $replace,
                                    $line
                                );
                            }
                        }

                        $content .= $line;
                    }

                    if (count($this->found[$file]) && !$options['dryRun'])
                    {
                        file_put_contents($file, $content);
                    }

                    fclose($fp);
                }
            }
            else
            {
                $this->warning(
                    "File '{$file}' is not writable, find and replace has failed!",
                    __FILE__,
                    __LINE__
                );
            }
        }

        return $this->found;
    }

    public function highlightMatches($matches)
    {
        return '{b}'.$matches[0].'{/b}';
    }

    public function replace($find, $replace, $content)
    {
        switch ($this->type)
        {
            case 'regexp':
            {
                return preg_replace(
                    $find,
                    $replace,
                    $content
                );
            }
            case 'stristr':
            {
                return str_ireplace(
                    $find,
                    $replace,
                    $content
                );
            }
            case 'strstr':
            default:
            {
                return str_replace(
                    $find,
                    $replace,
                    $content
                );
            }
        }
    }

    public function scanLine($file, $line, $lineNumber, $find, $replace)
    {
        $matches = array();
        $match = false;

        $matchHighlighted = nil;

        switch ($this->type)
        {
            case 'regexp':
            {
                if (!empty($line) && !empty($find))
                {
                    preg_match_all($find, $line, $matches);

                    if (count($matches) && is_array($matches[0]))
                    {
                        $match = true;

                        $matchHighlighted = preg_replace_callback(
                            $find,
                            array(
                                $this,
                                'highlightMatches'
                            ),
                            $line
                        );
                    }
                }

                break;
            }
            case 'stristr':
            {
                if (!empty($line) && !empty($find) && stristr($line, $find))
                {
                    $match = true;

                    $matchHighlighted = str_ireplace(
                        $find,
                        '{b}'.$find.'{/b}',
                        $line
                    );
                }

                break;
            }
            case 'strstr':
            default:
            {
                if (!empty($line) && !empty($find) && strstr($line, $find))
                {
                    $match = true;

                    $matchHighlighted = str_replace(
                        $find,
                        '{b}'.$find.'{/b}',
                        $line
                    );
                }
            }
        }

        if ($match && $this->isReplaceFile($file, $lineNumber))
        {
            $this->found[$file][] = array(
                'file' => $file,
                'line' => $line,
                'matchesHighlighted' => str_replace(
                    array(
                        '{b}',
                        '{/b}'
                    ),
                    array(
                        '<b>',
                        '</b>'
                    ),
                    htmlspecialchars($matchHighlighted)
                ),
                'lineNumber' => $lineNumber,
                'find' => $find,
                'replace' => $replace
            );

            return true;
        }

        return false;
    }

    private function isReplaceFile($path, $lineNumber)
    {
        if (!count($this->replaceFiles))
        {
            return true;
        }

        foreach ($this->replaceFiles as $i => $file)
        {
            if ($file['file'] == $path && $file['line'] == $lineNumber)
            {
                return true;
            }
        }

        return false;
    }

    public function scanFolders(array $paths)
    {
        foreach ($paths as $path)
        {
            $this->scanFiles($path);
        }
    }

    public function scanFiles($path)
    {
        $this->console("Scanning: '{$path}'");
        $this->_scanFiles($path);
        $this->console();
    }

    public function _scanFiles($path)
    {
        if (file_exists($path))
        {
            $files = scandir($path);

            foreach ($files as $file)
            {
                $this->console('.', false);

                if (substr($file, 0, 1) != '.')
                {
                    if (is_dir($path.'/'.$file))
                    {
                        if (count($this->excludeFolders) && in_array($file, $this->excludeFolders, true))
                        {
                            continue;
                        }

                        if (count($this->includeFolders) && in_array($file, $this->includeFolders, true))
                        {
                            $this->includeFolderMatches[] = $path.'/'.$file;
                        }

                        $this->folders[] = $path.'/'.$file;
                        $this->_scanFiles($path.'/'.$file);
                    }
                    else
                    {
                        if ($this->scanTextFiles)
                        {
                            $type = $this->pipeCommand(
                                '/usr/bin/file',
                                '-b '.escapeshellarg($path.'/'.$file)
                            );

                            if (!preg_match('/text/', $type))
                            {
                                continue;
                            }
                        }

                        $ext = '';

                        if (strstr($file, '.'))
                        {
                            $ext = $this->getExtension($file);
                        }

                        $parentFolder = basename($path);

                        if (count($this->includeFilesInFolders))
                        {
                            $foldersInPath = explode('/', $path);

                            $includeFile = false;

                            foreach ($this->includeFilesInFolders as $includeFolder)
                            {
                                foreach ($foldersInPath as $folderInPath)
                                {
                                    if ($folderInPath == $includeFolder)
                                    {
                                        $includeFile = true;
                                        break;
                                    }
                                }

                                if ($includeFile)
                                {
                                    break;
                                }
                            }

                            if (!$includeFile)
                            {
                                continue;
                            }
                        }

                        if (count($this->excludeFileTypes) && !empty($ext) && in_array($ext, $this->excludeFileTypes, true))
                        {
                            continue;
                        }

                        if (count($this->excludeFiles) && in_array($file, $this->excludeFiles, true))
                        {
                            continue;
                        }

                        if (!empty($this->matchFileName) && !empty($this->matchFileNameType))
                        {
                            $matched = false;

                            switch ($this->matchFileNameType)
                            {
                                case 'exactly':
                                {
                                    if ($file === $this->matchFileName)
                                    {
                                        $matched = true;
                                    }

                                    break;
                                }
                                case 'strstr':
                                {
                                    if (strstr($file, $this->matchFileName))
                                    {
                                        $matched = true;
                                    }

                                    break;
                                }
                                case 'stristr':
                                {
                                    if (stristr($file, $this->matchFileName))
                                    {
                                        $matched = true;
                                    }

                                    break;
                                }
                                case 'regexp':
                                {
                                    if (preg_match($this->matchFileName, $file))
                                    {
                                        $matched = true;
                                    }

                                    break;
                                }
                            }

                            if ($matched && (!count($this->includeFileTypes) || (count($this->includeFileTypes) && !empty($ext) && in_array($ext, $this->includeFileTypes, true))))
                            {
                                $this->files[] = $path.'/'.$file;
                            }
                        }
                        else if (!count($this->includeFileTypes) || (count($this->includeFileTypes) && !empty($ext) && in_array($ext, $this->includeFileTypes, true)))
                        {
                            $this->files[] = $path.'/'.$file;
                        }
                    }
                }
            }
        }
        else
        {
            $this->warning(
                "Unable to scan path '{$path}' because it does not exist.",
                __FILE__,
                __LINE__
            );
        }
    }

    public function resetFolders()
    {
        $this->folders = array();
    }

    public function resetFiles()
    {
        $this->files = array();
    }

    public function resetFilesAndFolders()
    {
        $this->folders = array();
        $this->files = array();
    }

    public function getFolders()
    {
        return $this->folders;
    }

    public function getIncludeFolderMatches()
    {
        return $this->includeFolderMatches;
    }

    public function getFiles()
    {
        sort($this->files);
        return $this->files;
    }

    public function move($files)
    {
        $results = array();

        if (is_array($files))
        {
            foreach ($files as $source => $destination)
            {
                $this->findTheRightPath($source); # (tm) and (c)
                $this->findTheRightPath($destination); # (tm) and (c)

                $results[] = array(
                    'mv'                 => `svn mv {$source} {$destination}`,
                    'commit'             => `svn commit -m 'Moved source {$source} to destination {$destination}'`,
                    'update source'      => `svn update {$source}`,
                    'update destination' => `svn update {$destination}`
                );
            }
        }

        return $results;
    }

    public function delete($file)
    {
        $results = array();

        if (is_array($file))
        {
            $files = $file;

            foreach ($files as $file)
            {
                $this->deleteFile($file, $results);
            }
        }
        else
        {
            $this->deleteFile($file, $results);
        }

        return $results;
    }

    private function deleteFile($file, &$results)
    {
        $this->findTheRightPath($file); # (tm) and (c)

        if (!empty($file) && $file != '/')
        {
            $results[] = array(
                'delete' => `svn delete {$file}`,
                'commit' => `svn commit {$file} -m 'Deleted file {$file}'`,
                'update' => `svn update {$file}`
            );

            if (file_exists($file))
            {
                # If it's still there, this'll delete it real good.
                is_dir($file)? `rm -rf {$file}` : `rm -f {$file}`;
            }
        }
    }

    public function findTheRightPath(&$file)
    {
        if (!file_exists($file))
        {
            $original = $file;

            $file = $this->getIncludePath($this->hServerDocumentRoot.'/'.$file);

            if (!file_exists($file))
            {
                $this->warning(
                    "Major bummer here, file '{$file}' does not exist!  I'm not able to ".
                    "shitcan it. So I'm bailing out dude. Better luck next time. ",
                    __FILE__,
                    __LINE__
                );
            }
        }
    }

    public function setAside($files)
    {
        # Export a copy of these files from the repository
        # Delete the file from the repository
        $path = $this->hFrameworkLibraryPath.'/Legacy';

        if (!file_exists($path))
        {
            mkdir($path);
        }

        $results = array();

        foreach ($files as $file)
        {
            $results[] = array(
                'export' => `svn export {$file} {$path}`,
                'delete' => `svn delete {$file}`,
                'commit' => `svn commit {$file} -m 'Deleted file {$file}, set aside to {$this->hFrameworkLibraryRoot}/Legacy'`
            );
        }

        return $results;
    }
}

?>