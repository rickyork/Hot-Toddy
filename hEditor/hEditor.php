<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Editor
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
# <h1>Hot Toddy Editor Application</h1>
# <p>
#   The editor application provides a single document GUI for creating and maintaining
#   files and file meta data stored in Hot Toddy's file system (HtFS).  The editor
#   provides either a WYSIWYG editor interface or text editting that embeds the
#   3rd-party Ace text editor for both text editting and code highlighting.
# </p>
# <p>
#   The editor also provides a plugin API, so that it can re-used and extended for other
#   CMS applications.
# </p>
# <p>
#   Eventually the editor will be modified to include Hot Toddy's own WYSIWYG editor, which
#   is built into the hEditor/hEditorTemplate plugin.
# </p>
# @end

class hEditor extends hPlugin {

    private $hFile;
    private $hFinderTree;

    public function hConstructor()
    {
        if ($this->isLoggedIn())
        {
            $this->getEditor();
        }
        else
        {
            $this->notLoggedIn();
        }
    }

    private function getEditor()
    {
        $this->redirectIfSecureIsEnabled();

        $this->hFileDocument = '';
        $this->hFileCSS = '';
        $this->hFileJavaScript = '';

        $this->jQuery('Sortable');

        // Causes problems with the WYSIWYG
        $this->hFileXHTML = false;
        $this->hFileMIME = 'text/html';

        // Transitional because of the iframe
        $this->hFileDoctype = $this->getTemplate('Doctype');

        if (isset($_GET['hEditorConf']))
        {
            $conf = $this->hFrameworkConfigurationPath.'/hEditor '.hString::scrubString($_GET['hEditorConf']).'.conf';

            if (file_exists($conf))
            {
                $this->setVariables(parse_ini_file($conf));
            }
        }

        $this->getPluginFiles();
        $this->getPluginJavaScript('/hCodeStyle/hCodeStyle.template', true);

        //$this->getPluginJavaScript('/hEditor/JS/WYSIWYG/Templates', true);
        $this->getPluginCSS('/hEditor/CSS/WYSIWYG/Templates', true);

        $this->getPluginCSS('ie7');

        if ($this->hEditorTemplateStylesheet)
        {
            $this->getPluginCSS($this->hEditorTemplateStylesheet, true);
        }

        $this->hTemplatePath = '/hEditor/hEditor.template.php';
        $this->hFileFavicon = '/hEditor/Pictures/Editor.ico';

        $this->hFinderTreeStandAlone = true;
        $this->hFinderTreeDisplayFiles = true;
        $this->hFinderTreeLoadingActivity = false;

        $this->hFileTitle = $this->hServerHost.' Editor';
        $this->hFileTitlePrepend = '';
        $this->hFileTitleAppend  = '';

        $leftColumn = '';

        if ($this->hEditorEnableTree(true))
        {
            $this->hFinderTreeHomeDirectory = false;

            $this->hFinderTree = $this->plugin('hFinder/hFinderTree');

            if (is_array($this->hEditorFilterPaths))
            {
                $this->hFinderTree->setFilterPaths(
                    $this->hEditorFilterPaths
                );
            }

            $leftColumn = $this->hFinderTree->getTree();
        }
        else if ($this->hEditorPlugin(nil))
        {
            $plugin = $this->plugin($this->hEditorPlugin);
            $leftColumn = $plugin->getEditor();
        }

        $this->plugin('hApplication/hApplicationStatus');

        $fileId   = 'new1';
        $fileName = 'New Document [1]';
        $filePath = '/hFile/blank';

        $this->hFileDocument .= $this->getTemplate(
            'Editor',
            array(
                'hEditorLeft' => $leftColumn,
                'hEditorDocument' => $this->getDocumentHTML(
                    $fileId,
                    $fileName,
                    $filePath
                ),
                'hEditorTabs' => $this->getDocumentTabs(
                    $fileId,
                    $fileName,
                    $filePath
                ),
                'hEditorSaveAs' => $this->getSaveAsButton(),
                'hEditorEnableDocumentTabs' => $this->hEditorEnableDocumentTabs(true)
            )
        );
    }

    private function getDocumentTabs($fileId, $fileName, $filePath)
    {
        if ($this->hEditorEnableDocumentTabs(true))
        {
            return $this->getTemplate(
                'Document Tabs',
                array(
                    'hEditorDocumentId' => $fileId,
                    'hEditorDocumentName' => $fileName
                )
            );
        }
        else
        {
            $this->hFileCSS .= $this->getTemplate('Disable Document Tabs');
            return '';
        }
    }

    private function getDocumentHTML(&$fileId, &$fileName, &$filePath)
    {
        if (isset($_GET['path']))
        {
            hString::safelyDecodeURL($_GET['path']);

            $this->hFile = $this->library('hFile');
            $this->hFile->query($_GET['path']);

            $fileId   = $this->hFile->fileId;

            $fileName = basename($_GET['path']);
            $filePath = $_GET['path'];
        }

        $document = array(
            'path' => !empty($filePath)? urlencode($filePath) : ''
        );

        if (isset($_GET['hEditorConf']))
        {
            $document['hEditorConf'] = urlencode(hString::scrubString($_GET['hEditorConf']));
        }

        return $this->getTemplate(
            'Document',
            array(
                'hEditorDocumentName' => $fileName,
                'hEditorDocumentWYSIWYGPath' => $filePath.'?hEditorTemplateEnabled=1&hEditorTemplateIsEmbedded=1',
                'hEditorDocumentPath' => $filePath,
                'hEditorDocumentId' => $fileId,
                'hEditorDocumentURL' => '/Applications/Editor/Document.html?'.$this->getQueryString($document)
            )
        );
    }

    private function getSaveAsButton()
    {
        return $this->hEditorEnableSaveAs(true)? $this->getTemplate('Save As') : '';
    }
}

?>