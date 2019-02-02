<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File Breadcrumbs
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
# <h1>File Breadcrumb API</h1>
# <p>
#   The breadcrumb API is used to add breadcrumb links to a site that help a user
#   identify where they are on a site, and how to get back to pages higher in the
#   document hierarchy.  Breadcrumbs in Hot Toddy are created by giving files
#   parent/child relationships with one another using the <var>hFileParentId</var>
#   field of the
#   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hFiles/hFiles.sql' class='code'>hFiles</a>
#   database table.
# </p>
# @end

class hFileBreadcrumbs extends hPlugin {

    public function getBreadcrumbs()
    {
        # @return HTML
        # <p>
        #   The value of <var>hFileBreadcrumbs</var>.
        # </p>
        # @end

        # @description
        # <h2>Getting the Default or Custom Breadcrumbs</h2>
        # <p>
        #   A call to <var>getBreadcrumbs()</var> returns the framework variable
        #   <var>hFileBreadcrumbs</var>, if it has a value. The <var>hFileBreadcrumbs</var> variable
        #   should be used in plugins that want to define custom breadcrumbs, within that plugin itself.
        # </p>
        # <p>
        #   If <var>hFileBreadcrumbs</var> is nt defined, or empty, the result of
        #   <a href='#getFileBreadcrumbs' class='code'>getFileBreadcrumbs()</a> is assigned
        #   to it, and then returned.
        # </p>
        # @end

        if (!$this->hFileBreadcrumbs)
        {
            $this->hFileBreadcrumbs = $this->getFileBreadcrumbs();
        }

        return $this->hFileBreadcrumbs;
    }

    public function getFileBreadcrumbs($fileId = 0, $custom = false, $linkToSelf = false)
    {
        # @return HTML

        # @description
        # <h2>Getting the Default Breadcrumbs</h2>
        # <p>
        #   Default breadcrumbs retrive the breadcrumb path all the way up the parent/child
        #   hierarchy to the furthest ancestor of the current document. The argument
        #   <var>$linkToSelf</var> decides whether or not there will be a link to the
        #   document the user is currently looking at (the last document in the breadcrumbs).
        #   Providing a link to this document is redundant, since clicking it would only
        #   reload the current page and provide nothing useful.
        # </p>
        # <h3>Translating Breadcrumbs</h3>
        # <p>
        #   Each breadcrumb label comes from the <var>hFileBreadcrumbTitle</var> framework variable
        #   attached to each file in the breadcrumb heirarchy.  This framework variable is optional,
        #   and the default value will be the file's <var>hFileTitle</var> if there is no
        #   <var>hFileBreadcrumbTitle</var> specified.  Whether the title is the <var>hFileBreadcrumbTitle</var>
        #   or <var>hFileTitle</var>, the final label will be passed through the
        #   <a href='/Hot Toddy/Documentation?hLanguage#translate' class='code'>translate()</a> method
        #   of the <a href='/Hot Toddy/Documentation?hLanguage' class='code'>hLanguage</a> object.  If
        #   the word or phrase passed to <a href='/Hot Toddy/Documentation?hLanguage#translate' class='code'>translate()</a>
        #   has a foreign language translation in the selected language, it will be automatically translated
        #   to that translation when that language is selected.
        # </p>
        # <p>
        #   Breadcrumbs can be configured to customize the look and feel of the breadcrumbs themselves,
        #   you can customize breadcrumbs by defining the framework variables listed below:
        # </p>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>Variable</th>
        #           <th>Default Value</th>
        #           <th>Description</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td class='code'>hFileBreadcrumbSeparatorClass</td>
        #           <td class='code'>BreadcrumbSeparator</td>
        #           <td>
        #               The class name added to the breadcrumb separator, a <var>&lt;span&gt;</var>
        #               element that appears between each breadcrumb.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileBreadcrumbHereClass</td>
        #           <td class='code'>BreadcrumbHere</td>
        #           <td>
        #               The class name added to the breadcrumb representing the current page, typically the
        #               last breadcrumb (an <var>&lt;li&gt;</var> element).
        #           </td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        if ($this->hFileBreadcrumbs)
        {
            return $this->hFileBreadcrumbs;
        }

        if (empty($fileId))
        {
            $fileId  = $this->hFileId;
            $parentId = $this->hFileParentId;
        }

        if (!empty($fileId))
        {
            $parentId = $this->getFileParentId((int) $fileId);
        }

        $files = array();

        while ($parentId > 0)
        {
            $data = $this->getFileInformation($parentId);

            $files[] = $data;
            $parentId = $data['hFileParentId'];
        }

        $files = array_reverse($files);

        $breadcrumbs = array();

        foreach ($files as $file)
        {
            $breadcrumbs['hFileId'][] = $file['hFileId'];

            $breadcrumbs['hFileTitle'][] = $this->translate(
                hString::decodeHTML(
                    $this->hFileBreadcrumbTitle(
                        $file['hFileTitle'],
                        $file['hFileId']
                    )
                )
            );

            $breadcrumbs['hFilePath'][] = $file['hFilePath'];
        }

        $linkToFile = ($fileId != $this->hFileId);

        return $this->getTemplate(
            'Breadcrumbs',
            array(
                'hFileBreadcrumbs' => $breadcrumbs,
                'hFileBreadcrumbSeparatorClass' => $this->hFileBreadcrumbSeparatorClass('BreadcrumbSeparator'),
                'hFileBreadcrumbHereClass' => $this->hFileBreadcrumbHereClass('BreadcrumbHere'),
                'hFileBreadcrumbSeparator' => $this->hFileBreadcrumbSeparator('&rarr;'),
                'hFileBreadcrumbHomePath' => $this->hFileBreadcrumbsHomePath('/index.html'),
                'hFileBreadcrumbHomeLabel' => $this->translate(
                    $this->hFileBreadcrumbHomeLabel('Home')
                ),
                'linkToFile' => $linkToFile,
                'hFileBreadcrumbPath' => $linkToFile? $this->getFilePathByFileId($fileId) : '',
                'hFileBreadcrumbTitle' => $linkToFile? $this->translate(
                    $this->hFileBreadcrumbTitle(
                        $this->getFileTitle($fileId),
                        $fileId
                    )
                ) : '',
                'linkToSelf' => $linkToSelf,
            )
        );
    }

