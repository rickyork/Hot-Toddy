$.fn.extend({

    openLocationMenu : function()
    {
        this.addClass('hFinderPathOn');

        this.find('div#hFinderLocationMenu')
            .show();
    },

    closeLocationMenu : function()
    {
        this.removeClass('hFinderPathOn');

        this.find('div#hFinderLocationMenu')
            .hide();
    }
});

finder.location = {

    ready : function()
    {
        hot.event(
            'requestDirectory',
            finder.location.parse,
            finder.location
        );

        $('li#hFinder-Path').click(
            function()
            {
                $(this).openLocationMenu();
            }
        );
    },

    parse : function()
    {
        $('div#hFinderLocationMenu ul').html('');

        var matchPlus = new RegExp('/\+/g');

        var files = unescape(finder.path.replace(matchPlus, ' ')).split('/');
        files.reverse();

        // $files.pop();
        // /stuff/this/that/either/or
        // or/either/that/this/stuff/
        this.pathFiles = unescape(finder.path.replace(matchPlus, ' ')).split('/');

        $(files).each(
            function(key, value)
            {
                var file = value;
                var path = finder.location.getPath();

                if (!file.length)
                {
                    file = finder.location.diskName;
                    path = '/';
                }

                if (path == finder.location.path)
                {
                    file = finder.location.diskName;
                    path = finder.location.path;
                }

                // hFinderToolbar.addToolbarMenuItemEvent($li);
                if (typeof(file) != 'undefined')
                {

                    $('div#hFinderLocationMenu ul').append(
                        "<li data-file-path='" + path + "'>" +
                            "<span>" + finder.decode(file) + "</span>" +
                        "</li>"
                    );

                    $('div#hFinderLocationMenu ul li:last-child').click(
                        function()
                        {
                            finder.contextMenu.close();
                            finder.requestDirectory($(this).getFilePath());
                        }
                    );

                    if (path == unescape(finder.location.path))
                    {
                        return false;
                    }
                }
            }
        );
    },

    getPath : function(pathFiles)
    {
        path = this.pathFiles.join('/');
        this.pathFiles.pop();

        return path;
    }
};


$(document).ready(
    function()
    {
        finder.location.ready();
    }
);
