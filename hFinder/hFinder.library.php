<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Finder Library
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

class hFinderLibrary extends hPlugin {

    private $hFile;
    private $hFinderIcons;
    private $hImage;
    private $hFinderView;

    public $fileCount = 0;

    // The following arrays define which image formats hFinder supports
    // for thumbnail generation, depending on the image library used.
    public $thumbnailFormats = array();

    public function hConstructor()
    {
        if ($this->hFinderLoadPluginFiles(true))
        {
            $this->getPluginFiles();
            $this->getPluginJavaScript('template');
            $this->getPluginCSS('/Applications/Finder/Icons', true);
        }

        $this->hFile = $this->library('hFile');
        $this->hFinderIcons = $this->library('hFinder/hFinderIcons');
        $this->hImage = $this->library('hImage');

        $this->thumbnailFormats = $this->hImage->getSupportedFormats();

        if (is_array($this->hFinderFilterPaths) && !$this->inGroup('root'))
        {
            $this->hFile->setFilterPaths(
                $this->hFinderFilterPaths
            );
        }
    }

    public function validateView(&$view)
    {
        # @return string

        # @description
        # <h2>Validating the View</h2>
        # <p>
        #
        # </p>
        # @end

        if (isset($view) && !empty($view))
        {
            switch ($view)
            {
                case 'Icons':
                case 'Columns':
                case 'CoverFlow':
                case 'Tiles':
                case 'Details':
                case 'XDetails':
                case 'Table':
                case 'List':
                case 'Flat':
                {
                    break;
                }
                default:
                {
                    $view = '';
                }
            }
        }
        else
        {
            $view = '';
        }

        return $view;
    }

    private function getNodeClassNames(&$file, &$class, &$wrapperClass, &$iconClass, $res)
    {
        # @return void

        # @description
        # <h2>Getting Node Class Names</h2>
        # <p>
        #
        # </p>
        # @end

        $class = '';
        $wrapperClass = '';
        $iconClass = '';

        if (substr($file['hFileName'], 0, 1) == '.')
        {
            $class .= ' hFinderHidden';
            $wrapperClass .= ' hFinderHiddenWrapper';
        }

        if ($file['hFileIsServer'])
        {
            $class .= ' hFinderServer';
            $wrapperClass .= ' hFinderServerWrapper';
        }

        if (!empty($file['hFileLabel']) && $file['hFileLabel'] != 'none')
        {
            $class .= ' hFinderLabel hFinderLabel'.ucwords($file['hFileLabel']);
        }

        if ($file['isDirectory'])
        {
            $iconClass .= ' '.$this->hFinderIcons->getIconClassName(
                $this->hFile->getDirectoryPseudoMIME(
                    $file['hFilePath'],
                    !empty($file['hFileIconId'])? $file['hFileIconId'] : 0
                ),
                $res,
                ''
            );

            if (!empty($file['hDirectoryIsApplication']))
            {
                $class .= ' hFinderApplication';
            }
        }
        else
        {
            if ($this->fileCount == 1)
            {
                $class .= ' hFinderFirstNode';
            }

            if ($file['hasThumbnail'])
            {
                $iconClass .= ' hFinderIconThumbnail';
            }

            $iconClass .= ' '.$this->hFinderIcons->getIconClassName(
                $file['hFileMIME'],
                $res,
                $file['hFileName']
            );

            $this->fileCount++;
        }
    }

    public function getSortedDirectory($path, $sortBy = 'name', $asc = true, $link = false, $res = '48x48', $searchTerms = null)
    {
        # @return array

        # @description
        # <h2>Retrieving a Sorted Directory</h2>
        # <p>
        #
        # </p>
        # @end

        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);

        $thumbnailGenerator = $this->getFilePathByPlugin('hFile/hFileThumbnail');

        // Name
        // Date Modified
        // Date Created
        // Size
        // Kind
        // Label
        if (!is_array($path))
        {
            switch ($path)
            {
                case 'Search':
                {
                    $directories = array();

                    $files = $this->hFile->search(
                        '/Applications/Finder/index.html',
                        $searchTerms
                    );

                    break;
                }
                case 'Images':
                case 'Movies':
                case 'Documents':
                case 'Time':
                {
                    $directories = array();

                    $files = $this->hFile->searchByPreset(
                        '/Applications/Finder/index.html',
                        $path,
                        $searchTerms
                    );

                    break;
                }
                default:
                {
                    $directories = $this->hFile->getAllDirectories($path);

                    $files = $this->hFile->getAllFiles($path);
                }
            }
        }

        if ($directories == 403 || $files == 403)
        {
            return 403;
        }

        $this->hFinderDirectoryCount = count($directories);
        $this->hFinderFileCount = count($files);

        // Bumfuckers.  array_merge does not maintain index association,
        // meaning, if it were used, directories named with just numbers
        // get lost.
        //$files = array_merge($files, $directories);

        foreach ($directories as $key => $value)
        {
            $files[$key] = $value;
        }

        $sorted = array();

