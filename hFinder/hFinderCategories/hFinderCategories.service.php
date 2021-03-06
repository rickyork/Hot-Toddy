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

class hFinderCategoriesService extends hService {

    private $hFileIcon;

    public function hConstructor()
    {
        hString::safelyDecodeURL($_GET['path']);
        hString::safelyDecodeURL($_POST['path']);
    }

    public function moveToCategory()
    {
        if (empty($_POST['toCategoryPath']) || empty($_POST['fromCategoryPath']) || empty($_POST['hFileId']))
        {
            $this->JSON(-5);
            return;
        }
    }

    public function getCategory()
    {
        if (!isset($_GET['path']))
        {
            $this->JSON(-5);
            return;
        }

        $hCategoryId = $this->getCategoryIdFromPath($_GET['path']);

        if (!$this->hCategories->hasPermission($hCategoryId, 'r'))
        {
            $this->JSON(-1);
            return;
        }

        $hFileIconId = $this->hCategories->selectColumn('hFileIconId', (int) $hCategoryId);

        $json = array();

        if (!empty($hFileIconId))
        {
            $json['hFileIconId'] = $hFileIconId;
            $json['hFileIconPath'] = $this->getFilePathByFileId($hFileIconId);
        }

        $hFiles = $this->hCategoryFiles->selectResults('hFileId', (int) $hCategoryId, nil, 'hCategoryFileSortIndex');

        $this->hFileIcon = $this->library('hFile/hFileIcon');

        $json['hCategoryFiles'] = array();

        foreach ($hFiles as $hFileId)
        {
            $iconPath = $this->hFileIcon->getFileIconPath($hFileId);

            if ($this->hDesktopApplicationStyle(false))
            {
                $iconPath = substr($iconPath, 1);
            }

            $json['hCategoryFiles'][] = array(
                'hCategoryFileId'    => $hFileId,
                'hCategoryFileIcon'  => $iconPath,
                'hCategoryFilePath'  => $this->getFilePathByFileId($hFileId),
                'hCategoryFileTitle' => hString::entitiesToUTF8($this->getFileTitle($hFileId))
            );
        }

        $this->JSON($json);
    }

    public function saveCategoryIcon()
    {
        if (!isset($_GET['hFileIconPath']) || !isset($_GET['hFilePath']))
        {
            $this->JSON(-5);
            return;
        }

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

        hString::safelyDecodeURL($_GET['hFileIconPath']);
        hString::safelyDecodeURL($_GET['hFilePath']);

        $hFileId = $this->getFileIdByFilePath($_GET['hFileIconPath']);

        if (!empty($hFileId))
        {
            $hCategoryId = $this->getCategoryIdFromPath($_GET['hFilePath']);

            $this->hCategories->update(
                array(
                    'hFileIconId' => (int) $hFileId
                ),
                (int) $hCategoryId
            );
        }
    }

    public function saveCategory()
    {
        if (!isset($_POST['hFilePath']))
        {
            $this->JSON(-5);
            return;
        }

        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }


        hString::safelyDecodeURL($_POST['hFilePath']);
        $hCategoryId = $this->getCategoryIdFromPath($_POST['hFilePath']);

        if (!$this->hCategories->hasPermission($hCategoryId, 'rw'))
        {
            $this->JSON(-1);
            return;
        }

        $this->hCategoryFiles->delete('hCategoryId', (int) $hCategoryId);

        if (isset($_POST['hCategoryFiles']) && is_array($_POST['hCategoryFiles']))
        {
            $i = 0;

            foreach ($_POST['hCategoryFiles'] as $hFileId)
            {
                if (!empty($hFileId))
                {
                    $this->hCategoryFiles->insert(
                        array(
                            'hCategoryId'            => (int) $hCategoryId,
                            'hFileId'                => (int) $hFileId,
                            'hCategoryFileSortIndex' => (int) $i
                        )
                    );
                }

                $i++;
            }
        }

        if (isset($_POST['hCategories']) && is_array($_POST['hCategories']))
        {
            $i = 0;

            foreach ($_POST['hCategories'] as $hCategoryId)
            {
                $this->hCategories->update(
                    array(
                        'hCategorySortIndex' => (int) $i
                    ),
                    array(
                        'hCategoryId' => (int) $hCategoryId
                    )
                );

                $i++;
            }
        }

        $this->JSON(1);
    }

    private function getCategoryIdFromPath($path)
    {
        $categories = explode('/', $path);

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

        $parentId = 0;

        if (count($categories))
        {
            foreach ($categories as $category)
            {
                $query = $this->hCategories->selectQuery(
                    'hCategoryId',
                    array(
                        'hCategoryName' => $category,
                        'hCategoryParentId' => $parentId
                    )
                );

                if ($this->hDatabase->resultsExist($query))
                {
                    $parentId = $this->hDatabase->getColumn($query);
                }
            }
        }

        return $parentId;
    }
}

?>