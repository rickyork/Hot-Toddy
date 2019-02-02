$.fn.extend({

    openBranch : function()
    {
        var hasChildren = this.next().children('ul').length;

        var branchDoesntExistOrIsntOpenOrOverride = (
            !hasChildren ||
            (hasChildren && !this.next().children('ul').hasClass('hFinderTreeDirectoryBranchOn')) ||
            typeof arguments[0] !== 'undefined'
        );

        if (branchDoesntExistOrIsntOpenOrOverride)
        {
            this.next()
                .loadBranch();
        }
        else
        {
            this.next()
                .find('ul')
                .closeBranch();
        }

        return this;
    },

    loadBranch : function()
    {
        this.load(
            hot.path(
                '/hFinder/getBranch', {
                    path : this.prevAll('div.hFinderTreeDirectory').attr('data-file-path'),
                    displayFiles : (finder.tree.displayFiles? '1' : '0')
            }),
            function()
            {
                var file = $(this);

                if (file.prevAll('span.hFinderTreeHasChildren').length)
                {
                    if (file.prevAll('div.hFinderTreeDirectory, div.hFinderTreeFile').hasClass('hFinderTreeSelected'))
                    {
                        file.prevAll('span.hFinderTreeHasChildren')
                            .addClass('hFinderTreeBranchIsOpen')
                            .addClass('hFinderTreeBranchIsSelected')
                            .children('img')
                                .sourceFile('downArrowBlack.png');
                    }
                    else
                    {
                        file.prevAll('span.hFinderTreeHasChildren')
                            .addClass('hFinderTreeBranchIsOpen')
                            .removeClass('hFinderTreeBranchIsSelected')
                            .children('img')
                                .sourceFile('downArrow.png');
                    }
                }

                var ancestorCount = 0;
                var parent = this.parentNode;

                // Get parent div count
                while (parent.className != 'hFinderTree')
                {
                    if (parent.nodeName.toLowerCase() == 'li')
                    {
                        ancestorCount++;
                    }

                    parent = parent.parentNode;
                }

                ancestorCount--;

                file.find('div.hFinderTreeDirectory, div.hFinderTreeFile')
                    .css('padding-left', (55 + (15 * ancestorCount)) + 'px');

                file.find('span.hFinderTreeHasChildren')
                    .css('left', (15 + (15 * ancestorCount)) + 'px');

                file.find('div.hFinderTreeIcon')
                    .css('left', (30 + (15 * ancestorCount)) + 'px');

                finder.tree.events(file);
            }
        );

        return this;
    },

    closeBranch : function()
    {
        this.removeClass('hFinderTreeDirectoryBranchOn');

        var isSelected = (
            this.parents('.hFinderTreeDirectoryBranch:first')
                .find('div.hFinderTreeDirectory:first, div.hFinderTreeFile:first')
                .hasClass('hFinderTreeSelected')
        );

        var span = this.parents('.hFinderTreeDirectoryBranch:first')
                       .find('span.hFinderTreeHasChildren:first');

        if (isSelected)
        {
            span.addClass('hFinderTreeBranchIsSelected')
                .removeClass('hFinderTreeBranchIsOpen');
        }
        else
        {
            span.removeClass('hFinderTreeBranchIsSelected')
                .removeClass('hFinderTreeBranchIsOpen');
        }

        span.find('img')
            .sourceFile('rightArrow' + (isSelected? 'Black' : '') + '.png');

        return this;
    }
});

if (typeof finder == 'undefined')
{
    var finder = {};
}

