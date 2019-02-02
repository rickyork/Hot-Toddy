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

class hPhotoDatabase extends hPlugin {

    private $hCategoryDatabase;

    public function hConstructor()
    {
        $this->hCategoryDatabase = $this->database('hCategory');
    }

    public function getAllPhotoFiles()
    {
        return $this->hDatabase->getResults(
            $this->getTemplateSQL()
        );
    }

    public function addPhoto($fileId, array $categories = array())
    {
        $this->hCategoryDatabase->addFileToCategory(
            $fileId,
            '/Categories/.Photos'
        );

        if (count($categories))
        {
            foreach ($categories as $categoryId)
            {
                $this->hCategoryDatabase->addFileToCategory(
                    $fileId,
                    $categoryId
                );
            }
        }
    }
}

?>