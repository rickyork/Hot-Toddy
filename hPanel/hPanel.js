var panel = {
    ready : function()
    {
        this.title = $('title').text();
        
        $('li.hPanelApplication').click(
            function()
            {
                $(this).select('hPanelApplication');
                $('title').text('Loading ' + $(this).find('div.hPanelCaption').text() + '...');
                
                var path = $(this).attr('data-file-path');

                if (path == '/Applications/Finder')
                {
                    path += '?hFinderButtons=1';
                }

                $('iframe#hPanelApplication').attr('src', hot.path(path));
                $('iframe#hPanelApplication').get(0).onload = function()
                {
                    top.panel.loaded();
                };
            }
        );
        
        $('div#hPanelControlShowAll').click(
            function()
            {
                $('title').text(panel.title);
                $('div#hPanelApplicationWrapper').fadeOut();
            }
        );
    },

    getFrame : function(iframe)
    {
        var iframe = $('iframe#hPanelApplication').get(0);

        if (iframe && typeof(iframe) != 'undefined')
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

    loaded : function()
    {
        var selected = hot.selected('hPanelApplication');

        $('div#hPanelApplicationWrapper').fadeIn();
        $('title').text(selected.find('div.hPanelCaption').text());
    }
};

$(document).ready(
    function()
    {
        panel.ready();
    }
);
