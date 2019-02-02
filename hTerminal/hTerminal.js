var terminal = {
    ready : function()
    {
        $('div#hTerminalOutput').bind(
            'touchmove',
            function(e)
            {
                e.preventDefault();
            }
        );

        $('div#hTerminalInput').get(0).contentEditable = true;
        
        $('div#hTerminalInput').keypress(
            function(e)
            {
                if (e.keyCode == 13)
                {
                    e.preventDefault();
                    terminal.execute($('div#hTerminalInput').text());
                    $('div#hTerminalInput').text('');
                }
            }
        );
        
        $(document).on(
            'dblclick',
            'div.hTerminalOutput h4',
            function()
            {
                terminal.execute($(this).text()); 
            }
        );
        
        $(document).on(
            'click',
            'div.hTerminalOutput h4',
            function()
            {
                $(this).next('div.hTerminalOutputInner').slideToggle('slow');
            }
        );
        
        $('input#hTerminalClear').click(
            function(e)
            {
                e.preventDefault();
                $('div#hTerminalOutput').html('');
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
                    '/hTerminal/saveWindowDimensions', {
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
    },

    execute : function(command)
    {
        http.post(
            '/hTerminal/execute', {
                operation : 'Execute Command',
                command : command
            },
            function(json)
            {
                $('div#hTerminalOutput')
                    .append(json.output)
                    .animate({
                        scrollTop : $('div#hTerminalOutput').attr("scrollHeight") 
                    }, 500
                );
            }
        );
    }
};

$(document)
    .ready(
        function()
        {
            terminal.ready();
        }
    )
    .bind(
        'touchmove',
         function(e)
         {
             e.preventDefault();
         }
    );
