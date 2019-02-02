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

class hFinderSideColumn extends hPlugin {

    private $hFinderTree;

    public function hConstructor()
    {
        $this->getPluginFiles();
    }

    public function get()
    {
        //$this->hFinderTreeDefaultPath = '/'.$this->hFrameworkSite;
        $this->hFinderTreeHomeDirectory = false;

        //$this->hFinderTreeRootOverrideDefaultPath = false;

        $this->hFinderTree = $this->plugin('hFinder/hFinderTree');

        if ($this->hFinderSideColumnFilterCategories(true))
        {
            $this->hFinderTree->setFilterPaths(array('/Categories'), true);
        }

        $hFinderTree = $this->hFinderTree->getTree();

        $hFinderCategoryTree = '';
        $hFinderCategoryPath = '';

        if ($this->hFinderCategoriesEnabled(true))
        {
            $hFinderCategoryPath = isset($_GET['categoryPath'])? $_GET['categoryPath'] : '/Categories';

            $this->hFinderTree->setFilterPaths(array(), true);
            $this->hFinderTreeDefaultPath = $hFinderCategoryPath;
            $hFinderCategoryTree = $this->hFinderTree->getTree();
        }

        $hFinderServerTree = '';
        $hFinderServerPath = '/System/Server'.$this->hFinderSideColumnServerPath('');
        $hFinderServerName = $this->hOS == 'Darwin'? `scutil --get ComputerName` : `hostname`;

        # if ($this->hFinderSideColumnEnableServerView($this->inGroup('root')))
        # {
        #     $this->hFinderTreeDefaultPath = $hFinderServerPath;
        #     $this->hFinderTreeHomeDirectory = false;
        #     $this->hFinderTreeRootOverrideDefaultPath = false;
        #
        #     $hFinderServerTree = $this->hFinderTree->getTree();
        # }

        $hUserName = $_SESSION['hUserName'];

        $hFinderDefaultPath = $this->hFinderDefaultPath('/');

        if ($this->inGroup('root') && $this->hFinderRootOverrideDefaultPath(true))
        {
            $hFinderDefaultPath = '/';
        }

        return $this->getTemplate(
            'Side Column',
            array(
                'sideColumnWidth'              => $this->user->getVariable('hFinderSideColumnWidth', 204),
                'homeExists'                   => $this->getDirectoryId('/Users/'.$hUserName) > 0,
                'hFinderDiskName'              => $this->hFinderDiskName($this->hServerHost),
                'hFinderDefaultPath'           => $hFinderDefaultPath,
                'hFinderSideColumnPlaces'      => $this->hFinderSideColumnPlaces(true),
                'hFinderSideColumnPlaceServer' => $this->hFinderSideColumnPlaceServer($this->inGroup('root')),
                'hUserName'                    => $hUserName,
                'hFinderTree'                  => $hFinderTree,
                'hFinderCategoryPath'          => $hFinderCategoryPath,
                'hFinderCategoryTree'          => $hFinderCategoryTree,
                'hFinderServerTree'            => $hFinderServerTree,
                'hFinderServerName'            => $hFinderServerName,
                'hFinderServerPath'            => $hFinderServerPath,
                'hFinderSideColumnSmartFolders'=> $this->hFinderSideColumnSmartFolders(true)
            )
        );
    }
}

?>