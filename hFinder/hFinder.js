$.fn.extend({

    isDirectory : function()
    {
        return this.is('[id*=Directory]');
    },

    moveTo : null,
    moveFrom : null,

    // destination.Move(sourcePath);
    move: function(sourcePath)
    {
        finder.moveTo = this;
        finder.moveFrom = sourcePath;

        var destinationPath = this.getFilePath();

        var folders = sourcePath.split('/');
        folders.pop();
        var baseSourcePath = folders.join('/');

        if (destinationPath == baseSourcePath || destinationPath == '/' && !baseSourcePath.length)
        {
            return;
        }

        http.get(
            '/hFile/move', {
                path : destinationPath,
                sourcePath : sourcePath,
                replace : (arguments[1]? 1 : 0)
            },
            function(json)
            {
                switch (parseInt(json))
                {
                    case -3:
                    {
                        var file = this.moveFrom.split('/').pop();

                        dialogue.confirm({
                            label : "<p>A file or folder with the name <b>" + name + "</b> already exists.</p>" +
                                    "<p>Would you like to <b>PERMANENTLY</b> replace it?</p>",
                            ok : "Replace File",
                            cancel : "Don't Replace File",
                            callback : {
                                fn : function(response)
                                {
                                    if (response)
                                    {
                                        this.moveTo.move(this.moveFrom, true);
                                    }
                                },
                                context : finder
                            }
                        });

                        break;
                    };
                    case 1:
                    {
                        this.refresh();

                        if (this.tree)
                        {
                            this.tree.refreshBranchByDirectoryPath(this.moveTo.getFilePath());
                            this.tree.refreshBranchByDirectoryPath(this.moveFrom);
                        }
                        break;
                    };
                }
            },
            finder
        );
    },

    deleteFile : function()
    {
        dialogue.confirm({
            label :
                "<p>Are you sure you want to <b>PERMANENTLY</b> delete <b>" + this.getFileName() + "</b>?</p>" +
                "<p>This cannot be undone.</p>",
            ok : "Delete File",
            cancel : "Don't Delete File",
            callback : {
                fn : function(response)
                {
                    if (response)
                    {
                        http.get(
                            '/hFile/delete', {
                                operation : 'Delete File',
                                path : this.getFilePath()
                            },
                            function(json)
                            {
                                this.deleteFileNode();
                            },
                            this
                        );
                    }
                },
                context : this
            }
        });

        return $;
    },

    removeFileFromCategory : function()
    {
        var node = this;

        http.get(
            '/hFile/removeFileFromCategory', {
                operation : 'Remove File From Category',
                path : finder.path,
                file : this.getFilePath()
            },
            function(json)
            {
                node.deleteFileNode();
            },
            finder
        );

        return $;
    },

    getDirectory : function()
    {
        if (!this.isDirectory())
        {
            var filewin = hot.window(
                this.getFilePath(), {
                    hFileLastModified : this.find('span.hFinderFileLastModifiedTimestamp').text()
                },
                '_blank', {
                    scrollbars : true,
                    resizable : true
                }
            );
        }
        else
        {
            if ((this.parent('li').hasClass('hFinderTreeApplication') || this.hasClass('hFinderApplication')) && !finder.shortcuts.shiftKey)
            {
                hot.window(
                    this.getFilePath(),
                    null,
                    1050,
                    650,
                    '_blank', {
                        scrollbars : true,
                        resizable : true
                    }
                );
            }
            else
            {
                finder.requestDirectory(this.getFilePath());
            }
        }

        return this;
    },

    getColumnFileProperties : function()
    {
        finder.requestedColumnFile = this.getFilePath();

        http.get(
            '/hFinder/getColumnFileProperties', {
                operation : 'Get Column File Properties',
                path : this.getFilePath()
            },
            function(html)
            {
                var requestedPath = this.requestedColumnFile;

                $('div.hFinderColumnsFileProperties')
                    .parents('.hFinderColumns')
                    .remove();

                if ($('div.hFinderColumns[data-file-path="' + requestedPath + '"]').length)
                {
                    $('div.hFinderColumns[data-file-path="' + requestedPath + '"]').replaceWith(data);
                }
                else
                {
                    var folders = requestedPath.split('/');
                    folders.pop();

                    var baseRequestedPath = folders.join('/');

                    if (baseRequestedPath.length && baseRequestedPath != '/')
                    {
                        $('div.hFinderColumns[data-file-path^="' + baseRequestedPath + '/"]').remove();
                    }
                    else
                    {
                        $('div.hFinderColumns').each(
                            function()
                            {
                                if ($(this).attr('data-file-path') != '/')
                                {
                                    $(this).remove();
                                }
                            }
                        );
                    }

                    $('div#hFinderColumnsWrapper').append(html);

                    this.onAppendColumn();
                }
            },
            finder
        );
    },

    getFileName : function()
    {
        return this.find('span.hFinder' + (this.isDirectory()? 'Directory' : 'File') + 'Name span').text();
    },

    getFilePath : function()
    {
        return this.attr('data-file-path');
    },

    getFileProperties : function()
    {
        finder.properties = hot.window(
            '/Applications/Finder/properties.html', {
                operation : 'Get File Properties',
                path : this.getFilePath()
            },
            600, 500, 'hFinderProperties', {
                scrollbars : false,
                resizable : true
            }
        );
    },

    getInfo : function()
    {
        finder.infoWindow[this.getFilePath()] = hot.window(
            '/Applications/Finder/Info.html', {
                path : this.getFilePath()
            },
            300,
            1000,
            '_blank', {
                scrollbars : false,
                resizable : true
            }
        );
    },

    getFileTitle : function()
    {
        return this.find('h4.hFinderFileTitle').text();
    },

    getExtension : function()
    {
        return finder.getExtension(this.getFileName());
    },

    getMIMEType : function()
    {
        return this.find('span.hFinderFileMIME').text();
    },

    renamePath : null,
    newName : null,

    renameFile : function(newName)
    {
        var replace = false;
        var error = false;

        if (!arguments[1])
        {
            var context = null;

            switch (finder.view)
            {
                case 'Icons':
                {
                    context = $('div#hFinderFilesInner');
                    break;
                };
                case 'List':
                {
                    context = $(this).parents('div.hFinderListInner:first');
                    break;
                };
                case 'Columns':
                {
                    context = $(this).parents('div.hFinderColumnsInner:first');
                    break;
                };
            }

            var file = finder.fileExists(newName, context);
            var replace = false;
            var error = false;

            if (file.exists)
            {
                if (finder.confirmReplace(newName))
                {
                    replace = true;
                }
                else
                {
                    error = true;
                }
            }

            var path = $(this).getFilePath();
        }
        else
        {
            var path = arguments[1];

            if (arguments[2])
            {
                replace = arguments[2];
            }
        }

        var oldName = path.split('/').pop();

        var message = '';
        var extension = '';
        var oldExtension = '';
        var newExtensoin = '';

        if (!$(this).isDirectory())
        {
            oldExtension = finder.getExtension(oldName);
            newExtension = finder.getExtension(newName);

            if (oldName.indexOf('.') != -1 && newName.indexOf('.') != -1 && oldExtension != newExtension)
            {
                var message =
                    "Are you sure you want to change the\n" +
                    "extension from \"." + oldExtension + "\" to .\"" + newExtension + "\"?\n\n" +
                    "If you make this change, your document may not function properly.";
            }
            else if (oldName.indexOf('.') == -1 && newName.indexOf('.') != -1)
            {
                var message =
                    "Are you sure you want to add the extension \"" + newExtension + "\" to the file name?\n\n" +
                    "If you make this change, your document may not function properly.";
            }
            else if (oldName.indexOf('.') != -1 && newName.indexOf('.') == -1)
            {
                var message =
                    "Are you sure you want to remove the extension \"" + oldExtension + "\" from the file name?\n\n" +
                    "If you make this change, your document may not function properly.";
            }

            if (message)
            {
                if (!confirm(message))
                {
                    error = true;
                }
                else
                {
                    extension = oldExtension + ',' + newExtension;
                }
            }
        }

        if (newName.substring(0, 1) == '.')
        {
            var message =
                "Are you sure you want to use a name that begins with a dot \".\"? " +
                "The website treats items with these names as invisible files.";

            if (!confirm(message))
            {
                error = true;
            }
        }

        if (!error)
        {
            finder.newName = newName;

            this.select('hFinder');

            finder.renamePath = path;

            http.get(
                '/hFile/rename', {
                    operation : 'Rename File',
                    path : path,
                    rename : newName,
                    replace : replace? 1 : 0,
                    extension : extension
                },
                function(json)
                {
                    var newName = this.newName;

                    if (parseInt(json) == -3)
                    {
                        if (this.confirmReplace(newName))
                        {
                            $.fn.renameFile(newName, this.renamePath, true);
                        }
                        else
                        {
                            finder.saveFileNameInfoDialogueCallback({
                                    path : this.renamePath
                                },
                                true
                            );
                            return;
                        }
                    }

                    // The file name appears in the title
                    if (this.renamePath)
                    {
                        var node = $("div.hFinderNode[data-file-path='" + this.renamePath + "']");

                        if (node.length)
                        {
                            var paths = this.renamePath.split('/');

                            paths.pop();
                            paths.push(newName);

                            var path = paths.join('/');

                            this.saveFileNameInfoDialogueCallback({
                                path : this.renamePath,
                                fileName : newName,
                                updatedPath : path
                            });

                            node.attr('data-file-path', path);
                            // The file name appears in "hFinderFileName" or "hFinderDirectoryName"
                            node.find('span.hFinder' + (node.isDirectory()? 'Directory' : 'File') + 'Name span').text(newName);

                            if (this.tree)
                            {
                                if (node.isDirectory())
                                {
                                    var directoryId = node.splitId();

                                    branch = $('div#hFinderTreeDirectory-' + directoryId);

                                    if (branch)
                                    {
                                        branch.attr('data-file-path', path);

                                        $('div#hFinderTreeDirectory-' + directoryId +' span').text(newName);

                                        $('div#hFinderTreeDirectory-' + directoryId + ' + ul div.hFinderTreeDirectory').each(
                                            function()
                                            {
                                                // path = $this->getConcatenatedPath($newPath, substr($data['directory'], strlen($this->path)));
                                                var oldPath = $(this).getFilePath();
                                                var newPath = this.getConcatenatedPath(path, oldPath.substr(path.length));

                                                $(this).attr('data-file-path', newPath);
                                            }
                                        );
                                    }
                                }
                            }
                        }
                    }
                    else
                    {
                        this.refresh();
                    }
                },
                finder
            );
        }
        else
        {
            finder.saveFileNameInfoDialogueCallback({path: path}, true);
        }

        return this;
    },

    inlineRename : function()
    {
        // See if the file object is being passed instead of the file name object.
        this.parents('div.hFinderNode').addClass('hFinderRenameActive');

        if (!this.hasClass('hFinderFileName') && !this.hasClass('hFinderDirectoryName'))
        {
            node = this.find('span.hFinderFileName, span.hFinderDirectoryName');
        }

        var span = this.find('span');
        span.addClass('hFinderFileNameOff');

        var fileName = span.text();

        if (!this.find('input').length)
        {
            var input = document.createElement('input');
            input.type = 'text';
            input.size = (fileName.length > 6)? fileName.length - 1 : 6;
            input.value = fileName;

            finder.oldName = fileName;

            this.append(input);

            var input = this.find('input');

            input
                .keydown(
                    function(event)
                    {
                        if (event.keyCode == 13)
                        {
                            event.preventDefault();
                            $(this).completeRename();
                        }
                        else
                        {
                            this.size = (this.value.length > 6)? this.value.length : 6;
                        }
                    }
                )
                .blur(
                    function()
                    {
                        $(this).completeRename();
                        // if (finder.oldName !== this.value && this.value.length)
                        // {
                        //     if ($(this).parents('div.hFinderFile').length)
                        //     {
                        //         $(this).parents('div.hFinderFile').renameFile(this.value);
                        //     }
                        //     else
                        //     {
                        //         $(this).parents('div.hFinderDirectory').renameFile(this.value);
                        //     }
                        // }
                        //
                        // $(this).parents('span').children('span').removeClass('hFinderFileNameOff');
                        //
                        // $(this).parents('div.hFinderNode').removeClass('hFinderRenameActive');
                        //
                        // $(this).remove();
                    }
                );

            input.get(0).select();
            input.get(0).focus();
        }

        return this;
    },

    completeRename : function()
    {
        if (this.val() && this.val().length && finder.oldName !== this.val())
        {
            if (this.parents('div.hFinderFile').length)
            {
                this.parents('div.hFinderFile')
                    .renameFile(this.val());
            }
            else
            {
                this.parents('div.hFinderDirectory')
                    .renameFile(this.val());
            }
        }

        this.parents('span')
            .children('span')
            .removeClass('hFinderFileNameOff');

        this.parents('div.hFinderNode:first')
            .removeClass('hFinderRenameActive');

        var node = this.parents('div.hFinderNode:first');

        this.remove();

        return node;
    },

    deleteFileNode : function()
    {
        var unselect = arguments[0] === undefined ? false : arguments[0];

        if (finder.view == 'Columns')
        {
            var selector =
                'div.hFinderColumns[data-file-path^="' + this.attr('data-file-path') + '/"], ' +
                'div.hFinderColumns[data-file-path="' + this.attr('data-file-path') + '"]';

            $(selector).remove();
        }

        $('div#hFinderTreeDirectory-' + this.splitId())
            .parent('li')
            .remove();

        $('div#hFinderTreeHomeDirectory-' + this.splitId())
            .parent('li')
            .remove();

        if (unselect)
        {
            hot.unselect('hFinder');
        }
        else
        {
            this.selectNextFile();
        }

        this.remove();

        // Safari is losing focus after deleting an item from the DOM.
        // This prevents keyboard shortcuts from working at all.
        // This works around it.
        if ($('input#hFinderSearchTerms').length)
        {
            $('input#hFinderSearchTerms').focus().blur();
        }
        else if ($(document).find('input:first').length)
        {
            $(document).find('input:first').focus().blur();
        }
    },

    selectNextFile : function()
    {
        if (this.next('div.hFinderNode').length)
        {
            return (
                this.next('div.hFinderNode')
                    .select('hFinder')
            );
        }
        else
        {
            return (
                this.siblings('div.hFinderNode:first')
                    .select('hFinder')
            );
        }
    },

    selectPreviousFile : function()
    {
        if (this.prev().length)
        {
            return (
                this.prev('div.hFinderNode')
                    .select('hFinder')
            );
        }
        else
        {
            return (
                this.siblings('div.hFinderNode:last')
                    .select('hFinder')
            );
        }
    },

    selectBelowFile : function()
    {
        var file = this;
        var position = this.position();
        var left = position.left;
        var top = position.top;

        if (this.next().length)
        {
            this.nextAll('div.hFinderNode').each(
                function()
                {
                    var position = $(this).position();

                    if (position.top > top && position.left == left)
                    {
                        file = $(this);
                        return false;
                    }
                }
            );
        }
        else
        {
            file = this.siblings('div.hFinderNode:first');
        }

        return file.select('hFinder');
    },

    selectAboveFile : function()
    {
        var file = this;
        var position = this.position();

        var left = position.left;
        var top = position.top;

        if (this.prev().length)
        {
            this.prevAll('div.hFinderNode').each(
                function()
                {
                    var position = $(this).position();

                    if (position.top < top && position.left == left)
                    {
                        file = $(this);
                        return false;
                    }
                }
            );
        }
        else
        {
            file = this.siblings('div.hFinderNode:last');
        }

        return file.select('hFinder');
    },

    toggleList : function()
    {
        var node = $(this);

        if (node.next('div.hFinderList').length)
        {
            if (node.next('div.hFinderList').hasClass('hFinderListOff'))
            {
                node.find('div.hFinderDirectoryArrow')
                    .addClass('hFinderDirectoryHasChildrenOn');

                node.next('div.hFinderList')
                    .removeClass('hFinderListOff');
            }
            else
            {
                node.find('div.hFinderDirectoryArrow')
                    .removeClass('hFinderDirectoryHasChildrenOn');

                node.next('div.hFinderList')
                    .addClass('hFinderListOff');
            }
        }
        else
        {
            node.find('div.hFinderDirectoryArrow')
                .addClass('hFinderDirectoryHasChildrenOn');

            finder.listAppend = true;
            finder.requestDirectory(node.getFilePath());
        }
    },

    unzip : function()
    {
        if (this.getExtension() == 'zip' || this.getMIMEType() == 'application/zip')
        {
            http.get(
                '/hFile/unzip', {
                    operation : 'Unzip File',
                    path: this.getFilePath()
                },
                function(json)
                {
                    finder.refresh();
                }
            );
        }
    },

    disableByType : function()
    {
        if (finder.types.length)
        {
            this.each(
                function()
                {
                    var node = $(this);

                    var type = node.getMIMEType();
                    var extension = node.getExtension();
                    var isDirectory = node.isDirectory();

                    if ($.inArray('files', finder.types) != -1 && !isDirectory)
                    {
                        return true; // continue;
                    }

                    if ($.inArray('folders', finder.types) != -1 && isDirectory)
                    {
                        return true; // continue;
                    }

                    if (!isDirectory)
                    {
                        if ($.inArray(type, finder.types) == -1 && $.inArray(extension, finder.types) == -1)
                        {
                            node.addClass('hFinderDisabled');
                        }
                    }
                    else
                    {
                        node.addClass('hFinderDisabled');
                    }
                }
            );
        }
    }
});

