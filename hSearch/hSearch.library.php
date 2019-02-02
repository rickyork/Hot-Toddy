<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Search Library
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
# @description
# <h1>Search API</h1>
# <p class='hDocumentationWarning'>
#   The pagination portion of this API is DEPRECATED.  Please use
#   <a href='/Hot Toddy/Documentation?hPagination'>hPagination</a> instead.
# </p>
# @end

class hSearchLibrary extends hPlugin {

    private $cursor = array();
    private $sourceSearch;
    private $document;
    private $parameters = array();

    public function hConstructor()
    {
        $this->sourceSearch = ($this->inGroup('Website Administrators') && !empty($_GET['hSearchSource']));
    }

    public function &getCSS()
    {
        # @return hSearchLibrary

        # @description
        # <h2>Including Search CSS</h2>
        # <p>
        #
        # </p>
        # @end

        $this->getPluginCSS('hSearch');

        return $this;
    }

    # Returns the URL parameters used to track where in the
    #

    public function getCursor($cursor = '')
    {
        # @return string

        # @description
        # <h2>Getting a Search Cursor</h2>
        # <p>
        #
        # </p>
        # @end

        if (empty($cursor))
        {
            $cursor = $this->get('searchCursor', 'cursor', nil);
        }

        if (!empty($cursor))
        {
            $bits = explode('/', $cursor);

            if (!count($bits) || count($bits) != 3)
            {
                $this->hSearchOrderBy = 'ASC';  // Order By
                $this->hSearchPage    = 1;  // The page the user is currently on
                $this->hSearchChapter = 1;
            }
            else
            {
                $this->hSearchOrderBy = (bool) $bits[0]? 'ASC' : 'DESC';  // Order By
                $this->hSearchPage    = (int)  $bits[1];                  // The page the user is currently on
                $this->hSearchChapter = (int)  $bits[2];                  // A grouping of pages

                return $this->cursor;
            }
        }
        else
        {
            $this->hSearchOrderBy = 'ASC';  // Order By
            $this->hSearchPage    = 1;      // The page the user is currently on
            $this->hSearchChapter = 1;
        }

        return false;
    }

    public function getPageNumber()
    {
        # @return integer

        # @description
        # <h2>Getting the Page Number</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->hSearchPage;
    }

    public function &setParameters($resultCount)
    {
        # @return hSearchLibrary

        # @description
        # <h2>Setting Search Parameters</h2>
        # <p>
        #
        # </p>
        # @end

        $this->getCursor();

        $this->hSearchResultCount = $resultCount;
        $this->hSearchPageCount   = ceil($this->hSearchResultCount / $this->hSearchResultsPerPage(10));

        return $this;
    }

    public function getPageCount()
    {
        # @return integer

        # @description
        # <h2>Getting a Page Count</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->hSearchPageCount(0);
    }

    public function getLimit()
    {
        # @return string

        # @description
        # <h2>Getting the Limit Clause</h2>
        # <p>
        #
        # </p>
        # @end

        $this->getCursor();

        return (($this->hSearchPage * $this->hSearchResultsPerPage(10)) - $this->hSearchResultsPerPage(10)).','.$this->hSearchResultsPerPage(10);
    }

    private function getLink($page, $chapter)
    {
        # @return string

        # @description
        # <h2>Creating Search Links</h2>
        # <p>
        #
        # </p>
        # @end

        if (isset($_GET['method']) && !in_array('method', $this->parameters))
        {
            $this->parameters['method'] = $_GET['method'];
        }

        return $this->document.'?'.
            $this->getQueryString(
                array_merge(
                    array(
                        'hSearchCursor' => ($this->hSearchOrderBy == 'ASC'? 1 : 0)."/{$page}/{$chapter}"
                    ),
                    $this->parameters
                )
            );
    }

