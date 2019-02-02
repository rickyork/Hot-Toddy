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
# <h1>Blog API</h1>
# <p>
#   <var>hCalendarBlog</var> is a customizable blogging plugin, and it handles most aspects
#   of presenting a blog.
# </p>
# @end

class hBlogLibrary extends hPlugin {

    private $hCalendarDatabase;
    private $hCategoryDatabase;
    private $hListDatabase;
    private $hFileComments;
    private $hFileCommentsDatabase;
    private $hList;
    private $hPagination;
    private $hFacebook;
    private $hSearchNavigation = nil;

    private $postCount = nil;
    private $hCalendarResourceLastModified;

    public function hConstructor()
    {
        $this->hFileDocumentSelector = '';

        $this->getPluginCSS();

        $this->hFileGetMetaData = false;
        $this->hCalendarDatabase = $this->database('hCalendar');

        # hCalendarRecentPosts = "1:2:3,9:5:3"
        # hCalendarRecentPostsTitles = "Title Here|Title Here"
        $blogRollLinks = $this->hCalendarBlogRollLinks(nil);
        $links = array();

        if (isset($blogRollLinks) && is_array($blogRollLinks) && count($blogRollLinks))
        {
            foreach ($blogRollLinks as $link)
            {
                $links['hCalendarBlogRollLink'][] = $link->link;
                $links['hCalendarBlogRollLabel'][] = $link->label;

                if (isset($link->target))
                {
                    $links['hCalendarBlogRollLinkTarget'][] = $link->target;
                }
            }
        }
        else
        {
            $links = false;
        }
    }

    public function getBlog()
    {
        # @return HTML

        # @description
        # <h2>Getting a Blog</h2>
        # <p>
        #
        # </p>
        # @end

        if (($this->hCalendarBlogPost(false) || $this->hCalendarNewsPost(false)) && !isset($_GET['hCalendarMonth']))
        {
            $blog = $this->getPost();
        }
        else
        {
            $blog = $this->getPosts();
        }

        $archive = '';

        if ($this->hCalendarBlogArchiveEnabled(true))
        {
            $archive = $this->getArchive();
        }

        return $this->getTemplate(
            'hBlogTemplate:Blog',
            array(
                'blog' => $blog,
                'archive' => $archive
            )
        );
    }

    public function getTagData($categoryId, $blogPath)
    {
        # @return array

        # @description
        # <h2>Getting Tags</h2>
        # <p>
        #   Returns tag data (categories) for a blog post.
        # </p>
        # @end

        $categoryTags = array();
        $categoryId = 0;

        if (!empty($categoryId))
        {
            $this->hCategoryDatabase = $this->database('hCategory');
            $this->hCategoryDatabase->setDatabaseReturnFormat('getResultsForTemplate');

            $categoryTags = $this->hCategoryDatabase->getSubCategories(
                $categoryId,
                $blogPath,
                true,
                true
            );

            if (!empty($_GET['hCategoryId']))
            {
                $this->hCalendarDatabase->setCategoryId($_GET['hCategoryId']);
                $categoryId = (int) $_GET['hCategoryId'];
            }
        }

        return array(
            'categoryTags' => $categoryTags,
            'categoryId' => (int) $categoryId
        );
    }

    public function getTags($tags, $filePath)
    {
        # @return HTML

        # @description
        # <h2>Getting Tags Formatted in HTML</h2>
        # <p>
        #   Returns HTML formatted tag data for a blog post.
        # </p>
        # <p>
        #   An alternative HTML template can be specified by setting the <var>hBlogTagTemplate</var>
        #   framework variable.
        # </p>
        # @end

        return $this->getTemplate(
            'hBlogTagTemplate:Tags',
            array(
                'tags' => $tags,
                'filePath' => $filePath
            )
        );
    }

    public function getRollCall($rollCallTitle, array $rollCallLinks)
    {
        # @return HTML

        # @description
        # <h2>Getting Roll Call Links Formatted in HTML</h2>
        # <p>
        #   Returns roll call links formatted in HTML.
        # </p>
        # <p>
        #   An alternative HTML template can be specified by setting the <var>hBlogRollCallTemplate</var>
        #   framework variable.
        # </p>
        # @end

        return $this->getTemplate(
            'hBlogRollCallTemplate:Roll Call',
            array(
                'rollCallTitle' => $rollCallTitle,
                'rollCallLinks' => $rollCallLinks
            )
        );
    }

