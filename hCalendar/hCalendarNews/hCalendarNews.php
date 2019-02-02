<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Calendar News
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
#
# Displays all events, from all categories, in a tabbed layout.

class hCalendarNews extends hPlugin {

    private $hCalendarDatabase;
    
    private $hCalendarCategories = array();
    private $counter;

    public function hConstructor()
    {
        $this->getPluginFiles();
        
        if ($this->hCalendarNewsPlugin(null))
        {
            $this->plugin($this->hCalendarNewsPlugin);
        }

        $this->hCalendarDatabase = $this->database('hCalendar');

        $this->hCalendarCategories = $this->hCalendarDatabase->getCategories();

        if ($this->hCalendarNewsFilterCategories(null))
        {
            $filter = explode(',', $this->hCalendarNewsFilterCategories);
            
            foreach ($this->hCalendarCategories as $calendarCategoryId => $calendarCategoryName)
            {
                if (in_array($calendarCategoryId, $filter))
                {
                    unset($this->hCalendarCategories[$calendarCategoryId]);
                }
            }
        }

        if ($this->hCalendarId(0) > 0 && $this->hCalendarCategoryId(0) > 0)
        {
            # This is an event file... 
            $this->hFileDocument = $this->getTemplate(
                'Full Story',
                array(
                    'hCalendarNewsNavigation' => $this->getNavigation(),
                    'hCalendarNewsTitle'      => $this->hFileTitle,
                    'hCalendarNewsBody'       => $this->hFileDocument
                )
            );

            if ($this->hCalendarNewsTitle(null))
            {
                $this->hFileTitle = $this->hCalendarNewsTitle;
            }
        }
        else
        {
            # Viewing all news
            $this->hFileDocument = $this->getTemplate(
                'News',
                array(
                    'hCalendarNewsNavigation' => $this->getNavigation(),
                    'hCalendarNewsBody'       => $this->getBody(),
                    'hCalendarNewsPagation'   => $this->getPagation()
                )
            );
        }
    }

    public function getNavigation()
    {
        # @return HTML
        
        # @description 
        # <h2>Getting Navigation for News</h2>
        # <p>
        #
        # </p>
        # @end
    
        $html = '';

        foreach ($this->hCalendarCategories as $calendarCategoryId => $calendarCategoryName)
        {
            $class = '';

            if ($this->hCalendarCategoryId(0) == $calendarCategoryId || isset($_GET['hCalendarCategoryId']) && $_GET['hCalendarCategoryId'] == $calendarCategoryId)
            {
                $class = ' hCalendarNewsCategoryTabSelected';
            }

            $html .= $this->getTemplate(
                'Tab',
                array(
                    'hCalendarNewsCategoryTabClass' => $class,
                    'hCalendarNewsCategoryId'       => $calendarCategoryId,
                    'hCalendarNewsCategoryPath'     => $this->hCalendarNewsURL(null).'?hCalendarCategoryId='.$calendarCategoryId,
                    'hCalendarNewsCategoryTabLabel' => $calendarCategoryName
                )
            );
        }

        return $html;
    }

    public function getBody()
    {
        # @return HTML
        
        # @description 
        # <h2>Getting the News Body</h2>
        # <p>
        #
        # </p>
        # @end
    
        $html = '';

        if (isset($_GET['hCalendarCategoryId']))
        {
            $calendarCategoryId = (int) $_GET['hCalendarCategoryId'];
        }
        else
        {
            $calendarCategoryId = $this->hCalendarNewsDefaultCategoryId(1);
        }
        
        $calendarCategoryName = $this->hCalendarDatabase->getCategoryName($calendarCategoryId);

        $files = $this->hCalendarDatabase->getFiles($this->hCalendarIdForNews(1), $calendarCategoryId, 10, null, true, 'DESC');

        if (count($files))
        {
            $stories = '';
            $this->counter = 0;

            foreach ($files as $file)
            {   
                $stories .= $this->getStory($file);
                $this->counter++;
            }

            $html .= $this->getTemplate(
                'Body',
                array(
                    'hCalendarNewsBodyCategoryId' => $calendarCategoryId,
                    'hCalendarNewsCategory' => $stories
                )
            );
        }
        else
        {
            $html .= $this->getTemplate(
                'No Stories',
                array(
                    'hCalendarCategoryName' => $calendarCategoryName
                )
            );
        }

        return $html;
    }

    public function getStory($file)
    {
        # @return HTML
        
        # @description 
        # <h2>Getting a News Story</h2>
        # <p>
        #
        # </p>
        # @end
    
        $copy = '';

        if ($this->hCalendarNewsStoryDisplayCopy(false))
        {
            $copy = $this->hCalendarNewsFullStories(false)? $file['hFileDescription'] : $file['hFileDocument'];
        }

        $moreLink = '';

        if ($this->hCalendarNewsStoryReadMoreLink(true))
        {
            $moreLink = $this->getTemplate(
                'More Link',
                array(
                    'hCalendarNewsStoryReadMoreLinkLabel' => $this->hCalendarNewsStoryReadMoreLinkLabel('More...')
                )
            );
        }

        return $this->getTemplate(
            'Story',
            array(
                'hCalendarNewsStoryId'           => $file['hFileId'],
                'hCalendarNewsDate'              => date($this->hCalendarNewsStoryDateFormat('m/d/y'), $file['hCalendarDate']),
                'hCalendarNewsHeadline'          => $this->hFileHeadingTitle($file['hFileTitle'], $file['hFileId']),
                'hCalendarNewsStoryCopy'         => $copy,
                'hFilePath'                      => $file['hFilePath'],
                'hCalendarNewsStoryClass'        => ($this->counter & 1)? ' hCalendarNewsStoryEven' : ' hCalendarNewsStoryOdd',
                'hCalendarNewsStoryReadMoreLink' => $moreLink
            )
        );
    }

    public function getPagation()
    {
        # @return void
        
        # @description 
        # <h2>Getting Pagation</h2>
        # <p>
        #    <i>Not yet implemented.</i>
        # </p>
        # @end
    
        $html = '';
        
        return $html;
    }
}

?>