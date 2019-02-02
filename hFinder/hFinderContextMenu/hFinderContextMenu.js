$.fn.extend({

    openContextMenu : function(event, id)
    {
        event.preventDefault();

        var viewport = finder.contextMenu.getViewportDimensions();

        menu = $('div#' + id);

        // Reset offset values to their defaults
        menu.css({
            top : 'auto',
            right : 'auto',
            bottom : 'auto',
            left : 'auto'
        });

        menu.show().addClass('hFinderContextMenuOn');

        // Optional context items must be displayed before
        // adjusting the offset!
        if (id == 'hFinderFileContextMenu')
        {
            if (!this.isDirectory())
            {
                if (finder.beginsPath('/Categories'))
                {
                    menu.find('li.hFinderContextMenuUploadFile, li#hFinderContextReplaceFile, li#hFinderContextDuplicate').hide();
                    $('li#hFinderContextDelete').text('Remove From Category');
                }
                else
                {
                    $('li#hFinderContextDelete').text('Delete');
                    menu.find('li.hFinderContextMenuFileItem').show();

                    if (this.getExtension() != 'zip' && this.getMIMEType() != 'application/zip')
                    {
                        $('li#hFinderContextUnzip').hide();
                    }
                }
            }
            else
            {
                if (finder.beginsPath('/Categories'))
                {
                    $('li#hFinderContextDelete').text('Delete Category');
                }
                else
                {
                    $('li#hFinderContextDelete').text('Delete');
                }

                menu.find('li.hFinderContextMenuFileItem').hide();
            }
        }

        /**
        * If the height or width of the context menu is greater than the amount of pixels
        * from the point of click to the right or bottom edge of the viewport
        * adjust the offset accordingly
        */
        if (menu.outerHeight() > (viewport.y - event.pageY))
        {
            menu.css('bottom', (viewport.y - event.pageY) + 'px');
        }
        else
        {
            menu.css('top', event.pageY + 'px');
        }

        if (menu.outerWidth() > (viewport.x - event.pageX))
        {
            menu.css('right', (viewport.x - event.pageX) + 'px');
        }
        else
        {
            menu.css('left', event.pageX + 'px');
        }
    },

    arrangeBy : function()
    {
        var viewport = finder.contextMenu.getViewportDimensions();

        var top = this.parents('div.hFinderContextMenu:first').css('top');
        var right = this.parents('div.hFinderContextMenu:first').css('right');
        var bottom = this.parents('div.hFinderContextMenu:first').css('bottom');
        var left = this.parents('div.hFinderContextMenu:first').css('left');

    }
});

if (finder === undefined)
{
    var finder = {};
}

