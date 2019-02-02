<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Category Service
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

class hCategoryService extends hService {

    private $hCategoryDatabase;
    private $hFile;

    public function hConstructor()
    {
        # @return JSON

        # @description
        # <h2>Category Listener Constructor</h2>
        # <p>
        #
        # </p>
        # @end

        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }

        if (!$this->inGroup('Website Administrators'))
        {
            $this->JSON(-1);
            return;
        }
    }

    public function newCategory()
    {
        # @return JSON

        # @description
        # <h2>Creating a Category</h2>
        # <p>
        #
        # </p>
        # @end

        $this->hCategoryDatabase = $this->database('hCategory');
        $this->hFile = $this->library('hFile');

        // Yet another way to create categories.
        if (!isset($_GET['hCategoryParentId']))
        {
            $this->JSON(-5);
            return;
        }

        if (empty($_GET['hCategoryName']))
        {
            $this->JSON(-5);
            return;
        }

        // Get the category's file path
        $categoryParentId = (int) $_GET['hCategoryParentId'];

        if (!$this->hCategories->hasPermission($categoryParentId, 'rw'))
        {
            $this->JSON(-1);
            return;
        }

        $categoryPath = $this->getCategoryPath($categoryParentId);
        $categoryName = $_GET['hCategoryName'];

        $categoryNewPath = $this->getConcatenatedPath(
            $categoryPath,
            $categoryName
        );

        // See if the category already exists...
        if (!$this->categoryExists($categoryNewPath))
        {
            $categoryId = $this->hFile->newDirectory(
                $categoryPath,
                $categoryName
            );

            preg_match('/\d+/', $categoryId, $matches);

            $this->JSON(
                array(
                    'hCategoryId' => $matches[0],
                    'hCategoryParentId' => (string) $categoryParentId,
                    'hCategoryName' => $categoryName
                )
            );

            return;
        }
        else
        {
            // A category already exists...
            $this->JSON(-3);
            return;
        }
    }

    public function deleteCategories()
    {
        # @return JSON

        # @description
        # <h2>Deleting Categories</h2>
        # <p>
        #
        # </p>
        # @end

        $this->hFile = $this->library('hFile');

        if (!isset($_GET['hCategories']) || !is_array($_GET['hCategories']))
        {
            $this->JSON(-5);
            return;
        }

        foreach ($_GET['hCategories'] as $categoryId)
        {
            if (!$this->hCategories->hasPermission($categoryId, 'rw'))
            {
                $this->JSON(-1);
                return;
            }
        }

        foreach ($_GET['hCategories'] as $categoryId)
        {
            $this->hFile->delete($this->getCategoryPath((int) $categoryId));
        }

        $this->JSON(1);
        return;
    }
}

?>