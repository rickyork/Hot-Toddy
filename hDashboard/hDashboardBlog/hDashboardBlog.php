<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Dashboard Blog
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

class hDashboardBlog extends hPlugin {

    private $hDialogue;
    private $hForm;
    private $hPagination;
    private $hCalendarDatabase;

    public function hConstructor()
    {
        if ($this->isLoggedIn())
        {
            if ($this->inGroup('Website Administrators') || $this->inGroup('Calendar Administrators'))
            {
                $this->getBlogDashboard();
            }
            else
            {
                $this->notAuthorized();
            }
        }
        else
        {
            $this->notLoggedIn();
        }
    }

    public function getBlogDashboard()
    {
        $this->hDashboard = $this->library('hDashboard');

        $this->plugin('hApplication/hApplicationStatus');

        $this->jQuery('Datepicker');

        $this->getPluginFiles();
        $this->getPluginCSS('hPagination');

        $this->HotToddySideBoxContent = $this->getTemplate('Actions');

        $this->hFileHeadingTitle = 'Manage Blog';

        $this->hCalendarDatabase = $this->database('hCalendar');
        $this->hPagination = $this->library('hPagination');

        $this->hDialogue = $this->library('hDialogue');
        $this->hForm = $this->library('hForm');

        $this->hFileDocument =
            $this->getNews().
            $this->getForm();
    }

    public function getNews()
    {
        $this->hPagination->parseCursor();

        $files = $this->hCalendarDatabase->getFilesForTemplate(
            array(
                'hCalendarDate' => 'm/d/y'
            ),
            $this->hCalendarId(1),
            $this->hCalendarCategoryId(3),
            $this->hPagination->getLimit(),
            nil,
            true,
            'DESC'
        );

        $count = $this->hCalendarDatabase->getResultCount();

        $this->hPagination->setResultCount($count);

        $navigation = $this->hPagination->getNavigationVariables();

        return $this->getTemplate(
            'Blog',
            array_merge(
                array(
                    'files' => $files
                ),
                $navigation
            )
        );
    }

    public function getForm()
    {
        $this->hForm
            ->addDiv('hDashboardBlogDiv')
            ->addFieldset('Blog Post', '100%', '75px,')

            ->addTextInput(
                'hFileTitle',
                'Title:',
                100
            )

            ->addWYSIWYGInput(
                'hFileDescription',
                'Summary: -L',
                '',
                '50,3',
                '100%,100px',
                '',
                'BasicCMS'
            )
            ->addWYSIWYGInput(
                'hFileDocument',
                'Blog Post: -L',
                '',
                '50,5',
                '100%,300px',
                '',
                'BasicCMS'
            )

            ->setVariable(
                'hFormAppendInput',
                $this->getTemplate('Calendar Icon')
            )

            ->addTextInput(
                'hCalendarDate',
                'Date:',
                20
            )

            ->addTableCell('')
            ->addCheckboxInput(
                'hUserPermissionsWorld',
                'Make Public?'
            )

            ->addTableCell('')
            ->addCheckboxInput(
                'hFileComments',
                'Enable Comments?'
            );

        return $this->hDialogue
            ->newDialogue('hDashboardBlog')
            ->setForm($this->hForm)
            ->addButtons('Save', 'Cancel')
            ->getDialogue(nil, 'Blog Post');
    }

}

?>