    public function getRecentPosts($calendarId = 1, $calendarCategoryId = 3, array $options = array())
    {
        # @return HTML

        # @description
        # <h2>Getting Recent Posts Formatted in HTML</h2>
        # <p>
        #   Returns a listing of recent posts in HTML formatting. Recent posts are cached once
        #   retrieved, the cache is automatically updated when the modified time of the calendar
        #   resource is updated.
        # </p>
        # <p>
        #   An alternative HTML template can be specified by setting the <var>hBlogRecentPostsTemplate</var>
        #   framework variable.
        # </p>
        # @end

        $categoryId = 0;

        if (isset($options['categoryId']))
        {
            $categoryId = (int) $options['categoryId'];
        }

        $fileId = $this->hFileId;

        if (isset($options['fileId']))
        {
            $fileId = (int) $options['fileId'];
        }

        $recentPosts = $this->hCalendarDatabase->getCached(
            'hBlogRecentPosts',
            $calendarId,
            $calendarCategoryId,
            $fileId.','.$categoryId
        );

        if ($recentPosts === false)
        {
            $title = 'Recent Posts';

            if (isset($options['title']))
            {
                $title = $options['title'];
            }

            $dateFormat = 'm/d/Y';

            if (isset($options['dateFormat']))
            {
                $dateFormat = $options['dateFormat'];
            }

            $beginTimeFormat = 'm/d/Y h:i a';

            if (isset($options['beginTimeFormat']))
            {
                $beginTimeFormat = $options['beginTimeFormat'];
            }

            $endTimeFormat = 'm/d/Y h:i a';

            if (isset($options['endTimeFormat']))
            {
                $endTimeFormat = $options['endTimeFormat'];
            }

            $recentPostCount = 12;

            if (isset($options['recentPostCount']))
            {
                $recentPostCount = $options['recentPostCount'];
            }

            $linkArguments = nil;

            if (isset($options['linkArguments']))
            {
                $linkArguments = $options['linkArguments'];
            }

            $recentPosts = $this->getTemplate(
                'hBlogRecentPostsTemplate:Recent Posts',
                array(
                    'recentPostsTitle' => $title,
                    'recentPosts' => $this->hCalendarDatabase->getFilesForTemplate(
                        array(
                            'hCalendarDate' => $dateFormat,
                            'hCalendarBeginTime' => $beginTimeFormat,
                            'hCalendarEndTime' => $endTimeFormat,
                        ),
                        $calendarId,
                        $calendarCategoryId,
                        $recentPostCount,
                        nil,
                        true,
                        'DESC'
                    ),
                    'linkArguments' => $this->hBlogPostLinkArguments(nil)
                )
            );

            $this->hCalendarDatabase->saveToCache(
                $recentPosts,
                'hBlogRecentPosts',
                $calendarId,
                $calendarCategoryId,
                $fileId.','.$categoryId
            );
        }

        return $recentPosts;
    }

    public function getFacebookThumbnail($fileId = 0)
    {
        # @return URL

        # @description
        # <h2>Getting a Facebook Thumbnail</h2>
        # <p>
        #   Returns a file path to use for a Facebook thumbnail, which is used when users
        #   share a link to the blog post.
        # </p>
        # @end

        if (!$fileId)
        {
            $fileId = $this->hFileId;
        }

        return (
            "http://".
            $this->hServerHost.
            $this->cloakSitesPath(
                $this->getFilePathByFileId($fileId)
            )
        );
    }

    public function getRSSHTML($calendarId = 1, $calendarCategoryId = 3, $fileId = 0)
    {
        # @return HTML

        # @description
        # <h2>Getting RSS Formatted in HTML</h2>
        # <p>
        #   Returns a <var>&lt;link&gt;</var> element suitable for inclusion in the
        #   <var>&lt;head&gt;</var> of a document, which allows users to subscribe to
        #   RSS feeds using their browser's or application's UI.
        # </p>
        # <p>
        #   The following HTML template is used to generate the <var>&lt;link&gt;</var>
        #   element.
        # <code>
        #    &lt;link rel='alternate' type='application/rss+xml' title='RSS' href='{rss}' /&gt;
        # </code>
        # @end

        return $this->getTemplate(
            'hBlogRSSTemplate:RSS',
            array(
                'rss' => $this->getRSSPath(
                    $calendarId,
                    $calendarCategoryId,
                    $fileId
                )
            )
        );
    }

