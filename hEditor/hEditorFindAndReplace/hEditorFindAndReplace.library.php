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

class hEditorFindAndReplaceLibrary extends hPlugin {

    private $hFileUtilities;

    private $type;

    public function hConstructor($arguments)
    {
        if (isset($arguments['includeFileTypes']))
        {
            $options['includeFileTypes'] = $arguments['includeFileTypes'];
        }
        else
        {
            $options['includeFileTypes'] = array();
        }

        if (isset($arguments['excludeFileTypes']))
        {
            $options['excludeFileTypes'] = $arguments['excludeFileTypes'];
        }

        if (isset($arguments['matchFileName']))
        {
            $options['matchFileName'] = hString::decodeEntitiesAndUTF8($arguments['matchFileName']);
        }

        if (isset($arguments['matchFileNameType']))
        {
            $options['matchFileNameType'] = $arguments['matchFileNameType'];
        }

        if (!isset($arguments['scanFolder']) && !isset($arguments['scanFolders']))
        {
            $arguments['scanFolders'] = array(
                $this->hFrameworkPath.'/Hot Toddy',
                $this->hFrameworkPath.$this->hFrameworkPluginRoot('/Plugins')
            );
        }

        if (isset($arguments['scanFolder']))
        {
            $options['scanFolder'] = $arguments['scanFolder'];
        }

        if (isset($arguments['scanFolders']) && is_array($arguments['scanFolders']))
        {
            $folders = $arguments['scanFolders'];

            foreach ($folders as $i => $folder)
            {
                if ($this->isServerPath($folders[$i]))
                {
                    hString::safelyDecodeURL($folders[$i]);

                    $folders[$i] = $this->getServerFileSystemPath($folders[$i]);
                }
            }

            $options['scanFolders'] = $folders;
        }

        if (isset($arguments['type']))
        {
            $this->type = $arguments['type'];
        }

        if (isset($arguments['replaceFiles']))
        {
            $files = $arguments['replaceFiles'];

            $replaceFiles = array();

            $i = 0;

            foreach ($files as $file)
            {
                hString::safelyDecodeURL($file);

                list($file, $line) = explode(':', $file);

                $replaceFiles[$i] = array(
                    'file' => $file,
                    'line' => $line
                );

                $i++;
            }

            $options['replaceFiles'] = $replaceFiles;
        }

        $options['autoScanEnabled'] = false;

        $this->hFileUtilities = $this->library('hFile/hFileUtilities', $options);
    }

    public function find($find)
    {
        return $this->prepareResults(
            $this->hFileUtilities->findAndReplace(
                $find,
                nil,
                array(
                    'dryRun' => true,
                    'type' => $this->type
                )
            )
        );
    }

    public function replace($find, $replace)
    {
        return $this->prepareResults(
            $this->hFileUtilities->findAndReplace(
                $find,
                $replace,
                array(
                    'dryRun' => false,
                    'type' => $this->type
                )
            )
        );
    }

    # Results are formatted, so that they are suitable to use directly in a
    # template.

    private function prepareResults($results)
    {
        $items = array();

        foreach ($results as $file => $matches)
        {
            if (is_array($matches) && count($matches))
            {
                foreach ($matches as $match)
                {
                    if (is_array($match) && count($match))
                    {
                        array_push($items, $match);
                    }
                }
            }
        }

        return $items;
    }
}

?>