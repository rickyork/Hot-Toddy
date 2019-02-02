$.fn.extend({

    getDocument : function()
    {
        return this.each(
            function()
            {
                if (this.title)
                {
                    if (this.className.indexOf('File') != -1)
                    {
                        var id = $(this).splitId();

                        if (!$('li#hEditorDocumentTab-' + id).length)
                        {
                            var path = this.title;

                            editor.createDocument(
                                editor.getDocumentNameFromPath(path),
                                id,
                                path,
                                false,
                                $(this).hasClass('hFinderTreeServer')
                            );
                        }
                        else
                        {
                            $('li#hEditorDocumentTab-' + id).toggleSelectedDocument();
                        }
                    }
                    else if ($(this).next.hasClass('hFinderTreeHasChildren'))
                    {
                        $(this).next().openBranch();
                    }
                }
            }
        );
    },

    closeDocument : function()
    {
        var obj;

        var nextToBeSelected = {};

        if (this.prev('li.hEditorDocuments').length)
        {
            nextToBeSelected = this.prev('li.hEditorDocuments');
        }
        else if (this.next('li.hEditorDocuments').length)
        {
            nextToBeSelected = this.next('li.hEditorDocuments');
        }

        if (this.hasClass('hEditorSelectedDocumentTab'))
        {
            $('#hEditorDocumentName').html('');
            $('#hEditorDocumentPath').html('');

            editor.selectedDocument.remove();
            editor.selectedDocumentTab.remove();
        }
        else
        {
            $('div#hEditorDocument-' + this.splitId()).remove();
            this.remove();
        }

        if (nextToBeSelected && nextToBeSelected.length)
        {
            nextToBeSelected.toggleSelectedDocument();
        }
        else if (!$('li.hEditorDocuments').length)
        {
            if (arguments[0])
            {
                window.close();
            }
            else
            {
                $('li#hEditorNewDocument').click();
            }
        }
    },

    toggleSelectedDocument : function()
    {
        var div = $('div#hEditorDocument-' + this.splitId());

        if (editor.selectedDocument)
        {
            editor.selectedDocument.removeClass('hEditorSelectedDocument');
        }

        if (editor.selectedDocumentTab)
        {
            editor.selectedDocumentTab.removeClass('hEditorSelectedDocumentTab');
        }

        editor.selectedDocument = div;

        if (!this.hasClass('hEditorSelectedDocument'))
        {
            editor.selectedDocument.addClass('hEditorSelectedDocument');
        }

        if (!this.hasClass('hEditorSelectedDocument'))
        {
            editor.selectedDocumentTab = this;
        }

        if (editor.selectedDocumentTab && editor.selectedDocumentTab.length)
        {
            editor.selectedDocumentTab.addClass('hEditorSelectedDocumentTab');
        }

        if (this.find('span.hEditorDocumentName').length)
        {
            var fileName = this.find('span.hEditorDocumentName').html();

            fileName = fileName.replace(/\+/g, ' ');

            $('div#hEditorDocumentName')
                .html(fileName);

            $('div#hEditorDocumentPath')
                .html(this.attr('title'));
        }
    },

    toggleSelectedLanguage : function()
    {

    },

    toggleSelectedPanel : function()
    {
        //this.select('hEditorAction');

        $('li.hEditorModeActive')
            .removeClass('hEditorModeActive');

        this.addClass('hEditorModeActive');

        var id = this.attr('id');

        if (id == 'hEditorModeWYSIWYG')
        {
            $('iframe.hEditorDocumentWYSIWYGFrame')
                .show();

            $('iframe.hEditorDocumentPreviewFrame')
                .hide();

            $('iframe.hEditorDocumentFrame')
                .hide();

            editor.toggleWYSIWYG(true);
            editor.toggleFindAndReplace(false);
        }
        else if (id == 'hEditorModePreview')
        {
            $('iframe.hEditorDocumentWYSIWYGFrame')
                .hide();

            $('iframe.hEditorDocumentPreviewFrame')
                .show();

            $('iframe.hEditorDocumentFrame')
                .hide();

            editor.toggleWYSIWYG(false);
            editor.toggleFindAndReplace(false);
        }
        else
        {
            editor.toggleWYSIWYG(false);

            $('iframe.hEditorDocumentWYSIWYGFrame')
                .hide();

            $('iframe.hEditorDocumentPreviewFrame')
                .hide();

            $('iframe.hEditorDocumentFrame')
                .show();

            $('iframe.hEditorDocumentFrame').each(
                function()
                {
                    var obj = editor.getFrame($(this));

                    switch (id)
                    {
                        case 'hEditorModeSource':
                        {
                            obj.$('div.hFormDivision:not(div#hEditorDocumentContent)')
                                .hide();

                            obj.$('div.hDialogueContentWrapper')
                                .css('position', 'static');

                            obj.$('div.hDialogueTabsOuter')
                                .hide();

                            obj.$('div#hEditorDocumentContent')
                                .show();

                            editor.toggleFindAndReplace(true);
                            break;
                        };
                        case 'hEditorModeMetaData':
                        {
                            obj.$('div.hDialogueTabsOuter')
                                .show();

                            obj.$('div.hDialogueContentWrapper')
                                .css('position', 'absolute');

                            obj.$('li#hDialogueTab-hEditorDocumentProperties')
                                .click();

                            obj.$('div#hEditorDocumentContent')
                                .hide();

                            editor.toggleFindAndReplace(false);
                            break;
                        };
                    }
                }
            );
        }
    },

    getDocumentName : function()
    {

    }
});