finder.contextMenu = {
    target : null,

    ready : function()
    {
        if (finder && finder.addEvent)
        {
            finder.addEvent(
                'click',
                function(event)
                {
                    if (event.ctrlKey)
                    {
                        $(this).openContextMenu(event);
                    }
                }
            );

            finder.addEvent(
                'contextmenu',
                function(event)
                {
                    event.stopPropagation();
                    $(this).openContextMenu(event, 'hFinderFileContextMenu');
                    $(this).select('hFinder');
                    finder.getLabel();
                }
            );
        }

        $('div.hFinderFiles').bind(
            'contextmenu.finder',
            function(event)
            {
                event.stopPropagation();
                finder.contextMenu.target = event.target;
                $(this).openContextMenu(event, 'hFinderFolderContextMenu');
            }
        );

        $(document)
            .on(
                'mouseenter.finderContextMenu',
                'div.hFinderContextMenu li',
                function()
                {
                    if (!$(this).hasClass('hFinderContextMenuSeparator') && !$(this).hasClass('hFinderContextMenuLabels') && !$(this).hasClass('hFinderContextMenuItemDisabled'))
                    {
                        $(this).addClass('hFinderContextMenuItemOn');
                    }
                }
            )
            .on(
                'mouseleave.finderContextMenu',
                'div.hFinderContextMenu li',
                function()
                {
                    $(this).removeClass('hFinderContextMenuItemOn');
                }
            );

        $('div#hFinderFileContextMenu span.hFileLabel').click(
             function()
             {
                 finder.label.set(
                     hot.selected('hFinder').getFilePath(),
                     $(this).attr('title')
                 );

                 finder.contextMenu.close();
             }
         );

        // Attach events for each contextmenu item
        $('li#hFinderContextOpen').click(
            function()
            {
                hot.selected('hFinder').getDirectory();
                finder.contextMenu.close();
            }
        );

        $('li#hFinderContextEdit').click(
            function()
            {
                if (!$(this).hasClass('hFinderContextMenuItemDisabled'))
                {
                    hot.window(
                        '/Applications/Editor', {
                            path : hot.selected('hFinder').getFilePath()
                        },
                        1200,
                        800,
                        hot.selected('hFinder').getFileName().replace('.', ''), {
                            scrollbars : false,
                            resizable : true
                        }
                    );

                    finder.contextMenu.close();
                }
            }
        );

        $('li#hFinderContextUnzip').click(
            function()
            {
                if (!$(this).hasClass('hFinderContextMenuItemDisabled'))
                {
                    var selected = hot.selected('hFinder');
                    selected.unzip();
                    finder.contextMenu.close();
                }
            }
        );

        $('li#hFinderContextDelete').click(
            function()
            {
                if (!$(this).hasClass('hFinderContextMenuItemDisabled'))
                {
                    var selected = hot.selected('hFinder');

                    if (finder.beginsPath('/Categories') && !selected.isDirectory())
                    {
                        hot.selected('hFinder').removeFileFromCategory();
                    }
                    else
                    {
                        hot.selected('hFinder').deleteFile();
                    }

                    finder.contextMenu.close();
                }
            }
        );

        $('li#hFinderContextRename').click(
            function()
            {
                if (!$(this).hasClass('hFinderContextMenuItemDisabled'))
                {
                    hot.selected('hFinder').find('span.hFinderDirectoryName, span.hFinderFileName').inlineRename();
                    finder.contextMenu.close();
                }
            }
        );

        $('li#hFinderContextEmail').click(
            function()
            {
                if (!$(this).hasClass('hFinderContextMenuItemDisabled'))
                {
                    finder.contextMenu.close();
                    location.href = 'mailto:?subject=&body=' + escape('http://' + server.host + escape(hot.path(hot.selected('hFinder').getFilePath())));
                }
            }
        );

        $('li#hFinderContextDuplicate').click(
            function()
            {
                if (!$(this).hasClass('hFinderContextMenuItemDisabled'))
                {
                    http.get(
                        '/hFile/duplicate', {
                            path : hot.selected('hFinder').getFilePath()
                        },
                        function()
                        {
                            this.refresh();
                            this.contextMenu.close();
                        },
                        finder
                    );
                }
            }
        );

        $('li.hFinderContextMenuNewFolder').click(
            function()
            {
                finder.contextMenu.close();
                finder.newDirectory(finder.contextMenu.target);
            }
        );

        $('li.hFinderContextMenuNewFile').click(
            function()
            {
                if (!$(this).hasClass('hFinderContextMenuItemDisabled'))
                {
                    finder.contextMenu.close();
                    finder.newFile(finder.contextMenu.target);
                }
            }
        );

        $('li.hFinderContextMenuUploadFile').click(
            function()
            {
                finder.contextMenu.close();

                if (!$(this).hasClass('hFinderContextMenuItemDisabled'))
                {
                    finder.upload.openPanel();
                }
            }
        );

        $('li#hFinderContextReplaceFile').click(
            function()
            {
                if (!$(this).hasClass('hFinderContextMenuItemDisabled'))
                {
                    finder.contextMenu.close();
                    finder.editFile.openPanel();
                }
            }
        );

        $('li#hFinderFolderContextMenuGetInfo').click(
            function()
            {
                if (!$(this).hasClass('hFinderContextMenuItemDisabled'))
                {
                    finder.contextMenu.close();
                    $('div.hFinderFiles').attr('title', finder.path);
                    $('div.hFinderFiles').attr('data-file-path', finder.path);
                    $('div.hFinderFiles').getInfo();
                }
            }
        );

        $('li#hFinderContextPermissions').click(
            function()
            {
                var frameworkResourceId;
                var frameworkResourceKey;
                var selected = hot.selected('hFinder');

                if (finder.beginsPath(selected.getFilePath(), '/Categories'))
                {
                    frameworkResourceId = 20;

                    if (selected.getFilePath() == '/Categories')
                    {
                        frameworkResourceKey = 0;
                    }
                    else
                    {
                        var id = selected.splitId();
                        var exp = new RegExp('[0-9]', 'g');
                        var matches = id.match(exp);

                        if (matches && matches.length)
                        {
                            frameworkResourceKey = matches.join('');
                        }
                        else
                        {
                            dialogue.alert({
                                title: 'Error',
                                label : 'Unable to get the category Id to launch permissions dialogue.'
                            });

                            return;
                        }
                    }

                }
                else if (selected.isDirectory())
                {
                    frameworkResourceId = 2;
                    frameworkResourceKey = selected.splitId();
                }
                else
                {
                    frameworkResourceId = 1;
                    frameworkResourceKey = selected.splitId();
                }

                hot.window(
                    '/System/Applications/permissions.html', {
                        hFrameworkResourceId : frameworkResourceId,
                        hFrameworkResourceKey : frameworkResourceKey
                    },
                    800,
                    600,
                    'hUserPermissions', {
                        menubar : false,
                        location : false,
                        statusbar : false,
                        titlebar : false,
                        toolbar : false,
                        scrollbars : true,
                        resizable : false,
                        alwaysraised : true,
                        "z-lock" : true
                    }
                );

                finder.contextMenu.close();
            }
        );

        $('li#hFinderContextProperties').click(
            function()
            {
                hot.selected('hFinder').getFileProperties();
                finder.contextMenu.close();
            }
        );

        $('li.hFinderContextGetInfo').click(
            function()
            {
                if (!$(this).hasClass('hFinderContextMenuItemDisabled'))
                {
                    if ($(this).parents('div#hFinderFileContextMenu').length)
                    {
                        hot.selected('hFinder').getInfo();
                    }

                    finder.contextMenu.close();
                }
            }
        );

        $('li#hFinderContextSymbolicLink').click(
            function()
            {
                if (!$(this).hasClass('hFinderContextMenuItemDisabled'))
                {
                    finder.contextMenu.close();
                }
            }
        );

        $('div.hFinderContextMenu').hover(
            function()
            {
                finder.contextMenu.tracker = true;

                if (finder)
                {
                    finder.fileActive = true;
                }
            },
            function()
            {
                finder.contextMenu.tracker = false;

                if (finder)
                {
                    finder.fileActive = false;
                }
            }
        );

        //$(document).click(
        //    function()
        //    {
        //        if (!finder.contextMenu.tracker)
        //        {
        //            $('div.hFinderContextMenu').hide();
        //        }
        //    }
        //);

        /*
        $('li.hFinderContextMenuArrangeBy').hover(
            function()
            {
                $(this).children('div.hFinderContextMenu').show();
                $(this).arrangeBy();
            },
            function()
            {
                $(this).children('div.hFinderContextMenu').hide();
            }
        );
        */
    },

    getViewportDimensions : function()
    {
        var vpx, vpy;

        if (self.innerHeight)
        {
            // all except Explorer
            vpx = self.innerWidth;
            vpy = self.innerHeight;
        }
        else if (document.documentElement && document.documentElement.clientHeight)
        {
            // Explorer 6 Strict Mode
            vpx = document.documentElement.clientWidth;
            vpy = document.documentElement.clientHeight;
        }
        else if (document.body)
        {
            // other Explorers
            vpx = document.body.clientWidth;
            vpy = document.body.clientHeight;
        }

        return {
            x : vpx,
            y : vpy
        };
    },

    close : function()
    {
        $('div.hFinderContextMenu:visible').each(
            function()
            {
                hot.fire.call($(this), 'onCloseContextMenu');
            }
        );

        $('div.hFinderContextMenu')
            .hide()
            .removeClass('hFinderContextMenuOn')
            .removeClass('hFinderContextMenuFileActions')
            .find('li.hFinderContextMenuItem')
                .not('.hFinderContextMenuUploadFile')
                .removeClass('hFinderContextMenuItemDisabled');

        $('div.hFinderContextMenu').find('li.hFinderToolbarActionMenuItem').hide();

        if ($('li#hFinder-Action').length)
        {
            $('li#hFinder-Action').removeClass('hFinderActionOn');
        }

        if ($('li#hFinder-Path').length)
        {
            $('li#hFinder-Path').removeClass('hFinderPathOn');
        }
    },

    tracker : false,

    checkTracker : function()
    {
        if (!this.tracker)
        {
            finder.contextMenu.close();
        }
    }
};

$(document).ready(
    function()
    {
        finder.contextMenu.ready();
    }
);

$(document).mousedown(
    function()
    {
        finder.contextMenu.checkTracker();
    }
);