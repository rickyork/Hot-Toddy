<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Movie Listener Plugin
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

class hMovieService extends hService {

    private $hMovie;

    public function hConstructor()
    {
        $this->hMovie = $this->library('hMovie');
    }

    public function getMovies()
    {    
        if (!isset($_GET['path']))
        {
            $this->JSON('-5');
            return;
        }

        $path = $_GET['path'];

        if ($path == '/Categories/.Movies/Movies')
        {
            $path = '/Categories/.Movies';
        }

        $this->HTML($this->hMovie->getMovies($path));
    }
}

?>