<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Editor Template
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
#
# Template Editor is a WYSIWYG designed to appear within any page of a given site,
# so that you can edit a document in place and see it update with the correct
# styling within the actual template as opposed to being loaded in a separate
# WYSIWYG editor outside of the site's template.
#
# This collection of plugins, JavaScript and CSS prefixed "hEditorTemplate" comprise
# the whole of the in-page or in-site WYSIWYG editor.  Some configuration is required
# to use this editor, such as defining templates.  See the hEditorTemplate..json
# file for an explanation.
#
# This plugin is completely separate from the hEditor and hEditorDocument plugins,
# which together made the framework's "Editor" application and only has the name
# "hEditorTemplate" because I couldn't think of a better name or location for it.
# This plugin may in the future be renamed or relocated to better distinguish it from
# the other editor application.
#

class hEditorTemplate extends hPlugin {

    private $hFinderTree;
    private $hPhoto;
    private $hMovie;

    public function hConstructor()
    {
        # Make sure the user has permission to edit the document.
        #
        # hEditorTemplateFileId can be specified if the editable content isn't the hFileId
        # of the current document.  You might use this configuration in a wildcard catch-all
        # path, for example.  i.e., http://www.example.com/something/* where everything in
        # /something is directed to a particular plugin rather than an independent file.
        #
        # hEditorTemplateForcePermission can be specified if you're checking permission elsewhere
        # and want to override the check here.
        if ($this->hasPermission('hFiles:'.$this->hEditorTemplateFileId($this->hFileId).':rw') || $this->hEditorTemplateForcePermission(false))
        {
            $this->hFileDocumentParseEnabled = false;

            # I began work on an embeddable version of this same WYSIWYG editor, which
            # will eventually either replace or supplement FCKEditor. This work is not
            # yet completed, however, as there needs to be additional containment and API
            # put in place to make an embedded version more feasible.
            $embedded = $this->hEditorTemplateIsEmbedded(false);

            #$this->jQuery('Draggable', 'Droppable', 'Sortable');
            $this->jQuery('Draggable');

            $this->getPluginCSS();
            $this->getPluginJavaScript('template');

            # CSS files for this plugin are broken down into smaller files to make the
            # CSS portion of this plugin easier to manage.
            $files = array(
                'Panel',
                'Modal Dialogue',
                'Template'
            );

            foreach ($files as $file)
            {
                $this->getPluginCSS('/hEditor/hEditorTemplate/CSS/'.$file, true);
            }

            # Include source editor
            $this->getPluginJavaScript('/Library/Ace/build/src/ace', true);
            $this->getPluginJavaScript('/Library/Ace/build/src/mode-html', true);
            $this->getPluginJavaScript('/Library/Ace/build/src/theme-textmate', true);

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

                $this->getPluginCSS('/hEditor/hEditorTemplate/CSS/Objects', true);

                $objects = $this->getTemplate(
                    'Objects',
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
                $this->getPluginCSS('/hEditor/hEditorTemplate/CSS/Embedded', true);
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
                    'Template Editor',
                    array(
                        'hFileId' => $this->hEditorTemplateFileId($this->hFileId),
                        'objects' => $objects,
                        'wildcardPath' => $this->hFileWildcardPath
                    )
                ).
                # This HTML template provides a floating Editor panel that contains
                # formatting controls for the WYSIWYG.
                $this->getPanel(
                    'Editor',
                    'hEditor',
                    array(
                        'Format'     => $this->getTemplate('Format'),
                        'Template'   => $this->getTemplate('Template')
                        #'Properties' => $this->getTemplate('Properties'),
                        #'Dimensions' => $this->getTemplate('Dimensions'),
                        #'Background' => $this->getTemplate('Background'),
                        #'Layout'     => $this->getTemplate('Layout'),
                        #'Content'    => $this->getTemplate('Content')
                    )
                );

            # This provides a dialogue for modifying links in the editor.
            $this->plugin('hEditor/hEditorTemplate/hEditorTemplateLink');

            # This provides a dialogue for modifying the "src" path of images
            # in the editor, and an alternative method of selecting images.  This
            # dialogue allows you to directly browse the file system for images,
            # for example.
            $this->plugin('hEditor/hEditorTemplate/hEditorTemplateImage');

            # Same with this one, but for video.
            $this->plugin('hEditor/hEditorTemplate/hEditorTemplateMovie');

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
            $panelTabs['hEditorTemplatePanelTabId'][$i]    = str_replace(' ', '', $label);
            $panelTabs['hEditorTemplatePanelTabLabel'][$i] = $label;

            $panelPanes['hEditorTemplatePanelPaneId'][$i]  = str_replace(' ', '', $label);
            $panelPanes['hEditorTemplatePanelPane'][$i]    = $pane;

            $i++;
        }

        return $this->getTemplate(
            'Panel',
            array(
                'hEditorTemplatePanelName'     => $name,
                'hEditorTemplatePanelNameAsId' => str_replace(array(' ', '/'), '', $name),
                'hEditorTemplatePanelId'       => $id,
                'hEditorTemplatePanelTabs'     => $panelTabs,
                'hEditorTemplatePanelPanes'    => $panelPanes
            )
        );
    }
}

?>