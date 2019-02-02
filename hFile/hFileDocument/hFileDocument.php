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
# <h1>Document API</h1>
# <p>
#   <var>hFileDocument</var> is a collection of methods used to quickly get information
#   about documents stored in the Hot Toddy File System, or HtFS, in addition to some
#   methods that assist in quickly assembling templated HTML documents.
# </p>
# @end

class hFileDocument extends hPlugin {

    private $hFileIcon;
    private $touchScrollIncluded = false;

    public function hConstructor()
    {
        # @return void

        # @description
        # <h2>Constructor</h2>
        # <p>
        #   <var>hFileDocument</var>'s constructor includes the
        #   <a href='/Hot Toddy/Documentation?hFile/hFileIcon/hFileIcon.library.php' class='code'>hFile/hFileIcon</a>
        #   library.  <var>hFileIcon</var> is used to get the right icon to display
        #   with a given document.
        # </p>
        # @end

        $this->hFileIcon = $this->library('hFile/hFileIcon');
    }

    public function getFileMetaDataForTemplate(array $results, $encoding = true)
    {
        # @return array

        # @description
        # <h2>Getting File Meta Data for Templates</h2>
        # <p>
        #   <var>getFileMetaDataForTemplate()</var> expects to get an array that's ready to
        #   go into a template.  If that array has a collection of files, identified by
        #   there being an index for <var>hFileId</var>, then it appends some additional file
        #   data to the array, and automatically decodes some fields that are encoded and
        #   escaped using HTML entities for database storage.
        # </p>
        # <p>
        #   It adds the following data:
        # </p>
        # <ul>
        #   <li><var>hFileIconPath</var> - The path to the file's icon at 32x32 resolution.</li>
        #   <li><var>hFilePath</var> - If the <var>hFilePath</var> is not already in the dataset, it is added.</li>
        #   <li>
        #       <var>hFileSideboxTitle</var> - If there is an <var>hListFileId</var> present, that means data
        #       relating to an <a href='/Hot Toddy/Documentation?hList' class='code'>hList</a> was added,
        #       and since there is list data present, the <var>hFileSideboxTitle</var>, which is an alternative
        #       title for a file that can be applied to a sidebox (as opposed to the meta title, or the
        #       heading title) is added to the dataset.
        #   </li>
        #   <li>
        #       <var>hFileHeadingTitle</var> - If there is no <var>hListFileId</var>, then the
        #       heading title is added to the dataset, if there is one.  The <var>hFileHeadingTitle</var> is
        #       an alternative document title that can be specified independently of a document's meta
        #       title.  In HTML documents, the document's meta title would be the content that goes
        #       in the <var>&lt;title&gt;</var> tags.
        #   </li>
        # </ul>
        # <p>
        #   The following data is HTML decoded using
        #   <a href='/Hot Toddy/Documentation?hString#decodeHTML' class='code'>hString::decodeHTML()</a>,
        #   if the <var>$encoding</var> argument is passed as <var>true</var>, it is by default:
        # </p>
        # <ul>
        #   <li>
        #       <var>hFileDescription</var> - This field is encoded. HTML special characters are replaced
        #       with HTML entities for storage in the database.  Decoding is done so that the field can
        #       be inserted into HTML.  This field is also used in the <var>&lt;meta name='description' /&gt;</var>
        #       tag in the HTML headers, when it is used there, any HTML tags are automatically stripped.
        #   </li>
        #   <li>
        #       <var>hFileDocument</var> - This field is encoded. HTML special characters are replaced
        #       with HTML entities for storage in the database.  Decoding is done so that the field can
        #       be inserted into HTML.  This field is used to store the primary content of a document.
        #   </li>
        # </ul>
        # @end

        if (!isset($results['hFileId']) && isset($results['hFileId']))
        {
            $results['hFileId'] = $results['hFileId'];
        }

        if (!isset($results['hListFileId']) && isset($results['hListFileId']))
        {
            $results['hListFileId'] = $results['hListFileId'];
        }

        // $results['hFileId'] = array();
        if (isset($results['hFileId']) && is_array($results['hFileId']))
        {
            foreach ($results['hFileId'] as $i => $fileId)
            {
                if (!isset($results['hFilePath'][$i]))
                {
                    $results['hFilePath'][$i] = $this->getFilePathByFileId($fileId);
                }

                $results['hFileIconPath'][$i] = $this->hFileIcon->getFileIconPath($fileId, nil, nil, $this->hFileIconResolution('32x32'));

                if (isset($results['hFileDescription'][$i]) && $encoding)
                {
                    $results['hFileDescription'][$i] = hString::decodeHTML($results['hFileDescription'][$i]);
                }

                if (isset($results['hFileDocument'][$i]) && $encoding)
                {
                    $results['hFileDocument'][$i] = hString::decodeHTML($results['hFileDocument'][$i]);
                }

                if (isset($results['hListFileId'][$i]))
                {
                    $hFileSideboxTitle = $this->hFileSideboxTitle($results['hFileTitle'][$i], $fileId);

                    if (empty($hFileSideboxTitle) && isset($results['hFileName'][$i]))
                    {
                        $hFileSideboxTitle = $results['hFileName'][$i];
                    }

                    $results['hFileSideboxTitle'][$i] = $hFileSideboxTitle;
                }
                else
                {
                    $hFileHeadingTitle = $this->hFileHeadingTitle($results['hFileTitle'][$i], $fileId);

                    if (empty($hFileHeadingTitle) && isset($results['hFileName'][$i]))
                    {
                        $hFileHeadingTitle = $results['hFileName'][$i];
                    }

                    $results['hFileHeadingTitle'][$i] = $hFileHeadingTitle;
                }
            }
        }

        return $results;
    }