    public function getRSSLink($calendarId = 1, $calendarCategoryId = 3, $fileId = 0)
    {
        # @return HTML

        # @description
        # <h2>Getting RSS UI For the Blog</h2>
        # <p>
        #   Returns an HTML formatted RSS link.
        # </p>
        # <p>
        #   An alternative HTML template can be specified by setting the <var>hBlogRSSLinkTemplate</var>
        #   framework variable.
        # </p>
        # @end

        return $this->getTemplate(
            'hBlogRSSLinkTemplate:RSS Link',
            array(
                'rssLink' => $this->getRSSPath(
                    $calendarId,
                    $calendarCategoryId,
                    $fileId
                )
            )
        );
    }

    public function getRSSPath($calendarId = 1, $calendarCategoryId = 3, $fileId = 0)
    {
        # @return URI

        # @description
        # <h2>Getting an RSS URI</h2>
        # <p>
        #   Returns the path to the RSS feed.
        # </p>
        # @end

        if (!$fileId)
        {
            $fileId = $this->hFileId;
        }

        return (
            '/hCalendar/RSS?calendar='.
                $fileId.'/'.
                $calendarId.'/'.
                $calendarCategoryId
        );
    }

    public function &enableSyntaxHighlighting()
    {
        # @return hBlogLibrary

        # @description
        # <h2>Enabling Syntax Highlighting for Technical Content</h2>
        # <p>
        #   Includes the requisite CSS and JavaScript to enable syntax highlighting for
        #   a technically-oriented blog post.
        # </p>
        # @end

        $this->getPluginCSS('/Library/SyntaxHighlighter/Styles/shCore', true);
        $this->getPluginCSS('/Library/SyntaxHighlighter/Styles/shThemeDefault', true);

        $this->getPluginJavaScript();
        $this->getPluginJavaScript('/Library/SyntaxHighlighter/Scripts/shCore', true);

        $brushes = array(
            'Bash',
            'Css',
            'JScript',
            'Php',
            'Sql',
            'Xml',
            'Plain'
        );

        foreach ($brushes as $brush)
        {
            $this->getPluginJavascript('/Library/SyntaxHighlighter/Scripts/shBrush'.$brush, true);
        }

        return $this;
    }

    public function getPost($fileId = 0, array $options = array())
    {
        # @return HTML

        # @description
        # <h2>Fetching an Individual Blog Post</h2>
        # <p>
        #   <var>getPost()</var> retrieves and displays a single post, and is used when
        #   a user clicks on a full article link (the article's permanent link).
        # </p>
        # <p>
        #   An alternative HTML template can be specified by setting the <var>hBlogPostTemplate</var>
        #   framework variable.
        # </p>
        # @end

        $fileId = empty($fileId)? $this->hFileId : $fileId;

        if ($fileId != $this->hFileId)
        {
            $userId = $this->getFileOwner($fileId);
            $document = $this->getFileDocument($fileId);

            $title = $this->hFileHeadingTitle(
                $this->getFileTitle($fileId),
                $fileId
            );
        }
        else
        {
            $userId = $this->hUserId;
            $document = $this->hFileDocument;

            $title = $this->hFileHeadingTitle(
                $this->hFileTitle,
                $fileId
            );
        }

        $dateFormat = 'F j, Y';

        if (isset($options['dateFormat']))
        {
            $dateFormat = $options['dateFormat'];
        }

        $nameSource = 'firstName';

        if (isset($options['nameSource']))
        {
            $nameSource = $options['nameSource'];
        }

        #$this->hEditorTemplateEnabled = true;
        #$this->hFileDocumentSelector = 'div.hCalendarBlogPostDocumentInner';

/*
        // Caching is disabled until I define how comments should work within the
        // context of a cached post.
        $post = $this->hFileCache->getCachedDocument(
            'hCalendarBlogPost', $this->hFileId, $this->hCalendarResourceLastModified
        );

        if ($post === false)
        {
*/

            $file = $this->hCalendarDatabase->getFile($fileId);

            $this->hFileComments = $this->library('hFile/hFileComments');

            $post = $this->getTemplate(
                'hBlogPostTemplate:Post',
                array(
                    'blogPost' => $document,
                    'title' => $title,
                    'author' => $this->getAuthor(
                        $userId,
                        $nameSource
                    ),
                    'date' => date(
                        $dateFormat,
                        (int) $file['hCalendarDate']
                    ),
                    'comments' => $this->hFileComments->getComments()
                )
            );

/*
            $this->hFileCache->saveDocumentToCache('hCalendarBlogPost', $this->hFileId, $post);
        }
*/

        return $post;
    }

