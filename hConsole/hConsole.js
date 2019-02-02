var console = {
    limit : 0,
    log : null,
    cursor : null,

    ready : function()
    {
        $('ul#hConsoleLogs ul li').click(
            function()
            {
                $(this).select('hConsoleLog');
                console.get($(this).splitId(), null);
            }
        );

        $('span#hConsoleRefresh').click(
            function(event)
            {
                event.preventDefault();
                console.refresh();
            }
        );

        $('input#hConsoleRecordCount').keyup(
            function()
            {
                if ($(this).val() && parseInt($(this).val()) > 0)
                {
                    console.refresh();
                }
            }
        );

        $('input#hFrameworkErrorsTruncate').click(
            function(event)
            {
                event.preventDefault();

                dialogue.confirm(
                    {
                        label : "Are you sure you want to delete all errors?",
                        ok : "Truncate Error Log",
                        cancel : "Don't Truncate Error Log"
                    },
                    function(confirm)
                    {
                        if (confirm)
                        {
                            console.truncateErrors();
                        }
                    }
                );
            }
        );

        if (this.width > screen.width)
        {
            this.width = screen.width;
        }

        if (this.height > screen.height)
        {
            this.height = screen.height;
        }

        window.resizeTo(this.width, this.height);
        window.moveTo((screen.width - this.width) / 2, (screen.height - this.height) / 2);

        $(window).resize(
            function()
            {
                http.get(
                    '/hConsole/saveWindowDimensions', {
                        operation : 'Save Windows Dimensions',
                        width : $(window).width(),
                        height : $(window).height()
                    },
                    function(json)
                    {

                    }
                );

                $('div#hConsoleLogContainer').css({
                    width : $('div#hConsoleItems > table').innerWidth() + 'px'
                });
            }
        );

        $('div#hConsoleResizeGrip').mousedown(
            function(event)
            {
                console.resizeIsActive = true;
                console.coordinates = {
                    x : event.pageX,
                    y : event.pageY,
                    width : $('ul#hConsoleLogs').width()
                };
            }
        );

        $(document)
            .mousemove(
                function(event)
                {
                    if (console.resizeIsActive)
                    {
                        console.onResize(event);
                    }
                }
            )
            .mouseup(
                function(event)
                {
                    if (console.resizeIsActive)
                    {
                        console.resizeIsActive = false;
                        console.saveColumnDimensions();
                    }
                }
            );

        if (this.logsWidth)
        {
            this.resize(this.logsWidth);
        }
    },

    onResize : function(event)
    {
        this.resize(this.coordinates.width - (this.coordinates.x  - event.pageX));
    },

    resize : function(width)
    {
        if (width < 155)
        {
            width = 155;
        }
        else if (width > 350)
        {
            width = 350;
        }

        this.resizedTo = width;

        $('ul#hConsoleLogs').width(width + 'px');

        $('div#hConsoleResizeGrip').css('left', (width - 1) + 'px');

        $('div#hConsoleToolbar').css('left', (width + 1) + 'px');
        $('div#hConsoleItems').css('left', (width + 1) + 'px');
        $('div#hConsoleControls').css('left', (width + 1) + 'px');
    },

    saveColumnDimensions : function()
    {
        if (this.resizedTo)
        {
            http.get(
                '/hConsole/saveColumnDimensions', {
                    operation : 'Save Column Dimensions',
                    width : this.resizedTo
                },
                function(json)
                {
                    console.resizedTo = 0;

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

    truncateErrors : function()
    {
        http.get(
            '/hConsole/truncateErrorLog', {
                operation : 'Truncate Error Log'
            },
            function(json)
            {
                console.refresh();
            }
        );
    },

    refresh : function()
    {
        if (this.log)
        {
            this.get(this.log, this.cursor);
        }
    },

    get : function(log, cursor)
    {
        $('div#' + this.log + 'Controls').hide();

        this.log = log;
        this.cursor = cursor;

        $('div#' + this.log + 'Controls').show();

        http.get(
            '/hConsole/get', {
                operation : 'Get Log',
                hConsoleLog : log,
                hSearchCursor : cursor? cursor : '',
                hConsoleRecordCount : $('input#hConsoleRecordCount').val()
            },
            function(json)
            {
                $('div#hConsoleItems').html(json.log);
                $('div#hConsolePagingNavigation').html(json.search);

                $('div#hConsoleLogContainer').css({
                    width : $('div#hConsoleItems > table').innerWidth() + 'px'
                });

                console.onLoadLog();
            }
        );
    },

    onLoadLog : function()
    {
        $('ul.hSearchNavigation a').click(
            function(event)
            {
                event.preventDefault();
                console.get(hot.selected('hConsoleLog').splitId(), $(this).attr('href').split('=').pop());
            }
        );

        $('div#hConsoleItems table tbody tr').click(
            function()
            {
                $(this).select('hConsoleLogRecord');
            }
        );

        $('div.hUserAgent').dblclick(
            function()
            {
                $('body').append(
                    $(this).clone()
                       .addClass('hUserAgentDialogue')
                       .removeClass('hUserAgent')
                       .dblclick(
                           function()
                           {
                               $(this).remove();
                           }
                       )
                );
            }
        );

        $('div.hFrameworkError').dblclick(
            function()
            {
                $('body').append(
                    $(this).clone()
                       .addClass('hFrameworkErrorDialogue')
                       .removeClass('hFrameworkError')
                       .dblclick(
                           function()
                           {
                               $(this).remove();
                           }
                       )
                );
            }
        );
    }
};

$(document)
    .ready(
        function()
        {
            console.ready();
        }
    )
    .bind(
        'touchmove',
         function(event)
         {
             event.preventDefault();
         }
    );
