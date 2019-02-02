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
# <h1>Pagination API</li>
# <p>
#    This object provides an API for either a reusable pagination UI, or a completely custom
#    pagination UI.  You can use either the pre-built HTML for pagination, or access
#    ready-to-use template variables to make your own pagination UI.  Pagination can then be
#    dropped into any query requiring that data be broken over multiple pages.
# </p>
# <p>
#    Methods common to any pagination implementation are the following:
# </p>
# <ul>
#    <li>
#        <a href='#getCSS'>getCSS()</a> <i>(Optional)</i><br />
#        Include <var>hPagination.css</var> in the document, if you're using the pre-built pagination HTML template.
#    </li>
#    <li>
#        <a href='#parseCursor'>parseCursor()</a> <i>(Required)</i><br />
#        Set up the <var>LIMIT</var> clause for the query.
#    </li>
#    <li>
#        <a href='#getLimit'>getLimit()</a> <i>(Required)</i><br />
#        Return the <var>LIMIT</var> clause for the query.
#    </li>
#    <li>
#        <a href='#setResultCount'>setResultCount()</a> <i>(Required)</i><br />
#        Set up variables needed for generating navigation.
#    </li>
#    <li>
#        <a href='#getNavigationVariables'>getNavigationVariables()</a> <i>(Optional)</i><br />
#        Get template variables for a custom pagination implementation.
#    </li>
#    <li>
#        <a href='#getNavigationTemplate'>getNavigationTemplate()</a> <i>(Optional)</i><br />
#        Get the pre-build pagination HTML template.
#    </li>
# </ul>
# <p>
#    The preceding method calls can be used to create a complete pagination implementation.
# </p>
# @end

class hPaginationLibrary extends hPlugin {

    private $orderBy = 'ASC';
    private $page = 0;
    private $chapter = 0;
    private $cursor;
    private $resultCount = 0;
    private $pageCount = 0;
    private $chapterCount = 0;

    private $resultsPerPage = 0;
    private $pagesPerChapter = 0;

    private $chapterUpper = 0;
    private $chapterLower = 0;

    private $parameters;
    private $path;
    private $rawCursor;

    public function hConstructor($arguments)
    {
        if (isset($arguments['cursor']))
        {
            $this->rawCursor = $arguments['cursor'];
        }
        else
        {
            $this->rawCursor = $this->get('cursor', 'searchCursor', nil);
        }
    }

    public function &getCSS()
    {
        # @return hPaginationLibrary

        # @description
        # <h2>Getting Pagination CSS</h2>
        # <p>
        #    Includes the ready-made <var>hPagination</var> CSS file that can accompany the pre-built
        #    HTML pagination UI.
        # </p>
        # @end
        $this->getPluginCSS();

        return $this;
    }

    public function &setResultsPerPage($numberOfResultsPerPage)
    {
        # @return hPaginationLibrary

        # @description
        # <h2>Setting the Number of Results Per Page</h2>
        # <p>
        #     Sets the number of results that will appear on each page.
        # </p>
        # @end

        $this->resultsPerPage = (int) $numberOfResultsPerPage;
        return $this;
    }

    public function getResultsPerPage()
    {
        # @return integer

        # @description
        # <h2>Getting the Number of Results Per Page</h2>
        # <p>
        #    Returns the number of results per page.
        # </p>
        # @end

        return $this->resultsPerPage;
    }

    public function &setPagesPerChapter($numberOfPagesPerChapter)
    {
        # @return hPaginationLibrary

        # @description
        # <h2>Setting the Number of Pages Per Chapter</h2>
        # <p>
        #    Sets the number of pages per chapter.
        # </p>
        # @end

        $this->pagesPerChapter = (int) $numberOfPagesPerChapter;

        return $this;
    }