    public function makeBreadcrumbs($crumbs, $prependLineage = false, $fileId = 0)
    {
        if (is_array($crumbs))
        {
            foreach ($crumbs as $link => $text)
            {
                $html .= $this->getTemplate(
                    'Make Breadcrumbs',
                    array(
                        'hFileBreadcrumbSeparatorClass' => $this->hFileBreadcrumbSeparatorClass('BreadcrumbSeparator'),
                        'hFileBreadcrumbSeparator' => $this->hFileBreadcrumbSeparator('&rarr;'),
                        'hFileBreadcrumbPath' => $link,
                        'hFileBreadcrumbTitle' => $text,
                        'linkToSelf' => $link == 'self',
                        'hFileBreadcrumbHereClass' => $this->hFileBreadcrumbHereClass('BreadcrumbHere'),
                    )
                );
            }

            if ($prependLineage)
            {
                $this->hFileBreadcrumbHereClass = '';

                $html = $this->getFileBreadcrumbs($fileId, true, true).$html;
            }

            $this->hFileBreadcrumbs = $html;
        }
    }

    # Return an array of file ids containing the complete lineage of a document.
    # This is an array representation of breadcrumbs, where the only contents of the array are the
    # document ids.
    #
    # @param  int    $fileParentId = 0
    # @param  array  $line = array()  Array of ids contained in document lineage

    public function getFileLineage($fileParentId = 0, $line = array())
    {
        if (empty($line))
        {
            $line[] = $this->hFileId;
        }

        if ($parentId != 0)
        {
            $line[] = (int) $fileParentId;

            $line = $this->getFileLineage(
                $this->hFiles->selectColumn(
                    'hFileParentId',
                    (int) $fileParentId
                ),
                $line
            );
        }

        return $line;
    }
}

?>