        foreach ($files as $file)
        {
            $sort = '';

            switch ($sortBy)
            {
                case 'name':
                {
                    $sort = $file['hFileName'];
                    break;
                }
                case 'kind':
                {
                    $sort = isset($file['hDirectoryIsApplication'])? 'directory' : $this->getExtension($file['hFileName']);
                    break;
                }
                case 'created':
                {
                    $sort = $file['hFileCreated'];
                    break;
                }
                case 'modified':
                {
                    $sort = $file['hFileLastModified'];
                    break;
                }
                case 'label':
                {

                }
                case 'category':
                {
                    $sort = $file['hCategoryFileSortIndex'];
                    break;
                }
                case 'size':
                {
                    $sort = $file['hFileSize'];
                    break;
                }
                case 'none':
                {
                    $sort = null;
                    break;
                }
            }

            $sorted[$file['hFileName']] = $sort;
        }

        if ($sortBy != 'none')
        {
            ($asc)? asort($sorted) : arsort($sorted);
        }

        foreach ($sorted as $name => $sort)
        {
            if (isset($files[$name]))
            {
                $file = $files[$name];

                if (!isset($file['hFileIsServer']))
                {
                    $file['hFileIsServer'] = false;
                }

                $file['hasThumbnail'] = false;
                $ext = $this->getExtension($file['hFileName']);

                $file['hFileExtension'] = $ext;
                $file['isDirectory'] = ($file['hFileMIME'] == 'directory');

                $file['hasThumbnail'] = (!$file['isDirectory'] && in_array($ext, $this->thumbnailFormats));

                if ($file['hasThumbnail'])
                {
                    $file['hFinderThumbnailPath'] =
                        ($this->hDesktopApplicationStyle? "http://{$this->hServerHost}" : '').
                        $thumbnailGenerator.'?'.
                            'path='.urlencode($this->getEncodedPath($file['hFilePath'], false)).
                            '&hFileLastModified='.$file['hFileLastModified'];
                }
                else
                {
                    $file['hFinderThumbnailPath'] = '';
                }

                $fileSymbolicLinkTo = false;

                if (!isset($file['hFileTitle']))
                {
                    $file['hFileTitle'] = '';
                }

                if (!isset($file['hFileId']))
                {
                    $file['hFileId'] = 0;
                }

                if (!$file['hFileIsServer'] && !empty($file['hFileId']))
                {
                    $file['hFileTitle'] = $this->hFileHeadingTitle($file['hFileTitle'], $file['hFileId']);

/*
                    if ($fileSymbolicLinkTo = $this->hFileSymbolicLinkTo(false, $file['hFileId']))
                    {
                        $file['hFileTitle']       = $this->getFileTitle($fileSymbolicLinkTo);
                        $file['hFileDescription'] = $this->getFileDescription($fileSymbolicLinkTo);
                    }
*/
                }

                $file['hFileSymbolicLinkTo'] = $fileSymbolicLinkTo;

                $this->getNodeClassNames(
                    $file,
                    $finderClass,
                    $finderWrapperClass,
                    $finderIconClass,
                    $res
                );

                $file['hFinderClass']        = $finderClass;
                $file['hFinderWrapperClass'] = $finderWrapperClass;
                $file['hFinderIconClass']    = $finderIconClass;

                $file['hFileLastModifiedDate'] = nil;

                if ($file['hFileLastModified'] > 0)
                {
                    $file['hFileLastModifiedDate'] = date('m/d/y h:i a', $file['hFileLastModified']);
                }

                $file['hFileCreatedDate'] = nil;

                if ($file['hFileCreated'] > 0)
                {
                    $file['hFileCreatedDate'] = date('m/d/y h:i a', $file['hFileCreated']);
                }

                $file['hFileSize'] = nil;

                if (isset($file['hFileSize']))
                {
                    $file['hFileSize'] = $this->bytes($file['hFileSize']);
                }

                $file['hFilePath'] = $this->getEncodedPath($file['hFilePath']);

                if (isset($file['hDirectoryCount']))
                {
                    $file['hasChildren'] = ($file['hDirectoryCount'] > 0 || $file['hFileCount'] > 0);
                }
                else
                {
                    $file['hasChildren'] = false;
                }

                if (empty($file['hFileMIME']))
                {
                    $file['hFileMIME'] = $ext;
                }

                if (isset($file['hFileDescription']))
                {
                    $file['hFileDescription'] = strip_tags(
                        hString::decodeHTML($file['hFileDescription'])
                    );
                }
                else
                {
                    $file['hFileDescription'] = '';
                }

                if (!isset($file['hDirectoryId']))
                {
                    $file['hDirectoryId'] = 0;
                }

                $file['hDesktopApplicationStyle'] = $this->hDesktopApplicationStyle(false);
                $file['hFileLink'] = $link;

                $sorted[$name] = $file;
            }
        }

