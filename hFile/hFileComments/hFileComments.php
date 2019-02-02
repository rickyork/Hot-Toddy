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

class hFileComments extends hPlugin {

    private $hFileCommentsDatabase;
    private $hFileComments;
    private $hCalendarDatabase;

    public function hConstructor()
    {
        $this->getPluginFiles();

        $this->hCalendarBlogArchiveEnabled = false;

        $this->hFileComments = $this->library('hFile/hFileComments');
        $this->hFileCommentsDatabase = $this->database('hFile/hFileComments');
        $this->hCalendarDatabase = $this->database('hCalendar');

/*
        if ($this->hCalendarId && $this->hCalendarCategoryId)
        {
            $this->hCalendarBlogPost = $this->hFileDocument;

            $this->hFileDocument = '';

            $this->plugin('hCalendar/hCalendarBlog');
        }
*/

        $this->hFileDocument = $this->hFileComments->getCommentsAndDocument();
    }
}

?>