    public function getAuthor($userId, $nameSource = 'firstName')
    {
        # @return string | nil

        # @description
        # <h2>Retrieving the Blog Post Author</h2>
        # <p>
        #   Each post can optionally display the author of the post along with it.  Whether or not
        #   the post's author is displayed is controlled via the boolean <var>hCalendarBlogPostAuthor</var>
        #   framework variable, which is set to <var>true</var>, by default.  Which means, by default,
        #   each blog post's author's name is displayed.
        # </p>
        # <p>
        #   The blog post's author's name can be customized.  By default, the author's first name
        #   as defined in the author's <a href='/Hot Toddy/Documentation?hContact/hContact.library.php'>hContact</a>
        #   rolodex card (<var>hContactFirstName</var>) is used.  You may also use a full name
        #   (<var>hContactDisplayName</var>) or a user name (<var>hUserName</var>).
        #   To customize which name is used set the <var>hCalendarBlogPostAuthorNameSource</var> framework
        #   variable to one of <var>firstName</var>, <var>displayName</var>, or <var>userName</var>.
        # </p>
        # @end

        $author = '';

        switch ($nameSource)
        {
            case 'displayname':
            {
                return $this->user->getFullName($userId);
            }
            case 'firstname':
            {
                return $this->user->getFirstName($userId);
            }
            case 'username':
            default:
            {
                return $this->user->getUserName($userId);
            }
        }
    }

    public function getPosts($calendarId = 1, $calendarCategoryId = 3, array $options = array())
    {
        # @return HTML

        # @description
        # <h2>Getting Blog Posts</h2>
        # <p>
        #   Returns an HTML formatted listing of blog post summaries. The listing of summaries is
        #   cached after the first time the listing is accessed, and the cache is updated automatically
        #   when the calendar resource is modified.
        # </p>
        # <p>
        #   An alternative HTML template can be specified by setting the <var>hBlogPostsTemplate</var>
        #   framework variable.
        # </p>
        # @end

        $posts = $this->hCalendarDatabase->getCached(
            'hBlogPosts',
            (int) $calendarId,
            (int) $calendarCategoryId,
            $_SERVER['REQUEST_URI']
        );

        if ($posts === false)
        {
            $this->hPagination = $this->library('hPagination');

            if (isset($options['month']))
            {
                $time = (int) $options['month'];

                $range =
                    ">= {$time},<= ".
                    mktime(
                        0,
                        0,
                        0,
                        date('n', $time),
                        date('t', $time),
                        date('Y', $time)
                    );
            }
            else
            {
                $range = nil;
            }

            if (!$range)
            {
                $this->hPagination->setResultsPerPage(
                    isset($options['postsPerPage'])? (int) $options['postsPerPage'] : 7
                );
            }

            $dateFormat = 'F j, Y';

            if (isset($options['dateFormat']))
            {
                $dateFormat = $options['dateFormat'];
            }

            $beginTimeFormat = 'F j, Y h:i a';

            if (isset($options['beginTimeFormat']))
            {
                $beginTimeFormat = $options['beginTimeFormat'];
            }

            $endTimeFormat = 'F j, Y h:i a';

            if (isset($options['endTimeFormat']))
            {
                $endTimeFormat = $options['endTimeFormat'];
            }

            $fileId = $this->hFileId;

            if (isset($options['fileId']))
            {
                $fileId = $options['fileId'];
            }

            $files = $this->hCalendarDatabase->getFilesForTemplate(
                array(
                    'hCalendarDate' => $dateFormat,
                    'hCalendarBeginTime' => $beginTimeFormat,
                    'hCalendarEndTime' => $endTimeFormat
                ),
                (int) $calendarId,
                (int) $calendarCategoryId,
                $range? 0 : $this->hPagination->getLimit(),
                $range,
                $range? false : true,
                'DESC'
            );

            $this->postCount = $this->hCalendarDatabase->getResultCount();

            $pagination = nil;

            if (!$range)
            {
                $parameters = array();

                if (isset($_GET['categoryId']))
                {
                    $parameters['categoryId'] = $_GET['categoryId'];
                }

                $this->hPagination->setResultCount($this->postCount);

                $pagination = $this->hPagination->getNavigationTemplate(
                    $this->getFilePathByFileId($fileId),
                    $parameters
                );
            }

            $linkArguments = nil;

            if (isset($options['linkArguments']))
            {
                $linkArguments = $options['linkArguments'];
            }

            $nameSource = 'firstName';

            if (isset($options['nameSource']))
            {
                $nameSource = $options['nameSource'];
            }

            if (isset($files['hFileId']) && is_array($files['hFileId']) && count($files['hFileId']))
            {
                foreach ($files['hFileId'] as $i => $fileId)
                {
                    $files['author'][$i] = $this->getAuthor(
                        $files['hUserId'][$i],
                        $nameSource
                    );
                }
            }

            $posts = $this->getTemplate(
                'hBlogPostsTemplate:Posts',
                array(
                    'files' => $files,
                    'linkArguments' => $linkArguments,
                    'pagination' => $pagination
                )
            );

            $this->hCalendarDatabase->saveToCache(
                $posts,
                'hBlogPosts',
                (int) $calendarId,
                (int) $calendarCategoryId,
                $_SERVER['REQUEST_URI']
            );
        }

        return $posts;
    }