        return $this->hDatabase->getResultsForTemplate($sorted);
    }

    public function getEncodedPath($filePath, $toUTF8 = true)
    {
        # @return string

        # @description
        # <h2>Getting an Encoded File Path</h2>
        # <p>
        #
        # </p>
        # @end

        $paths = explode('/', $toUTF8 ? hString::entitiesToUTF8($filePath, false) : $filePath);

        foreach ($paths as &$path)
        {
            $path = urlencode($path);
        }

        return implode('/', $paths);
    }

    public function search($searchTerms)
    {
        # @return string

        # @description
        # <h2>Searching Files</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->getTemplate(
            'Icons',
            array(
                'hFiles' => $this->getSortedDirectory(
                    'Search',
                    'none',
                    true,
                    false,
                    '48x48',
                    $searchTerms
                )
            )
        );
    }

    public function searchByPreset($type, $time = array())
    {
        # @return string

        # @description
        # <h2>Searching By Preset</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->getTemplate(
            'Icons',
            array(
                'hFiles' => $this->getSortedDirectory(
                    $type,
                    'none',
                    true,
                    false,
                    '48x48',
                    $time
                )
            )
        );
    }

    public function getDirectory($path, $view, $sortBy = 'name', $asc = true, $link = false)
    {
        # @return string

        # @description
        # <h2>Getting a Directory</h2>
        # <p>
        #
        # </p>
        # @end

        $html = '';

        $this->user->saveVariable('hFinderPath', $path);

        $this->hFile->query($path);

        if ($this->hFile->exists($path) && $this->hFile->isDirectory)
        {
            if ($this->hFile->userIsReadAuthorized)
            {
                $res = $this->getIconResolution($view);

                $files = $this->getSortedDirectory($path, $sortBy, $asc, $link, $res);

                if ($files == 403)
                {
                    $html .= $this->getFinderError(403);
                }
                else
                {
                    switch ($view)
                    {
                        case 'Columns':
                        {
                            $html .= $this->getTemplate(
                                'Columns',
                                array(
                                    'hFilePath' => $this->getEncodedPath($path),
                                    'hFiles' => $files
                                )
                            );
                            break;
                        }
                        case 'List':
                        case 'CoverFlow':
                        {
                            $html .= $this->getTemplate(
                                'List',
                                array(
                                    'hFilePath' => $this->getEncodedPath($path),
                                    'hFiles' => $files
                                )
                            );
                            break;
                        }
                        case 'Icons':
                        case 'Tiles':
                        default:
                        {
                            $html .= $this->getTemplate(
                                'Icons',
                                array(
                                    'hFiles' => $files
                                )
                            );
                        }
                    }
                }
            }
            else
            {
                $html .= $this->getFinderError(
                    $this->isLoggedIn() ? 403 : 401
                );
            }
        }
        else
        {
            $html .= $this->getFinderError(404);
        }

        return $html;
    }

    public function &getHeaders()
    {
        # @return void

        # @description
        # <h2>Getting Finder CSS and JavaScript</h2>
        # <p>
        #
        # </p>
        # @end

        $this->getPluginFiles();
        return $this;
    }

    public function getIconResolution($view)
    {
        # @return string

        # @description
        # <h2>Getting an Icon's Resolution</h2>
        # <p>
        #
        # </p>
        # @end

        switch ($view)
        {
            case 'XDetails':
            case 'Icons':
            case 'Details':
            {
                return '48x48';
            }
            case 'Tiles':
            {
                return '32x32';
            }
            case 'Table':
            case 'List':
            case 'CoverFlow':
            case 'Columns':
            {
                return '16x16';
            }
            case 'Flat':
            {
                return '16x16';
            }
        }
    }

    private function get($code)
    {
        switch ($code)
        {
            case 404:
            {
                $error = 'File not found';
                $text  = "The server could not find the folder or file that you requested.";
                break;
            }
            case 401:
            {
                $error = 'Unauthorized';
                $text  = "It looks like you've been logged at, please refresh and login again to access the file system.";
                break;
            }
            case 403:
            {
                $error = 'Access Denied';
                $text  = "You don't have access to this folder or file, try clicking on a different folder or file.";
                break;
            }
        }

        return $this->getTemplate(
            'Error',
            array(
                'error' => $error,
                'text' => $text
            )
        );
    }

    public function getBottomBox($class, $titles, $html, $boxes = '')
    {
        $box = '';

        if (is_array($titles) && is_array($html) && is_array($boxes))
        {
            foreach ($titles as $i => $title)
            {
                $box .= $this->getInnerBottomBox($title, $html[$i], $boxes[$i]);
            }
        }
        else
        {
            $box .= $this->getInnerBottomBox($titles, $html, $boxes);
        }

        return $this->getTemplate(
            'Bottom Box',
            array(
                'class' => $class,
                'box' => $box
            )
        );
    }

    private function getInnerBottomBox($title, $html, $box = '')
    {
        if (!empty($box))
        {
            $box = ' '.$box;
        }

        return $this->getTemplate(
            'Inner Bottom Box',
            array(
                'box' => $box,
                'title' => $title,
                'html' => $html
            )
        );
    }
}

?>