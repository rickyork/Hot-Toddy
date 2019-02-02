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
# <h1>Category Plugin</h1>
# <p>
#   Creates a GUI for navigating category and file associations within those categories.
# </p>
# @end

class hCategory extends hPlugin {

    private $hCategoryDatabase;
    private $hFileIcon;

    public function hConstructor()
    {
        # @return void

        # @description
        # <h2>Category Constructor</h2>
        # <p>
        #
        # </p>
        # @end

        hString::scrubArray($_GET);

        $this->hCategoryDatabase = $this->database('hCategory');

        $this->hCategoryDatabase->setDatabaseReturnFormat('getResultsForTemplate');

        $this->hCategoryRootId = $this->hCategoryId(0);

        $this->hCategoryId = isset($_GET['hCategoryId'])? (int) $_GET['hCategoryId'] : (int) $this->hCategoryId(0);

        if ($this->hCategoryDatabase->categoryExists($this->hCategoryId))
        {
            $this->getPluginCSS();

            $this->hCategoryName = $this->hCategoryDatabase->getCategoryName($this->hCategoryId);

            if (!$this->hCategoryId && $this->hCategoryDefaultName('Categories'))
            {
                $this->hCategoryName = $this->hCategoryDefaultName('Categories');
            }

            $fileTitle = $this->hCategoryDefaultName($this->hCategoryName);

            $this->getSubCategories();
        }
        else
        {
            $this->warning("There is no category associated with id '{$this->hCategoryId}'.", __FILE__, __LINE__);
        }

        if ($this->hFileBreadcrumbsEnabled(false))
        {
            $this->setCategoryBreadcrumbs();
        }

        if (isset($fileTitle))
        {
            $this->hFileTitle = $fileTitle;
        }
    }

    public function getSubCategories()
    {
        # @return HTML

        # @description
        # <h2>Getting Sub Categories</h2>
        # <p>
        #
        # </p>
        # @end

        $fileMethod = $this->hCategoryFileRetrievalMethod('getCategoryFiles');

        $files = $this->hCategoryDatabase->{"{$fileMethod}"}($this->hCategoryId);

        $this->hFileDocument = $this->getTemplate(
            'Category',
            array(
                'hCategories'        => $this->hCategoryDatabase->getSubCategories($this->hCategoryId),
                'hCategoryCount'     => $this->hCategoryDatabase->getCategoryCount(),
                'hFiles'             => $files,
                'hCategoryFileCount' => $this->hCategoryDatabase->getCategoryFileCount()
            )
        );
    }

    public function setCategoryBreadcrumbs()
    {
        # @return void

        # @description
        # <h2>Setting Breadcrumbs for Categories</h2>
        # <p>
        #   Sets breadcrumbs for the selected category.
        # </p>
        # @end

        $categories = array();

        //$categories[$this->hFilePath.'?hCategoryId='.$this->hCategoryRootId] = $this->hCategoryDatabase->getCategoryName($this->hCategoryRootId);

        $line = array();

        $categoryId = $this->hCategoryId;

        for ($i = 0; $categoryId <> $this->hCategoryRootId; $i++)
        {
            $data = $this->hCategories->selectAssociative(
                array(
                    'hCategoryId',
                    'hCategoryName',
                    'hCategoryParentId'
                ),
                (int) $categoryId
            );

            if (count($data))
            {
                $line[$data['hCategoryId']] = $data['hCategoryName'];
                $categoryId = $data['hCategoryParentId'];
            }

            if ($i == 50)
            {
                break;
            }
        }

        $line = array_reverse($line, true);

        foreach ($line as $categoryId => $categoryName)
        {
            $categories[$this->hFilePath.'?hCategoryId='.$categoryId] = $categoryName;
        }

        $this->makeBreadcrumbs($categories, true);
    }

}

?>