    public function getFileDocument($fileId = 0)
    {
        # @return string

        # @description
        # <h2>Getting a File's Contentd</h2>
        # <p>
        # Returns the document body stored in the <var>hFileDocuments</var> database
        # for the specified <var>$fileId</var>.
        # </p>
        # <p>
        # The document is returned decoded, with embedded fileId references, such as {/fileId:1}
        # expanded as paths.
        # </p>
        # @end

        if (!is_numeric(($fileId)))
        {
            $fileId = $this->getFileIdByFilePath($fileId);
        }

        $hFileDocument = $this->hFileDocuments->selectColumn(
            'hFileDocument',
            array(
                'hFileId' => empty($fileId)? (int) $this->hFileId : (int) $fileId
            )
        );

        return $this->expandDocumentIds(hString::decodeHTML($hFileDocument));
    }

    public function getDocument()
    {
        # @return string

        # @description
        # <p>
        #   Returns the content of the <var>hFileDocument</var> framework variable.
        # </p>
        # <p class='hDocumentationWarning'>
        #   <b>Warning:</b> This method is deprecated and should not be used, instead
        #   of calling this method, you should simply access the <var>hFileDocument</var>
        #   framework variable directly.
        # </p>
        # @end

        return $this->hFileDocument;
    }

    public function getFileTitle($fileId)
    {
        # @return string

        # @description
        # <h2>Getting a File's Title</h2>
        # <p>
        # Returns the document title in <var>hFileTitle</var> for the specified <var>$fileId</var>.
        # </p>
        # @end

        return $this->hFileDocuments->selectColumn(
            'hFileTitle',
            (int) $fileId
        );
    }

    public function getFileDescription($fileId)
    {
        # @return string

        # @description
        # <h2>Getting a File's Description</h2>
        # <p>
        # Returns the HTML decoded meta description stored in <var>hFileDescription</var> for the specified <var>$fileId</var>.
        # </p>
        # @end

        return hString::decodeHTML(
            $this->hFileDocuments->selectColumn(
                'hFileDescription',
                (int) $fileId
            )
        );
    }

    public function getFileParentId($fileId)
    {
        # @return integer

        # @description
        # <h2>Getting a File's Parent Id</h2>
        # <p>
        # Returns the file parent stored in <var>hFileParentId</var> for the specified <var>$fileId</var>.
        # </p>
        # @end

        return (int) $this->hFiles->selectColumn(
            'hFileParentId',
            (int) $fileId
        );
    }

    public function getFileOwner($fileId)
    {
        # @return integer

        # @description
        # <h2>Getting a File's Owner</h2>
        # <p>
        #   Return's the <var>hUserId</var> of the owner of the specified <var>$fileId</var>.
        # </p>
        # @end

        return (int) $this->hFiles->selectColumn(
            'hUserId',
            (int) $fileId
        );
    }

    public function getFileKeywords($fileId)
    {
        # @return string

        # @description
        # <h2>Getting a File's Keywords</h2>
        # <p>
        #   Returns the <var>hFileKeywords</var> for the specified <var>$fileId</var>.
        # </p>
        # @end

        return $this->hFileDocuments->selectColumn(
            'hFileKeywords',
            (int) $fileId
        );
    }