    public function getArchive($calendarId = 1, $calendarCategoryId = 3, array $options = array())
    {
        # @return HTML

        # @description
        # <h2>Getting Archive Links</h2>
        # <p>
        #   A call to <var>getArchive()</var> returns HTML lists of months and years.  Only months
        #   and years containing blog posts are returned, and the number of blog posts contained in
        #   a given month is listed beside a month.  Here's an example of the HTML returned:
        # </p>
        # <code>&lt;div id='hCalendarBlogArchiveWrapper'&gt;
        #    &lt;h4&gt;Archive&lt;/h4&gt;
        #    &lt;div id='hCalendarBlogArchive'&gt;
        #        &lt;ul class='hCalendarBlogArchiveYears'&gt;
        #            &lt;li class='hCalendarBlogArchivePresent'&gt;
        #                &lt;a href='/Blog.html'&gt;Current Posts&lt;/a&gt;
        #            &lt;/li&gt;
        #            &lt;li class='hCalendarBlogArchiveYear'&gt;
        #                &lt;span&gt;2012&lt;/span&gt;
        #                &lt;ul class='hCalendarBlogArchiveMonths'&gt;
        #                    &lt;li class='hCalendarBlogArchiveMonth'&gt;
        #                        &lt;a href='/Blog.html?hCalendarMonth=1330578000'&gt;March&lt;/a&gt;
        #                        &lt;span class='hCalendarBlogArchiveEventCount'&gt;1&lt;/span&gt;
        #                    &lt;/li&gt;
        #                &lt;/ul&gt;
        #            &lt;/li&gt;
        #            &lt;li class='hCalendarBlogArchiveYear'&gt;
        #                &lt;span&gt;2011&lt;/span&gt;
        #                &lt;ul class='hCalendarBlogArchiveMonths'&gt;
        #                    &lt;li class='hCalendarBlogArchiveMonth'&gt;
        #                        &lt;a href='/Blog.html?hCalendarMonth=1301630400'&gt;April&lt;/a&gt;
        #                        &lt;span class='hCalendarBlogArchiveEventCount'&gt;2&lt;/span&gt;
        #                    &lt;/li&gt;
        #                    &lt;li class='hCalendarBlogArchiveMonth'&gt;
        #                        &lt;a href='/Blog.html?hCalendarMonth=1304222400'&gt;May&lt;/a&gt;
        #                        &lt;span class='hCalendarBlogArchiveEventCount'&gt;1&lt;/span&gt;
        #                    &lt;/li&gt;
        #                    &lt;li class='hCalendarBlogArchiveMonth'&gt;
        #                        &lt;a href='/Blog.html?hCalendarMonth=1309492800'&gt;July&lt;/a&gt;
        #                        &lt;span class='hCalendarBlogArchiveEventCount'&gt;1&lt;/span&gt;
        #                    &lt;/li&gt;
        #                    &lt;li class='hCalendarBlogArchiveMonth'&gt;
        #                        &lt;a href='/Blog.html?hCalendarMonth=1312171200'&gt;August&lt;/a&gt;
        #                        &lt;span class='hCalendarBlogArchiveEventCount'&gt;1&lt;/span&gt;
        #                    &lt;/li&gt;
        #                    &lt;li class='hCalendarBlogArchiveMonth'&gt;
        #                        &lt;a href='/Blog.html?hCalendarMonth=1320120000'&gt;November&lt;/a&gt;
        #                        &lt;span class='hCalendarBlogArchiveEventCount'&gt;1&lt;/span&gt;
        #                    &lt;/li&gt;
        #                &lt;/ul&gt;
        #            &lt;/li&gt;
        #        &lt;/ul&gt;
        #     &lt;/div&gt;
        # &lt;/div&gt;</code>
        # <p>
        #   The blog archive is cached to speed up display of blog-related pages.  See:
        #   <a href='/Hot Toddy/Documentation?hCalendar/hCalendar.database.php#getCached'>getCached()</a>
        #   for more information about caching.
        # </p>
        # <p>
        #   Years are returned in descending order, with the most recent first, and the oldest appearing last,
        #   then months within each year are sorted in ascending order.
        # </p>
        # <p>
        #   An alternative HTML template can be specified by setting the <var>hBlogArchiveTemplate</var>
        #   framework variable.
        # </p>
        # @end

        $archive = $this->hCalendarDatabase->getCached(
            'hBlogArchive',
            (int) $calendarId,
            (int) $calendarCategoryId
        );

        if ($archive === false)
        {
            $fileId = $this->hFileId;

            if (isset($options['fileId']))
            {
                $fileId = $options['fileId'];
            }

            $filePath = $this->hFilePath;

            if ($fileId != $this->hFileId)
            {
                $filePath = $this->getFilePathByFileId($fileId);
            }

            $oldestDate = $this->hCalendarDatabase->getOldestDate(
                $this->hCalendarId(1),
                $this->hCalendarCategoryId(3)
            );

            $newestDate = $this->hCalendarDatabase->getNewestDate(
                $this->hCalendarId(1),
                $this->hCalendarCategoryId(3)
            );

            if (is_numeric($oldestDate) && is_numeric($newestDate) && $oldestDate > 0 && $newestDate > 0)
            {
                # Get the range of years for the archive.
                $years = range(
                    date('Y', $oldestDate),
                    date('Y', $newestDate)
                );

                rsort($years);

                # To build links to archived events
                # Get the first date and the last date for all events
                $months = range(1, 12);

                $archiveYears = array();

                $i = 0;

                foreach ($years as $year)
                {
                    $archiveYears['year'][$i] = $year;

                    $archiveYears['months'][$i] = $this->hCalendarDatabase->getArchiveMonths(
                        $year,
                        (int) $calendarId,
                        (int) $calendarCategoryId
                    );

                    $archiveYears['filePath'][$i] = $this->hCalendarArchivePath($filePath);
                    $i++;
                }

                $archiveHeading = 'Archive';

                if (isset($options['archiveHeading']))
                {
                    $archiveHeading = $options['archiveHeading'];
                }

                $archivePath = $filePath;

                if (isset($options['archivePath']))
                {
                    $archivePath = $options['archivePath'];
                }

                $currentPostsLabel = 'Current Posts';

                if (isset($options['currentPostsLabel']))
                {
                    $currentPostsLabel = $options['currentPostsLabel'];
                }

                $archive = $this->getTemplate(
                    'hBlogArchiveTemplate:Archive',
                    array(
                        'archiveHeading' => $archiveHeading,
                        'filePath' => $archivePath,
                        'present' => $currentPostsLabel,
                        'years' => $archiveYears
                    )
                );

                $this->hCalendarDatabase->saveToCache(
                    $archive,
                    'hBlogArchive',
                    (int) $calendarId,
                    (int) $calendarCategoryId
                );
            }
        }

        return $archive;
    }
}

?>