<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Desktop Application Library
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

class hDesktopApplicationLibrary extends hPlugin {

    private $packagePath;
    private $packagePaths = array();

    public function makePackage($document, $applicationName, $applicationXML)
    {
        // Unlimited execution time...
        ini_set('max_execution_time', 0);

        $applicationsFolder = $this->hFrameworkApplicationPath;

        if (!file_exists($applicationsFolder))
        {
            if (is_writable($this->hFrameworkPath))
            {
                `mkdir {$applicationsFolder}`;
            }
            else
            {
                $this->warning(
                    "Unable to make Applications folder because ".$this->hFrameworkPath." is not writable.",
                    __FILE__,
                    __LINE__
                );
            }
        }

        $this->packagePath = $applicationsFolder.'/'.$applicationName;

        # Start over fresh each time...
        `rm -rf {$this->packagePath}`;

        if (!file_exists($this->packagePath))
        {
            `mkdir {$this->packagePath}`;
        }

        $subFolders = array(
            'Library',
            'JS',
            'CSS',
            'XML',
            'Files',
            'images'
        );

        foreach ($subFolders as $folder)
        {
            if (!file_exists($this->packagePath.'/'.$folder))
            {
                `mkdir {$this->packagePath}/{$folder}`;
            }
        }

        $this->parsePaths($document);

        file_put_contents($this->packagePath.'/'.$applicationName.'.html', $document);

        # Make a another package for the login page...
        $this->addDocumentToPackage('/Applications/User/Desktop Login.html', 'Login');

        # file_put_contents($this->packagePath.'/'.$applicationName.'-app.xml', $applicationXML);
    }

    public function getPackagePath()
    {
        return $this->packagePath;
    }

    public function addDocumentToPackage($path, $documentName)
    {
        $hUserPassword = $this->hUsers->selectColumn('hUserPassword', 1);

        $document = file_get_contents("http://{$this->hServerHost}{$path}?hUserId=1&hUserAuthenticationToken=1,".urlencode($hUserPassword)."&hDesktopApplication=1");
        $this->parsePaths($document);
        file_put_contents($this->packagePath.'/'.$documentName.'.html', $document);
    }

    public function parsePaths(&$document, $type = 'html')
    {
        # Automatically add XML CData wrappers where appropriate
        # if XHTML is enabled.
        if ($type == 'xhtml')
        {
            $document = preg_replace_callback(
                array(
                    '/(<style[^>]*?>)(.*?)(<\/style>)/siU',
                    '/(<script[^>]*?>)(.*?)(<\/script>)/si'
                ),
                array(
                    $this,
                    'XMLCDataCallback'
                ),
                $document
            );
        }

        if ($type == 'html' || $type == 'xhtml')
        {
            $document = preg_replace_callback(
                "/(href|action|src|background)\=(\'|\")(.*)(\'|\")/iU",
                array(
                    $this,
                    'attributePathCallback'
                ),
                $document
            );
        }

        if ($type == 'css' || $type == 'html' || $type == 'xhtml')
        {
            $document = preg_replace_callback(
                "/(url\()(\"|\')(.*)(\"|\')(\))/iU",
                array($this, 'CSSPathCallback'),
                $document
            );

            $document = preg_replace_callback(
                "/(\@import\s)(\'|\")(.*)(\'|\")/iU",
                array($this, 'CSSImportPathCallback'),
                $document
            );
        }
    }

    public function attributePathCallback($matches)
    {
        # $matches[1] // Attribute
        # $matches[2] // Left Quote
        # $matches[3] // Path
        # $matches[4] // Right Quote
        $attribute = $matches[1];
        $quote = $matches[2];
        $path = $matches[3];

        $document = '';
        $modifyDestinationPath = true;

        $destinationPath = $this->getDocument(
            $document,
            $path,
            $modifyDestinationPath
        );

        if (!in_array($this->packagePath.$destinationPath, $this->packagePaths) && !empty($document))
        {
            file_put_contents(
                $this->packagePath.$destinationPath,
                $document
            );

            array_push(
                $this->packagePaths,
                $this->packagePath.$destinationPath
            );
        }

        if ($modifyDestinationPath)
        {
            $destinationPath = substr($destinationPath, 1);
        }

        return $attribute.'='.$quote.$destinationPath.$quote;
    }

    public function CSSPathCallback($matches)
    {
        $opening = $matches[1];
        $quote   = $matches[2];
        $path    = $matches[3];
        $closing = $matches[5];

        $document = '';
        $modifyDestinationPath = true;

        $destinationPath = $this->getDocument(
            $document,
            $path,
            $modifyDestinationPath
        );

        if (!in_array($this->packagePath.$destinationPath, $this->packagePaths) && !empty($document))
        {
            file_put_contents(
                $this->packagePath.$destinationPath,
                $document
            );

            array_push(
                $this->packagePaths,
                $this->packagePath.$destinationPath
            );
        }

        if ($modifyDestinationPath)
        {
            $destinationPath = '..'.$destinationPath;
        }

        return $opening.$quote.$destinationPath.$quote.$closing;
    }