finder.tree = {
    addedEvents : [],
    displayFiles : false,

    ready : function()
    {
        hot.event(
            'hFinderTreeSelected',
            function()
            {
                if (this.nextAll('div.hFinderTreeBranchWrapper').length)
                {
                    var isOpen = (
                        this.nextAll('div.hFinderTreeBranchWrapper')
                            .find('ul:first')
                            .hasClass('hFinderTreeDirectoryBranchOn')
                    );

                    if (this.nextAll('span.hFinderTreeHasChildren:first').length)
                    {
                        var span = this.nextAll('span.hFinderTreeHasChildren:first');

                        if (isOpen)
                        {
                            span.addClass('hFinderTreeBranchIsSelected')
                                .addClass('hFinderTreeBranchIsOpen');
                        }
                        else
                        {
                            span.addClass('hFinderTreeBranchIsSelected')
                                .removeClass('hFinderTreeBranchIsOpen');
                        }

                        span.find('img')
                            .sourceFile((isOpen? 'down' : 'right') + 'ArrowBlack.png');
                    }
                }
            }
        );

        hot.event(
            'hFinderTreeUnselected',
            function()
            {
                if (this.nextAll('div.hFinderTreeBranchWrapper').length)
                {
                    var isOpen = (
                        this.nextAll('div.hFinderTreeBranchWrapper')
                            .find('ul:first')
                            .hasClass('hFinderTreeDirectoryBranchOn')
                    );

                    if (this.nextAll('span.hFinderTreeHasChildren').length)
                    {
                        var span = this.nextAll('span.hFinderTreeHasChildren:first');

                        if (isOpen)
                        {
                            span.addClass('hFinderTreeBranchIsOpen')
                                .removeClass('hFinderTreeBranchIsSelected');
                        }
                        else
                        {
                            span.removeClass('hFinderTreeBranchIsOpen')
                                .removeClass('hFinderTreeBranchIsSelected');
                        }

                        span.find('img')
                            .sourceFile((isOpen? 'down' : 'right') + 'Arrow.png');
                    }
                }
            }
        );

        $(document).on(
            'mousedown',
            '.hFinderTreeDirectory, .hFinderTreeFile',
            function()
            {
                if (typeof this.draggable !== 'undefined')
                {
                    this.draggable = true;
                }

                if (this.style && this.style.WebkitUserDrag)
                {
                    this.style.WebkitUserDrag = 'element';
                }

                finder.tree.getDirectory.call($(this));
            }
        );

        this.addEvent({

            dragstart : function(event)
            {
                var file = $(this);

                if (typeof finder !== 'undefined')
                {
                    //finder.delayDrag(event);
                    finder.dropIsCategory = false;
                }

                event.stopPropagation();
                event.originalEvent.dataTransfer.effectAllowed = 'copyMove';

                var html = file.outerHTML();

                // Data is passed this way for two reasons
                //     1. IE only supports a precious few types of data, one of them being text.
                //     2. The relevant event data needs to be passed this way in order to
                //            facilitate drag and drop between multiple instances of the browser.
                event.originalEvent
                     .dataTransfer
                     .setData((hot.userAgent == 'ie')? 'Text' : 'text/html', html);

                if (!file.isDirectory())
                {
                     var url = 'http://' + server.host + file.getFilePath();

                    // This check seems to be needed for IE and Firefox.
                    // Works fine without this in Safari.  Firefox complains about
                    // an invalid URL without it.
                    if (event.originalEvent.dataTransfer.constructor == Clipboard && event.originalEvent.dataTransfer.setData('DownloadURL', 'http://' + server.host))
                    {
                        var mime = file.attr('data-file-mime');
                        var fileName = file.children('span').text();

                        event.originalEvent
                             .dataTransfer
                             .setData('DownloadURL', mime + ':' + fileName + ':' + url);
                    }
                }

                if (hot.userAgent != 'ie')
                {
                    event.originalEvent.dataTransfer.setData('text/plain', html);
                }
            },

            dragend : function(event)
            {
                var file = $(this);

                // If the value is 'none' a drop was not successful.
                if (event.originalEvent.dataTransfer.dropEffect != 'none')
                {
                    if (event.originalEvent.dataTransfer.dropEffect == 'move')
                    {
                        //finder.Dragging = false;
                        if (typeof finder !== 'undefined')
                        {
                            if (!finder.dropIsCategory)
                            {
                                file.remove();
                            }
                        }
                        else
                        {
                            file.remove();
                        }
                    }
                }
            },

            dragover: function(event)
            {
                event.preventDefault();
                event.stopPropagation();

                var file = $(this);

                if (file.isDirectory())
                {
                    file.addClass('hFinderTreeDragOver');

                    //event.dataTransfer.dropEffect = (!event.altKey)? 'move' : 'copy';
                    event.originalEvent.dataTransfer.dropEffect = (!finder.tree.altKey)? 'move' : 'copy';
                }
            },

            dragenter : function(event)
            {
                event.preventDefault();
                event.stopPropagation();
            },

            dragleave : function(event)
            {
                var file = $(this);

                if (file.isDirectory())
                {
                    event.preventDefault();
                    file.removeClass('hFinderTreeDragOver');
                }
            },

            drop : function(event)
            {
                var file = $(this);

                if (typeof finder !== 'undefined')
                {
                    if (finder.beginsPath(file.getFilePath(), '/Categories'))
                    {
                        finder.dropIsCategory = true;
                    }
                }

                if (file.isDirectory())
                {
                    event.preventDefault();
                    event.stopPropagation();

                    var hasDroppedFiles = (
                        event.originalEvent.dataTransfer &&
                        event.originalEvent.dataTransfer.files &&
                        event.originalEvent.dataTransfer.files.length &&
                        finder &&
                        finder.dragDrop
                    );

                    if (hasDroppedFiles)
                    {
                        file.removeClass('hFinderTreeDragOver');

                        if (finder.dropIsCategory)
                        {
                            return;
                        }

                        event.preventDefault();
                        event.stopPropagation();

                        finder.dragDrop.openProgressDialogue(event.originalEvent.dataTransfer.files, file.getFilePath());
                        return;
                    }

                    var html = finder.getDropHTML(event);

                    file.removeClass('hFinderTreeDragOver')
                        .move(html.attr('data-file-path'));
                }
            }
        });

        $(document).on(
            'click',
            'span.hFinderTreeHasChildren',
            function()
            {
                var file = $(this);

                if (file.parent('h4').length)
                {
                    return;
                }

                file.openBranch();
                file.prevAll('div.hFinderTreeDirectory').select('hFinderTree');
            }
        );

        this.events();
    },

    addEvent : function(event)
    {
        if (typeof event == 'string')
        {
            // Have to remember the events so they can be reattached when
            // the user navigates to another directory, modifies the view,
            // or does something else to refresh/change/modify the directory
            // window's contents
            var fn = arguments[1];

            this.addedEvents.push({
                event : event,
                fn : fn,
                context : arguments[2]? arguments[2] : null
            });

            if (!arguments[3])
            {
                if (arguments[2] && arguments[2].length)
                {
                    arguments[2].find('.hFinderTreeDirectory, .hFinderTreeFile').bind(event, fn);
                }
                else
                {
                    $('.hFinderTreeDirectory, .hFinderTreeFile').bind(event, fn);
                }
            }
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

    events : function()
    {
        // Much more efficient.
        var context = arguments[0]? arguments[0] : $(document);

        context.find('.hFinderTreeDirectory, .hFinderTreeFile').each(
            function()
            {
                var node = $(this);

                $(finder.tree.addedEvents).each(
                    function()
                    {
                        if (this.context && this.context.length)
                        {
                            if (node.parents('#' + this.context.attr('id')).length)
                            {
                                node.unbind(this.event);
                                node.bind(this.event, this.fn);
                            }
                        }
                        else
                        {
                            node.unbind(this.event);
                            node.bind(this.event, this.fn);
                        }
                    }
                );
            }
        );
    },

    getDirectory : function()
    {
        if (this.get(0).dragDrop)
        {
            // IE won't come out to play without this method call.
            this.get(0).dragDrop();
        }

        this.select('hFinderTree');

        if ($('div.hFinderFiles').length)
        {
            if (finder.view == 'Columns')
            {
                finder.columnNavigate = true;
            }

            this.getDirectory();
        }
    },

    refreshBranchByFileId : function(fileId)
    {
        this.refreshBranch('hFinderTreeFile-' + fileId);
    },

    refreshBranchByDirectoryId : function(directoryId)
    {
        this.refreshBranch('hFinderTreeDirectory-' + directoryId);
    },

    refreshBranchByDirectoryPath : function(directoryPath)
    {
        // Get the directory Id, then refresh by Id
        var div = $('div.hFinderTree div[data-file-path="' + directoryPath + '"]');

        if (div.length)
        {
            this.refreshBranch('hFinderTreeDirectory-' + div.splitId());
        }
    },

    deleteBranchByDirectoryPath : function(directoryPath)
    {
        $('div.hFinderTree div[data-file-path="' + directoryPath + '"]').parent().remove();
    },

    newFolder : function()
    {
        if (!hot.selected('hFinderTree').length)
        {
            $('div.hFinderTreeRoot').select('hFinderTree');
        }

        var folder = prompt('New Folder:', '');

        if (folder && folder.length)
        {
            http.get(
                '/hFile/newDirectory', {
                    operation : 'Create Folder',
                    path : hot.selected('hFinderTree').attr('data-file-path'),
                    directory : folder
                },
                function(json)
                {
                    var directoryId = json;

                    var selected = hot.selected('hFinderTree');

                    if (!selected.parents('li:first').find('div.hFinderTreeBranchWrapper').length)
                    {
                        selected
                            .parents('li:first')
                            .append(
                                $('<span/>')
                                    .addClass('hFinderTreeHasChildren')
                                    .html(
                                        $('<img/>')
                                            .attr({
                                                src : '/hApplication/hApplicationUI/Pictures/Arrows/rightArrow.png',
                                                id : 'hFinderTreeIcon-' + directoryId,
                                                alt : '+',
                                                title : 'Click to expand'
                                            })
                                            .addClass('hFinderTreeHasChildren')
                                    )
                            )
                            .append(
                                $('<div/>').addClass('hFinderTreeBranchWrapper')
                            );

                        selected
                            .parents('li:first')
                            .openBranch();
                    }
                    else
                    {
                        finder.tree.RefreshBranchByDirectoryId(
                            hot.selected('hFinderTree').splitId()
                        );
                    }
                }
            );
        }
    },

    deleteFolder : function()
    {
        if (hot.selected('hFinderTree').length)
        {
            var path = hot.selected('hFinderTree').attr('data-file-path');

            switch (path)
            {
                case '/':
                case '/Categories':
                {
                    alert('This folder cannot be deleted!');
                    break;
                };
                default:
                {
                    if (confirm('Are you certain you want to PERMANENTLY delete "' + hot.selected('hFinderTree').text() + '"?'))
                    {
                        http.get(
                            '/hFile/delete', {
                                operation : 'Delete File',
                                path : path
                            },
                            function(json)
                            {
                                var selected = hot.selected('hFinderTree');

                                var ul = null;

                                if (selected.parents('ul:first').find('li').length == 1)
                                {
                                    ul = selected.parents('ul:first');
                                }

                                selected.parents('li:first').remove();

                                if (ul && ul.length)
                                {
                                    var li = ul.parents('.hFinderTreeDirectoryBranch:first');

                                    li.find('span.hFinderTreeHasChildren').remove();
                                    li.find('div.hFinderTreeBranchWrapper').remove();
                                }
                            }
                        );
                    }
                };
            }
        }
        else
        {
            alert("You have not selected anything to delete!");
        }
    },

    refreshBranch : function(id)
    {
        var div = $('div#' + id);

        // The branch is open
        if (div.length)
        {
            // Get its parent...
            var li = div.parents('li:first');

            if (li.children('span.hFinderTreeHasChildren').length)
            {
                li.children('span.hFinderTreeHasChildren').openBranch(true);
            }
            else if (li.children('div.hFinderTreeBranchWrapper').length)
            {
                // No img means it's the root...
                li.children('div.hFinderTreeBranchWrapper').loadBranch();
            }
        }
    }
};

$(document).ready(
    function()
    {
        finder.tree.ready();
    }
);
