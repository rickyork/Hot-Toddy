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

class hFinderCategories extends hPlugin {

    private $hFinder;

    public function hConstructor()
    {
        $categoryButtons = true;

        if (isset($_GET['path']) && !empty($_GET['setDefaultPath']) && $this->isCategoryPath($_GET['path']))
        {
            if ($this->hFinderBodyClass)
            {
                $this->hFinderBodyClass .= ' hFinderCategories';
            }
            else
            {
                $this->hFinderBodyClass = 'hFinderCategories';
            }

            $this->hFinderHasSearch = false;

            // The entire view *is* categories.
            $this->hFinderCategoriesEnabled = false;
            $this->hFinderSideColumnFilterCategories = false;
            $categoryButtons = false;
        }

        if ($this->beginsPath($this->hFinderPath, '/Categories') && !strstr($this->hFinderBodyClass, ' hFinderCategories'))
        {
            $this->hFinderBodyClass .= ' hFinderCategories';
        }

        $this->hFinder = $this->library('hFinder');

        $this->jQuery('Sortable');

        $this->getPluginFiles();
    }
}

?>