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
# <h1>Calendar Blog API</h1>
# <p>
#   <var>hCalendarBlog</var> is a customizable blogging plugin, and it handles most aspects
#   of presenting a blog.
# </p>
# @end

class hCalendarBlog extends hPlugin {

    private $hCalendarDatabase;
    private $hCategoryDatabase;
    private $hListDatabase;
    private $hFileComments;
    private $hFileCommentsDatabase;
    private $hList;
    private $hSearch;
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

        $fileId = $this->hCalendarFileThumbnailId(0, $this->hFileId);

        if ($fileId)
        {
            $this->hFacebookThumbnail = "http://".$this->hServerHost.$this->cloakSitesPath($this->getFilePathByFileId($fileId));
        }

        $categoryTags = array();

        $categoryId = 0;

        if ($this->hCalendarTagCategoryId(0))
        {
            $this->hCategoryDatabase = $this->database('hCategory');
            $this->hCategoryDatabase->setDatabaseReturnFormat('getResultsForTemplate');

            $categoryTags = $this->hCategoryDatabase->getSubCategories(
                $this->hCalendarTagCategoryId,
                $this->hCalendarBlogPath,
                true,
                true
            );

            if (!empty($_GET['hCategoryId']))
            {
                $this->hCalendarDatabase->setCategoryId($_GET['hCategoryId']);
                $categoryId = (int) $_GET['hCategoryId'];
            }
        }

        $rss = '/hCalendar/RSS?calendar='.$this->hFileId.'/'.$this->hCalendarId(1).'/'.$this->hCalendarCategoryId(3);

        $this->hFileRSS = $this->getTemplate(
            'hCalendarBlogRSSTemplate:RSS',
            array(
                'rss' => $rss
            )
        );

        # hCalendarRecentPosts = "1:2:3,9:5:3"
        # hCalendarRecentPostsTitles = "Title Here|Title Here"
        $blogRollLinks = $this->hCalendarBlogRollLinks(nil);
        $links = array();

