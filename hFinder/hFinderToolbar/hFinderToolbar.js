$.fn.extend({
    openActionMenu : function()
    {
        var file = hot.selected('hFinder');
        var selected = true;
        var menu = $('div#hFinderFileContextMenu');

        if (!file.length)
        {
            selected = false;                     
        }

        var isDirectory = (selected)? file.isDirectory() : false;

        this.addClass('hFinderActionOn');

        menu.find('li.hFinderToolbarActionMenuItem').show();
        menu.find('li.hFinderContextMenuFileItem').show();

        if (!selected)
        {
            menu.find('li.hFinderContextMenuItem').addClass('hFinderContextMenuItemDisabled');
        }
        else
        {
            finder.getLabel();

            if (isDirectory)
            {
                menu.find('li.hFinderContextMenuFileItem').hide();
            }
            else
            {
                if (file.getExtension() != 'zip' && file.getMIMEType() != 'application/zip')
                {
                    $('li#hFinderContextUnzip').hide();
                }
            }
        }

        menu
            .addClass('hFinderContextMenuFileActions')
            .addClass('hFinderContextMenuOn')
            .css({
                top: ($(this).offset().top + 25) + 'px',
                right: 'auto',
                bottom: 'auto',
                left: $(this).offset().left + 'px'
            })
            .show();

        if (finder.beginsPath('/Categories'))
        {
            menu.find(
                'li.hFinderContextMenuUploadFile, ' + 
                'li.hFinderContextMenuNewFile, ' + 
                'li#hFinderContextReplaceFile, ' + 
                'li#hFinderContextDuplicate'
            ).hide();
        }
    },
    
    closeActionMenu : function()
    {
        this.removeClass('hFinderActionOn');
        $('div#hFinderFileContextMenu').hide();
    }
});

finder.toolbar = {
    menuTracker : [],

    trackers : [
        'Action',
        'Path'
    ],

    imageObjects : [],

    ready : function()
    {
        finder.location.parse();

        $(finder.views).each(
            function()
            {
                $('li#hFinderMenu' + this).click(
                    function()
                    {
                        finder.switchView(this);
                    }
                );
            }
        );

        $('li.hFinderToolbarView')
            .active(
                function()
                {
                    if ($(this).hasClass('hFinder' + $(this).splitId() + 'On'))
                    {
                        $(this).addClass('hFinder' + $(this).splitId() + 'OnActive');
                    }
                    else
                    {
                        $(this).addClass('hFinder' + $(this).splitId() + 'Active');
                    }
                },
                function()
                {
                    $(this).removeClass('hFinder' + $(this).splitId() + 'OnActive');
                    $(this).removeClass('hFinder' + $(this).splitId() + 'Active');
                },
                function()
                {
                    finder.switchView(this);
                }
            );

        $('li#hFinderNewDirectory').click(
            function()
            {
                finder.newDirectory();        
            }
        );

        $('li#hFinderDefaultView').click(
            function()
            {
                finder.setDefaultView();
            }
        );

        $('li#hFinderNewDocument').click(
            function()
            {
                hot.window('/Applications/Editor', null, 1200, 800, {scrollbars: false, resizable: true});
            }
        );

        $('li#hFinder-Refresh')
            .active(
                function()
                {
                    $(this).addClass('hFinderRefreshOn');
                },
                function()
                {
                    $(this).removeClass('hFinderRefreshOn');
                },
                function()
                {
                    finder.refresh();
                }
            );

        $('li#hFinder-Action').click(
            function()
            {
                $(this).openActionMenu();
            }
        );

        if (typeof(finder.history) != 'undefined')
        {
            finder.history.ready();
        }
    }
};

$(document).ready(
    function()
    {
        finder.toolbar.ready();
    }
);
