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
# <h1>Movie API Installer</h1>
# <p>
#    Installs the Movie API by creating three new categories:
# </p>
# <ul>
#    <li class='code'>/Categories/.Movies</li>
#    <li class='code'>/Categories/.Movies/Events</li>
#    <li class='code'>/Categories/.Movies/Movies</li>
# </ul>
# <p>
#    Then the program searches the Hot Toddy file system for 
#    all movie files and adds those files to base <var>/Categories/.Movies</var>
#    category.
# </p>
# <p>
#    The <var>/Categories/.Movies</var> category does not show up as a 
#    folder in the Finder, since this functionality could be broken or 
#    produce unexpected results if users were aloud to see or tamper 
#    with the category/folder.
# </p>
# @end

class hMovieInstallLibrary extends hPlugin {

    private $hFile;
    private $hCategoryDatabase;
    private $hMovieDatabase;

    public function hConstructor()
    {
        $this->hFile = $this->library('hFile');
        $this->hCategoryDatabase = $this->database('hCategory');
        $this->hMovieDatabase = $this->database('hMovie');

        $this->hFile->newDirectory('/Categories', '.Movies', 1);
        $this->hFile->newDirectory('/Categories/.Movies', 'Events', 1);
        $this->hFile->newDirectory('/Categories/.Movies', 'Movies', 1);

        $hFiles = $this->hMovieDatabase->getAllMovieFiles();

        if (is_array($files))
        {
            foreach ($files as $fileId)
            {
                $this->hMovieDatabase->addMovie($fileId);
            }
        }
        else
        {
            $this->notice("No movie files were found.", __FILE__, __LINE__);
        }
    }
}

?>