var finder = {

    types : [],

    location : '',
    view : '',
    sortBy: '',

    dropIsCategory : false,
    dropTargetIsSame : false,
    clickCounter : 0,
    lastClickedItem : null,

    ready : function()
    {
        $('div.hFinderFiles')
            .on(
                'dragover.finderFiles',
                function(event)
                {
                    event.preventDefault();
                    event.stopPropagation();

                    // Dragging will always be false if dragging began in another window.
                    finder.dropTargetIsSame = true;

                    if (!finder.dragging)
                    {
                        $(this).addClass('hFinderFilesDragOver');

                        //event.dataTransfer.dropEffect = (!event.altKey)? 'move' : 'copy';
                        event.originalEvent.dataTransfer.dropEffect = (!finder.altKey)? 'move' : 'copy';
                    }
                }
            )
            .on(
                'dragenter.finderFiles',
                function(event)
                {
                    event.preventDefault();
                    event.stopPropagation();
                }
            )
            .on(
                'dragleave.finderFiles',
                function(event)
                {
                    finder.dropTargetIsSame = false;

                    if (finder.view != 'Columns' && !finder.dragging)
                    {
                        event.preventDefault();
                        $(this).removeClass('hFinderFilesDragOver');
                    }
                }
            )
            .on(
                'drop.finderFiles',
                function(event)
                {
                    $(this).removeClass('hFinderFilesDragOver');

                    if (finder.view != 'Columns' && !finder.dragging && !event.originalEvent.dataTransfer.files.length)
                    {
                        finder.dropTargetIsSame = true;

                        // event.stopPropagation();
                        var html = finder.getDropHTML(event);

                        $(this)
                            .attr('data-file-path', finder.path)
                            .move(
                                html.attr('data-file-path')
                            );
                    }
                    else if (event.originalEvent.dataTransfer && event.originalEvent.dataTransfer.files && event.originalEvent.dataTransfer.files.length)
                    {
                        if (finder.beginsPath('/Categories'))
                        {
                            return;
                        }

                        event.preventDefault();
                        event.stopPropagation();

                        finder.dragDrop.openProgressDialogue(
                            event.originalEvent.dataTransfer.files,
                            finder.path
                        );
                    }
                }
            )
            .on(
                'mousedown.finderFiles',
                function(event)
                {
                    if (!finder.fileActive)
                    {
                        if (event.target.nodeName.toLowerCase() != 'input')
                        {
                            hot.unselect('hFinder');
                        }
                    }
                }
            );

        $(document)
            .on(
                'dblclick.finder',
                'div.hFinderNode',
                function(event)
                {
                    if (finder.dragTimeout)
                    {
                        clearTimeout(finder.dragTimeout);
                    }

                    finder.mousedownNode = null;

                    var target = '';

                    if (event && event.target && $(event.target).length)
                    {
                        target = $(event.target);
                    }

                    var fileNameNodeSelector = 'span.hFinderDirectoryName, span.hFinderFileName';

                    if (target && target.length && (target.parents(fileNameNodeSelector).length || target.is(fileNameNodeSelector)))
                    {
                        target.parents('span.hFinderDirectoryName, span.hFinderFileName').inlineRename();
                    }
                    else
                    {
                        hot.fire(
                            'hFinderNodeDoubleClick', {
                                hFinderNode : $(this)
                            }
                        );

                        hot.fire(
                            'nodeDoubleClick', {
                                node : $(this)
                            }
                        );

                        if (finder.view == 'Columns')
                        {
                            finder.columnNavigate = true;
                        }

                        $(this).getDirectory();
                    }
                }
            )
            .on(
                'click',
                'div.hFinderNode',
                function(event)
                {
                    if (finder.dragTimeout)
                    {
                        clearTimeout(finder.dragTimeout);
                    }

                    finder.mousedownNode = null;

                    hot.fire(
                        'hFinderNodeClick', {
                            hFinderNode : this
                        }
                    );

                    hot.fire(
                        'nodeClick', {
                            node : this
                        }
                    );

                    finder.columnsAppend = false;
                    finder.listAppend = false;

                    switch (finder.view)
                    {
                        case 'Columns':
                        {
                            if ($(this).isDirectory())
                            {
                                finder.columnsAppend = true;

                                if (!$(this).hasClass('hFinderApplication') || finder.shortcuts.shiftKey)
                                {
                                    $(this).getDirectory();
                                }
                                else
                                {
                                    $(this).getColumnFileProperties();
                                }
                            }
                            else
                            {
                                $(this).getColumnFileProperties();
                            }

                            break;
                        };
                    }
                }
            )
            .on(
                'mousedown',
                'div.hFinderNode',
                function(event)
                {
                    event.stopPropagation();
                    finder.mouseIsDown = true;

                    finder.mousedownNode = this;

                    if (hot.userAgent == 'ie' && hot.userAgentVersion < 10)
                    {
                        finder.dragTimeout = setTimeout('finder.delayDrag();', 200);
                    }
                    else
                    {
                        if (typeof this.draggable !== 'undefined')
                        {
                            this.draggable = true;
                        }

                        if (this.style && typeof this.style.WebkitUserDrag !== 'undefined')
                        {
                            this.style.WebkitUserDrag = 'element';
                        }
                    }

                    switch (finder.view)
                    {
                        case 'Columns':
                        case 'List':
                        case 'CoverFlow':
                        case 'Icons':
                        {
                            $(this).select('hFinder');
                            break;
                        }
                    }
                }
            )
            .on(
                'mouseup',
                'div.hFinderNode',
                function(event)
                {
                    event.stopPropagation();
                    finder.mouseIsDown = false;

                    if (finder.dragTimeout)
                    {
                        clearTimeout(finder.dragTimeout);
                    }

                    finder.mousedownNode = this;

                    if (typeof this.draggable !== 'undefined')
                    {
                        this.draggable = false;
                    }

                    if (this.style && typeof this.style.WebkitUserDrag !== 'undefined')
                    {
                        this.style.WebkitUserDrag = 'auto';
                    }
                }
            )
            .on(
                'mousedown',
                'div.hFinderNode div.hFinderIcon, ' +
                'div.hFinderNode span.hFinderDirectoryName, ' +
                'div.hFinderNode span.hFinderFileName, ' +
                'div.hFinderNode div.hFinderThumbnail',
                function()
                {
                    switch (finder.view)
                    {
                        case 'Icons':
                        {
                            $(this).parents('div.hFinderNode').select('hFinder');
                            break;
                        }
                    }
                }
            )
            .on(
                'mouseenter',
                'div.hFinderNode div.hFinderIcon, ' +
                'div.hFinderNode span.hFinderDirectoryName, ' +
                'div.hFinderNode span.hFinderFileName, ' +
                'div.hFinderNode div.hFinderThumbnail',
                function()
                {
                    finder.fileActive = true;
                }
            )
            .on(
                'mouseleave',
                'div.hFinderNode div.hFinderIcon, ' +
                'div.hFinderNode span.hFinderDirectoryName, ' +
                'div.hFinderNode span.hFinderFileName, ' +
                'div.hFinderNode div.hFinderThumbnail',
                function()
                {
                    finder.fileActive = false;
                }
            );

        hot.event(
            'hFinderSelected',
            function(node)
            {
                if (finder.lastClickedItem == $(this).attr('id'))
                {
                    finder.clickCounter = 2;
                }
                else
                {
                    finder.clickCounter = 1;
                }

                finder.lastClickedItem = $(this).attr('id');
            }
        );

        hot.event(
            'hFinderUnselected',
            function(node)
            {
                finder.clickCounter = 0;
            }
        );

        this.addEvent({

            dragstart : function(event)
            {
                event.stopPropagation();
                event.originalEvent.dataTransfer.effectAllowed = 'copyMove';

                finder.dropIsCategory = false;

                if (hot.userAgent == 'ie' && hot.userAgentVersion <= 10)
                {
                    // In IE, the click and the dblclick events are not called if dragDrop()
                    // has bee activated, and dragStart is called every time a click occurs,
                    // whether or not the user is actually starting a drag.
                    // $(this).trigger('click');
                    //
                    // if (finder.clickCounter == 2)
                    // {
                    //     $(this).trigger('dblclick');
                    //     finder.clickCounter = 0;
                    // }
                }

                //finder.delayDrag(event);

                if ($(this).hasClass('hFinderRenameActive') || finder.beginsPath('/Categories'))
                {
                    event.preventDefault();
                    return;
                }

                finder.dragging = true;

                var html = $(this).outerHTML();

                // Data is passed this way for two reasons
                //     1. IE only supports a precious few types of data, one of them being text.
                //     2. The relevant event data needs to be passed this way in order to
                //            facilitate drag and drop between multiple instances of the browser.

                //event.originalEvent.dataTransfer.setData('url', url);
                event.originalEvent.dataTransfer.setData(hot.userAgent == 'ie'? 'Text' : 'text/html', html);

                if (!$(this).isDirectory())
                {
                    var url = 'http://' + server.host + $(this).getFilePath();

                    // This check seems to be needed for IE and Firefox.
                    // Works fine without this in Safari.  Firefox complains about
                    // an invalid URL without it.
                    if (Clipboard !== undefined && event.originalEvent.dataTransfer.constructor == Clipboard && event.originalEvent.dataTransfer.setData('DownloadURL', 'http://' + server.host))
                    {
                        var mime = $(this).getMIMEType();
                        var fileName = $(this).find('span.hFinderFileName span').text();

                        event.originalEvent.dataTransfer.setData('DownloadURL', mime + ':' + fileName + ':' + url);
                    }
                }

                if (hot.userAgent != 'ie')
                {
                    event.originalEvent.dataTransfer.setData('text/plain', html);
                }
            },

            dragenter : function(event)
            {
                event.preventDefault();
                event.stopPropagation();
            },

            dragend : function(event)
            {
                // Ping the server to see if the file was moved from
                // its original location.  If it was, then remove it from
                // this window.  Yes it's a dirty, dirty hack.
                finder.dragFile = $(this);
                setTimeout('finder.checkFileMove();', 1000);

                finder.dragging = false;
            },

            dragover : function(event)
            {
                finder.dropTargetIsSame = false;

                if ($(this).isDirectory() && !$(this).hasClass('hFinderApplication'))
                {
                    event.preventDefault();
                    event.stopPropagation();

                    $(this).addClass('hFinderDragOver');

                    //e.dataTransfer.dropEffect = (!e.altKey)? 'move' : 'copy';
                    event.originalEvent.dataTransfer.dropEffect = (!finder.altKey)? 'move' : 'copy';
                }
            },

            dragleave : function(event)
            {
                finder.dropTargetIsSame = true;

                if ($(this).isDirectory() && !$(this).hasClass('hFinderApplication'))
                {
                    event.preventDefault();
                    $(this).removeClass('hFinderDragOver')
                }
            },

            drop : function(event)
            {
                $(this).removeClass('hFinderDragOver');

                if (finder.beginsPath($(this).getFilePath(), '/Categories'))
                {
                    finder.dropIsCategory = true;
                }

                if ($(this).isDirectory() && !$(this).hasClass('hFinderApplication'))
                {
                    event.preventDefault();
                    event.stopPropagation();

                    if (event.originalEvent.dataTransfer && event.originalEvent.dataTransfer.files && event.originalEvent.dataTransfer.files.length)
                    {
                        finder.dragDrop.openProgressDialogue(
                            event.originalEvent.dataTransfer.files,
                            $(this).getFilePath()
                        );

                        return;
                    }

                    var html = finder.getDropHTML(event);

                    if ($(this).getFilePath() == html.data('file-path'))
                    {
                        finder.dropTargetIsSame = true;
                        return;
                    }

                    $(this).move(html.data('file-path'));
                }
            }
        });

        if ($('li#hFinder-View' + this.view).length)
        {
            $('li#hFinder-View' + this.view)
                .addClass('hFinderView' + this.view + 'On');
        }

        $(document)
            .on(
                'mousedown',
                'div.hFinderColumnGrip',
                function(event)
                {
                    var node = $(this).parents('div.hFinderColumns:first');

                    finder.resizeColumnNode = node;

                    finder.resizeColumnNodeDimensions = {
                        width : node.width(),
                        height : node.height()
                    };

                    finder.resizeColumnCoordinates = {
                        x : event.pageX,
                        y : event.pageY
                    };
                }
            );

        $(document)
            .mousemove(
                function(event)
                {
                    if (finder.resizeColumnNode && finder.resizeColumnNode.length)
                    {
                        finder.resizeColumn(event);
                    }
                }
            )
            .mouseup(
                function(event)
                {
                    if (finder.resizeColumnNode && finder.resizeColumnNode.length)
                    {
                        finder.resizeColumnNode = {};
                        finder.resizeColumnCoordinates = {};
                        finder.resizeColumnNodeDimensions = null;
                    }
                }
            );
    },

    resizeColumnNode : {},
    resizeColumnCoordinates : {},
    resizeColumnNodeDimensions : {},

    resizeColumn : function(event)
    {
        var x = (this.resizeColumnCoordinates.x - event.pageX);
        var y = (this.resizeColumnCoordinates.y - event.pageY);

        var width = this.resizeColumnNodeDimensions.width;

        var resizedWidth = width - x;

        if (resizedWidth < 100)
        {
            resizedWidth = 100;
        }
        else if (resizedWidth > 500)
        {
            resizedWidth = 500;
        }

        this.resizeColumnNode.width(resizedWidth + 'px');

        this.resizeColumnNode
            .find('span.hFinderFileName, span.hFinderDirectoryName')
                .css('max-width', (resizedWidth - 64) + 'px');

        this.setColumnWrapperWidth();
    },

    getDropHTML : function(event)
    {
        var html = event.originalEvent.dataTransfer.getData(
            (hot.userAgent == 'ie')? 'Text' : 'text/html'
        );

        // Chrome inserts a <meta> element with the content-type and charset.
        // Thanks, but I don't need that.
        return $(html.replace(/\<(meta).*?\>/gmi, ''));
    },

    checkFileMove : function()
    {
        http.get(
            '/hFile/exists', {
                path : this.dragFile.getFilePath()
            },
            function(json)
            {
                if (parseInt(json) == -404 || !json)
                {
                    finder.dragFile.remove();
                    finder.dragFile = null;
                }
            }
        );
    },

    events : [],

    addEvent : function(event)
    {
        if (typeof event == 'string')
        {
            // Have to remember the events so they can be reattached when
            // the user navigates to another directory, modifies the view,
            // or does something else to refresh/change/modify the directory
            // window's contents
            var fn = arguments[1];

            this.events.push({
                event : event,
                fn : fn
            });

            $('div.hFinderNode').off(event, fn);
            $('div.hFinderNode').on(event, fn);
        }
        else if (typeof event == 'object')
        {
            for (var property in event)
            {
                if (typeof property == 'string')
                {
                    this.addEvent(property, event[property]);
                }
            }
        }
    },

    eventCallback : [],

    addEventCallback : function(fn)
    {
        this.eventCallback.push(fn);
    },

    dragElement : null,

    fileActive : false,

    columnsAppend : false,
    listAppend : false,

    columnNavigate : false,

    requestedColumnFile : null,

    fileEvents : function()
    {
        var nodes = arguments[0] ? $(arguments[0] + ' div.hFinderNode') : $('div.hFinderNode');

        if (nodes.length)
        {
            $(nodes).each(
                function()
                {
                    var node = $(this);

                    $(finder.eventCallback).each(
                        function()
                        {
                            this.call(finder, node);
                        }
                    );
                }
            );
        }

        $(finder.events).each(
            function()
            {
                nodes.off(this.event, this.fn);
                nodes.on(this.event, this.fn);
            }
        );
    },

    delayDrag : function()
    {
        if (this.dragTimeout)
        {
            clearTimeout(this.dragTimeout);
        }

        if (this.mouseIsDown)
        {
            if (typeof this.mousedownNode.draggable !== 'undefined')
            {
                this.mousedownNode.draggable = true;
            }

            if (this.mousedownNode.style && typeof this.mousedownNode.style.WebkitUserDrag !== 'undefined')
            {
                this.mousedownNode.style.WebkitUserDrag = 'element';
            }

            if (typeof this.mousedownNode.dragDrop !== 'undefined')
            {
                this.mousedownNode.dragDrop();
            }
        }

        this.mousedownNode = null;
    },

    getConcatenatedPath : function(path, name)
    {
        return path + (name.substring(0, 1) != '/' && path.substring(path.length - 1, 1) != '/' ? '/' : '') + name;
    },

    beginsPath : function(path)
    {
        if (arguments.length == 1)
        {
            var beginning = path;
            var path = decodeURIComponent(this.path);
        }
        else if (arguments.length == 2)
        {
            var beginning = arguments[1];
        }

        return path.substring(0, beginning.length + 1) == beginning + '/' || path == beginning;
    },

    getConfigurationArguments : function()
    {
        var args = arguments[0]? arguments[0] : {};

        if (get.hFinderConf)
        {
            args.hFinderConf = get.hFinderConf;
        }

        if (arguments[1])
        {
            args.path = arguments[1];
        }
        else if (get.path)
        {
            args.path = decodeURIComponent(get.path);
        }

        if (arguments[2])
        {
            args.hFinderDiskName = arguments[2];
        }
        else if (get.hFinderDiskName)
        {
            args.hFinderDiskName = get.hFinderDiskName;
        }

        if (get.setDefaultPath)
        {
            args.setDefaultPath = get.setDefaultPath;
        }

        return args;
    },

    refreshColumn : false,
    refreshView : false,

    refresh : function()
    {
        this.refreshView = true;
        this.requestDirectory();
    },

    requestedNode : null,
    requestedPath : null,
    requestedView : null,

    requestDirectory : function()
    {
        hot.fire(
            'beforeRequestDirectory', {
                path : arguments[0] ? arguments[0] : this.path,
                view : arguments[1] ? arguments[1] : this.view,
                sortBy : arguments[2] ? arguments[1] : this.sortBy
            }
        );

        this.requestedPath = arguments[0] ? arguments[0] : this.path;
        this.requestedView = arguments[1] ? arguments[1] : this.view;
        this.requestedSortBy = arguments[2] ? arguments[1] : this.sortBy;

        this.requestedNode = hot.selected('hFinder');

        if (this.requestedPath !== undefined)
        {
            http.get(
                '/hFinder/getDirectory', {
                    operation : 'Get Directory',
                    view : this.requestedView,
                    path : this.requestedPath,
                    sortBy : this.requestedSortBy ? this.requestedSortBy : 'name'
                },
                this.onRequestDirectoryCallback,
                this
            );
        }
        else if (console && console.log)
        {
            console.log("Unable to execute 'finder.requestDirectory' the path is undefined.");
        }
    },

    onRequestDirectoryCallback : function(data)
    {
        if (data && data.find)
        {
            switch (parseInt(data.find('response').text()))
            {
                case -401:
                {
                    dialogue.alert({
                        title : 'Error',
                        label : "<p>The folder \"" + this.requestedPath + "\" could not " +
                                "be opened because you do not have sufficient access " +
                                "privileges.</p>"
                    });
                    return;
                };
            }
        }

        var basePath = this.path;
        var requestedPath = this.requestedPath;

        if (this.view == 'Columns' && this.columnsAppend)
        {
            var lastPath = $('div.hFinderColumns:last-child').attr('data-file-path');

            var folders = requestedPath.split('/');
            folders.pop();

            var baseRequestedPath = folders.join('/');

            if (baseRequestedPath.length && baseRequestedPath != '/')
            {
                $('div.hFinderColumns[data-file-path^="' + baseRequestedPath + '/"]').remove();
            }
            else
            {
                $('div.hFinderColumns').each(
                    function()
                    {
                        if ($(this).attr('data-file-path') != '/')
                        {
                            $(this).remove();
                        }
                    }
                );
            }

            $('div.hFinderColumnsFileProperties').parents('.hFinderColumns').remove();

            $('div#hFinderColumnsWrapper').append(data);

            this.columnEvents(requestedPath);
            this.fileEvents('div.hFinderColumns[data-file-path="' + requestedPath + '"]');

            this.columnsAppend = false;

            this.onAppendColumn();
        }
        else if (this.view == 'List' && this.listAppend)
        {
            $('div.hFinderNode[data-file-path="' + requestedPath + '"]').after(data);

            this.fileEvents('div.hFinderList[data-file-path="' + requestedPath + '"]');
            this.listEvents(requestedPath);

            // Correct Positioning...
            var $i = ($('div.hFinderList[data-file-path="' + requestedPath + '"]')
                        .parents('div.hFinderList').length * 17);

            $('div.hFinderList[data-file-path="' + requestedPath + '"]')
                .find('span.hFinderDirectoryName')
                .css({
                    left : (45 + $i) + 'px'
                });

            $('div.hFinderList[data-file-path="' + requestedPath + '"]')
                .find('span.hFinderFileName')
                .css({
                    left : (45 + $i) + 'px'
                });

            $('div.hFinderList[data-file-path="' + requestedPath + '"]')
                .find('div.hFinderIcon')
                .css({
                    left : (25 + $i) + 'px'
                });

            $('div.hFinderList[data-file-path="' + requestedPath + '"]')
                .find('div.hFinderDirectoryArrow')
                .css({
                    left : (5 + $i) + 'px'
                });

            this.listAppend = false;
        }
        else
        {
            hot.unselect('hFinder');

            switch (this.view)
            {
                case 'Columns':
                {
                    if (!this.columnNavigate && $('div.hFinderColumns[data-file-path="' + requestedPath + '"]').length)
                    {
                        $('div.hFinderColumns[data-file-path="' + requestedPath + '"]').replaceWith(data);

                        this.columnEvents(requestedPath);
                        this.fileEvents('div.hFinderColumns[data-file-path="' + requestedPath + '"]');

                        if ($('div.hFinderColumns[data-file-path="' + requestedPath + '"]').next().length && !$('div.hFinderColumns[data-file-path="' + requestedPath + '"]').next().find('div.hFinderColumnsFileProperties').length)
                        {
                            this.requestDirectory(
                                $('div.hFinderColumns[data-file-path="' + requestedPath + '"]')
                                .next()
                                .attr('data-file-path')
                            );
                        }

                        $('div.hFinderColumns').each(
                            function()
                            {
                                var path = $(this).attr('data-file-path');

                                $('div.hFinderDirectory[data-file-path="' + path + '"]')
                                    .addClass('hFinderDirectoryMarker');
                            }
                        );
                    }
                    else
                    {
                        $('div.hFinderFiles').html(
                            $('<div/>')
                                .attr('id', 'hFinderFilesInner')
                                .html(
                                    $('<div/>')
                                        .attr('id', 'hFinderColumnsWrapper')
                                        .html(data)
                                )
                        );

                        this.path = requestedPath;
                        this.columnEvents(requestedPath);
                        this.fileEvents();
                        this.columnNavigate = false;
                    }

                    break;
                }
                default:
                {
                    if (this.view == 'List')
                    {
                        data = (
                            $('<div/>')
                                .attr('id', 'hFinderListFilesInner')
                                .data('file-path', requestedPath)
                                .html(data)
                        );
                    }

                    $('div.hFinderFiles').html(data);

                    $('div.hFinderList').css({
                        height : '100%'
                    });

                    this.path = requestedPath;
                    this.fileEvents();

                    if (this.view == 'List')
                    {
                        this.listEvents($('div#hFinderListFilesInner'));
                        this.listEvents();
                    }
                }
            }
        }

        if (this.refreshView && this.tree)
        {
            this.tree.refreshBranchByDirectoryPath(decodeURIComponent(this.path));
            this.refreshView = false;
        }

        hot.fire(
            'requestDirectory', {
                nodes : $('div.hFinderNode')
            }
        );
    },

    fileExists : function(file)
    {
        // The 2nd argument provides the element to start from so that a check of the proposed file name
        // against the right folder's file names can be done.
        var node = null;
        var path = null;

        if (arguments[1])
        {
            if (arguments[1].indexOf && (arguments[1].indexOf('/') == -1 || arguments[1].indexOf('%2F') == -1))
            {
                path = decodeURIComponent(arguments[1]);
            }
            else
            {
                node = $(arguments[1]);
            }
        }

        switch (this.view)
        {
            case 'List':
            {
                if (!node || node && node.length && (node.attr('id') == 'hFinderListFilesInner' || node.hasClass('hFinderFiles')))
                {
                    node = $('div#hFinderListFilesInner > div.hFinderList > div.hFinderList');
                    path = this.path;
                }

                break;
            };
            case 'Columns':
            {
                if (!node || node && node.length && !node.hasClass('hFinderColumn') && !node.hasClass('hFinderColumnsInner'))
                {
                    var node = $('div#hFinderColumnsWrapper > div.hFinderColumns:last-child');

                    if (node.find('div.hFinderColumnsFileProperties').length)
                    {
                        node = node.prev();
                    }

                    path = node.getFilePath();
                    node = node.children('div.hFinderColumnsInner');
                }

                break;
            };
            case 'Icons':
            {
                node = $('div#hFinderFilesInner');
                path = finder.path;
                break;
            };
        }

        var exists = false;

        if (node && node.length)
        {
            node.children('div.hFinderNode').each(
                function()
                {
                    // HtFS is case insensitive
                    if ($(this).find('span.hFinderFileName span, span.hFinderDirectoryName span').text() == file)
                    {
                        exists = true;
                    }
                }
            );

            return {
                exists : exists,
                path : path
            };
        }
        else
        {
            var response = parseInt(
                http.get({
                        url : '/hFile/exists',
                        synchronous : true,
                        operation : 'File Exists'
                    }, {
                        path : finder.getConcatenatedPath(path, file)
                    }
                )
            );

            return {
                exists : response != -404,
                path : path
            };
        }
    },

    onAppendColumn : function()
    {
        this.setColumnWrapperWidth();

        $('div.hFinderColumns').each(
            function()
            {
                var path = $(this).attr('data-file-path');

                $('div.hFinderDirectory[data-file-path="' + path + '"]')
                    .addClass('hFinderDirectoryMarker');
            }
        );

        // Remove markers that are no longer valid
        $('div.hFinderDirectoryMarker').each(
            function()
            {
                if (!$('div.hFinderColumns[data-file-path="' + $(this).attr('data-file-path') + '"]').length)
                {
                    $(this).removeClass('hFinderDirectoryMarker');
                }
            }
        );
    },

    setColumnWrapperWidth : function()
    {
        var columnWidth = 0;

        $('div.hFinderColumns').each(
            function()
            {
                columnWidth += $(this).outerWidth();
            }
        );

        $('div#hFinderColumnsWrapper')
            .css('width', columnWidth + 'px');
    },

    listEvents : function()
    {
        var node = null;

        if (arguments[0])
        {
            switch (typeof arguments[0])
            {
                case 'object':
                case 'array':
                {
                    node = arguments[0];
                    break;
                };
                case 'string':
                default:
                {
                    node = $('div.hFinderList[data-file-path="' + arguments[0] + '"]');
                };
            }
        }
        else
        {
            node = $('div.hFinderList');
        }

        node
            .on(
                'dragover',
                function(event)
                {
                    // Dragging will always be false if dragging began in another window.
                    event.preventDefault();
                    event.stopPropagation();

                    $(this).addClass('hFinderListDragOver');

                    //e.dataTransfer.dropEffect = (!e.altKey)? 'move' : 'copy';
                    event.originalEvent.dataTransfer.dropEffect = (!finder.altKey)? 'move' : 'copy';
                }
            )
            .on(
                'dragenter',
                function(event)
                {
                    event.preventDefault();
                    event.stopPropagation();
                }
            )
            .on(
                'dragleave',
                function(event)
                {
                    event.preventDefault();
                    event.stopPropagation();
                    $(this).removeClass('hFinderListDragOver');
                }
            )
            .on(
                'drop',
                function(event)
                {
                    event.stopPropagation();
                    event.preventDefault();

                    if (event.originalEvent.dataTransfer && event.originalEvent.dataTransfer.files && event.originalEvent.dataTransfer.files.length)
                    {
                        $(this).removeClass('hFinderListDragOver');

                        if (finder.beginsPath($(this).getFilePath(), '/Categories'))
                        {
                            return;
                        }

                        finder.dragDrop.openProgressDialogue(
                            event.originalEvent.dataTransfer.files,
                            $(this).getFilePath()
                        );

                        return;
                    }

                    var html = finder.getDropHTML(event);

                    $(this)
                        .removeClass('hFinderListDragOver')
                        .move(
                            html.attr('data-file-path')
                        );
                }
            );

        if (node.attr('id') != 'hFinderListFilesInner')
        {
            node.find('div.hFinderDirectoryArrow').click(
                function(event)
                {
                    event.stopPropagation();

                    $(this)
                        .parents('div.hFinderNode')
                        .toggleList();
                }
            );
        }
    },

    columnEvents : function(path)
    {
        $('div.hFinderColumns[data-file-path="' + path + '"]')
            .on(
                'dragover',
                function(event)
                {
                    // Dragging will always be false if dragging began in another window.
                    event.preventDefault();
                    event.stopPropagation();

                    $(this).addClass('hFinderColumnsDragOver');

                    //e.dataTransfer.dropEffect = (!e.altKey)? 'move' : 'copy';
                    event.originalEvent.dataTransfer.dropEffect = (!finder.altKey)? 'move' : 'copy';
                }
            )
            .on(
                'dragenter',
                function(event)
                {
                    event.preventDefault();
                    event.stopPropagation();
                }
            )
            .on(
                'dragleave',
                function(event)
                {
                    event.preventDefault();
                    event.stopPropagation();
                    $(this).removeClass('hFinderColumnsDragOver');
                }
            )
            .on(
                'drop',
                function(event)
                {
                    event.preventDefault();
                    event.stopPropagation();

                    if (event.originalEvent.dataTransfer && event.originalEvent.dataTransfer.files && event.originalEvent.dataTransfer.files.length)
                    {
                        $(this).removeClass('hFinderColumnsDragOver');

                        if (finder.beginsPath($(this).getFilePath(), '/Categories'))
                        {
                            return;
                        }

                        finder.dragDrop.openProgressDialogue(
                            event.originalEvent.dataTransfer.files,
                            $(this).getFilePath()
                        );

                        return;
                    }

                    var html = finder.getDropHTML(event);

                    $(this).removeClass('hFinderColumnsDragOver')
                        .move(html.attr('data-file-path'));
                }
            );
    },

    views : [
        'Tiles',
        'Icons',
        'Table',
        'XDetails',
        'Details',
        'List',
        'Columns',
        'CoverFlow'
    ],

    switchView : function(obj)
    {
        $(this.views).each(
            function()
            {
                if (obj.id.indexOf(this) != -1)
                {
                    finder.setView(this);
                    return false;
                }
            }
        );
    },

    setView : function(view)
    {
        if (view == 'CoverFlow')
        {
            this.coverFlow = true;
            view = 'List';
        }
        else
        {
            this.coverFlow = false;
        }

        $('body').removeClass('hFinderFiles' + this.view);
        $('body').addClass('hFinderFiles' + view);

        $('div.hFinderFiles')
            .attr('id', 'hFinderFiles' + view);

        this.view = $.trim(view);

        this.updateIcons();

        $('li.hFinderToolbarView').each(
            function()
            {
                $(this).removeClass('hFinder' + $(this).splitId() + 'On');
            }
        );

        $('li#hFinder-View' + (this.coverFlow? 'CoverFlow' : view ))
            .addClass('hFinderView' + (this.coverFlow? 'CoverFlow' : view ) + 'On');

        if (this.view == 'List')
        {
            $('div#hFinderListHeader').show();
            $('div.hFinderFiles').addClass('hFinderFilesListHeader');
        }
        else
        {
            $('div#hFinderListHeader').hide();
            $('div.hFinderFiles').removeClass('hFinderFilesListHeader');
        }

        if (!arguments[1])
        {
            this.refresh(); // Quick and dirty hack.
        }
    },

    getViewResolution : function()
    {
        switch (this.view)
        {
            case 'XDetails':
            case 'Details':
            {
                return '48x48';
            };
            case 'Tiles':
            {
                return '32x32';
            };
            case 'Table':
            case 'List':
            case 'Flat':
            case 'Columns':
            case 'CoverFlow':
            {
                return '16x16';
            };
            case 'Icons':
            {
                return '48x48';
            };
        }

        return '48x48';
    },

    updateIcons : function()
    {
        var resolution = this.getViewResolution();

        $('div.hFinderIcon').each(
            function()
            {
                var bits = this.className.split('-');
                bits.pop();
                bits.push(resolution);
                this.className = bits.join('-');
            }
        );
    },

    setDefaultView : function()
    {
        http.get(
            '/hFinder/setDefaultView', {
                operation : 'Set Default View',
                view : this.view
            },
            function()
            {
                $('#hFinderOperationsMenu').hide();
            }
        );
    },

    setSortBy : function(sortBy)
    {
        this.sortBy = sortBy;
        this.refresh();
    },

    hasTabooCharactersInName : function(name)
    {
        if (name.indexOf('/') != -1 || name.indexOf('\\') != -1 || name.indexOf('?') != -1)
        {
            dialogue.alert({
                title : 'Error',
                label : 'File and Folder names may not contain forward slashes, back slashes or question marks.'
            });
            return true;
        }

        return false;
    },

    promptForNewFile : function(context, promptText, defaultText)
    {
        var file = null;
        var error = false;
        var replace = false;

        if (null !== (name = prompt(promptText, defaultText)))
        {
            if (name !== undefined && name.length && !this.hasTabooCharactersInName(name))
            {
                var file = this.fileExists(name, context);

                if (file.exists)
                {
                    if (this.confirmReplace(name))
                    {
                        replace = true;
                    }
                    else
                    {
                        error = true;
                    }
                }
            }
            else
            {
                error = true;
            }
        }
        else
        {
            error = true;
        }

        return {
            error : error,
            name : name,
            replace : replace,
            path : file? file.path : null
        };
    },

    newDirectory : function(context)
    {
        var file = this.promptForNewFile(context, 'Create a new folder:', 'New Folder');

        if (!file.error)
        {
            http.get(
                '/hFile/newDirectory', {
                    operation : 'New Folder',
                    path: file.path,
                    directory: file.name,
                    replace: file.replace? 1 : 0
                },
                function(json)
                {
                    this.refresh();
                },
                this
            );
        }
    },

    getLabel : function()
    {
        http.get(
            '/hFile/getLabel', {
                //operation : 'Get Label',
                path : hot.selected('hFinder').getFilePath()
            },
            function(json)
            {
                this.label.select(json);
            },
            this
        );
    },

    newFile : function(context)
    {
        var file = this.promptForNewFile(context, "Create a new file:", "New File");

        if (!file.error)
        {
            http.get(
                '/hFile/touch', {
                    operation : 'New File',
                    path: file.path,
                    file: file.name,
                    replace: file.replace? 1 : 0
                },
                function(json)
                {
                    this.refresh();
                },
                this
            );
        }
    },

    info : [],
    infoWindow : [],

    setInfo : function(obj)
    {
        if (!this.info[obj.path] || arguments[1])
        {
            this.info[obj.path] = obj;
        }
        else
        {
            if (obj.fileName)
            {
                this.info[obj.path].fileName = obj.fileName;
            }

            if (obj.comments)
            {
                this.info[obj.path].comments = obj.comments;
            }
        }
    },

    saveInfo : function(obj)
    {
        if (arguments[1])
        {
            this.infoWindow[obj.path] = null;
        }

        this.setInfo(obj);

        if (this.info[obj.path].onloadFileName != this.info[obj.path].fileName)
        {
            // Rename the file.
            // First, see if the file is in the file view, and rename using the normal API,
            // if not, rename the file anyway.
            var node = $("div.hFinderNode[data-file-path='" + obj.path + "']");

            if (node.length)
            {
                node.renameFile(this.info[obj.path].fileName);
            }
            else
            {
                $.fn.renameFile(this.info[obj.path].fileName, obj.path);
            }
        }

        if (this.info[obj.path].onloadComments != this.info[obj.path].comments)
        {

        }
    },

    saveFileNameInfoDialogueCallback : function(obj)
    {
        if (obj && obj.path && this.info[obj.path])
        {
            if (arguments[1])
            {
                this.infoWindow[obj.path].finder.info.updateFile(this.info[obj.path]);
                return;
            }

            this.info[obj.updatedPath] = {
                path : obj.updatedPath,
                onloadFileName : obj.fileName,
                onloadComments : this.info[obj.path].onloadComments
            };

            this.infoWindow[obj.path].finder.info.updateFile(this.info[obj.updatedPath]);

            this.infoWindow[obj.updatedPath] = finder.infoWindow[obj.path];
            this.infoWindow[obj.path] = null;

            this.info[obj.path] = {};
        }
    },

    saveComments : function(path, comments)
    {
        http.get(
            '/hFile/saveComments', {
                operation : 'Save Comments',
                path : path,
                comments : comments
            },
            function(json)
            {

            }
        );
    },

    getExtension : function(file)
    {
        if (file && file.length)
        {
            return file.split('.').pop();
        }
        else
        {
            return '';
        }
    },

    confirmReplace : function(name)
    {
        return confirm(
            "A file or folder with the name " + name + " already exists.\n\n" +
            "Would you like to PERMANENTLY replace it?"
        );
    },

    hasErrors : function(json, operation)
    {
        return http.responseHasErrors(json, operation);
    },

    decode : function(str)
    {
        var result = '';

        for (var i = 0; i < str.length; i++)
        {
            if (str.charAt(i) == '+')
            {
                result += " ";
            }
            else
            {
                result += str.charAt(i);
            }
        }

        return unescape(result);
    }
};

if (get.dialogue)
{
    finder.dialogue = {};
}

$(document)
    .on(
        'selectstart',
        function(event)
        {
            event.preventDefault();
        }
    )
    .mousedown(
        function(event)
        {
            finder.mouseIsDown = true;

            if (event && event.target)
            {
                var target = $(event.target);

                if (target.length && (!target.parents('div.hFinderNode').length || !target.is('div.hFinderNode')))
                {
                    finder.clickCounter = 0;
                }
            }
        }
    )
    .mouseup(
        function()
        {
            finder.mouseIsDown = false;
        }
    )
    .on(
        'touchmove',
         function(event)
         {
             event.preventDefault();
         }
    )
    .ready(
        function()
        {
            if ($('div.hFinderFiles'))
            {
                finder.ready();
            }
        }
    );