    public function CSSImportPathCallback($matches)
    {
        $import = $matches[1];
        $quote  = $matches[2];
        $path   = $matches[3];

        $document = '';
        $modifyDestinationPath = true;

        $destinationPath = $this->getDocument(
            $document,
            $path,
            $modifyDestinationPath
        );

        if (!in_array($this->packagePath.$destinationPath, $this->packagePaths) && !empty($document))
        {
            file_put_contents(
                $this->packagePath.$destinationPath,
                $document
            );

            array_push(
                $this->packagePaths,
                $this->packagePath.$destinationPath
            );
        }

        if ($modifyDestinationPath)
        {
            $destinationPath = basename($destinationPath);
        }

        return $import.$quote.$destinationPath.$quote;
    }

    public function XMLCDataCallback($matches)
    {
        if (!empty($matches[2]))
        {
            return $matches[1]."<![CDATA[\n".$matches[2]."]]>".$matches[3];
        }

        return $matches[0];
    }

    public function getDocument(&$document, $path, &$modifyDestinationPath)
    {
        $uri = parse_url($path);

        if (isset($uri['path']))
        {
            $extension = $this->getExtension($uri['path']);

            # Get the file contents...
            switch (true)
            {
                case file_exists($this->hFrameworkPath.'/Hot Toddy/'.$uri['path']):
                {
                    $fileSystemPath = $this->hFrameworkPath.'/Hot Toddy/'.$uri['path'];
                    break;
                }
                case $this->beginsPath($uri['path'], $this->hFrameworkLibraryRoot):
                {
                    # Get the file from the "/Library" folder
                    $fileSystemPath = $this->hFrameworkLibraryPath.$this->getEndOfPath($uri['path'], $this->hFrameworkLibraryRoot);
                    break;
                }
                case $this->beginsPath($uri['path'], '/images/icons'):
                {
                    $fileSystemPath = "http://".$this->hServerHost.$uri['path'];
                    $this->makeImagePath(dirname($uri['path']));
                    $destinationPath = $uri['path'];
                    break;
                }
                case $this->beginsPath($uri['path'], '/images'):
                {
                    $fileSystemPath = $this->hServerDocumentRoot.$uri['path'];
                    $this->makeImagePath(dirname($uri['path']));
                    $destinationPath = $uri['path'];
                    break;
                }
                case $this->beginsPath($uri['path'], $this->hFrameworkApplicationRoot):
                {
                    $fileSystemPath = "http://".$this->hServerHost.$uri['path'];
                    break;
                }
                default:
                {
                    $fileSystemPath = '';
                    $destinationPath = $uri['path'];
                    $modifyDestinationPath = false;
                }
            }

            $bits = explode('/', $uri['path']);
            $file = array_pop($bits);

            $parse = '';

            switch ($extension)
            {
                case 'js':
                {
                    if ($file == 'AIRAliases.js')
                    {
                        $destinationPath = $file;
                        $modifyDestinationPath = false;
                    }
                    else
                    {
                        $destinationPath = '/JS/'.$file;
                    }

                    #$this->parsePaths($document, 'js');
                    break;
                }
                case 'css':
                {
                    $destinationPath = '/CSS/'.$file;
                    $parse = 'css';
                    break;
                }
                case 'xml':
                {
                    $destinationPath = '/XML/'.$file;
                    break;
                }
            }

            if (isset($destinationPath))
            {
                if (!in_array($this->packagePath.$destinationPath, $this->packagePaths))
                {
                    if (!empty($fileSystemPath) && (file_exists($fileSystemPath) || substr($fileSystemPath, 0, strlen('http://')) == 'http://'))
                    {
                        $document = file_get_contents($fileSystemPath);
                    }
                }

                if (!empty($parse))
                {
                    $this->parsePaths($document, $parse);
                }

                return $destinationPath;
            }
            else
            {
                $modifyDestinationPath = false;
                return $uri['path'];
            }
        }
        else
        {
            $modifyDestinationPath = false;
            return $path;
        }
    }

    public function makeImagePath($imageFolder)
    {
        $imageFolders = explode('/', $imageFolder);

        array_shift($imageFolders);

        $currentPath = '';

        foreach ($imageFolders as $folder)
        {
            $currentPath .= '/'.$folder;

            if (!file_exists($this->packagePath.$currentPath))
            {
                `mkdir {$this->packagePath}{$currentPath}`;
            }
        }
    }
}

?>