        if (isset($blogRollLinks) && is_array($blogRollLinks) && count($blogRollLinks))
        {
            foreach ($blogRollLinks as $link)
            {
                $links['hCalendarBlogRollLink'][]  = $link->link;
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

        $recentPosts = $this->hCalendarDatabase->getCached(
            'hCalendarBlogRecentPosts',
            $this->hCalendarId(1),
            $this->hCalendarCategoryId(3),
            $this->hFileId.','.$categoryId
        );

        if ($recentPosts === false)
        {
            $recentPosts = $this->getTemplate(
                'hCalendarBlogRecentPostsTemplate:Recent Posts',
                array(
                    'hCalendarRecentPostsTitle' => $this->hCalendarRecentPostsTitle('Recent Posts'),
                    'hCalendarRecentPosts'      => $this->getRecentPosts(),
                    'hCalendarBlogRollTitle'    => $this->hCalendarBlogRollTitle('Blog Roll'),
                    'hCalendarBlogRollLinks'    => $links
                )
            );

            $this->hCalendarDatabase->saveToCache(
                $recentPosts,
                'hCalendarBlogRecentPosts',
                $this->hCalendarId(1),
                $this->hCalendarCategoryId(3),
                $this->hFileId.','.$categoryId
            );
        }

        $recentPosts = array($recentPosts);

        if ($this->hCalendarRecentPosts(nil))
        {
            $calendars = is_array($this->hCalendarRecentPosts)? $this->hCalendarRecentPosts : explode(',', $this->hCalendarRecentPosts);
            $calendarTitles = is_array($this->hCalendarRecentPostsTitles)? $this->hCalendarRecentPostsTitles : explode('|', $this->hCalendarRecentPostsTitles);

            foreach ($calendars as $i => $calendar)
            {
                list($calendarId, $calendarCategoryId, $calendarBlogLinkCount) = explode(':', $calendar);

                array_push(
                    $recentPosts,
                    $this->getTemplate(
                        'hCalendarBlogRecentPostsTemplate:Recent Posts',
                        array(
                            'hCalendarRecentPostsTitle' => $calendarTitles[$i],
                            'hCalendarRecentPosts'      => $this->getRecentPosts($calendarId, $calendarCategoryId, $calendarBlogLinkCount),
                            'hCalendarBlogRollLinks'    => false
                        )
                    )
                );
            }
        }

        if ($this->hCalendarRecentPosts(true))
        {
            $recentPosts = implode(
                $this->getTemplate('Recent Posts Separator'),
                $recentPosts
            );
        }

        if ($this->hCalendarRecentPostsVariable(nil))
        {
            $variable = $this->hCalendarRecentPostsVariable(nil);
            $this->$variable = $recentPosts;

            $recentPosts = '';
        }


        if ($this->hCalendarRSSLink(nil))
        {
            $this->hCalendarRSSLink = $rss;
        }
        else
        {
            $rss = $this->hCalendarRSSLink;
        }

        if (!$this->hCalendarRSSIcon(nil))
        {
            $this->hCalendarRSSIcon = '/images/icons/24x24/rss.png';
        }

        if (!$this->hCalendarIncludeRSSLink(true))
        {
            $rss = '';
        }

        $this->hFileDocument = $this->getTemplate(
            'hCalendarBlogTemplate:Blog',
            array(
                'hCalendarBlog'        => ($this->hCalendarBlogPost(false) || $this->hCalendarNewsPost(false)) && !isset($_GET['hCalendarMonth'])? $this->getPost() : $this->getPosts(),
                'rssLink'              => $rss,
                'rssIcon'              => $this->hCalendarRSSIcon('/images/icons/24x24/rss.png'),
                'hCalendarRecentPosts' => $recentPosts,
                'hCalendarBlogPath'    => $this->hCalendarBlogPath($this->hFilePath),
                'hCalendarOtherPosts'  => '',
                'hCalendarTagCategoryId' => $this->hCalendarTagCategoryId(0),
                'hCategoryTags'        => $categoryTags,
                'hCalendarBlogArchive' => $this->hCalendarBlogArchiveEnabled(true)? $this->getArchive() : '',
                'postCount'            => $this->postCount
            )
        );
    }

    private function getPost()
    {
        # @return HTML

        # @description
        # <h2>Fetching an Individual Blog Post</h2>
        # <p>
        #   <var>getPost()</var> retrieves and displays a single post, and is used when
        #   a user clicks on a full article link (the article's permanent link).
        # </p>
        # @end

        if ($this->hCalendarBlogSyntaxHighlighting(false))
        {
            $this->getPluginCSS('/Library/SyntaxHighlighter/Styles/shCore', true);
            $this->getPluginCSS('/Library/SyntaxHighlighter/Styles/shThemeDefault', true);

            $this->getPluginJavaScript();
            $this->getPluginJavaScript('/Library/SyntaxHighlighter/Scripts/shCore', true);

            $brushes = array('Bash', 'Css', 'JScript', 'Php', 'Sql', 'Xml', 'Plain');

            foreach ($brushes as $brush)
            {
                $this->getPluginJavascript('/Library/SyntaxHighlighter/Scripts/shBrush'.$brush, true);
            }
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

            $file = $this->hCalendarDatabase->getFile($this->hFileId);

            $this->hFileComments = $this->library('hFile/hFileComments');

            if ($this->hCalendarBlogTemplateMarkup(false))
            {
                $this->hFileDocument = $this->parseTemplateMarkup($this->hFileDocument);
            }

            $post = $this->getTemplate(
                'hCalendarBlogSinglePostTemplate:Single Post',
                array(
                    'hCalendarBlogPostSummary'  => $this->hFileDocument,
                    'hCalendarBlogPost'         => $this->hCalendarBlogPost(false),
                    'hCalendarLink'             => $this->hCalendarLink(nil, $this->hFileId),
                    'hCalendarBlogPostTitle'    => $this->hFileHeadingTitle($this->hFileTitle),
                    'hCalendarBlogPostAuthor'   => $this->getAuthor($this->hUserId),
                    'hCalendarBlogPostDate'     => date($this->hCalendarBlogPostDateFormat('F j, Y'), $file['hCalendarDate']),
                    'hCalendarBlogPostComments' => $this->hFileComments->getComments()
                )
            );

/*
            $this->hFileCache->saveDocumentToCache('hCalendarBlogPost', $this->hFileId, $post);
        }
*/

        return $post;
    }

    private function getAuthor($userId)
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

        if ($this->hCalendarBlogPostAuthor(true))
        {
            switch (strtolower($this->hCalendarBlogPostAuthorNameSource('firstName')))
            {
                case 'displayname':
                {
                    $author = $this->user->getFullName($userId);
                    break;
                }
                case 'firstname':
                {
                    $author = $this->user->getFirstName($userId);
                    break;
                }
                case 'username':
                {
                    $author = $this->user->getUserName($userId);
                    break;
                }
            }
        }

        return $author;
    }

    private function getPosts()
    {
        # @return HTML

        # @description
        # <h2>Getting Blog Posts</h2>
        # <p>
        #
        # </p>
        # @end

        $posts = $this->hCalendarDatabase->getCached(
            'hCalendarBlogPosts',
            $this->hCalendarId(1),
            $this->hCalendarCategoryId(3),
            $_SERVER['REQUEST_URI']
        );

        if ($posts === false)
        {
            $this->hSearch = $this->library('hSearch');

            if (isset($_GET['hCalendarMonth']))
            {
                $time = (int) $_GET['hCalendarMonth'];
                $range = ">= {$time},<= ".mktime(0, 0, 0, date('n', $time), date('t', $time), date('Y', $time));
            }
            else
            {
                $range = nil;
            }

            if (!$range)
            {
                $this->hSearchResultsPerPage = $this->hCalendarBlogCount(7);
            }

            $files = $this->hCalendarDatabase->getFilesForTemplate(
                array(
                    'hCalendarDate' => $this->hCalendarBlogPostDateFormat('F j, Y'),
                    'hCalendarBeginTime' => $this->hCalendarBlogPostBeginTimeFormat('F j, Y h:i a'),
                    'hCalendarEndTime' => $this->hCalendarBlogPostEndTimeFormat('F j, Y h:i a')
                ),
                $this->hCalendarId(1),
                $this->hCalendarCategoryId(3),
                $range? 0 : $this->hSearch->getLimit(),
                $range,
                $range? false : true,
                'DESC'
            );

            $this->postCount = $this->hCalendarDatabase->getResultCount();

            $searchNavigation = nil;

            if (!$range)
            {
                $parameters = array();

                if (isset($_GET['hCategoryId']))
                {
                    $parameters['hCategoryId'] = $_GET['hCategoryId'];
                }

                $this->hSearch->setParameters($this->postCount);
                $searchNavigation = $this->hSearch->getNavigationHTML(
                    $this->hFilePath,
                    $parameters
                );
            }

            if (isset($files['hFileId']) && is_array($files['hFileId']) && count($files['hFileId']))
            {
                foreach ($files['hFileId'] as $i => $fileId)
                {
                    $files['hCalendarLink'][$i] = $this->hCalendarLink(nil, $fileId);
                }
            }

            $posts = $this->getTemplate(
                'hCalendarBlogPostTemplate:Post',
                array(
                    'hFiles' => $files,
                    'hCalendarBlogPostLinkArguments' => $this->hCalendarBlogPostLinkArguments(nil),
                    'hCalendarBlogPostAuthor' => $this->hCalendarBlogPostAuthor(true),
                    'hCalendarBlogFullName' => $this->hCalendarBlogFullName(false),
                    'hCalendarBlogFirstName' => $this->hCalendarBlogFirstName(true),
                    'hCalendarBlogUserName' => $this->hCalendarBlogUserName(false),
                    'hSearchNavigation' => $searchNavigation
                )
            );

            $this->hCalendarDatabase->saveToCache(
                $posts,
                'hCalendarBlogPosts',
                $this->hCalendarId(1),
                $this->hCalendarCategoryId(3),
                $_SERVER['REQUEST_URI']
            );
        }

        return $posts;
    }

    private function getRecentPosts($calendarId = 0, $calendarCategoryId = 0, $calendarBlogLinkCount = 0)
    {
        # @return HTML

        # @description
        # <h2>Getting Recent Posts</h2>
        # <p>
        #   Gets recent posts made to the blog from the calendar specified in <var>$calendarId</var>,
        #   the calendar category specified in <var>$calendarCategoryId</var>, and limits to the
        #   number of recent posts specified in <var>$calendarBlogLinkCount</var>.   These arguments
        #   are all optional, if left unspecified the defaults will be controlled by framework variable
        #   configurations.
        # </p>
        # <p>
        #   The following framework variables can be configured
        #   to customize the recent posts template:
        # </p>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>Variable</th>
        #           <th>Default</th>
        #           <th>Description</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td class='code'>hCalendarBlogRecentPostTemplate</td>
        #           <td class='code'><a href='/System/Framework/Hot Toddy/hCalendar/hCalendarBlog/HTML/Recent Post.html' target='_blank'>/hCalendar/hCalendarBlog/HTML/Recent Post.html</a></td>
        #           <td>
        #               The HTML template used to format recent post links.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hCalendarBlogRecentPostDateFormat</td>
        #           <td class='code'>m/d/y</td>
        #           <td>
        #               The date format for the date the article was posted.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hCalendarBlogRecentPostBeginTimeFormat</td>
        #           <td class='code'>m/d/y h:i a</td>
        #           <td>
        #               The date format for the date/time the event starts (if
        #               applicable).
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hCalendarBlogRecentPostEndTimeFormat</td>
        #           <td class='code'>m/d/y h:i a</td>
        #           <td>
        #               The date format for the date/time the event ends (if
        #               applicable).
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hCalendarId</td>
        #           <td class='code'>1</td>
        #           <td>
        #               The calendarId of the Hot Toddy calendar the blog posts
        #               are posted to.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hCalendarCategoryId</td>
        #           <td class='code'>3</td>
        #           <td>
        #               The calendarCategoryId of the Hot Toddy calendar category the blog posts
        #               are posted to.  The default, 3, is the 'Blog' category.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hCalendarBlogRecentLinkCount</td>
        #           <td class='code'>12</td>
        #           <td>
        #               The number of recent posts to display.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hCalendarBlogPostLinkArguments</td>
        #           <td class='code'>nil</td>
        #           <td>
        #               An optional URL query string that can be added to the urls.
        #           </td>
        #       </tr>
        #   </tbody>
        # </table>
        # <p>
        #   Recent posts are displayed in descending order, with the most recent post appearing
        #   first.
        # </p>
        # @end

        return $this->getTemplate(
            'hCalendarBlogRecentPostTemplate:Recent Post',
            array(
                'hFiles' => $this->hCalendarDatabase->getFilesForTemplate(
                    array(
                        'hCalendarDate'      => $this->hCalendarBlogRecentPostDateFormat('m/d/y'),
                        'hCalendarBeginTime' => $this->hCalendarBlogRecentPostBeginTimeFormat('m/d/y h:i a'),
                        'hCalendarEndTime'   => $this->hCalendarBlogRecentPostEndTimeFormat('m/d/y h:i a'),
                    ),
                    $calendarId > 0? $calendarId : $this->hCalendarId(1),
                    $calendarCategoryId > 0? $calendarCategoryId : $this->hCalendarCategoryId(3),
                    $calendarBlogLinkCount > 0? $calendarBlogLinkCount : $this->hCalendarBlogRecentLinkCount(12),
                    nil,
                    true,
                    'DESC'
                ),
                'hCalendarBlogPostLinkArguments' => $this->hCalendarBlogPostLinkArguments(nil),
            )
        );
    }

    private function getArchive()
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
        # @end

        $archive = $this->hCalendarDatabase->getCached(
            'hCalendarBlogArchive',
            $this->hCalendarId(1),
            $this->hCalendarCategoryId(3)
        );

        if ($archive === false)
        {
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

                $calendarBlogArchiveYears = '';

                foreach ($years as $year)
                {
                    $calendarBlogArchiveYears .= $this->getTemplate(
                        'hCalendarBlogArchiveYearsTemplate:Archive Year',
                        array(
                            'hCalendarBlogArchiveYear' => $year,
                            'hCalendarBlogArchiveMonths' => $this->hCalendarDatabase->getArchiveMonths(
                                $year, $this->hCalendarId(1), $this->hCalendarCategoryId(3)
                            ),
                            'hFilePath' => $this->hCalendarArchivePath($this->hFilePath)
                        )
                    );
                }

                $archive = $this->getTemplate(
                    'hCalendarBlogArchiveTemplate:Archive',
                    array(
                        'hCalendarBlogArchiveHeading' => $this->hCalendarBlogArchiveHeading('Archive'),
                        'hFilePath' => $this->hCalendarArchivePath($this->hFilePath),
                        'hCalendarBlogArchivePresent' => $this->hCalendarBlogArchivePresent('Current Posts'),
                        'hCalendarBlogArchiveYears' => $calendarBlogArchiveYears
                    )
                );

                $this->hCalendarDatabase->saveToCache(
                    $archive,
                    'hCalendarBlogArchive',
                    $this->hCalendarId(1),
                    $this->hCalendarCategoryId(3)
                );
            }
        }

        return $archive;
    }
}

?>