    public function getFileInformation($fileId)
    {
        # @return array

        # @description
        # <h2>Getting a File's Information</h2>
        # <p>
        #   Returns the following information for the specified <var>$fileId</var>:
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td>hFilePath</td>
        #       </tr>
        #       <tr>
        #           <td>hFileId</td>
        #       </tr>
        #       <tr>
        #           <td>hFileName</td>
        #       </tr>
        #       <tr>
        #           <td>hFileParentId</td>
        #       </tr>
        #       <tr>
        #           <td>hFileTitle</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        return $this->hDatabase->selectAssociative(
            array(
                'hFilePath',
                'hFiles' => array(
                    'hFileId',
                    'hFileName',
                    'hFileParentId'
                ),
                'hFileDocuments' => array(
                    'hFileTitle'
                )
            ),
            array(
                'hFiles',
                'hFileDocuments'
            ),
            array(
                'hFiles.hFileId' => array(
                    array('=', 'hFileDocuments.hFileId'),
                    array('=', (int) $fileId)
                )
            )
        );
    }

    public function getFilePlugin($fileId)
    {
        # @return array

        # @description
        # <h2>Getting a File's Plugin</h2>
        # <p>
        #   Returns the <var>hPlugin</var> for the
        #   specified <var>$fileId</var>.
        # </p>
        # @end

        return $this->hFiles->selectAssociative(
            array(
                'hPlugin'
            ),
            $fileId
        );
    }

    public function getFileField($field, $fileId, $table = 'hFileDocuments')
    {
        # @return mixed

        # @description
        # <h2>Getting an Arbitrary File Field</h2>
        # <p>
        #   <b>DEPRECATED</b>. Use database methods such as <var>selectColumn</var>
        #   instead.
        # </p>
        # <p>
        #   Returns <var>$field</var> from the database table <var>$table</var>
        #   where in that database table, <var>hFileId</var> is <var>$fileId</var>.
        # </p>
        # <p>
        #   <var>$fileId</var> can be specified as a <var>$filePath</var> as well,
        #   if it is a path, it will be automatically converted to a <var>$fileId</var>.
        # </p>
        # @end

        if (!is_numeric(($fileId)))
        {
            $fileId = $this->getFileIdByFilePath($fileId);
        }

        return $this->hDatabase->selectColumn(
            $field,
            $table,
            array(
                'hFileId' => (int) $fileId
            )
        );
    }

    public function getFileHeaders()
    {
        # @return string

        # @description
        # <h2>Getting HTML/XHTML Header Content</h2>
        # <p>
        #   Returns the top portion of an HTML/XHTML file.  The bits after <var>&lt;html&gt;</var>
        #   including <var>&lt;head&gt;</var> and <var>&lt;/head&gt;</var> and all the content
        #   between.
        # </p>
        # <h3>Framework Variable Configurations</h3>
        # <p>
        #   You can use the following variables to customize the content used in the HTML headers:
        # </p>
        # <table>
        #   <colgroup>
        #       <col />
        #       <col />
        #       <col />
        #   </colgroup>
        #   <thead>
        #       <tr>
        #           <th>Variable</th>
        #           <th>Default</th>
        #           <th>Description</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td class='code'>hFacebookThumbnail</td>
        #           <td class='code'>nil</td>
        #           <td>
        #               Defines a thumbnail image to be used when the link to the document is
        #               posted to Facebook.  Creates the following:
        #               <var>&lt;meta property='og:image' content='{/hFacebookThumbnail}' /&gt;</var>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileCharset<br />hLanguageCharset</td>
        #           <td class='code'>utf-8</td>
        #           <td>
        #               The character set of the document.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileCopyright</td>
        #           <td class='code'>&copy; Copyright {hFrameworkName} All Rights Reserved.</td>
        #           <td>
        #               Sets the content of <var>&lt;meta name='copyright' /&gt;</var>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileCSS</td>
        #           <td class='code'>nil</td>
        #           <td>
        #               HTML for specifying style sheets.  See: <a href='#setHeaders' class='code'>setHeaders()</a>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileDescription</td>
        #           <td class='code'>nil</td>
        #           <td>
        #              Sets the content of <var>&lt;meta name='description' /&gt;</var>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileDoctype</td>
        #           <td class='code'>&lt;!DOCTYPE HTML&gt;</td>
        #           <td>
        #              The doctype declaration, default is the HTML5 doctype.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileFavicon</td>
        #           <td class='code'>nil</td>
        #           <td>
        #              Adds the following to the headers:
        #              <var>&lt;link rel='shortcut icon' href='{/hFileFavicon}' type='image/x-icon' /&gt;</var>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileJavaScript</td>
        #           <td class='code'>nil</td>
        #           <td>
        #               HTML used to add JavaScript to the HTML <var>&lt;head&gt;</var> element.
        #               See: <a href='#setHeaders' class='code'>setHeaders()</a>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileKeywords</td>
        #           <td class='code'>nil</td>
        #           <td>
        #              Sets the content of <var>&lt;meta name='keywords' /&gt;</var>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileLanguage<br />hLanguageLocalization</td>
        #           <td class='code'>en-us</td>
        #           <td>
        #               The two-letter language code with two letter country code representing the language localization of the site.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileLanguageCode<br />hLanguageCode</td>
        #           <td class='code'>en</td>
        #           <td>
        #               The two-letter language code representing the language of the site.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileLastModified</td>
        #           <td class='code'>Unix Timestamp</td>
        #           <td>
        #              When set it creates the following:
        #              <var>&lt;meta name='date' content='{/php.date('c', "{hFileLastModified}")}' /&gt;</var>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileMediaElement</td>
        #           <td class='code'>false</td>
        #           <td>
        #               Includes the <var>MediaElement</var> JavaScript library, which provides cross-browser
        #               support for the HTML5 <var>&lt;video&gt;</var> and <var>&lt;audio&gt;</var> elements.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileMIME</td>
        #           <td class='code'>text/html</td>
        #           <td>
        #               The MIME type of the document.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileRobots</td>
        #           <td class='code'>index, follow</td>
        #           <td>
        #               Sets the content of <var>&lt;meta name='robots' /&gt;</var> and
        #               <var>&lt;meta name='googlebot' /&gt;</var>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileRSS</td>
        #           <td class='code'>nil</td>
        #           <td>
        #               HTML for specifying RSS/Atom feeds.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileTitle</td>
        #           <td class='code'>nil</td>
        #           <td>
        #               If set, this string appears in the <var>&lt;title&gt;</var> tag.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileTitleAppend</td>
        #           <td class='code'>nil</td>
        #           <td>
        #               If set, this string appears just before the closing <var>&lt;/title&gt;</var> tag,
        #               after the <var>hFileTitle</var>.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileTitlePrepend</td>
        #           <td class='code'>nil</td>
        #           <td>
        #               If set, this string appears just after the opening <var>&lt;title&gt;</var> tag,
        #               before the <var>hFileTitle</var>.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileXMLProlog</td>
        #           <td class='code'>nil</td>
        #           <td>
        #               The XML prolog would be set in this variable if this were an XHTML document
        #               configured to be served as XML.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileViewport</td>
        #           <td class='code'>&lt;meta name='viewport' content='width=1024' /&gt;</td>
        #           <td>
        #              Sets the width of the viewport for portable devices like the iPhone, iPod
        #              Touch, and iPad.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFrameworkEnableJavaScript</td>
        #           <td class='code'>true</td>
        #           <td>
        #              Whether or not JavaScript should be included in the document at all.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFrameworkName</td>
        #           <td class='code'>nil</td>
        #           <td>
        #              Sets the content of <var>&lt;meta name='author' /&gt;</var> and is used as
        #              the name of the website in many other plugins.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hGoogleChromeFrame</td>
        #           <td class='code'>true</td>
        #           <td>
        #               Includes the <var>X-UA-Compatible</var> header required to trigger rendering
        #               in Google Chrome Frame in Internet Explorer.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hGoogleSitemapKey</td>
        #           <td class='code'>true</td>
        #           <td>
        #               Sets the content of <var>&lt;meta name='verify-v1' /&gt;</var>, used for Google
        #               webmaster tools.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>jQueryPath</td>
        #           <td class='code'>{hFrameworkLibraryRoot}/jQuery/jQuery.js</td>
        #           <td>
        #               The path to jQuery.
        #           </td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        $html = '';

        if ($this->hFileMIME == 'text/html' || $this->hFileMIME == 'application/xhtml+xml')
        {
            if ($this->hFileXHTML(false))
            {
                $this->hFileXMLProlog = $this->getTemplateXML('Prolog');
            }

            $variables = array(
                'fileMIME' => $this->hFileMetaContentType(
                    $this->hFileMIME('text/html')
                ),
                'fileCharset' => $this->hLanguageCharset(
                    $this->hFileEncoding('utf-8')
                ),
                'languageLocalization' => $this->hLanguageLocalization(
                    $this->hFileLanguage('en-us')
                ),
                'fileLanguage' => $this->hLanguageCode(
                    $this->hFileLanguageCode('en')
                ),
                'fileTitle' => $this->translate(
                    strip_tags($this->hFileTitle)
                ),
                'fileDescription' => hString::encodeHTML(
                    strip_tags($this->hFileDescription),
                    true
                )
            );

            if ($this->hFrameworkEnableJQuery(true))
            {
                $variables['jQueryPath'] = $this->hFrameworkLibraryRoot.'/jQuery/jQuery.js';
            }

            if (!$this->hFileCopyright)
            {
                $this->hFileCopyright =
                    "&copy; Copyright ".date('Y')." {$this->hFrameworkName}, All Rights Reserved.";
            }

            return $this->getTemplate('Headers', $variables);
        }

        return $html;
    }

    public function noScript($html)
    {
        # @return string

        # @description
        # <h2>Adding No Script Content</h2>
        # <p>
        #   Calling <var>noScript()</var> returns the supplied <var>$html</var> wrapped with
        #   <var>&lt;noscript</var> tags.  Nothing is returned if the user agent is the W3C
        #   validator, which doesn't seem to like <var>&lt;noscript</var> content.
        # </p>
        # @end

        if ($this->userAgent->isW3C)
        {
            return '';
        }
        else
        {
            return $this->userAgent->isMobile? $html : $this->getTemplate('No Script', get_defined_vars());
        }
    }

    public function setHeaders($plugin, $method, $file = nil, $path = false)
    {
        # @return void

        # @description
        # <h2>Adding CSS and JavaScript Content to HTML Headers</h2>
        # <p>
        #   <var>setHeaders()</var> is called from plugins via middle man methods called
        #   <a href='/Hot Toddy/Documentation?hFramework/hFrameworkApplication#getPluginCSS' class='code'>getPluginCSS()</a>,
        #   <a href='/Hot Toddy/Documentation?hFramework/hFrameworkApplication#getPluginJavaScript' class='code'>getPluginJavaScript()</a>, and
        #   <a href='/Hot Toddy/Documentation?hFramework/hFrameworkApplication#getPluginFiles' class='code'>getPluginFiles()</a>,
        #   which means that <var>setHeaders()</var> is never called directly and should never
        #   be called directly.
        # </p>
        # @end

        if ($this->hPrivateFramework && is_object($this->hPrivateFramework))
        {
            if ($this->hPrivateFramework->getPrivateHeaders($plugin, $method, $file, $path))
            {
                #return $this;
                return;
            }
        }

        if (empty($path))
        {
            if (!empty($file) && !strstr($file, 'ie') && !strstr($file, 'print') && !strstr($file, 'template') && !strstr($file, 'windows'))
            {
                $plugin = $this->queryPlugin($file);
                $plugin = $plugin['path'];
                $file   = $path;
            }

            $name = basename(dirname($plugin));

            if (!empty($file))
            {
                switch ($file)
                {
                    case 'ie':
                    case 'ie6':
                    case 'ie7':
                    case 'ie8':
                    {
                        $name .= '.'.$file;
                        break;
                    }
                    case 'print':
                    case 'template':
                    {
                        $name .= '.'.$file;
                        break;
                    }
                    case 'windows':
                    {
                        if ($this->userAgent->isWindows)
                        {
                            $name .= '.'.$file;
                            break;
                        }
                        else
                        {
                            return;
                        }
                    }
                    default:
                    {
                        $name = $file;
                    }
                }
            }

            $path = dirname($plugin).'/'.$name;
        }
        else
        {
            $path = $file;
        }

        if (stristr($path, '.css'))
        {
            $path = str_ireplace('.css', '', $path);
        }

        if (stristr($path, '.js'))
        {
            $path = str_ireplace('.js', '', $path);
        }

        switch ($method)
        {
            case 'css':
            {
                $this->appendToCSS($path);
                break;
            }
            case 'js':
            {
                $this->appendToJavaScript($path);
                break;
            }
            case 'both':
            {
                $this->appendToCSS($path);
                $this->appendToJavaScript($path);
                break;
            }
        }

        #return $this;
    }

    public function appendToCSS($path)
    {
        # @return void

        # @description
        # <h2>Appending CSS</h2>
        # <p>
        #   Appends the provided path as a stylesheet in the CSS header files.
        # </p>
        # @end

        if (!strstr($path, '.css'))
        {
            $path .= '.css';
        }

        $path = $this->insertSubExtension(
            $path,
            'mobile',
            $this->userAgent->interfaceIdiomIsPhone
        );

        $this->hFileCSS .= $this->getCSS($path);

        #return $this;
    }

    public function appendToJavaScript($path)
    {
        # @return void

        # @description
        # <h2>Appending JavaScript</h2>
        # <p>
        #   Appends the provided path as JavaScript in the JavaScript header files.
        # </p>
        # @end

        if (!strstr($path, '.js'))
        {
            $path .= '.js';
        }

        $path = $this->insertSubExtension(
            $path,
            'mobile',
            $this->userAgent->interfaceIdiomIsPhone
        );

        $this->hFileJavaScript .= $this->getJavaScript($path);

        #return $this;
    }

    public function getJavaScript($path)
    {
        # @return HTML

        # @description
        # <h2>Getting a JavaScript Include Template</h2>
        # <p>
        #   Returns a snippet of HTML with the provided <var>$path</var> inserted
        #   as a JavaScript include. For example:
        # </p>
        # <code>
        #   &lt;script src='/path/to/script.js'&gt;
        #   &lt;/script&gt;
        # </code>
        # @end

        return $this->getTemplate(
            'JavaScript',
            array(
                'path' => $path
            )
        );
    }

    private function getPathInfo($path)
    {
        # @return array

        # @description
        # <h2>Getting Information From a Path</h2>
        # <p>
        #   Uses Hot Toddy's file naming conventions to extract information about a
        #   file from its path. i.e., it can determine whether a file is directed at a
        #   specific version of Internet Explorer from the path myCSSFile.ie6.css, or
        #   whether or not the file is a print style sheet, i.e., myCSSFile.print.css.
        # </p>
        # @end

        preg_match("/\.ie(\d*)\.|\.print\./", $path, $matches);

        return array(
            'isInternetExplorer' => !empty($matches[0]) && strstr($matches[0], '.ie'),
            'internetExplorerVersion' => !empty($matches[1])  && strstr($matches[0], '.ie')? $matches[1] : '',
            'isPrint' => !isset($_GET['testPrint']) && !empty($matches[0]) && strstr($matches[0], '.print.'),
            'path' => $path
        );
    }

    public function getCSS($path)
    {
        # @return HTML

        # @description
        # <h2>Getting a CSS Include Template</h2>
        # <p>
        #   Returns an HTML snippet with the provided <var>$path</var> inserted as a
        #   CSS include.
        # </p>
        # <code>
        #   &lt;link rel='stylesheet' href='/path/to/stylesheet.css' /&gt;
        # </code>
        # @end

        $data = $this->getPathInfo($path);

        if ($data['isInternetExplorer'] && $this->hDesktopApplication(false))
        {
            return '';
        }

        return $this->getTemplate('Stylesheet', $data);
    }

    public function jQuery()
    {
        # @return void

        # @description
        # <h2>Including jQuery UI Components</h2>
        # <p>
        #   Calling this function includes jQuery UI Core, as well as whatever jQuery
        #   UI modules are passed to this method as arguments.
        # </p>
        # @end

        $items = func_get_args(); // This function can't be passed as a parameter to another function.

        if (!$this->jQueryUICoreLoaded(false))
        {
            $items = array_merge(array('UI-Core'), $items);
            $this->jQueryUICoreLoaded = true;
        }

        foreach ($items as $item)
        {
            if ($item == 'Datepicker')
            {
                $item .= '/Datepicker';
            }

            $this->hFileJavaScript .= $this->getJavaScript(
                $this->hFrameworkLibraryRoot.'/jQuery/'.$item.'.js'
            );
        }

        #return $this;
    }

    public function jQueryPlugin()
    {
        # @return void

        # @description
        # <h2>Including a jQuery Plugin</h2>
        # <p>
        #   Includes one or more jQuery Plugins (installed in the /Library/jQuery/Plugins folder).
        # </p>
        # @end

        $items = func_get_args();

        foreach ($items as $item)
        {
            $this->hFileJavaScript .= $this->getJavaScript(
                $this->hFrameworkLibraryRoot."/jQuery/Plugins/{$item}".(strstr($item, '/')? '' : "/{$item}").'.js'
            );
        }

        #return $this;
    }
}

?>