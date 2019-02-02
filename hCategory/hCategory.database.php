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
# <h1>Category Database API</h1>
# <p>
#
# </p>
# @end

class hCategoryDatabase extends hPlugin {

    private $format = 'getResults';
    private $fileSort = '`hFileDocuments`.`hFileTitle` ASC';
    private $fileCount = 0;
    private $categoryCount = 0;

    private $hCategoryGroups;
    private $hUserGroups = array();

    private $exclusionCategories = array();
    private $selectedCategories = array();

    public function hConstructor()
    {
        if ($this->hCategoryGroupsPlugin(null))
        {
            $this->hCategoryGroups = $this->plugin($this->hCategoryGroupsPlugin);
            $this->hUserGroups = $this->hCategoryGroups->getGroups();
        }
    }

    public function &setDatabaseReturnFormat($format)
    {
        # @return hCategoryDatabase

        # @description
        # <h2>Setting Data Return Format</h2>
        # <p>
        #   Determines how database data will be returned. The default value is
        #   <var>getResults</var>.
        # </p>
        # @end

        $this->format = $format;
        return $this;
    }

    public function getFileCategories($fileId, $columns = array())
    {
        # @return array

        # @description
        # <h2>Getting File Categories</h2>
        # <p>
        #   Returns category data: <var>hCategoryId</var>, <var>hCategoryName</var>, and <var>hFileIconId</var>,
        #   for all categories that the provided <var>$fileId</var> argument is a member of.
        # </p>
        # <p>
        #   The columns returned from the <var>hCategories</var> table for the file can be customized in
        #   the provided <var>$columns</var> argument.
        # </p>
        # @end

        if (!is_array($columns))
        {
            $columns = array($columns);
        }

        if (!count($columns))
        {
            $columns = array(
                'hCategoryId',
                'hCategoryName',
                'hFileIconId'
            );
        }

        return $this->hDatabase->{"{$this->format}"}(
            array(
                'hCategories' => $columns
            ),
            array(
                'hCategories',
                'hCategoryFiles'
            ),
            array(
                'hCategories.hCategoryId' => 'hCategoryFiles.hCategoryId',
                'hCategoryFiles.hFileId'  => (int) $fileId
            ),
            'AND',
            'hCategoryName'
        );
    }

    public function getCategoryName($categoryId)
    {
        # @return string

        # @description
        # <h2>Getting a Category's Name</h2>
        # <p>
        #   Returns the <var>hCategoryName</var> for the provided <var>$categoryId</var> argument.
        # </p>
        # @end

        $this->checkCategoryId($categoryId);
        return $this->hCategories->selectColumn('hCategoryName', (int) $categoryId);
    }

    public function getCategories()
    {
        # @return array

        # @description
        # <h2>Retrieving Categories</h2>
        # <p>
        #   If the <var>$categoryParentId</var> argument is provided, this method retrieves children
        #   category data for the provided categoryId.
        # </p>
        # <p>
        #   If no arguments are provided, all categories are returned.
        # </p>
        # @end

        $arguments = func_get_args();

        if (isset($arguments[0]))
        {
            $categories = $this->hCategories->selectColumnsAsKeyValue(
                array(
                    'hCategoryId',
                    'hCategoryName'
                ),
                array(
                    'hCategoryId'       => array('>', 0),
                    'hCategoryParentId' => (int) $arguments[0],
                    'hCategoryName'     => array('NOT LIKE', '.%')
                ),
                'AND',
                'hCategoryName'
            );

            if (count($this->exclusionCategories))
            {
                foreach ($categories as $categoryId => $categoryName)
                {
                    $this->checkCategoryId($categoryId);

                    if (in_array((int) $categoryId, $this->exclusionCategories))
                    {
                        unset($categories[$categoryId]);
                    }
                }
            }

            if ($this->format == 'getResultsForTemplate')
            {
                $rtn = array();

                foreach ($categories as $categoryId => $categoryName)
                {
                    $this->checkCategoryId($categoryId);

                    $rtn['hCategoryId'][] = $categoryId;
                    $rtn['hCategoryName'][] = $categoryName;

                    $rtn['isSelected'][] = in_array($categoryId, $this->selectedCategories);
                }

                return $rtn;
            }

            return $categories;
        }
        else
        {
            return $this->hDatabase->{"{$this->format}"}(
                $this->getTemplateSQL()
            );
        }
    }

