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
# <h1>Photo API</h1>
# <p>
#    This object provides methods that combine to provide a UI for browsind and picking
#    photos.
# </p>
# @end

class hPhotoLibrary extends hPlugin {

    private $hFile;
    private $hCategoryDatabase;
    private $hFinderTree;

    public function hConstructor()
    {
        $this->getPluginJavaScript('/hPhoto/JS/Photos', true);
        $this->getPluginCSS('/hPhoto/CSS/Photos', true);

        if (!$this->categoryExists('/Categories/.Photos'))
        {
            $this->library('hPhoto/hPhotoInstall');
        }

        $this->hPhotoSliderPosition = $this->user->getVariable('hPhotoSliderPosition', 110);
    }

    public function getTree()
    {
        $this->hFinderTreeLoadPluginFiles = false;

        $this->hFinderTree = $this->plugin('hFinder/hFinderTree');

        $this->hFinderTreeDefaultPath = '/Categories/.Photos';
        $this->hFinderTreeHomeDirectory = false;
        $this->hFinderTreeRootOverrideDefaultPath = false;
        $this->hFinderCategoriesDiskName = 'Photos';

        return $this->getTemplate(
            'Tree',
            array(
                'hPhotoTree' => $this->hFinderTree->getTree(false)
            )
        );
    }

    public function getView()
    {
        return $this->getTemplate('View');
    }

    public function getPhotos($filePath)
    {
        $categoryId = $this->getCategoryIdFromPath($filePath);

        if (!empty($categoryId))
        {
            $this->hCategoryDatabase = $this->database('hCategory');

            $this->hCategoryDatabase->setDatabaseReturnFormat('getResultsForTemplate');

            $thumbnailGenerator = $this->getFilePathByPlugin('hFile/hFileThumbnail');

            $this->hCategoryDatabase->setCategoryFileSort('`hFiles`.`hFileName` ASC');

            $files = $this->hCategoryDatabase->getCategoryFiles($categoryId);

            if (is_array($files) && count($files))
            {
                foreach ($files['hFilePath'] as $i => $data)
                {
                    $files['hFilePathEncoded'][$i] = urlencode($data);

                    $caption = '';

                    if (!empty($files['hFileTitle'][$i]))
                    {
                        $caption = $files['hFileTitle'][$i];
                    }
                    else
                    {
                        $bits = explode('.', $files['hFileName'][$i]);
                        $caption = array_shift($bits);
                    }

                    $files['hPhotoCaption'][$i] = $caption;
                }

                return $this->getTemplate(
                    'Photos',
                    array(
                        'hFiles' => $files,
                        'thumbnailGenerator' => $thumbnailGenerator
                    )
                );
            }
            else
            {
                return '';
            }
        }
        else
        {
            $this->warning(
                "Category path, ".$filePath." does not exist.",
                __FILE__,
                __LINE__
            );
        }
    }
}

?>