    public function &parseCursor()
    {
        # @return hPaginationLibrary

        # @description
        # <h2>Parsing the Cursor</h2>
        # <p>
        #    Parses the <i>cursor</i> <var>$rawCursor</var> property, which contains the page,
        #    chapter, and how results should be sorted (ascending or descending)
        # </p>
        # <code>
        #    $_GET['cursor'] = '1/1/1'; # Sort ASC / Page 1 / Chapter 1
        #    $_GET['cursor'] = '0/1/1'; # Sort DESC / Page 1 / Chapter 1
        # </code>
        # @end

        if (!$this->resultsPerPage)
        {
            $this->setResultsPerPage($this->hPaginationResultsPerPage(10));
        }

        if (!$this->pagesPerChapter)
        {
            $this->setPagesPerChapter($this->hPaginationPagesPerChapter(7));
        }

        if (empty($this->cursor))
        {
            if (!empty($this->rawCursor))
            {
                if (substr_count($this->rawCursor, '/') != 2)
                {
                    $this->orderBy = 'ASC';
                    $this->page    = 1;
                    $this->chapter = 1;
                }
                else
                {
                    list($orderBy, $page, $chapter) = explode('/', $this->rawCursor);

                    $this->orderBy = (bool) $orderBy? 'ASC' : 'DESC';
                    $this->page = (int) $page;
                    $this->chapter = (int) $chapter;
                }
            }
            else
            {
                $this->orderBy = 'ASC';
                $this->page = 1;
                $this->chapter = 1;
            }

            $this->cursor = ($this->orderBy == 'ASC'? 1 : 0).'/'.$this->page.'/'.$this->chapter;
        }

        return $this;
    }

    public function getCursor()
    {
        # @return string

        # @description
        # <h2>Getting the Cursor</h2>
        # <p>
        #     Returns the cursor, which originates from the <var>GET</var> <var>cursor</var> argument.
        # </p>
        # @end

        return $this->cursor;
    }

    public function getPage()
    {
        # @return integer

        # @description
        # <h2>Getting the Page Number</h2>
        # <p>
        #    Returns the current page number.
        # </p>
        # @end

        return $this->page;
    }

    public function getPageCount()
    {
        # @return integer

        # @description
        # <h2>Getting the Page Count</h2>
        # <p>
        #    Returns the total page count.
        # </p>
        # @end

        return $this->pageCount;
    }

    public function getSortDirection()
    {
        # @return string

        # @description
        # <h2>Getting the Sort Direction</h2>
        # <p>
        #     Returns the sort direction, which is one of <i>ASC</i> or <i>DESC</i>.
        # </p>
        # @end

        return $this->orderBy;
    }

    public function &setSortDirection($sortDirection)
    {
        # @return hPaginationLibrary

        # @description
        # <h2>Setting the Sort Direction</h2>
        # <p>
        #    Sets the sort direction. 1 or ASC is ascending. 0 or DESC is descending.
        #    The <var>$sortDirection</var> argument is not case-sensitive.
        # </p>
        # @end

        switch (strtoupper($sortDirection))
        {
            case 'ASC':
            case 'DESC':
            {
                $this->orderBy = strtoupper($sortDirection);
                break;
            }
            case 0:
            {
                $this->orderBy = 'DESC';
                break;
            }
            case 1:
            {
                $this->orderBy = 'ASC';
                break;
            }
            default:
            {
                $this->warning(
                    "Invalid sort direction provided.",
                    __FILE__,
                    __LINE__
                );
            }
        }

        return $this;
    }

    public function &setResultCount($resultCount)
    {
        # @return hPaginationLibrary

        # @description
        # <h2>Setting the Result Count</h2>
        # <p>
        #    Use this method to set the <var>resultCount</var> after performing a query.  The
        #    query should specify <var>SQL_CALC_FOUND_ROWS</var> to get the number of rows
        #    independent of the <var>LIMIT</var> clause.
        # </p>
        # <p>
        #    This method also sets the <var>pageCount</var>.
        # </p>
        # <p>
        #    This method should be called just after your query is performed, prior to calling
        #    <a href='#getNavigationVariables'>getNavigationVariables()</a> or <a href='#getNavigationTemplate'>getNavigationTemplate()</a>
        # </p>
        # @end

        $this->parseCursor();

        $this->resultCount = (int) $resultCount;
        $this->pageCount = ceil($this->resultCount / $this->resultsPerPage);

        return $this;
    }

