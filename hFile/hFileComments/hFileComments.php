<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File Comments
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