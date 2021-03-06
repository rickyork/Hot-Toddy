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
# <h1>Searching Docuements with Search Plugin</h1>
#
#
# @end

class hSearch extends hPlugin {

    private $sourceSearch;
    private $isWebAdmin;
    private $hSearch;
    private $hSearchDatabase;

    public function hConstructor()
    {
        $this->hEditorTemplateEnabled = false;
        $this->hSearch = $this->library('hSearch');
        $this->hSearchDatabase = $this->database('hSearch');

        $this->isWebAdmin = $this->inGroup('Website Administrators');

        if (!$this->isWebAdmin)
        {
            hString::scrubArray($_GET);
        }

        $this->query();

        $this->getPluginFiles();
        $this->getPluginCSS('ie6');
    }

    public function query()
    {
        $this->hFileGetMetaData = true;

        $count = 0;
        $results = array();
        $searchTerms = '';
        $this->hSearchTerms = '';

        if (!$this->hFileTitle)
        {
            $this->hFileTitle = 'Search';
        }

        if (isset($_GET['q']) && !empty($_GET['q']))
        {
            $searchTerms = $_GET['q'];
            $this->hSearchTerms = $_GET['q'];
        }

        if (isset($_GET['method']))
        {
            $this->hSearchMethod = $_GET['method'];
        }

        switch ($this->hSearchMethod)
        {
            case 'Source':
            {
                $results = $this->hSearchDatabase->querySource(
                    $searchTerms,
                    $this->hSearch->getLimit()
                );

                break;
            }
            case 'Recent':
            {
                $this->hSearchHistoryRecent = true;
            }
            case 'History':
            {
                $results = $this->hSearchDatabase->queryHistory(
                    $searchTerms,
                    $this->hSearch->getLimit(),
                    $this->hSearchDirectory('/'.$this->hFrameworkSite),
                    $this->hSearchCategories(array())
                );

                $count = $this->hSearchDatabase->getResultCount();
                break;
            }
            case 'Like':
            {
                if (!empty($searchTerms))
                {
                    $results = $this->hSearchDatabase->queryLike(
                        $searchTerms,
                        $this->hSearch->getLimit(),
                        $this->hSearchDirectory('/'.$this->hFrameworkSite),
                        $this->hSearchCategories(array())
                    );
                }
                else
                {
                    $results = $this->hSearchDatabase->queryEmpty(
                        $this->hSearch->getLimit(),
                        $this->hSearchDirectory('/'.$this->hFrameworkSite),
                        $this->hSearchCategories(array())
                    );
                }

                $count = $this->hSearchDatabase->getResultCount();
                break;
            }
            case 'Default':
            default:
            {
                if (!empty($searchTerms))
                {
                    $results = $this->hSearchDatabase->query(
                        $searchTerms,
                        $this->hSearch->getLimit(),
                        $this->hSearchDirectory('/'.$this->hFrameworkSite),
                        $this->hSearchCategories(array())
                    );
                }
                else
                {
                    $results = $this->hSearchDatabase->queryEmpty(
                        $this->hSearch->getLimit(),
                        $this->hSearchDirectory('/'.$this->hFrameworkSite),
                        $this->hSearchCategories(array())
                    );
                }

                $count = $this->hSearchDatabase->getResultCount();
            }
        }

        if ($count > 0)
        {
            $this->hSearch->setParameters($count);

            if (!empty($searchTerms))
            {
                $this->hFileTitle = 'Search Results for <b>'.$searchTerms.'</b>';
            }

            $this->hSearchResults = (
                (
                    ($this->hSearchPage * $this->hSearchResultsPerPage) -
                    $this->hSearchResultsPerPage
                ) + $this->hSearchResultsPerPage
            );
        }

        $hSearchForm = '';

        if ($this->hSearchForm(true))
        {
            $hSearchForm = $this->getTemplate(
                $this->hSearchFormTemplatePath('Search'),
                array(
                    'hSearchTerms' => $searchTerms,
                    'hFilePath'    => $this->hFilePath
                )
            );
        }
        else if ($this->hSearchFormTemplate(null))
        {
            $hSearchForm = $this->hSearchFormTemplate;
        }

        $this->hFileDocument .= $this->getTemplate(
            'Results',
            array(
                'hSearchForm' => $hSearchForm,
                'hSearchResultCount' => $count,
                'hFiles' => $results,
                'hSearchTerms' => $searchTerms,
                'hSearchNavigation' => $this->hSearch->getNavigationHTML(
                    $this->hFilePath,
                    array(
                        'q' => $searchTerms
                    )
                ),
                'hSearchRepeatForm' => $this->hSearchRepeatForm(false)
            )
        );
    }
}

?>