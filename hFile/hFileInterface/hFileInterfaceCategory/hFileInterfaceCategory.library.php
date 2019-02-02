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

class hFileInterfaceCategoryLibrary extends hFileInterface {

    public $filterPaths = array();
    public $fileTypes = array();

    private $hCategoryDatabase;
    private $hUserPermissions;

    public $methodsWereAdded = false;

    public function hConstructor()
    {
        $this->hCategoryDatabase = $this->database('hCategory');
    }

    public function shouldBeCalled()
    {
        return $this->isCategory;
    }

    public function getMethods()
    {
        // Can't use something nice and simple like get_class_methods(),
        // it returns all methods from all parent objects, and I only want
        // this to return the methods from this object only.
        return array(
            'shouldBeCalled',
            'getMIMEType',
            'getTitle',
            'getSize',
            'getDescription',
            'getLastModified',
            'getCreated',
            'hasChildren',
            'getDirectories',
            'getFiles',
            'rename',
            'delete',
            'getAllChildren',
            'move',
            'newDirectory'
        );
    }

    public function getMIMEType()
    {
        return '';
    }

    public function getTitle()
    {
        return '';
    }

    public function getDescription()
    {
        return '';
    }

    public function getSize()
    {

    }

    public function getLastModified()
    {
        return '';
    }

    public function getCreated()
    {
        return '';
    }

    public function hasChildren($countFiles = false)
    {
        if (empty($this->categoryId))
        {
            $this->categoryId = $this->getCategoryIdFromPath($this->filePath);
        }

        $exists = $this->hCategories->selectExists(
            'hCategoryId',
            array(
                'hCategoryParentId' => $this->categoryId
            )
        );

        return (
            $exists ||
            $countFiles && $this->hCategoryFiles->selectExists('hFileId', $this->categoryId)
        );
    }

    public function getDirectories($checkPermissions = true)
    {
        if (empty($this->categoryId))
        {
            $this->categoryId = $this->getCategoryIdFromPath($this->filePath);
        }

        $directories = array();

        $query = $this->hDatabase->getResults(
            $this->getTemplate(
                dirname(__FILE__).'/SQL/getDirectories.sql',
                array_merge(
                    $this->getPermissionsVariablesForTemplate(
                        $checkPermissions,
                        false
                    ),
                    array(
                        'categoryId' => $this->categoryId,
                    )
                )
            )
        );

        foreach ($query as $data)
        {
            if (!empty($data['hCategoryId']))
            {
                $directories[$data['hCategoryName']] = array(
                    'hFileInterfaceObjectId'  => $data['hCategoryId'],
                    'hFileName'               => $data['hCategoryName'],
                    'hFilePath'               => $this->getCategoryPath($data['hCategoryId']),
                    'hFileIsServer'           => false,
                    'hDirectoryId'            => 'hCategoryId'.$data['hCategoryId'],
                    'hDirectoryIsApplication' => false,
                    'hFileIconId'             => 0,
                    'hFileCreated'            => (int) $data['hCategoryCreated'],
                    'hFileLastModified'       => (int) $data['hCategoryLastModified'],
                    'hFileMIME'               => 'directory',
                    'hFileLabel'              => nil,
                    'hFileSize'               => 0,
                    'hDirectoryCount'         => $data['hDirectoryCount'],
                    'hFileCount'              => $data['hFileCount'],
                    'hCategoryFileSortIndex'  => $data['hCategoryFileSortIndex']
                );
            }
        }

        return $directories;
    }

    public function getFiles($includeMetaData = true, $checkPermissions = true)
    {
        if (empty($this->categoryId))
        {
            $this->categoryId = $this->getCategoryIdFromPath($this->filePath);
        }

        $sql = $this->getTemplate(
            dirname(__FILE__).'/SQL/getFiles.sql',
            array_merge(
                $this->getPermissionsVariablesForTemplate($checkPermissions, false),
                array(
                    'categoryId' => (int) $this->categoryId,
                    'limit' => $this->hFileLimit(nil)
                )
            )
        );

        $query = $this->hDatabase->getResults($sql);

        return $this->getFileResults($query);
    }

    public function rename($newName)
    {
        $this->hCategories->update(
            array(
                'hCategoryName' => $newName
            ),
            (int) $this->getCategoryIdFromPath($this->filePath)
        );

        return true;
    }

    public function delete()
    {
        $categoryId = (int) $this->getCategoryIdFromPath($this->filePath);

        // Get category documents,
        // child categories, and all of their documents
        $categories = array($categoryId);
        $this->getAllChildren($categoryId, $categories);

        foreach ($categories as $categoryId)
        {
            $this->hCategoryFiles->delete(
                'hCategoryId',
                (int) $categoryId
            );

            $this->hCategories->delete(
                'hCategoryId',
                (int) $categoryId
            );
        }

        return true;
    }

    public function getAllChildren($categoryId, &$categories)
    {
        $query = $this->hCategories->select(
            'hCategoryId',
            array(
                'hCategoryParentId' => (int) $categoryId
            )
        );

        if (count($query))
        {
            foreach ($query as $data)
            {
                array_push(
                    $categories,
                    $data['hCategoryId']
                );

                $this->getAllChildren(
                    $data['hCategoryId'],
                    $categories
                );
            }
        }
    }

    public function move($sourcePath, $replace = false)
    {
        $categoryId = $this->getCategoryIdFromPath($this->filePath);

        $this->hFile->query($sourcePath);

        $this->hCategoryDatabase->addFileToCategory(
            $this->fileId,
            $categoryId
        );
    }

    public function newDirectory($newDirectoryName, $hUserId = 0)
    {
        $categoryId = $this->hCategories->insert(
            array(
                'hCategoryId' => nil,
                'hUserId' => (!empty($hUserId)? (int) $hUserId : ($this->isLoggedIn()? (int) $_SESSION['hUserId'] : 0)),
                'hCategoryName' => $newDirectoryName,
                'hFileIconId' => 0,
                'hCategoryParentId' => $this->getCategoryIdFromPath($this->filePath),
                'hCategoryRootId' => 0,
                'hCategoryLastModified' => 0,
                'hCategoryCreated' => time(),
                'hCategorySortIndex' => 0
            )
        );

        $this->hCategories->inheritPermissionsFrom($this->categoryId);
        $this->hCategories->savePermissions($categoryId);

        return 'hCategoryId'.$categoryId;
    }
}

?>