    public function categoryExists($categoryId)
    {
        # @return bool

        # @description
        # <h2>Determining if a Category Exists</h2>
        # <p>
        #   Determines if the provided <var>$categoryId</var> exists.
        # </p>
        # @end

        $this->checkCategoryId($categoryId);
        return $this->hCategories->selectExists('hCategoryName', (int) $categoryId);
    }

    public function &setCategoryFileSort($sort)
    {
        # @return hCategoryDatabase

        # @description
        # <h2>Setting the Category Sort</h2>
        # <p>
        #  Sets the internal <var>$fileSort</var> property.
        # </p>
        # @end
        $this->fileSort = $sort;

        return $this;
    }

    public function getCategoryFiles($categoryId, $userId = 0)
    {
        # @return array

        # @description
        # <h2>Getting Category Files</h2>
        # <p>
        #   Returns file data for the provided <var>$categoryId</var>.
        # </p>
        # @end

        $this->user->whichUserId($userId);
        $this->checkCategoryId($categoryId);

        $this->hFileGetMetaData = true;

        // If world read
        // If user is owner and owner has read
        // If user is in a group and group has read
        $results = $this->hDatabase->{"{$this->format}"}(
            $this->getTemplateSQL(
                array_merge(
                    $this->getPermissionsVariablesForTemplate('auto'),
                    array(
                        'categoryId'       => (int) $categoryId,
                        'categoryFileSort' => $this->fileSort
                    )
                )
            )
        );

        if (isset($results['hFileDescription']))
        {
            foreach ($results['hFileDescription'] as $i => &$fileDescription)
            {
                $fileDescription = hString::decodeHTML($fileDescription);
            }
        }

        $this->fileCount = $this->hDatabase->getResultCount();
        return $results;
    }

    public function getCategoryFilesByGroup($categoryId = 0, $userGroups = array(), $userId = 0)
    {
        # @return array

        # @description
        # <h2>Getting Category Files By Group</h2>
        # <p>
        #
        # </p>
        # @end

        $this->user->whichUserId($userId);
        $this->checkCategoryId($categoryId);

        $results = $this->hDatabase->{"{$this->format}"}(
            $this->getTemplateSQL(
                array_merge(
                    $this->getPermissionsVariablesForTemplate('auto'),
                    array(
                        'categoryId' => $categoryId,
                        'categoryFileSort' => $this->fileSort
                    )
                )
            )
        );

        $this->fileCount = $this->hDatabase->getResultCount();
        return $results;
    }

    public function getCategoryFileCount()
    {
        # @return integer

        # @description
        # <h2>Getting Category File Count</h2>
        # <p>
        #   Returns the value of the internal <var>$fileCount</var> property, which is set by
        #   various methods retrieving files.
        # </p>
        # @end

        return $this->fileCount;
    }

    public function getCategoryCount()
    {
        # @return integer

        # @description
        # <h2>Getting Category Count</h2>
        # <p>
        #   Returns the value of the internal <var>$categoryCount</var> property, which is set by
        #   various methods retrieving categories.
        # </p>
        # @end

        return $this->categoryCount;
    }

    public function &setExclusionCategories()
    {
        # @return hCategoryDatabase

        # @description
        # <h2>Setting Exclusion Categories</h2>
        # <p>
        #   Sets one or more exclusion categories, which can be provided in one or more arguments
        #   to this method. Exlusion categories are excluded from category results.
        # </p>
        # @end

        $categories = func_get_args();

        $this->exclusionCategories = array();

        foreach ($categories as $i => $categoryId)
        {
            $this->checkCategoryId($categoryId);
            $this->exclusionCategories[$i] = (int) $categoryId;
        }

        return $this;
    }

    public function &setSelectedCategories(array $categories)
    {
        # @return hCategoryDatabase

        # @description
        # <h2>Setting Selected Categories</h2>
        # <p>
        #   Sets the internal <var>$selectedCategories</var> property.
        # </p>
        # @end

        $this->selectedCategories = $categories;
        return $this;
    }

    public function &resetSelected()
    {
        # @return hCategoryDatabase

        # @description
        # <h2>Resetting Selected Categories</h2>
        # <p>
        #
        # </p>
        # @end

        $this->selectedCategories = array();
        return $this;
    }

    public function &resetExclusions()
    {
        # @return hCategoryDatabase

        # @description
        # <h2>Resetting Exclusion Categories</h2>
        # <p>
        #   Resets the internal <var>$exclusionCategories</var> property to an empty array.
        # </p>
        # @end

        $this->exclusionCategories = array();
        return $this;
    }

