$.fn.extend({
    stepBackward : function()
    {
        if (this.hasClass('hFinderBackOn'))
        {
            finder.history.stepBackward();
        }

        return this;
    },

    stepForward : function()
    {
        if (this.hasClass('hFinderForwardOn'))
        {
            finder.history.stepForward();
        }

        return this;
    }
});

finder.history = {
    history : [],
    back : false,
    forward : false,
    cursor : 0,

    ready : function()
    {
        $('li#hFinder-Back')
            .mousedown(
                function()
                {
                    if ($(this).hasClass('hFinderBackOn'))
                    {
                        $(this).addClass('hFinderBackActive');
                    }
                }
            )
            .mouseup(
                function()
                {
                    $(this).removeClass('hFinderBackActive');
                }
            )
            .click(
                function()
                {
                    $(this).stepBackward();
                }
            );

        $('li#hFinder-Forward')
            .mousedown(
                function()
                {
                    if ($(this).hasClass('hFinderForwardOn'))
                    {
                        $(this).addClass('hFinderForwardActive');
                    }
                }
            )
            .mouseup(
                function()
                {
                    $(this).removeClass('hFinderForwardActive');
                }
            )
            .click(
                function()
                {
                    $(this).stepForward();
                }
            );

        this.history.push(finder.path);
        this.cursor = 0;

        hot.event(
            'requestDirectory',
            function()
            {
                if (this.back || this.forward)
                {
                    this.back = false;
                    this.forward = false;
                }
                else if (finder.path != this.history[this.history.length - 1])
                {
                    // Cut off the forward history when a new request gets made.
                    if (this.cursor < this.history.length)
                    {
                        history = [];

                        for (var i = 0; i <= this.cursor; i++)
                        {
                            history[i] = this.history[i];
                        }

                        this.history = history;
                    }

                    var history = this.history;

                    this.history.push(finder.path);
                    this.cursor = this.history.length - 1;
                }

                var back = '';

                if (!this.cursor)
                {
                    $('li#hFinder-Back').removeClass('hFinderBackOn');
                }
                else if (this.history.length > 1)
                {
                    // Enabled
                    $('li#hFinder-Back').addClass('hFinderBackOn');
                }
                else
                {
                    $('li#hFinder-Back').removeClass('hFinderBackOn');
                }

                var forward = '';

                if (this.cursor == this.history.length - 1)
                {
                    $('li#hFinder-Forward').removeClass('hFinderForwardOn');
                }
                else if (this.cursor < this.history.length)
                {
                    $('li#hFinder-Forward').addClass('hFinderForwardOn');
                }
                else
                {
                    $('li#hFinder-Forward').removeClass('hFinderForwardOn');
                }
            },
            finder.history
        );
    },

    stepBackward : function()
    {
        this.back = true;

        this.cursor--;

        if (this.cursor >= 0 && this.cursor < this.history.length)
        {
            finder.requestDirectory(this.history[this.cursor]);
        }
    },

    stepForward : function()
    {
        this.forward = true;

        this.cursor++;

        if (this.cursor >= 0 && this.cursor < this.history.length)
        {
            finder.requestDirectory(this.history[this.cursor]);
        }
    }
};