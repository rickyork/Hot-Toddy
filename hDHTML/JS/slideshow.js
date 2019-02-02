var slideshows = [];

$.fn.extend({
    slideshow : function()
    {
        return this.each(
            function()
            {
                var node = $(this);

                if (typeof node.data('slideshow') === 'undefined')
                {
                    var offset = slideshows.length + 1;

                    slideshows[offset] = new slideshow(node, offset);

                    node.data('slideshow', offset);
                }
            }
        );
    }
});

var slideshow = hot.factory();

slideshow.prototype.init = function(node, slideCollection)
{
    this.counter = 1;
    this.isInterrupted = false;
    this.transitioning = false;
    this.resumeTimer = null;

    if (!node.find('ul.slideshowControls').length)
    {
        node.prepend(
            $('<ul/>').addClass('slideshowControls')
        );
    }

    node.find('ul.slideshowControls').html('');

    var slideInCollection = 1;

    node.find('.slide').each(
        function()
        {
            this.id = 'slide-' + slideCollection + '-' + slideInCollection;

            node.find('ul.slideshowControls')
                .append(
                    $('<li/>')
                        .attr(
                            'id',
                            'slideshowControl-' + slideCollection + '-' + slideInCollection
                        )
                        .html(
                            $('<span/>').text(slideInCollection)
                        )
                );
               
            slideInCollection++;
        }
    );

    node.find('ul.slideshowControls li:first')
        .addClass('slideshowControlActive');

    node.find('ul.slideshowControls li')
        .hover(
            function()
            {
                $(this).addClass('slideshowControlOn');
            },
            function()
            {
                $(this).removeClass('slideshowControlOn');
            }
        )
        .click(
            function()
            {
                if (!slideshows[slideCollection].transitioning)
                {
                    if (slideshows[slideCollection].resumeTimer)
                    {
                        clearTimeout(slideshows[slideCollection].resumeTimer);
                    }

                    slideshows[slideCollection].transitioning = true;
                    slideshows[slideCollection].isInterrupted = true;

                    var li = $(this);

                    node.find('ul.slideshowControls li')
                        .removeClass('slideshowControlActive');

                    node.find('.slide:visible')
                        .fadeOut('slow');

                    var slideInCollection = parseInt($(this).text());

                    var counter = slideInCollection + 1;

                    if ((slideInCollection + 1) > node.find('ul.slideshowControls li').length)
                    {
                        counter = 1;
                    }

                    slideshows[slideCollection].counter = counter;

                    $('#slide-' + slideCollection + '-' + slideInCollection).fadeIn(
                        'slow',
                        function()
                        {
                            li.addClass('slideshowControlActive');
                            
                            slideshows[slideCollection].transitioning = false;

                            slideshows[slideCollection].resumeTimer = setTimeout(
                                'slideshows[' + slideCollection + '].resume();', 
                                5000
                            );
                        }
                    );
                }
            }
        );

    this.resume = function()
    {
        this.isInterrupted = false;
        this.transition();
    };

    this.transition = function()
    {
        if (this.isInterrupted)
        {
            return;
        }

        node.find('.slide:visible')
            .fadeOut('slow');

        node.find('ul.slideshowControls li')
            .removeClass('slideshowControlActive');

        $('#slide-' + slideCollection + '-' + this.counter).fadeIn(
            'slow',
            function()
            {
                node.find('ul.slideshowControls li').each(
                    function()
                    {
                        if (parseInt($(this).text()) == slideshows[slideCollection].counter)
                        {
                            $(this).addClass('slideshowControlActive');
                        }
                    }
                );

                slideshows[slideCollection].counter++;

                if (slideshows[slideCollection].counter > node.find('ul.slideshowControls li').length)
                {
                    slideshows[slideCollection].counter = 1;
                }

                setTimeout('slideshows[' + slideCollection + '].transition();', 5000);
            }
        );
    };

    this.transition();
};

$(document).ready(
    function()
    {
        if ($('.slideshow').length)
        {
            $('.slideshow').slideshow();
        }
    }
);
