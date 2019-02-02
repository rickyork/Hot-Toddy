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
# <h2>Movie Tree API</h2>
# <p>
#    Provides a tree view for browsing and selecting movie files.
# </p>
# @end

class hMovieLibrary extends hPlugin {

    private $hFile;
    private $hCategoryDatabase;
    private $hFinderTree;

    public function hConstructor()
    {
        $this->getPluginFiles();

        if (!$this->categoryExists('/Categories/.Movies'))
        {
            $this->library('hMovie/hMovieInstall');
        }
    }

    public function getTree()
    {
        # @return string

        # @description
        # <p>
        #    Returns a tree-view for browsing and selecting movies.
        #    This portion of the view displays the categories and
        #    folders that contain movies.
        # </p>
        # @end

        $this->hFinderTreeLoadPluginFiles = false;

        $this->hFinderTree = $this->plugin('hFinder/hFinderTree');

        $this->hFinderTreeDefaultPath = '/Categories/.Movies';
        $this->hFinderTreeHomeDirectory = false;
        $this->hFinderTreeRootOverrideDefaultPath = false;
        $this->hFinderCategoriesDiskName = 'Movies';

        return $this->getTemplate(
            'Tree',
            array(
                'tree' => $this->hFinderTree->getTree()
            )
        );
    }

    public function getView()
    {
        # @return string

        # @description
        # <h2>Getting the Movie Tree Template</h2>
        # <p>
        #    Returns a thumbnail view for browsing and selecting movies.
        #    This portion of the view displays movie thumbnails.
        # </p>
        # @end

        return $this->getTemplate('View');
    }

    public function getMovies($filePath)
    {
        # @return string

        # @description
        # <h2>Getting Movies</h2>
        # <p>
        #    Returns movie thumnails based on the provided <var>$filePath</var>.
        #    <var>$filePath</var> can refer to a folder or a category.  In the
        #    context of the movie API, however, it almost always refers to a category.
        # </p>
        # @end

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

                    $files['hMovieCaption'][$i] = $caption;
                }

                return $this->getTemplate(
                    'Movies',
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