    public function getSubCategories($categoryId, $filePath = null, $checkPermissions = true)
    {
        # @return array

        # @description
        # <h2>Get Sub Categories</h2>
        # <p>
        #   Returns children categories for the provided <var>$categoryId</var>.
        # </p>
        # @end

        $this->checkCategoryId($categoryId);

        if (empty($filePath))
        {
            $filePath = $this->hFilePath;
        }

        $sql = $this->getTemplateSQL(
            array_merge(
                $this->getPermissionsVariablesForTemplate($checkPermissions),
                array(
                    'categoryId' => $categoryId
                )
            )
        );

        $results = $this->hDatabase->getResults($sql);

        $this->categoryCount = count($results);

        if ($this->format != 'getAssociativeArray')
        {
            foreach ($results as $i => $result)
            {
                if (in_array((int) $results[$i]['hCategoryId'], $this->exclusionCategories))
                {
                    unset($results[$i]);
                    continue;
                }

                if (!empty($results[$i]['hFileIconId']))
                {
                    $results[$i]['hFileIconPath'] = $this->getFilePathByFileId($result['hFileIconId']);
                }
                else
                {
                    $results[$i]['hFileIconPath'] = '/images/icons/'.$this->hCategoryIconResolution('128x128').'/category_folder.png';
                }

                $results[$i]['hFileCategoryPath'] = $filePath.'?hCategoryId='.$results[$i]['hCategoryId'];
                // $results[$i]['hCategoryFileCount'] = $this->fileCount;
            }
        }

        if ($this->format == 'getResultsForTemplate')
        {
            return $this->hDatabase->getResultsForTemplate($results);
        }

        return $results;
    }

    public function inCategory($fileId, $categoryId)
    {
        # @return boolean

        # @description
        # <h2>Determining if a File is in a Category</h2>
        # <p>
        #   Determines if the provided <var>$fileId</var> is within the provided
        #   <var>$categoryId</var>.
        # </p>
        # @end

        $this->checkCategoryId($categoryId);

        return $this->hCategoryFiles->selectExists(
            'hCategoryId',
            array(
                'hCategoryId' => (int) $categoryId,
                'hFileId'     => (int) $fileId
            )
        );
    }

    public function &removeFileFromAllCategories($fileId)
    {
        # @return hCategoryDatabase

        # @description
        # <h2>Removing a File From All Categories</h2>
        # <p>
        #   Removes the provided <var>$fileId</var> from all category associations.
        # </p>
        # @end

        $this->hCategoryFiles->delete('hFileId', (int) $fileId);
        return $this;
    }

    public function &removeFileFromCategory($fileId, $categoryId)
    {
        # @return hCategoryDatabase

        # @description
        # <h2>Removing a File From a Category</h2>
        # <p>
        #   Removes the provided <var>$fileId</var> from the provided <var>$categoryId</var>.
        # </p>
        # @end

        $this->hCategoryFiles->delete(
            array(
                'hCategoryId' => (int) $categoryId,
                'hFileId' => (int) $fileId
            )
        );

        return $this;
    }

    public function &addFileToCategories($fileId, array $categories)
    {
        # @return hCategoryDatabase

        # @description
        # <h2>Adding a File to Multiple Categories</h2>
        # <p>
        #   Adds the provided <var>$fileId</var> to the various categories provided
        #   in the <var>$categories</var> array argument.
        # </p>
        # @end

        $this->removeFileFromAllCategories($fileId);

        foreach ($categories as $categoryId)
        {
            $this->checkCategoryId($categoryId)->addFileToCategory($fileId, $categoryId);
        }

        return $this;
    }

    public function &addFileToCategory($fileId, $categoryId)
    {
        # @return hCategoryDatabase

        # @description
        # <h2>Adding a File to a Category</h2>
        # <p>
        #   Adds the specified <var>$fileId</var> to the indicated <var>$categoryId</var>.
        # </p>
        # @end

        $this->checkCategoryId($categoryId);

        if (!$this->inCategory($fileId, $categoryId))
        {
            $this->hCategoryFiles->insert((int) $categoryId, (int) $fileId, 0);
        }

        return $this;
    }

    public function &checkCategoryId(&$categoryId)
    {
        # @return hCategoryDatabase

        # @description
        # <h2>Checking a Category Id</h2>
        # <p>
        #   If the <var>$categoryId</var> argument is not numeric and is rather a category
        #   file path, it is converted to a numeric categoryId. The numeric value is assigned
        #   to the <var>$caetgoryId</var> argument, which is passed by reference.
        # </p>
        # @end

        if (!is_numeric($categoryId))
        {
            $categoryId = $this->getCategoryIdFromPath($categoryId);
        }

        return $this;
    }
}

?>