    public function getNavigation($document = null, $parameters = array())
    {
        # @return array

        # @description
        # <h2>Retrieving Search Navigation Variables</h2>
        # <p>
        #
        # </p>
        # @end

        if (empty($document))
        {
            $this->document = $this->hFilePath;
        }
        else
        {
            $this->document = $document;
        }

        $this->parameters = $parameters;

        if (empty($this->cursor))
        {
            $this->getCursor();
        }

        // 1. Divide the result count by the count_per_page to get the
        // page count
        if ($this->hSearchPageCount > 1)
        {
            // The chapter is found in the same way as the limit,
            // though this time the current chapter is used instead of the current page
            // and the pages per chapter is used instead of results per page.
            $this->hSearchChapterUpper = $this->hSearchChapter * $this->hSearchPagesPerChapter(7);
            $this->hSearchChapterLower = $this->hSearchChapterUpper - $this->hSearchPagesPerChapter(7);
            $this->hSearchChapterCount = ceil($this->hSearchPageCount / $this->hSearchPagesPerChapter(7));

            $variables = array();

            // Previous Page
            // Previous Chapter
            // Go to first chapter, first page
            //  <   <<   <<<

            // If the current page isn't 1, display previous page link
            // If the current chapter isn't 1, display previous chapter link
            // If the current page and chapter aren't 1, display first chapter, first page link
            if ($this->hSearchPage && $this->hSearchPage > 1)
            {
                $variables['hSearchPreviousPagePath'] = $this->getLink(
                    $this->hSearchPage - 1,
                    $this->hSearchPage - 1 <= $this->hSearchChapterLower? $this->hSearchChapter - 1 : $this->hSearchChapter
                );
            }
            else
            {
                $variables['hSearchPreviousPagePath'] = '';
            }

            if ($this->hSearchChapterLower && $this->hSearchChapter > 1)
            {
                $variables['hSearchChapterLowerPath'] = $this->getLink(
                    $this->hSearchChapterLower,
                    $this->hSearchChapter - 1
                );
            }

            if ($this->hSearchPage > 1 && $this->hSearchChapter > 1)
            {
                $variables['hSearchFirstPagePath'] = $this->getLink(1, 1);
            }

            // Pages
            // 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 | 10
            for ($page = $this->hSearchChapterLower + 1; $page <= $this->hSearchChapterUpper && $page <= $this->hSearchPageCount; $page++)
            {
                if ($page == $this->hSearchPage)
                {
                    $here = $page;
                    break;
                }
            }

            $variables['hSearchPages'] = array();

            for ($page = $this->hSearchChapterLower + 1; $page <= $this->hSearchChapterUpper && $page <= $this->hSearchPageCount; $page++)
            {
                $variables['hSearchPages']['hSearchPageFirst'][] = ($page == ($this->hSearchChapterLower + 1));
                $variables['hSearchPages']['hSearchPageLast'][] = ($page == $this->hSearchChapterUpper || $page == $this->hSearchPageCount);
                $variables['hSearchPages']['hSearchPageHere'][] = $here;
                $variables['hSearchPages']['hSearchPage'][] = $page;
                $variables['hSearchPages']['hSearchPagePath'][] = $this->getLink(
                    $page,
                    $this->hSearchChapter
                );
            }

            // Go to last chapter, last page
            // Next Chapter
            // Next Page
            // >>>   >>   >
            $variables['hSearchPreviousPageLabel'] = $this->hSearchPreviousPageLabel('Previous');
            $variables['hSearchPreviousPageTooltip'] = $this->hSearchPreviousPageTooltip('Previous Page');

            if ($this->hSearchChapter + 1 <= $this->hSearchChapterCount)
            {
                $variables['hSearchChapterUpperPath'] = $this->getLink(
                    $this->hSearchChapterUpper + 1,
                    $this->hSearchChapter + 1
                );

                $variables['hSearchChapterUpperLabel'] = $this->hSearchChapterUpper + 1;
            }

            $variables['hSearchNextPageLabel'] = $this->hSearchNextPageLabel('Next');
            $variables['hSearchNextPageTooltip'] = $this->hSearchNextPageTooltip('Next Page');

            if ($this->hSearchPage + 1 <= $this->hSearchPageCount)
            {
                $variables['hSearchNextPagePath']  = $this->getLink(
                    $this->hSearchPage + 1,
                    $this->hSearchPage + 1 > $this->hSearchChapterUpper? $this->hSearchChapter + 1 : $this->hSearchChapter
                );
            }
            else
            {
                $variables['hSearchNextPagePath'] = '';
            }

            $variables['hSearchLastPagePath'] = $this->getLink(
                $this->hSearchPageCount,
                $this->hSearchChapterCount
            );

            $variables['hSearchPagesPerChapter'] = $this->hSearchPagesPerChapter(7);

            return $variables;
        }
        else
        {
            return false;
        }
    }