    public function getLimit()
    {
        # @return string

        # @description
        # <h2>Getting the LIMIT Clause</h2>
        # <p>
        #    Returns the SQL <var>LIMIT</var> clause that should be used based on the
        #    <var>cursor</var> and the <var>resultsPerPage</var>.  To get the <var>LIMIT</var>
        #    clause, only <a href='#parseCursor'>parseCursor()</a> need to have been
        #    called at that point.
        # </p>
        # @end

        $this->parseCursor();

        return (($this->page * $this->resultsPerPage) - $this->resultsPerPage).','.$this->resultsPerPage;
    }

    public function getLink($page, $chapter, $path = '', $parameters = array())
    {
        # @return string

        # @description
        # <h2>Getting a Pagination Link</h2>
        # <p>
        #    Returns a pagination link with <var>cursor</var> argument, and whatever other
        #    arguments you'd like to include in the URL.  The <var>$page</var> and <var>$chapter</var>
        #    must be provided to generate a link.  If no <var>$path</var> is specified the
        #    current <var>hFilePath</var> for the current page will be used.  <var>$parameters</var>
        #    can be provided as an associative array in the <var>$parameters</var> argument.
        # </p>
        # @end

        if (!empty($path))
        {
            $this->path = $path;
        }

        if (empty($this->path))
        {
            $this->path = $this->hFilePath;
        }

        if (count($parameters))
        {
            $this->parameters = $parameters;
        }

        if (isset($_GET['method']) && !in_array('method', $this->parameters))
        {
            $this->parameters['method'] = $_GET['method'];
        }

        return $this->path.'?'.
            $this->getQueryString(
                array_merge(
                    $this->parameters,
                    array(
                        'cursor' => ($this->orderBy == 'ASC'? 1 : 0)."/{$page}/{$chapter}"
                    )
                )
            );
    }

