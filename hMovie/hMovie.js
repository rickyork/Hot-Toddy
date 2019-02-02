$.fn.extend({
    getMovies : function()
    {
        http.get(
            '/hMovie/getMovies', {
                operation : 'Get Movies',
                path : this.attr('data-file-path')
            },
            function(html)
            {
                $('div#hMovieView ul').html(html);
                movie.events();
            }
        );
    }
});

var movie = {
    lastPath : null,

    ready : function()
    {
        if (typeof(finder.tree) != 'undefined')
        {
            this.treeEvents();
        }
    },

    events : function()
    {
        $('li.hMovie')
            .click(
                function()
                {
                    $(this).select('hMovie');
                }
            )
            .mousedown(
                function()
                {
                    if (this.dragDrop)
                    {
                        // IE won't come out to play without this method call.
                        this.dragDrop();
                    }
                }
            )
            .on(
                'dragstart.movie',
                function(event)
                {
                    event.stopPropagation();
                    event.originalEvent.dataTransfer.effectAllowed = 'move';

                    if (typeof(editor) != 'undefined')
                    {
                        editor.dragging = true;
                    }

                    var path = $(this).attr('data-file-path');
                    var caption = $(this).find('div.hMovieCaption span').text();

                    movie.lastPath = path;

                    var img = document.createElement('img');
                    img.src = $(this).find('div.hMovieThumbnail img').attr('src') + '&hMovie=1';
                    img.title = path;
                    img.className = 'hMovie';
                    img.alt = caption;

                    var html = $(img).outerHTML();

                    // Data is passed this way for two reasons
                    //     1. IE only supports a precious few types of data, one of them being text.
                    //     2. The relevant event data needs to be passed this way in order to
                    //            facilitate drag and drop between multiple instances of the browser.
                    event.originalEvent.dataTransfer.setData((hot.userAgent == 'ie')? 'html' : 'text/html', html);
                }
            )
            .on(
                'dragend.movie',
                function(event)
                {

                }
            )
            .each(
                function()
                {
                    this.draggable = true;
                }
            );
    },

    treeEvents : function()
    {
        finder.tree.addEvent(
            'click',
            function()
            {
                $(this).getMovies();
            },
            $('div#hMovieTree'),
            true
        );
    }
};

$(document).ready(
    function()
    {
        movie.ready();
    }
);
