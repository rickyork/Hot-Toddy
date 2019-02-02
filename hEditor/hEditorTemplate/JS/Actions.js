/**
* Editor Actions
*
* Hot Toddy Events
*
*   editorSave - Fired before the document is saved.  Additional information can be
*   appended to the POST request by adding JSON style key/value pairs to editor.post.
*   Returning an explicit false will cancel the save.
*
*   editorSaveFailed - Fired if the server was unable to save the document for any
*   reason.  The response code is provided in the 1st argument.
*
*   editorSaveSuccessful - Fired if the server was able to save the document.  The
*   response code is provided in the 1st argument.
*
*   editorIsSource - Fired when the editor is put in source mode.
*
*   editorIsNotSource - Fired when the editor is taken out of source mode.
*
*   editorIsNotEditable - Fired when the editor is made uneditable.
*
*   editorIsEditable - Fired when the editor is made editable.
*
*   editorHasErrors
*/

$.extend(
    editor, {
        actionsReady : function()
        {
            // Provide the functionality of the "Edit" button in the
            // strip along the bottom of the window. When the document is
            // editable, the side panel, editor panel, and a transparent
            // "jamming" <div> element is present.  Basically, all editor
            // controls are enabled.  Disabling the "Edit" button, on the
            // other hand, gets rid of the top Editor and side panel, the
            // jamming <div> and any editor controls inserted into the
            // document's elements.
            $('li#hEditorTemplateEdit button').click(
                function(event)
                {
                    event.preventDefault();
                    editor.toggleEditability();
                }
            );

            //
            $('li#hEditorTemplateSource button').click(
                function(event)
                {
                    event.preventDefault();
                    editor.toggleSource();
                }
            );

            // Saves the document. to the server.
            $('li#hEditorTemplateSave button').click(
                function(event)
                {
                    event.preventDefault();
                    editor.save();
                }
            );

            $('li#hEditorTemplateCancel button').click(
                function(event)
                {
                    event.preventDefault();

                    if (confirm("Are you sure you want to close the editor?\n\nAll changes you've made up to this point will be lost."))
                    {
                        location.href =  editor.wildcardPath? editor.wildcardPath : editor.filePath;
                    }
                }
            );

            this.setEditability(true);

            // Selection must be prevented if the handle is being clicked and dragged,
            // as with sorting.  Not shutting off the selection API here results in
            // DOM errors that drag down the editor's responsiveness.
            //
            // Both this measure here and user-select: none in the stylesheet pretty
            // well fixes the problem.
            $(document).on(
                'mousedown',
                'div.hEditorTemplateNodeHandle',
                function()
                {
                    editor.preventSelection = true;

                    var selection = window.getSelection();
                    selection.removeAllRanges();
                }
            );

            $(document).on(
                'mouseup',
                function()
                {
                    if (editor.preventSelection)
                    {
                        editor.preventSelection = false;
                    }
                }
            );
        },

        toggleSource : function()
        {
            if (this.isSource)
            {
                this.setSource(false);
                $('li#hEditorTemplateSource').commandOff();
            }
            else
            {
                this.setSource(true);
                $('li#hEditorTemplateSource').commandOn();
            }
        },

        isEditable : false,

        setEditability : function(isEditable)
        {
            this.isEditable = isEditable;

            if (this.title && this.title.length)
            {
                // Take each node considered a title element and make it
                // editable or uneditable by setting the contenteditable attribute.
                this.title.each(this.toggleContentEditable);
            }

            if (!isEditable && this.isSource)
            {
                // If the document is not editable, but the source code is editable,
                // make the source code uneditable.
                this.toggleSource(false);
            }

            if (isEditable)
            {
                // If the document is editable, add the following class names to the following
                // elements which helps to style those elements and their descendants when the
                // editor is active.
                $('div#hEditorTemplateDocumentWrapper')
                    .addClass('hEditorTemplateDocumentWrapperOn');

                $('div#hEditorTemplateTitleWrapper')
                    .addClass('hEditorTemplateTitleWrapperOn');
            }
            else
            {
                // Conversely, remove those class names if the edtir is not editable.
                $('div#hEditorTemplateDocumentWrapper')
                    .removeClass('hEditorTemplateDocumentWrapperOn');

                $('div#hEditorTemplateTitleWrapper')
                    .removeClass('hEditorTemplateTitleWrapperOn');
            }

            // Get all the buttons designated as only having an effect and purpose when
            // editing is enabled.
            var buttons = $('div.hEditorContentEditableButtons');

            this.setPreviewLabel('Nothing');

            if (!isEditable)
            {
                hot.fire('editorIsNotEditable');

                //$('div#hEditorTemplateClone').sortable('destroy');
                hot.unselect('hEditorTemplateNode');

                // Remove the "active" class from the editor buttons and add the
                // disabled class.
                buttons.find('li')
                       .removeClass('hEditorTemplateButtonActive');

                buttons.addClass('hEditorTemplateDisabledButtons');

                // Call toSourceCode(), this unwraps nodes (removing editor controls from
                // editable nodes), removes <br /> elements.  Removes empty <p> elements.
                this.toSource();

                // Update the visual state of the "Edit" button, so that it does not
                // appear enabled.
                $('li#hEditorTemplateEdit').commandOff();

                // Get rid of the floating Editor panel.
                $('div#hEditor').fadeOut();

                // Get rid of the <div> overlay that prevents you from interacting with
                // content outside of editable nodes.
                $('div#hEditorTemplateOverlay').fadeOut();

                // Get rid of the side panel by invoking the low-resolution state.
                $('body').addClass('hEditorTemplateLowRes');

                $('div#hEditorTemplateDocumentWrapper')
                    .css('height', 'auto');

                this.document = $(this.documentSelector);

                if (this.document && this.document.length)
                {
                    this.document.each(
                        function()
                        {
                            var element = $(this);
                            var node    = $(this).clone(true);

                            $('div#hEditorTemplateDocumentWrapper').append(node);

                            $(this).remove();
                        }
                    );
                }

                // Empty the cloned element and hide it.
                $('div#hEditorTemplateClone').html('').hide();
            }
            else
            {
                if (!this.document || this.document.length)
                {
                    this.document = $(this.documentSelector);
                }

                if (this.document && this.document.length)
                {
                    this.document.each(
                        function()
                        {
                            if (!$.trim($(this).html()))
                            {
                                $(this).html("<p>Add content here...</p>");
                            }
                        }
                    );

                    hot.fire('editorIsEditable');

                    $('div#hEditorTemplateClone').html('').show();

                    this.document.each(
                        function()
                        {
                            var element = $(this);
                            var node    = $(this).clone(true);

                            $('div#hEditorTemplateClone').append(node);

                            $(this).remove();
                        }
                    );

                    var height = 0;

                    console.log(this.documentSelector);

                    $('div#hEditorTemplateClone').find(this.documentSelector).each(
                        function()
                        {
                            height += parseInt($(this).outerHeight(true));
                        }
                    );

                    $('div#hEditorTemplateDocumentWrapper').height(height + 'px').html('');

                    this.document = $(this.documentSelector);

                    this.document.dragAndDropEvents();
                    //this.document.sanitizeEvents();

                    this.toWYSIWYG();

                    buttons.removeClass('hEditorTemplateDisabledButtons');

    /*
                     $('div#hEditorTemplateClone')
                        .children(this.documentSelector)
                        .children('div.hEditorTemplateNodeWrapper')
                        .css('opacity', 0.3);
    */

                    $('li#hEditorTemplateEdit').commandOn();

                    $('div#hEditor').fadeIn();
                    $('div#hEditorTemplateOverlay').fadeIn();

                    if ($(window).width() < 1280)
                    {
                        $('body').addClass('hEditorTemplateLowRes');
                    }
                    else
                    {
                        $('body').removeClass('hEditorTemplateLowRes');
                    }

                    this.setSizeAndPosition();
                }
            }
        },

        toggleContentEditable : function()
        {
            this.contentEditable = editor.isEditable;
        },

        toggleEditability : function()
        {
            this.setEditability(!this.isEditable);
        },

        isSource : false,
        ace : null,

        setSource : function(isSource)
        {
            isSource? this.toSource() : this.toWYSIWYG();

            this.isSource = isSource;

            this.document.garbageCollection();

            this.setPreviewLabel('Nothing');

            this.document.each(
                function()
                {
                    if (editor.isSource)
                    {
                        $(this).hide();
                        $('div.hEditorTemplateSource').fadeIn();
                        $('div#hEditor').fadeOut();
                        $('body').addClass('hEditorTemplateLowRes');
                    }
                    else
                    {
                        $(this).show();
                        $('div.hEditorTemplateSource').fadeOut();
                        $('div#hEditor').fadeIn();

                        if ($(window).width() < 1280)
                        {
                            $('body').addClass('hEditorTemplateLowRes');
                        }
                        else
                        {
                            $('body').removeClass('hEditorTemplateLowRes');
                        }
                    }

                    if (!editor.ace)
                    {
                        editor.ace = ace.edit($('div.hEditorTemplateSource').get(0));
                        editor.ace.setTheme('ace/theme/textmate');

                        var editorMode = require('ace/mode/html').Mode;
                        editor.ace.getSession().setMode(new editorMode());
                    }

                    if (editor.isSource)
                    {
                        editor.ace.getSession().setValue($(this).html());
                    }
                    else
                    {
                        $(this).html(editor.ace.getSession().getValue());
                    }
                }
            );

            if (!isSource)
            {
                this.setEditability(true);
            }

            var buttons = $('div.hEditorContentEditableButtons');

            if (isSource)
            {
                hot.fire('editorIsSource');
                buttons.addClass('hEditorTemplateDisabledButtons');
            }
            else
            {
                hot.fire('editorIsNotSource');
                buttons.removeClass('hEditorTemplateDisabledButtons');
            }
        },

        toSource : function()
        {
            this.document
                .find('div.hEditorTemplateNodeWrapper')
                .unwrapNodes();

            this.document
                .find('br')
                .remove();

            this.document
                .find('p:empty')
                .remove();
        },

        toWYSIWYG : function()
        {
            this.document.sanitizeContent();
        },

        post : null,

        /**
        * Sends the document to the server to be saved via POST request to
        * /hEditor/hEditorTemplate/save
        *
        * The POST request is formed into a JSON object stored in editor.post
        *
        * See above for Hot Toddy events.
        */
        save : function()
        {
            this.setEditability(false);
            this.garbageCollection();

            this.post = {
                hFileId : this.fileId,
                hFileTitle : $('input#hFileTitle').val(),
                hFileDescription : $('textarea#hFileDescription').val(),
                hFileKeywords : $('textarea#hFileKeywords').val()
            };

            if (this.title && this.title.length)
            {
                this.post.hFileHeadingTitle = this.title.html();
            }

            this.document.removeAttr('contenteditable');

            this.document.find(this.blockNodes).removeAttr('contenteditable');

            this.document.removeAttr('style');
            this.document.removeClass('hEditorTemplateDocument');

            var corrupted = false;

            if ($(this.documentContainerSelector) && this.documentContainerSelector.length)
            {
                this.post.hFileDocument = $(this.documentContainerSelector).html();
            }
            else if (this.document && this.document.length)
            {
                this.post.hFileDocument = this.document.html();
            }
            else
            {
                corrupted = true;
            }

            var corrupted = (
                corrupted ||
                !this.post.hFileDocument ||
                this.post.hFileDocument == 'undefined' ||
                this.post.hFileDocument == 'null'
            );

            if (corrupted)
            {
                alert(
                    "Save Document Failed!\n\n" +
                    "An error occurred and the document could not be saved.\n\n" +
                    "Please contact an administrator to resolve this issue.\n\n" +
                    "Regarding: null document error"
                );

                return;
            }

            this.document.find('a').each(
                function()
                {
                    if (!this.className)
                    {
                        $(this).removeAttr('class');
                    }

                    $(this).removeAttr('draggable');
                }
            );

            this.document
                .find('span.hEditorCaret:not(.hEditorCaretTemplate)')
                .remove();

            // Display a message to the user in a box as the request takes place.
            application.status
                .message('Saving Document...');

            if (false !== hot.fire('editorSave'))
            {
                http.post(
                    {
                        url : '/hEditor/hEditorTemplate/save',
                        operation : 'Save Document',
                        onErrorCallback : function(json)
                        {
                            // Update the user that the save request failed to go through.
                            application.status.message('Save Document Failed!', true);
                            hot.fire('editorSaveFailed', json);
                        }
                    },
                    this.post,
                    function(json)
                    {
                        // Let the user know that the request went through properly.
                        application.status
                            .message('Document Saved!', true);

                        hot.fire('editorSaveSuccessful', json);

                        if (typeof(opener) != 'undefined')
                        {
                            // Refresh the original page, if this page is opened in a
                            // new window
                            opener.location.reload(true);
                        }

                        editor.document
                              .addClass('hEditorTemplateDocument');

                        editor.setEditability(true);
                    }
                );
            }
        }
    }
);

$(window).load(
    function()
    {
        editor.actionsReady();
    }
);