    public function getNavigationVariables($path = null, $parameters = array())
    {
        # @return array

        # @description
        # <h2>Getting Pagination Navigation Variables</h2>
        # <p>
        #    Returns all template variables required to create a custom pagination UI.
        #    The path that should be used in navigation links can be provided in <var>$path</var>,
        #    and additional <var>GET</var> arguments that should appear in each link beyond
        #    those required for pagination can be provided in <var>$parameters</var> as an
        #    associative array.
        # </p>
        # @end

        if (empty($path))
        {
            $this->path = $this->hFilePath;
        }
        else
        {
            $this->path = $path;
        }

        $this->parameters = $parameters;

        if (empty($this->cursor))
        {
            $this->parseCursor();
        }

        # 1. Divide the resultCount by the resultsPerPage to get the pageCount
        if ($this->pageCount > 1)
        {
            # The chapter is found in the same way as the limit,
            # though this time the current chapter is used instead of the current page
            # and the pages per chapter is used instead of results per page.
            $this->chapterUpper = $this->chapter * $this->pagesPerChapter;
            $this->chapterLower = $this->chapterUpper - $this->pagesPerChapter;
            $this->chapterCount = ceil($this->pageCount / $this->pagesPerChapter);

            $variables = array(
                'page'            => $this->page,
                'chapter'         => $this->chapter,
                'pageCount'       => $this->pageCount,
                'resultCount'     => $this->resultCount,
                'chapterCount'    => $this->chaperCount,
                'chapterUpper'    => $this->chapterUpper,
                'chapterLower'    => $this->chapterLower,
                'orderBy'         => $this->orderBy,
                'resultsPerPage'  => $this->resultsPerPage,
                'pagesPerChapter' => $this->pagesPerChapter
            );

            # Previous Page
            # Previous Chapter
            # Go to first chapter, first page
            #  <   <<   <<<

            # If the current page isn't 1, display previous page link
            # If the current chapter isn't 1, display previous chapter link
            # If the current page and chapter aren't 1, display first chapter, first page link
            if ($this->page && $this->page > 1)
            {
                $variables['previousPagePath'] = $this->getLink(
                    $this->page - 1,
                    $this->page - 1 <= $this->chapterLower? $this->chapter - 1 : $this->chapter
                );
            }
            else
            {
                $variables['previousPagePath'] = '';
            }

            if ($this->chapterLower && $this->chapter > 1)
            {
                $variables['chapterLowerPath'] = $this->getLink(
                    $this->chapterLower,
                    $this->chapter - 1
                );
            }

            if ($this->page > 1 && $this->chapter > 1)
            {
                $variables['firstPagePath'] = $this->getLink(1,1);
            }

            # Pages
            # 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 | 10
            for ($p = $this->chapterLower + 1; $p <= $this->chapterUpper && $p <= $this->pageCount; $p++)
            {
                if ($p == $this->page)
                {
                    $here = $p;
                    $variables['pageHere'] = $here;
                    break;
                }
            }

            $variables['pages'] = array();

            for ($p = $this->chapterLower + 1; $p <= $this->chapterUpper && $p <= $this->pageCount; $p++)
            {
                $variables['pages']['pageFirst'][] = ($p == ($this->chapterLower + 1));
                $variables['pages']['pageLast'][]  = ($p == $this->chapterUpper || $p == $this->pageCount);
                $variables['pages']['page'][]      = $p;

                $variables['pages']['pagePath'][] = $this->getLink(
                    $p,
                    $this->chapter
                );
            }

            # Go to last chapter, last page
            # Next Chapter
            # Next Page
            # >>>   >>   >
            $variables['previousPageLabel']   = $this->hPaginationPreviousPageLabel('Previous');
            $variables['previousPageTooltip'] = $this->hPaginationPreviousPageTooltip('Previous Page');

            if ($this->chapter + 1 <= $this->chapterCount)
            {
                $variables['chapterUpperPath']  = $this->getLink($this->chapterUpper + 1, $this->chapter + 1);
                $variables['chapterUpperLabel'] = $this->chapterUpper + 1;
            }

            $variables['nextPageLabel']   = $this->hPaginationNextPageLabel('Next');
            $variables['nextPageTooltip'] = $this->hPaginationNextPageTooltip('Next Page');

            if ($this->page + 1 <= $this->pageCount)
            {
                $variables['nextPagePath']  = $this->getLink(
                    $this->page + 1,
                    $this->page + 1 > $this->chapterUpper? $this->chapter + 1 : $this->chapter
                );
            }
            else
            {
                $variables['nextPagePath'] = '';
            }

            $variables['lastPagePath'] = $this->getLink(
                $this->pageCount,
                $this->chapterCount
            );

            $variables['pagesPerChapter'] = $this->pagesPerChapter;

            $variables['hasPages'] = is_array($variables['pages']) && count($variables['pages']);

            ksort($variables);

            return $variables;
        }
        else
        {
            return array();
        }
    }

    public function getNavigationTemplate($path = '', $parameters = array())
    {
        # @return string

        # @description
        # <h2>Getting a Pagination Navigation Template</h2>
        # <p>
        #    Returns pagination links as HTML in a pre-built UI.
        #    The path that should be used in navigation links can be provided in <var>$path</var>,
        #    and additional <var>GET</var> arguments that should appear in each link beyond
        #    those required for pagination can be provided in <var>$parameters</var> as an
        #    associative array.
        # </p>
        # @end

        if (empty($path))
        {
            $path = $this->hFilePath;
        }

        if (false !== ($variables = $this->getNavigationVariables($path, $parameters)))
        {
            return $this->getTemplate(
                'Navigation',
                $variables
            );
        }
        else
        {
            return '';
        }
    }
}

?>