    public function getNavigationHTML($hFilePath = '', $parameters = array())
    {
        # @return string

        # @description
        # <h2>Retrieving Search Navigation HTML</h2>
        # <p>
        #
        # </p>
        # @end

        if (empty($hFilePath))
        {
            $hFilePath = $this->hFilePath;
        }

        if (false !== ($variables = $this->getNavigation($hFilePath, $parameters)))
        {
            return $this->getTemplate('Navigation', $variables);
        }
        else
        {
            return "";
        }
    }

    public function highlightTerms($text, $terms)
    {
        # @return string

        # @description
        # <h2>Highlighting Search Terms Within the Result Sets</h2>
        # <p>
        #
        # </p>
        # @end

        if (empty($text))
        {
           return '';
        }

        if (!$this->sourceSearch)
        {
            $text = strip_tags($text);
        }

        $terms = trim($terms);

        if (!$this->hSearchSource(false))
        {
            $terms = hString::decodeEntitiesAndUTF8($terms);
            $terms = str_replace(
              array('"', "'"),
              '',
              $terms
            );

            if (stristr($terms, ' '))
            {
                $terms = explode(' ', $terms);

                $pos = strpos($text, $terms[0]);
                $pos_start = $pos - 50;

                if ($pos_start < 0)
                {
                    $pos_start = 0;
                }

                if (!$this->sourceSearch)
                {
                     $text = substr($text, $pos_start, 200);
                }

                $text = $this->highlightHTML($text, $terms);

                if ($pos_start > 0)
                {
                    $text = '...'.$text;
                }

                $text .= '...';
            }
            else if (!empty($terms))
            {
                $pos = strpos($text, $terms);

                if ($pos > 225)
                {
                    $pos_start = $pos - 25;

                    if ($pos_start < 0)
                    {
                        $pos_start = 0;
                    }
                }
                else
                {
                    $pos_start = 0;
                }

                if (!$this->sourceSearch)
                {
                    $text = substr($text, $pos_start, 250);
                }

                if ($pos_start > 0)
                {
                    $text = '...'.$text;
                }

                $text .= '...';

                $text = $this->highlightHTML($text, $terms);
            }
            else
            {
              $text = substr($text, 0, 250);

              if (strlen($text) > 250)
              {
                  $text .= '...';
              }
            }
        }
        else
        {
            $text = "<div id='hSearchResultPreview'>".$this->highlightHTML($text, $terms)."</div>";
        }

        return $text;
    }

    public function highlightHTML($text, $terms)
    {


        $text = hString::entitiesToUTF8($text);

        if (is_array($terms))
        {
            for ($i = 0; each($terms); $i++)
            {
                $text = str_ireplace(
                    $terms[$i],
                    "<span class='hSearchTerms'>{$terms[$i]}</span>",
                    $text
                );
            }
        }
        else
        {
            $text = str_ireplace(
                $terms,
                "<span class='hSearchTerms'>{$terms}</span>",
                $text
            );
        }

        return $text;
    }
}

?>