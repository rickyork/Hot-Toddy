/**
* Provides a fade transition, which disables all content.
*
* New content can be placed on top of the fade layer, which gives it the
* appearance of being in focus.
*/

var fade = {

    faded : false,

    obj : {},

    prep : function()
    {
        if (!$('div#hFade').length)
        {
            $('body').append(
                $('<div/>').attr('id', 'hFade')
            );

            $('div#hFade').css({
                position: 'fixed',
                top: 0,
                right: 0,
                bottom: 0,
                left: 0,
                width: '100%',
                height: '100%',
                zIndex: 999
            });
        }

        this.obj = $('div#hFade');

        scrollTo(0, 0);
    },

    getDefaultOptions : function()
    {
        var options = arguments[0] !== undefined ? arguments[0] : {};
        var defaults = arguments[1] !== undefined ? arguments[1] : {};

        if (options.opacity === undefined && hot.fadeOpacity !== undefined)
        {
            options.opacity = hot.fadeOpacity;
        }

        options.opacity = options.opacity ? options.opacity : defaults.opacity;

        if (options.animated === undefined && hot.animationEnabled !== undefined)
        {
            options.animated = hot.animationEnabled;
        }

        options.animated = options.animated !== undefined ? options.animated : true;

        if (options.animationSpeed === undefined)
        {
            options.animationSpeed = 'slow';
        }

        if (options.animation === undefined)
        {
            options.animation = {};
        }

        if (options.background === undefined)
        {
            options.background = 'white';
        }

        if (options.css === undefined)
        {
            options.css = {};
        }

        if (options.callbackContext === undefined)
        {
            options.callbackContext = null;
        }

        if (options.callback === undefined)
        {
            options.callback = function()
            {

            };
        }

        options.animationCallback = function()
        {
            if (options.callbackContext)
            {
                options.callback.apply(options.callbackContext, arguments);
            }
            else
            {
                options.callback.apply(this, arguments);
            }
        };

        return options;
    },

    begin : function()
    {
        if (arguments[0] === undefined)
        {
            arguments[0] = {};
        }

        if (arguments[1] === undefined)
        {
            arguments[1] = {
                opacity : 0.95
            };
        }

        var options = this.getDefaultOptions.apply(this.obj, arguments);

        this.prep();

        if (options.animated)
        {
            this.obj.animate(
                $.extend(
                    {
                        opacity : options.opacity
                    },
                    options.animation
                ),
                options.animationSpeed,
                options.animationCallback
            );
        }
        else
        {
            this.obj.css(
                $.extend(
                    {
                        display : 'block',
                        opacity : options.opacity,
                        background : options.background
                    },
                    options.css
                )
            );
        }

        this.faded = true;
    },

    beginReverse : function()
    {
        if (arguments[0] === undefined)
        {
            arguments[0] = {};
        }

        if (arguments[1] === undefined)
        {
            arguments[1] = {
                opacity : 0
            };
        }

        var options = this.getDefaultOptions.apply(this.obj, arguments);

        this.prep();

        if (this.obj.length)
        {
            if (options.animated)
            {
                this.obj.animate(
                    $.extend(
                        {
                            opacity : options.opacity
                        },
                        options.animation
                    ),
                    options.animationSpeed,
                    function()
                    {
                        $(this).hide();
                        options.animationCallback.apply($(this), arguments);
                    }
                );
            }
            else
            {
                this.obj.css(
                    $.extend(
                        {
                            display : 'none',
                            opacity : 0
                        },
                        options.css
                    )
                );
            }

            this.faded = false;
        }
    },

    hasFaded : function()
    {
        return this.faded;
    },

    end : function()
    {
        this.beginReverse();
        this.faded = false;
    }
};