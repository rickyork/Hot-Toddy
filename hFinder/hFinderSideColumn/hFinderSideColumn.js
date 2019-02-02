finder.sideColumn = {
    ready : function()
    {
        $('li.hFinderSideColumnOption').click(
            function()
            {
                $(this).select('hFinderTree');
            }
        );

        $('li.hFinderSideColumnFolder, h4.hFinderSideColumnWebsite').click(
            function()
            {
                if (!$(this).hasClass('hFinderSideColumnOption'))
                {
                    hot.unselect('hFinderTree');
                }

                finder.requestDirectory($(this).data('file-path'));
            }
        );

        $('li.hFinderSearchTime').click(
            function()
            {
                finder.setView('Icons', true);

                http.get(
                    '/hFinder/searchByPreset', {
                        hFileSearchPreset : 'Time',
                        hFileSearchTime : $(this).find('span').text()
                    },
                    finder.onRequestDirectoryCallback,
                    finder
                );
            }
        );

        $('li.hFinderSearchType').click(
            function()
            {
                finder.setView('Icons', true);

                switch ($(this).find('span').text())
                {
                    case 'All Images':
                    {
                        var type = 'Images';
                        break;
                    };
                    case 'All Movies':
                    {
                        var type = 'Movies';
                        break;
                    };
                    case 'All Documents':
                    {
                        var type = 'Documents';
                        break;
                    };
                }

                http.get(
                    '/hFinder/searchByPreset', {
                        hFileSearchPreset: type
                    },
                    finder.onRequestDirectoryCallback,
                    finder
                );
            }
        );

        $('div#hFinderSideColumnResizeGrip').mousedown(
            function(event)
            {
                finder.sideColumn.resizeIsActive = true;

                finder.sideColumn.coordinates = {
                    x : event.pageX,
                    y : event.pageY,
                    width : $('div#hFinderSideColumn').width()
                };
            }
        );

        $(document)
            .mousemove(
                function(event)
                {
                    if (finder.sideColumn.resizeIsActive)
                    {
                        finder.sideColumn.onResize(event);
                    }
                }
            )
            .mouseup(
                function(event)
                {
                    if (finder.sideColumn.resizeIsActive)
                    {
                        finder.sideColumn.resizeIsActive = false;
                        finder.sideColumn.saveSize();
                    }
                }
            );

        if (this.width)
        {
            this.resize(this.width);
        }
    },

    onResize : function(event)
    {
        this.resize(this.coordinates.width - (this.coordinates.x  - event.pageX));
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

        $('div#hFinderSideColumn').width(width + 'px');

        $('div#hFinderSideColumnResizeGrip').css('left', (width - 2) + 'px');

        $('div.hFinderFiles').css('margin-left', (width + 1) + 'px');
        $('div#hFinderListHeader').css('left', (width + 1) + 'px');

        if ($('div.hFinderUpload').length)
        {
            $('div.hFinderUpload').css('left', (width + 1) + 'px');
        }

        if ($('div.hFinderEditFile').length)
        {
            $('div.hFinderEditFile').css('left', (width + 1) + 'px');
        }
    },

    saveSize : function()
    {
        if (this.resizedTo)
        {
            http.get(
                '/hFinder/saveSize', {
                    operation : 'Save Side Column Size',
                    width : this.resizedTo
                },
                function(json)
                {
                    finder.sideColumn.resizedTo = 0;

                    switch (parseInt(json))
                    {
                        case 1:
                        {

                        }
                    }
                }
            );
        }
    }
};

$(document).ready(
    function()
    {
        finder.sideColumn.ready();
    }
);