var editor = {

    newDocumentCounter : 1,
    selectedDocumentTab : null,
    selectedDocument : null,
    selectedFrame : null,
    frameStatus : [],
    frameOffset : 0,

    toggleFindAndReplace : function(show)
    {
        if (show)
        {
            $('div#hEditorFind')
                .show();

            $('div#hEditorTabs')
                .css('top', '114px');

            $('div#hEditorDocument')
                .css('top', 0);
        }
        else
        {
            $('div#hEditorFind')
                .hide();

            $('div#hEditorTabs')
                .css('top', '78px');

            $('div#hEditorDocument')
                .css('top', '-36px');
        }
    },

    toggleWYSIWYG : function(show)
    {
        if (show)
        {
            $('span#hEditorContentSelected')
                .show();

            $('span#hEditorContentSelectedLabel')
                .show();

            $('div#hEditorActionsWYSIWYG')
                .show();
        }
        else
        {
            $('span#hEditorContentSelected')
                .hide();

            $('span#hEditorContentSelectedLabel')
                .hide();

            $('div#hEditorActionsWYSIWYG')
                .hide();
        }
    },

    ready : function()
    {
        $('div#hEditorModes ul li').click(
            function()
            {
                $(this).toggleSelectedPanel();
            }
        );

        $('div#hEditorTabsLeft ul li').click(
            function()
            {
                $('div#hEditorTabsLeft ul li')
                    .removeClass('hEditorTabFileSelected');

                $(this).addClass('hEditorTabFileSelected');

                if ($(this).is('li#hEditorTabFiles'))
                {
                    $('ul#hEditorTemplates')
                        .hide();

                    $('div.hFinderTree')
                        .show();
                }
                else if ($(this).is('li#hEditorTabTemplates'))
                {
                    $('ul#hEditorTemplates')
                        .show();

                    $('div.hFinderTree')
                        .hide();
                }
            }
        );

        $('li#hEditorTabFiles').click();

        $('li#hEditorModeSource').click();

        $(document).on(
            'click',
            '.hFinderTreeFile',
            function()
            {
                var node = $(this);

                node.getDocument();

                node.children('div.hFinderTreeFile')
                    .select('hFinderTree');
            }
        );

        $('#hEditorNewDocument').click(
            function()
            {
                editor.newDocument();
            }
        );

        $('li#hEditorTemplateSave').click(
            function(event)
            {
                event.preventDefault();
                editor.save();
            }
        );

        $('li#hEditorTemplateCancel').click(
            function(event)
            {
                event.preventDefault();
                window.close();
            }
        );

//        $('.hEditorSaveAs').mousedown(
//            function(event)
//            {
//                event.preventDefault();
//                editor.saveAs();
//            }
//        );

        $(document).on(
            'click',
            'span.hEditorDocumentTabClose',
            function(event)
            {
                event.stopPropagation();

                if ($(this).parents('li.hEditorDocuments:first').length)
                {
                    $(this).parents('li.hEditorDocuments:first').closeDocument();
                }
            }
        );

        $(document)
            .on(
                'mouseover',
                'li.hEditorTabButton',
                function()
                {
                    $(this).addClass('hEditorTabOn');
                }
            )
            .on(
                'mouseout',
                'li.hEditorTabButton',
                function()
                {
                    $(this).removeClass('hEditorTabOn');
                }
            );

        $(document).on(
            'click',
            'li.hEditorDocuments',
            function()
            {
                $(this).toggleSelectedDocument();
            }
        );

        $(document).on(
            'click',
            'li.hEditorLanguage',
            function()
            {
                $(this).toggleSelectedLanguage();
            }
        );

        $('.hEditorCancel').click(
            function(event)
            {
                event.preventDefault();
                window.close();
            }
        );

        if ($('div#hEditorTabs').children().length)
        {
            $('li.hEditorDocuments').toggleSelectedDocument();
        }
        else
        {
            $('div.hEditorSelectedDocument').toggleSelectedDocument();
        }

        $('ul#hEditorDocuments').sortable({
            cancel : 'li#hEditorNewDocument',
            items : 'li.hEditorDocuments',
            axis : 'x'
        });

        $('div#hEditorResizeGrip').mousedown(
            function(event)
            {
                editor.resizeIsActive = true;
                editor.coordinates = {
                    x : event.pageX,
                    y : event.pageY,
                    width : $('div.hFinderTree').width()
                };
            }
        );

        $(document)
            .mousemove(
                function(event)
                {
                    if (editor.resizeIsActive)
                    {
                        editor.onResize(event);
                    }
                }
            )
            .mouseup(
                function(event)
                {
                    if (editor.resizeIsActive)
                    {
                        editor.resizeIsActive = false;
                        editor.saveColumnDimensions();
                    }
                }
            );

        if (this.treeWidth)
        {
            this.resize(this.treeWidth);
        }

        if (this.width > screen.width)
        {
            this.width = screen.width;
        }

        if (this.height > screen.height)
        {
            this.height = screen.height;
        }

        window.resizeTo(this.width, this.height);

        window.moveTo(
            (screen.width - this.width) / 2,
            (screen.height - this.height) / 2
        );

        $(window).resize(
            function()
            {
                http.get(
                    '/hEditor/saveWindowDimensions', {
                        operation : 'Save Window Dimensions',
                        width : $(window).width(),
                        height : $(window).height()
                    },
                    function(json)
                    {

                    }
                );
            }
        );

        $('input#hEditorFindString').keypress(
            function(event)
            {
                if (event.keyCode == 13) // Return
                {
                    editor.find();
                }
            }
        );

        $('button#hEditorFindPrevious').click(
            function(event)
            {
                event.preventDefault();
                editor.findPrevious();
            }
        );

        $('button#hEditorFindNext').click(
            function(event)
            {
                event.preventDefault();
                editor.findNext();
            }
        );

        $('input#hEditorReplaceString').keypress(
            function(event)
            {
                if (event.keyCode == 13)
                {
                    editor.replace();
                }
            }
        );

        $('input#hEditorReplaceButton').click(
            function(event)
            {
                event.preventDefault();
                editor.replace();
            }
        );

        $('input#hEditorReplaceAllButton').click(
            function(event)
            {
                event.preventDefault();
                editor.replaceAll();
            }
        );

        $('a#hEditorFindOptions').click(
            function(event)
            {
                event.preventDefault();

                if ($(this).hasClass('hEditorFindOptionsOn'))
                {
                    $(this).removeClass('hEditorFindOptionsOn');
                    $('div#hEditorFindReplaceContextMenu').hide();
                }
                else
                {
                    editor.findReplaceContextMenuActive = true;

                    $(this).addClass('hEditorFindOptionsOn');

                    var offset = $(this).offset();

                    $('div#hEditorFindReplaceContextMenu')
                        .show()
                        .css({
                            top : (offset.top + $(this).height() + 1) + 'px',
                            left : offset.left + 'px'
                        });
                }
            }
        );

        $('div#hEditorFindReplaceContextMenu li')
            .hover(
                function()
                {
                    if (!$(this).hasClass('hFinderContextMenuSeparator'))
                    {
                        $(this).addClass('hFinderContextMenuItemOn');
                    }
                },
                function()
                {
                    $(this).removeClass('hFinderContextMenuItemOn');
                }
            )
            .click(
                function()
                {
                    if (!$(this).hasClass('hFinderContextMenuSeparator') && !$(this).hasClass('hFinderContextMenuItemDisabled'))
                    {
                        if ($(this).hasClass('hFinderContextMenuItemChecked'))
                        {
                            $(this).removeClass('hFinderContextMenuItemChecked');
                        }
                        else
                        {
                            $(this).addClass('hFinderContextMenuItemChecked');
                        }

                        editor.savePreferences();
                    }
                }
            );

        $('a#hEditorFindOptions, div#hEditorFindReplaceContextMenu').hover(
            function()
            {
                editor.findReplaceContextMenuActive = true;
            },
            function()
            {
                editor.findReplaceContextMenuActive = false;
            }
        );

        $(document).mousedown(
            function()
            {
                if (!editor.findReplaceContextMenuActive)
                {
                    $('a#hEditorFindOptions').removeClass('hEditorFindOptionsOn');
                    $('div#hEditorFindReplaceContextMenu').hide();
                }
            }
        );

        if (this.findReplace.backwards)
        {
            $('li#hEditorFindReplaceBackwards')
                .addClass('hFinderContextMenuItemChecked');
        }

        if (this.findReplace.wrap)
        {
            $('li#hEditorFindReplaceWrap')
                .addClass('hFinderContextMenuItemChecked');
        }

        if (this.findReplace.caseSensitive)
        {
            $('li#hEditorFindReplaceCaseSensitive')
                .addClass('hFinderContextMenuItemChecked');
        }

        if (this.findReplace.wholeWord)
        {
            $('li#hEditorFindReplaceWholeWord')
                .addClass('hFinderContextMenuItemChecked');
        }

        if (this.findReplace.regExp)
        {
            $('li#hEditorFindReplaceRegularExpression')
                .addClass('hFinderContextMenuItemChecked');
        }
    },

    savePreferences : function()
    {
        http.get(
            '/hEditor/savePreferences', {
                operation : 'Save Preferences',
                backwards :
                    $('li#hEditorFindReplaceBackwards')
                        .hasClass('hFinderContextMenuItemChecked') ? 1 : 0,
                wrap :
                    $('li#hEditorFindReplaceWrap')
                        .hasClass('hFinderContextMenuItemChecked') ? 1 : 0,
                caseSensitive :
                    $('li#hEditorFindReplaceCaseSensitive')
                        .hasClass('hFinderContextMenuItemChecked') ? 1 : 0,
                wholeWord :
                    $('li#hEditorFindReplaceWholeWord')
                        .hasClass('hFinderContextMenuItemChecked') ? 1 : 0,
                regExp :
                    $('li#hEditorFindReplaceRegularExpression')
                        .hasClass('hFinderContextMenuItemChecked') ? 1 : 0
            },
            function(json)
            {

            }
        );
    },

    find : function()
    {
        var find = $('input#hEditorFindString').val();

        var options = {
            backwards :
                $('li#hEditorFindReplaceBackwards').
                    hasClass('hFinderContextMenuItemChecked'),
            wrap :
                $('li#hEditorFindReplaceWrap')
                    .hasClass('hFinderContextMenuItemChecked'),
            caseSensitive :
                $('li#hEditorFindReplaceCaseSensitive')
                    .hasClass('hFinderContextMenuItemChecked'),
            wholeWord :
                $('li#hEditorFindReplaceWholeWord')
                    .hasClass('hFinderContextMenuItemChecked'),
            regExp :
                $('li#hEditorFindReplaceRegularExpression')
                    .hasClass('hFinderContextMenuItemChecked')
        };

        if (find && find.length)
        {
            this.selected().editor.document.find(find, options);
            return true;
        }

        return false;
    },

    findNext : function()
    {
        $('li#hEditorFindReplaceBackwards')
            .removeClass('hFinderContextMenuItemChecked');

        this.find();
    },

    findPrevious : function()
    {
        $('li#hEditorFindReplaceBackwards')
            .addClass('hFinderContextMenuItemChecked');

        this.find();
    },

    replace : function()
    {
        var replace = $('input#hEditorReplaceString').val();

        if (replace && replace.length)
        {
            this.selected().editor.document.replace(replace);
        }
    },

    replaceAll : function()
    {
        var replace = $('input#hEditorReplaceString').val();

        if (replace && replace.length)
        {
            this.selected().editor.document.replaceAll(replace);
        }
    },

    onResize : function(event)
    {
        var frameOffset = 0;

        if (arguments[1])
        {
            frameOffset = $('div#hEditorDocument iframe.hEditorDocumentFrame:visible').offset().left;
        }

        this.resize(
            this.coordinates.width - (
                this.coordinates.x  - (event.pageX + frameOffset)
            )
        );
    },

    resize : function(width)
    {
        if (width < 200)
        {
            width = 200;
        }
        else if (width > 500)
        {
            width = 500;
        }

        this.resizedTo = width;

        $('div#hEditorFindLeft')
            .width(width + 'px');

        $('div#hEditorTabsLeft')
            .width(width + 'px');

        $('div.hFinderTree')
            .width(width + 'px');

        $('ul#hEditorTemplates')
            .width((width - 4) + 'px');

        $('div#hEditorResizeGrip')
            .css('left', width + 'px');

        $('div#hEditorActions').css(
            'left', width + 'px');

        $('div#hEditorTabs')
            .css('left', (width + 1) + 'px');

        $('div#hEditorFind')
            .css('left', (width + 1) + 'px');

        $('div#hEditorDocument')
            .css('left', width + 'px');
    },

    saveColumnDimensions : function()
    {
        if (this.resizedTo)
        {
            http.get(
                '/hEditor/saveColumnDimensions', {
                    operation : 'Save Column Dimensions',
                    width : this.resizedTo
                },
                function(json)
                {
                    editor.resizedTo = 0;

                    switch (parseInt(json))
                    {
                        case 1:
                        {

                        }
                    }
                }
            );
        }
    },

    save : function()
    {
        $('input#hEditorSave')
            .attr('disabled', true);

        $('input#hEditorSaveAs')
            .attr('disabled', true);

        this.selected()
            .editor
            .document
            .save(arguments[0]? true : false);
    },

    saveAs : function()
    {
        var obj = this.selected();

        this.saveAsDialogue = hot.window(
            '/Applications/Finder/index.html', {
                dialogue: 'SaveAs',
                onSaveAs: 'editor.onSaveAs',
                path: obj.$('input#hDirectoryPath').val(),
                hFileName: obj.$('input#hFileName').val()
            },
            600,
            400,
            '_blank', {
                scrollbars: false,
                resizable: true
            }
        );
    },

    getFrame : function(iframe)
    {
        if (iframe.length)
        {
            iframe = iframe.get(0);
        }
        else
        {
            iframe = $('iframe.hEditorDocumentFrame:first').get(0);
        }

        if (iframe && iframe !== undefined)
        {
            if (iframe.contentWindow)
            {
                return iframe.contentWindow;
            }
            else if (iframe.contentDocument)
            {
                return iframe.contentDocument;
            }
            else if (window.frames[iframe.name])
            {
                return window.frames[iframe.name];
            }
            else
            {
                return eval("window." + iframe.name);
            }
        }
    },

    selected : function()
    {
        var id = this.selectedDocument.splitId();
        return this.getFrame($('iframe#hEditorDocumentFrame-' + id));
    },

    getPreviewFrame : function()
    {
        var id = this.selectedDocument.splitId();
        return this.getFrame($('iframe#hEditorDocumentPreviewFrame-' + id));
    },

    getWYSIWYGFrame : function()
    {
        var id = this.selectedDocument.splitId();
        return this.getFrame($('iframe#hEditorDocumentWYSIWYGFrame-' + id));
    },

    onSaveAs : function(directoryPath, fileName, replaceExisting)
    {
        var obj = editor.selected();

        obj.$('input#hFileName').val(fileName);
        obj.$('input#hDirectoryPath').val(directoryPath);
        obj.$('input#hFileReplaceExisting').val(replaceExisting? 1 : 0);

        obj.editor.document.save(replaceExisting || obj.$('input#hFileId').val()? true : false);
    },

    getDocumentPath : function()
    {
        return this.selectedDocumentTab.attr('title');
    },

    createDocument : function(name, id, path)
    {
        var obj = $('li#hEditorNewDocument');

        var newDocument = (arguments[3]);

        var li = document.createElement('li');

        li.id = 'hEditorDocumentTab-' + id;
        li.className = 'hEditorDocuments' + (newDocument? ' hEditorNewDocument' : ' hEditorDocument') + ' hEditorTabButton';
        li.title = path;

        var span = document.createElement('span');
        span.className = 'hEditorDocumentName';

        span.appendChild(document.createTextNode(name));

        li.appendChild(span);

        var span = document.createElement('span');
        span.className = 'hEditorDocumentTabClose';

        li.appendChild(span);

        $('ul#hEditorDocuments').append(li);

        var div = document.createElement('div');

        div.id = 'hEditorDocument-' + id;
        div.className = 'hEditorDocument';

        var iframe = document.createElement('iframe');

        iframe.src = hot.path(
            '/Applications/Editor/Document.html', {
                path : path,
                id : 'hEditorDocumentFrame-' + id
            }
        );

        iframe.id = 'hEditorDocumentFrame-' + id;
        iframe.name = 'hEditorDocumentFrame' + id;
        iframe.className = 'hEditorDocumentFrame';
        iframe.frameborder = 0;
        iframe.marginwidth = 0;
        iframe.marginheight = 0;
        iframe.border = 0;
        iframe.style.border = 'none';

        div.appendChild(iframe);

        $('iframe#hEditorDocumentFrame-' + id).attr('data-file-path', path);

        var iframe = document.createElement('iframe');

        iframe.src = arguments[4] || !path? '/hFile/blank' : path;

        iframe.id = 'hEditorDocumentPreviewFrame-' + id;
        iframe.name = 'hEditorDocumentPreviewFrame' + id;
        iframe.className = 'hEditorDocumentPreviewFrame';
        iframe.frameborder = 0;
        iframe.marginwidth = 0;
        iframe.marginheight = 0;
        iframe.border = 0;
        iframe.style.border = 'none';

        div.appendChild(iframe);

        $('iframe#hEditorDocumentPreviewFrame-' + id)
            .attr('data-file-path', path);

        $('#hEditorDocument').append(div);

        $(li).toggleSelectedDocument();
    },

    newDocument : function()
    {
        $('input#hEditorSave')
            .attr('disabled', true);

        $('input#hEditorSaveAs')
            .attr('disabled', true);

        this.newDocumentCounter++;
        this.createDocument('New Document [' + this.newDocumentCounter + ']', 'new' + this.newDocumentCounter, '', true);
    },

    getDocumentNameFromPath : function(path)
    {
        return path.split('/').pop();
    },

    refreshFileAttributes : function(fileName, filePath, fileId)
    {
        this.selectedDocument.attr('id', 'hEditorDocument-' + fileId);

        this.selectedDocument
            .find('iframe.hEditorDocumentFrame')
                .attr('id', 'hEditorDocumentFrame-' + fileId)
                .attr('name', 'hEditorDocumentFrame' + fileId)
                .attr('data-file-path', filePath);

        this.selectedDocument
            .find('iframe.hEditorDocumentPreviewFrame')
                .attr('id', 'hEditorDocumentPreviewFrame-' + fileId)
                .attr('name', 'hEditorDocumentPreviewFrame' + fileId)
                .attr('data-file-path', filePath)
                .attr('src', filePath);


        // Don't update the name attribute.    Safari, and perhaps others do
        // not update the name of the frame in the window.frames array...
        // this has to be worked around, it seems.
        //
        //    .attr('name', 'hEditorDocumentFrame' + $hFileId)

        fileName = fileName.replace('+', ' ');

        if (this.selectedDocumentTab && this.selectedDocumentTab.length)
        {
            this.selectedDocumentTab
                .attr('id', 'hEditorDocumentTab-' + fileId)
                .find('span.hEditorDocumentName')
                .text(fileName);
        }

        $('div#hEditorDocumentName').text(fileName);
        $('div#hEditorDocumentPath').text(filePath);

        // This already happens in the save callback.
        // finder.tree.refreshBranchByFileId(fileId);

        this.getPreviewFrame().location.reload(true);
    }
};

keyboard
	.shortcut(
	    {
	        saveDocument : "Command + S, Control + S"
	    },
	    function()
	    {
	        editor.save();
	    }
    )
    .shortcut(
        {
            newDocument : "Command + N, Control + N"
        },
        function()
        {
            editor.newDocument();
        }
    )
    .shortcut(
        {
            closeDocument : "Command + W, Control + W"
        },
        function()
        {
            if (editor.selectedDocumentTab && editor.selectedDocumentTab.length)
            {
                editor.selectedDocumentTab.closeDocument(true);
            }
        }
    );

$(document)
    .bind(
        'touchmove',
        function(event)
        {
            event.preventDefault();
        }
    )
    .ready(
        function()
        {
            //editor.ready();
        }
    );

$(window).load(
    function()
    {
        editor.ready();
    }
);
