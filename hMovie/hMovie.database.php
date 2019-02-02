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
# <h1>Movie Database API</h1>
# <p>
#    Provides database in/out for the <var>hMovie</var> API.
# </p>
# <p>
#    When supported video content is uploaded, it is automatically added to the
#    <var>/Categories/.Movies</var> category, making it possible to view all
#    movies uploaded to the entire website with simple, efficient queries.
# </p>
# @end

class hMovieDatabase extends hPlugin {

    private $hCategoryDatabase;

    public function hConstructor()
    {
        $this->hCategoryDatabase = $this->database('hCategory');
    }

    public function getAllMovieFiles()
    {
        # @return array

        # @description
        # <h2>Getting All Movie Files</h2>
        # <p>
        #    Returns a list of all movie files presented added to
        #    <var>/Categories/.Movies</var>
        # </p>
        # @end

        return $this->hDatabase->getResults(
            $this->getTemplateSQL()
        );
    }

    public function addMovie($fileId, array $categories = array())
    {
        # @return void

        # @description
        # <h2>Adding a Movie</h2>
        # <p>
        #    Adds a movie file to <var>/Categories/.Movies</var>
        # </p>
        # @end

        $this->hCategoryDatabase->addFileToCategory($fileId, '/Categories/.Movies');

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