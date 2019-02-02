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

class hFinderTree extends hPlugin {

    private $hFinder;
    private $hFinderTree;
    private $hFile;

    private $tree;

    public function hConstructor()
    {
        if ($this->hFinderTreeStandAlone(false))
        {
            $this->getPluginJavaScript('hFinder');
            //$this->plugin('hFinder/hFinderDnD');
        }

        //$this->hFinder     = $this->library('hFinder');
        $this->hFinderTree = $this->library('hFinder/hFinderTree');
        $this->hFile       = $this->library('hFile');

        if ($this->hFinderTreeLoadPluginFiles(true))
        {
            $this->getPluginFiles();
        }

        if ($this->hFinderTreeStandAlone(false))
        {
            if ($this->hFinderTreeSetTemplate(true))
            {
                $this->hTemplatePath = '/hFinder/hFinderTree/hFinderTree.template.php';
            }

            $this->hFileCSS .= $this->getTemplate('Icons');
        }

        $this->hFileJavaScript .= $this->getTemplate(
            'Configuration',
            array(
                'hFinderTreeDisplayFiles'    => $this->hFinderTreeDisplayFiles(false)?    'true' : 'false',
                'hFinderTreeLoadingActivity' => $this->hFinderTreeLoadingActivity(false)? 'true' : 'false'
            )
        );
    }

    public function setFilterPaths(array $paths, $override = false)
    {
        if ($override || !$this->inGroup('root'))
        {
            $this->hFinderTree->setFilterPaths($paths);
        }
    }

    public function setFileTypes(array $fileTypes)
    {
        $this->hFinderTree->setFileTypes($fileTypes);
    }

    public function getTree()
    {
        if ($this->hDesktopApplication(false))
        {
            $html = $this->getTemplate(
                'Tree',
                array(
                    'hFinderTree' => ''
                )
            );
        }
        else
        {
            $html = $this->getTemplate(
                'Tree',
                array(
                    'hFinderTree' => $this->hFinderTree->getTree()
                )
            );

            if ($this->hFinderTreeLoadingActivity(false))
            {
                $html .= $this->getTemplate('Activity');
            }
        }

        return $html;
    }
}

?>