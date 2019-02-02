<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy List Database
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
# @description
# <h1>List Database API</h1>
# <p>
#    This object provides database in/out for creating, managing, and deleting lists and
#    list files.
# </p>
# @end

class hListDatabase extends hPlugin {

    public function listExists($listName, $listCategoryId = 0)
    {
        # @return boolean

        # @description
        # <h2>Determining if a List Exists</h2>
        # <p>
        #   Determines if a list exists based on the provided <var>$listName</var> and
        #   <var>$listCategoryId</var>
        # </p>
        # @end

        return $this->hLists->selectExists(
            'hListId',
            array(
                'hListName' => $listName,
                'hListCategoryId' => (int) $listCategoryId
            )
        );
    }

    public function getListId($listName, $listCategoryId = 0)
    {
        # @return integer

        # @description
        # <h2>Retrieving a List Id</h2>
        # <p>
        #   Retrieves a <var>hListId</var> for the provided <var>$listName</var> and <var>$listCategoryId</var>.
        # </p>
        # @end

        return $this->hLists->selectColumn(
            'hListId',
            array(
                'hListName' => $listName,
                'hListCategoryId' => (int) $listCategoryId
            )
        );
    }

    public function save($listId, $listName, $listCategoryId = 0, $listSortIndex = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a List</h2>
        # <p>
        #   Saves a list in the <var>hLists</var> database table.
        # </p>
        # @end

        return $this->hLists->save(
            array(
                'hListId' => (int) $listId,
                'hListName' => $listName,
                'hListCategoryId' => (int) $listCategoryId,
                'hListSortIndex' => (int) $listSortIndex
            )
        );
    }

    public function getLists($listCategoryId)
    {
        # @return array

        # @description
        # <h2>Retrieving Lists</h2>
        # <p>
        #   Retrieves an array of lists for the specified <var>$listCategoryId</var>.
        # </p>
        # @end

        return $this->hLists->selectColumnsAsKeyValue(
            array(
                'hListId',
                'hListName'
            ),
            array(
                'hListCategoryId' => (int) $listCategoryId
            ),
            'AND',
            'hListSortIndex'
        );
    }

    public function getListsForTemplate($listCategoryId = 0, $fileId = 0)
    {
        # @return array

        # @description
        # <h2>Retrieving Lists for a Template</h2>
        # <p>
        #   Retrieves list data based on the provided <var>$listCategoryId</var> and <var>$fileId</var>
        #   that can be passed directly to a template.
        # </p>
        # @end

        $this->hDatabase->setPrependResult(''); // Add an empty option

        $productLists = $this->getLists($listCategoryId);

        $results = array();

        foreach ($productLists as $listId => $listName)
        {
            $results['hListId'][] = (int) $listId;
            $results['hListName'][] = $listName;

            if ($fileId)
            {
                $results['hListHasFiles'][] = $this->listHasFiles($listId, $fileId);
            }
        }

        return $results;
    }

    public function listHasFiles($listId, $fileId)
    {
        # @return boolean

        # @description
        # <h2>Determining if a List Has Files</h2>
        # <p>
        #   Determines if there are files attached to the provided
        #   <var>$listId</var> and <var>$fileId</var>.
        # </p>
        # @end

        return $this->hListFiles->selectExists(
            'hListFileId',
            array(
                'hFileId' => (int) $fileId,
                'hListId' => (int) $listId
            )
        );
    }

    public function getListFiles($listId, $fileId)
    {
        # @return array

        # @description
        # <h2>Retrieving List Files</h2>
        # <p>
        #   Retrieves list files for the provided <var>$listId</var> and <var>$fileId</var>.
        # </p>
        # @end

        return $this->hListFiles->select(
            array(
                'DISTINCT',
                'hListFileId'
            ),
            array(
                'hFileId' => (int) $fileId,
                'hListId' => (int) $listId
            ),
            'AND',
            'hListFileSortIndex'
        );
    }

    public function deleteFileLists($fileId, $lists)
    {
        # @return void

        # @description
        # <h2>Deleting List Files</h2>
        # <p>
        #   Deletes all list files associated with <var>$fileId</var> and <var>$lists</var> or
        #   <var>$listId</var>.
        # </p>
        # @end

        if (is_array($lists))
        {
            foreach ($lists as $listId)
            {
                $this->hListFiles->delete(
                    array(
                        'hListId' => (int) $listId,
                        'hFileId' => (int) $fileId
                    )
                );
            }
        }
        else
        {
            $this->hListFiles->delete(
                array(
                    'hListId' => (int) $lists,
                    'hFileId' => (int) $fileId
                )
            );
        }
    }

    public function saveFileLists($fileId, array $listFiles)
    {
        # @return void

        # @description
        # <h2>Saving List Files</h2>
        # <p>
        #   Saves list files to the specified <var>$fileId</var> where <var>$listFiles</var> is
        #   an array, each key representing an attached <var>$listFileId</var> and each value
        #   representing a <var>$listId</var>.
        # </p>
        # @end

        $listFileSortIndex = 0;

        foreach ($listFiles as $listFileId => $listId)
        {
            if (!empty($listFileId) && !empty($listId))
            {
                $this->hListFiles->insert(
                    array(
                        'hFileId' => (int) $fileId,
                        'hListId' => (int) $listId,
                        'hListFileId' => (int) $listFileId,
                        'hListFileSortIndex' => (int) $listFileSortIndex
                    )
                );

                $listFileSortIndex++;
            }
        }
    }
}

?>