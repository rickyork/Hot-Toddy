<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Editor Library
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

class hEditorLibrary extends hPlugin {

    public function hConstructor()
    {
        $this->redirectIfSecureIsEnabled();
    }

    public function wysiwyg()
    {
        if (isset($_GET['hEditorTemplateIsEmbedded']))
        {
            $this->hEditorTemplateIsEmbedded = true;
        }

        # Make sure the user has permission to edit the document.
        #
        # hEditorTemplateFileId can be specified if the editable content isn't the hFileId
        # of the current document.  You might use this configuration in a wildcard catch-all
        # path, for example.  i.e., http://www.example.com/something/* where everything in
        # /something is directed to a particular plugin rather than an independent file.
        #
        # hEditorTemplateForcePermission can be specified if you're checking permission elsewhere
        # and want to override the check here.
        if ($this->hFiles->hasPermission($this->hEditorTemplateFileId($this->hFileId), 'rw') || $this->hEditorTemplateForcePermission(false))
        {
            $this->hFileDocumentParseEnabled = false;

            # I began work on an embeddable version of this same WYSIWYG editor, which
            # will eventually either replace or supplement FCKEditor. This work is not
            # yet completed, however, as there needs to be additional containment and API
            # put in place to make an embedded version more feasible.
            $embedded = $this->hEditorTemplateIsEmbedded(false);

            #$this->jQuery('Draggable', 'Droppable', 'Sortable');
            $this->jQuery('Draggable');

            //$this->getPluginCSS();
            $this->getPluginJavaScript('template');
            $this->getPluginJavaScript('/hCodeStyle/hCodeStyle.template', true);

            # CSS files for this plugin are broken down into smaller files to make the
            # CSS portion of this plugin easier to manage.
            $files = array(
                'Editor',
                'Panel',
                'Modals',
                'Templates',
                'Modals/Link',
                'Modals/Movie',
                'Modals/Photo'
            );

            foreach ($files as $file)
            {
                $this->getPluginCSS('/hEditor/CSS/WYSIWYG/'.$file, true);
            }

            # Include source editor
            $this->getPluginJavaScript('/Library/Ace/src/ace', true);
            $this->getPluginJavaScript('/Library/Ace/src/mode-html', true);
            $this->getPluginJavaScript('/Library/Ace/src/theme-textmate', true);

            # hApplicationStatus provides a reusable message window that is most commonly used
            # to alert the user when "save" activity is taking place and what the status of
            # that activity is.  i.e. "Saving Document...",  "Save Failed!", "Save Successful!"
            $this->plugin('hApplication/hApplicationStatus');

            $objects = '';

            # The side panel offers quick selection of images, video, or documents.  This
            # side panel should be hidden if the embedded version is in use, or if the
            # screen resolution is too small to accommodate it.  If the screen resolution is
            # too small, the side panels are still loaded, but are hidden dynamically.
            #
            # The present screen resolution check measures the width of the browser window
            # and hides accordingly.  Therefore, it is possible to hide/reveal the side panel
            # on large resolution monitors by resizing the browser window.
            if (!$embedded)
            {
                $this->getPluginFiles('hFinder/hFinderTree');

                $this->hFinderTree = $this->library('hFinder/hFinderTree');

                # hPhoto is designed to provide an API around selecting and managing images.
                # This would be instead of dumping the user into the file system to find,
                # select, manage images.
                $this->hPhoto = $this->library('hPhoto');

                # hMovie is designed to provide an API around selection and managing video.
                # This would be instead of dumping the user into the file system to find,
                # select, manage video.
                $this->hMovie = $this->library('hMovie');

                $this->getPluginCSS('/hEditor/CSS/WYSIWYG/Objects', true);

                $objects = $this->getTemplate(
                    'WYSIWYG/Objects',
                    array(
                        'hPhotoTree' => !$embedded? $this->hPhoto->getTree() : '',
                        'hPhotoView' => !$embedded? $this->hPhoto->getView() : '',
                        'hMovieTree' => !$embedded? $this->hMovie->getTree() : '',
                        'hMovieView' => !$embedded? $this->hMovie->getView() : ''
                    )
                );
            }
            else
            {
                $this->getPluginCSS('/hEditor/CSS/WYSIWYG/Embedded', true);
            }

            $this->hFileDocumentAppend =
                # This HTML template provides the HTML for the chrome of the editor
                # A bottom toolbar with buttons, the side panels, if applicable.  And
                # a reusable HTML template that's used to wrap each element in a document
                # to provide additional editor controls (like resize handles, an "x" to
                # remove an element, etc.).
                #
                # Also included is a JavaScript configuration that contains information
                # about the presently loaded page.  Its path, fileId, and so on.
                $this->getTemplate(
                    'WYSIWYG/WYSIWYG',
                    array(
                        'hFileId' => $this->hEditorTemplateFileId($this->hFileId),
                        'objects' => $objects,
                        'wildcardPath' => $this->hFileWildcardPath
                    )
                ).
                $this->getTemplate('WYSIWYG/Modals/Link').
                $this->getTemplate('WYSIWYG/Modals/Movie').
                $this->getTemplate('WYSIWYG/Modals/Photo').
                # This HTML template provides a floating Editor panel that contains
                # formatting controls for the WYSIWYG.
                $this->getPanel(
                    'Editor',
                    'hEditorToolbarPanel',
                    array(
                        'Format'     => $this->getTemplate('WYSIWYG/Format Toolbar'),
                        'Template'   => $this->getTemplate('WYSIWYG/Templates')
                        #'Properties' => $this->getTemplate('Properties'),
                        #'Dimensions' => $this->getTemplate('Dimensions'),
                        #'Background' => $this->getTemplate('Background'),
                        #'Layout'     => $this->getTemplate('Layout'),
                        #'Content'    => $this->getTemplate('Content')
                    )
                );

            # This provides a dialogue for modifying links in the editor.
            #$this->plugin('hEditor/hEditorTemplate/hEditorTemplateLink');

            # This provides a dialogue for modifying the "src" path of images
            # in the editor, and an alternative method of selecting images.  This
            # dialogue allows you to directly browse the file system for images,
            # for example.
            #$this->plugin('hEditor/hEditorTemplate/hEditorTemplateImage');

            # Same with this one, but for video.
            #$this->plugin('hEditor/hEditorTemplate/hEditorTemplateMovie');

            #$this->plugin('hEditor/hEditorTemplate/hEditorTemplateLayers');
            #$this->plugin('hEditor/hEditorTemplate/hEditorTemplateProperties');
        }
        else
        {
            $this->notAuthorized();
        }
    }

    # This method was created with the idea that I might need additional panels
    # in the future, beyond the single Editor panel that is presently used.
    public function getPanel($name, $id, array $panes)
    {
        $panelTabs = array();
        $panelPanes = array();
        $i = 0;

        foreach ($panes as $label => $pane)
        {
            $panelTabs['hEditorTemplatePanelTabId'][$i] = str_replace(' ', '', $label);
            $panelTabs['hEditorTemplatePanelTabLabel'][$i] = $label;

            $panelPanes['hEditorTemplatePanelPaneId'][$i] = str_replace(' ', '', $label);
            $panelPanes['hEditorTemplatePanelPane'][$i] = $pane;

            $i++;
        }

        return $this->getTemplate(
            'WYSIWYG/Panel',
            array(
                'hEditorTemplatePanelName' => $name,
                'hEditorTemplatePanelNameAsId' => str_replace(array(' ', '/'), '', $name),
                'hEditorTemplatePanelId' => $id,
                'hEditorTemplatePanelTabs' => $panelTabs,
                'hEditorTemplatePanelPanes' => $panelPanes
            )
        );
    }
}

?>