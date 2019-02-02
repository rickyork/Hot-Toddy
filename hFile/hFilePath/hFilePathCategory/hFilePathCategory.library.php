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
# <h1>File Path Category API</h1>
# @end

class hFilePathCategoryLibrary extends hPlugin {

    public function isCategoryPath($path)
    {
        # @return boolean

        # @description
        # <h2>Determine Category Paths</h2>
        # <p>
        #   Categories allow a single file to be categorized in multiple locations with
        #   multiple tags, or groupings, or whatever a category might be used for.
        #   Categorization hierarchies in HtFS can be expressed and accessed as file
        #   paths and accessed via URLs.  <var>isCategoryPath()</var> determines whether
        #   or not a path is a category path.
        # </p>
        # @end

        return ($this->beginsPath($path, '/Categories') || $this->isHomeCategoryPath($path));
    }

    public function isHomeCategoryPath($path)
    {
        # @return boolean

        # @description
        # <h2>Determine Home Category Paths</h2>
        # <p>
        #   Categories can be created for individual users, so applications can categorize
        #   files on per-user basis. User-based categorization is tied to a dynamic folder
        #   in the user's home folder located in HtFS at <var>/Users/<i>User Name</i>/Categories</var>.
        #   User home folders can be enabled/disabled on demand.  See the <var>hUser</var> family of
        #   plugins for more information.
        # </p>
        # @end

        return preg_match('!^/Users/.+/Categories/.+$!', $path);
    }

    public function getCategoryPath($categoryId)
    {
        # @return string

        # @description
        # <h2>Get Category Path</h2>
        # <p>
        #   Takes the supplied <var>categoryId</var> and returns an HtFS path for it.
        # </p>
        # @end

        $categories = array();

        if ($categoryId > 0)
        {
            while ($categoryId > 0)
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
                    array_push($categories, $data['hCategoryName']);
                    $categoryId = $data['hCategoryParentId'];
                }
            }

            return '/Categories/'.implode('/', array_reverse($categories));
        }
        else
        {
            return '/Categories';
        }
    }

    public function getCategoryIdFromPath($filePath)
    {
        # @return integer

        # @description
        # <h2>Get Category Id From Path</h2>
        # <p>
        #   Returns a categoryId from the supplied HtFS path.
        # </p>
        # @end

        if (empty($filePath) || !strstr($filePath, '/'))
        {
            return 0;
        }

        $categories = explode('/', $filePath);

        if (!isset($categories[0]) || !is_array($categories))
        {
            return 0;
        }

        array_shift($categories);

        if ($categories[0] == 'Categories')
        {
            array_shift($categories);
        }
        else if ($categories[0] == 'Users')
        {
            array_shift($categories);
            array_shift($categories);
        }
        else
        {
            return 0;
        }

        $parentId = 0;

        if (count($categories))
        {
            foreach ($categories as $category)
            {
                $query = $this->hCategories->selectQuery(
                    'hCategoryId',
                    array(
                        'hCategoryName'     => $category,
                        'hCategoryParentId' => $parentId
                    )
                );

                if ($this->hDatabase->resultsExist($query))
                {
                    $parentId = $this->hDatabase->getColumn($query);
                }
                else
                {
                    # The category does not exist.
                    return false;
                }
            }
        }

        return $parentId;
    }

    public function categoryExists($categoryPath)
    {
        # @return boolean

        # @description
        # <h2>Determining a Category Exists</h2>
        # <p>
        #   Determines if the supplied <var>$categoryPath</var> exists.
        # </p>
        # @end

        if ($categoryPath == '/Categories')
        {
            return true;
        }
        else
        {
            return $this->getCategoryIdFromPath($categoryPath) > 0;
        }

        return false;
    }
}

?>