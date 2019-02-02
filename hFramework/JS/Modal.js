var modal = {

    overlayed : false,

    overlay : {},

    create : function()
    {
        if (!$('div#hDialogueModalOverlay').length)
        {
            $('body').append(
                $('<div/>')
                    .attr('id', 'hDialogueModalOverlay')
                    .addClass('hDialogueModalOverlay')
                    .addClass('hDialogueModalOverlayOff')
                    .css({
                        position : 'fixed',
                        top : 0,
                        right : 0,
                        bottom : 0,
                        left : 0,
                        width : '100%',
                        height : '100%',
                        zIndex : 999
                    })
            );
        }

        this.overlay = $('div#hDialogueModalOverlay');

        scrollTo(0, 0);
    },

    getDefaultOptions : function()
    {
        var options = typeof arguments[0] !== 'undefined' ? arguments[0] : {};
        var defaults = typeof arguments[1] !== 'undefined' ? arguments[1] : {};

        if (typeof options.animated === 'undefined' && typeof hot.animationEnabled !== 'undefined')
        {
            options.animated = hot.animationEnabled;
        }

        if (typeof options.animated === 'undefined' || typeof options.animated !== 'boolean')
        {
            options.animated = typeof defaults.animated !== 'undefined' ? defaults.animated : true;
        }

        if (typeof options.animationSpeed === 'undefined' || typeof options.animationSpeed !== 'string')
        {
            options.animationSpeed = typeof defaults.animationSpeed !== 'undefined' ? defaults.animationSpeed : 'slow';
        }

        if (typeof options.animation === 'undefined' || typeof options.animation !== 'object')
        {
            options.animation = typeof defaults.animation !== 'undefined' ? defaults.animation : {};
        }

        if (typeof options.background === 'undefined' || typeof options.background !== 'string')
        {
            options.background = typeof defaults.background !== 'undefined' ? defaults.background : 'white';
        }

        if (typeof options.css === 'undefined' || typeof options.css !== 'object')
        {
            options.css = typeof defaults.css !== 'undefined' ? defaults.css : {};
        }

        if (typeof options.opacity === 'undefined')
        {
            options.opacity = typeof defaults.opacity !== 'undefined' ? defaults.opacity : 0;
        }

        if (typeof options.callbackContext === 'undefined')
        {
            options.callbackContext = typeof defaults.callbackContext !== 'undefined' ? defaults.callbackContext : null;
        }

        if (typeof options.callback === 'undefined' || typeof options.callback !== 'function')
        {
            options.callback = typeof defaults.callback !== 'undefined' ? defaults.callback : function() {};
        }

        options.animationCallback = function(options)
        {
            if (options.callbackContext)
            {
                options.callback.apply(options.callbackContext, arguments);
            }
            else
            {
                options.callback.apply($(this), arguments);
            }
        };

        return options;
    },

    show : function()
    {
        var options = typeof arguments[0] !== 'undefined' ? arguments[0] : {};

        if (typeof options !== 'object')
        {
            options = {};
        }

        this.fadeIn.apply(
            this, [
                $.extend(
                    {
                        animated : false
                    },
                    options
                ),
                typeof arguments[1] !== 'undefined' ? arguments[1] : {}
            ]
        );
    },

    hide : function()
    {
        var options = typeof arguments[0] !== 'undefined' ? arguments[0] : {};

        if (typeof options !== 'object')
        {
            options = {};
        }

        this.fade.out.apply(
            this, [
                $.extend(
                    {
                        animated : false
                    },
                    options
                ),
                typeof arguments[1] !== 'undefined' ? arguments[1] : {}
            ]
        );
    },

    on : function()
    {
        this.fadeIn.apply(this, arguments);
    },

    off : function()
    {
        this.fadeOut.apply(this, arguments);
    },

    fadeIn : function()
    {
        var args = [];

        args[0] = typeof arguments[0] === 'undefined' ? {} : arguments[0];

        if (typeof arguments[1] === 'undefined')
        {
            args[1] = {
                opacity : 0.75,
                background : '#fff'
            };
        }
        else
        {
            args[1] = arguments[1];
        }

        var options = this.getDefaultOptions.apply(this.overlay, args);

        this.create();

        if (options.animated)
        {
            this.overlay
                .css(
                    $.extend(
                        {
                            opacity : 0,
                            background : options.background,
                            display : 'block'
                        },
                        options.css
                    )
                )
                .animate(
                    $.extend(
                        {
                            opacity : options.opacity
                        },
                        options.animation
                    ),
                    options.animationSpeed,
                    function()
                    {
                        $(this).addClass('hDialogueModalOverlayOn')
                               .removeClass('hDialogueModalOverlayOff');

                        options.animationCallback.call($(this), options);

                        modal.overlayed = true;
                    }
                );
        }
        else
        {
            this.overlay
                .css(
                    $.extend(
                        {
                            display : 'block',
                            opacity : options.opacity,
                            background : options.background
                        },
                        options.css
                    )
                )
                .addClass('hDialogueModalOverlayOn')
                .removeClass('hDialogueModalOverlayOff');

            this.overlayed = true;
        }
    },

    fadeOut : function()
    {
        if (typeof arguments[0] === 'undefined')
        {
            arguments[0] = {};
        }

        if (typeof arguments[1] === 'undefined')
        {
            arguments[1] = {
                opacity : 0
            };
        }

        var options = this.getDefaultOptions.apply(this.overlay, arguments);

        this.create();

        if (this.overlay && this.overlay.length)
        {
            if (options.animated)
            {
                this.overlay
                    .css(
                        $.extend(
                            {
                                background : options.background
                            },
                            options.css
                        )
                    )
                    .animate(
                        $.extend(
                            {
                                opacity : options.opacity
                            },
                            options.animation
                        ),
                        options.animationSpeed,
                        function()
                        {
                            $(this)
                                .hide()
                                .removeClass('hDialogueModalOverlayOn')
                                .addClass('hDialogueModalOverlayOff');

                            options.animationCallback.call($(this), options);

                            modal.overlayed = false;
                        }
                    );
            }
            else
            {
                this.overlay
                    .css(
                        $.extend(
                            {
                                display : 'none',
                                opacity : 0
                            },
                            options.css
                        )
                    )
                    .removeClass('hDialogueModalOverlayOn')
                    .addClass('hDialogueModalOverlayOff');

                this.overlayed = false;
            }
        }
    }
};