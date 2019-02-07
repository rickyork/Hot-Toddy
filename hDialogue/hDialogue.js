if (typeof dialogue === 'undefined')
{
    var dialogue = {};
}

$.fn.extend({

    setTitlebar : function(title)
    {
        return (
            this.find('h4.hDialogueTitlebar span')
                .html(title)
        );
    },

    unselectTab : function()
    {
        hot.unselect('hDialogueTab');

        hot.unselect('hApplicationUIButtonSegmentedAqua');

        hot.selected('hDialoguePane')
           .hide();

        if (this.splitId())
        {
            hot.unselect('hDialoguePane');

            hot.fire(
                'hDialogueUnselectTab', {
                    id : id,
                    label : this.find('span').text()
                }
            );
        }

        return this;
    },

    selectTab : function()
    {
        if (this && this.length)
        {
            this.select('hDialogueTab');
            this.select('hApplicationUIButtonSegmentedAqua');

            var selected = hot.selected('hDialoguePane');

            var id = this.splitId();

            if (selected && selected.length && selected.hide)
            {
                selected.hide();
            }

            if (id)
            {
                $('div#' + id)
                    .select('hDialoguePane')
                    .show();
            }

            hot.fire(
                'hDialogueSelectTab', {
                    id : id,
                    label : this.find('span').text()
                }
            );
        }

        return this;
    },

    openDialogue : function()
    {
        var options = dialogue.getOptions.apply(
            this,
            arguments
        );

        if (options.isModal)
        {
            modal.fadeIn();
        }

        if (this.find('div.hFormDivision').length == 1)
        {
            this.find('div.hFormDivision')
                .show();
        }
        else
        {
            this.find('div.hFormDivision:first')
                .show();
        }

        this.addClass('hDialogueOpen')
            .setFocus();

        if (options.animated)
        {
            switch (options.animationType.toLowerCase())
            {
                case 'slide':
                {
                    this.slideDown(
                        options.animationSpeed,
                        function()
                        {
                            $(this).css('display', 'block');

                            options.callbackContainer.apply(
                                $(this),
                                arguments
                            );
                        }
                    );

                    break;
                }
                case 'custom':
                {
                    this.animate(
                        options.animation,
                        options.animationSpeed,
                        function()
                        {
                            $(this).css('display', 'block');

                            options.callbackContainer.apply(
                                $(this),
                                arguments
                            );
                        }
                    );

                    break;
                }
                case 'fade':
                default:
                {
                    this.fadeIn(
                        options.animationSpeed,
                        function()
                        {
                            $(this).css('display', 'block');

                            options.callbackContainer.apply(
                                $(this),
                                arguments
                            );
                        }
                    );

                    break;
                }

            }
        }
        else
        {
            this.css('display', 'block');

            if (options.css && typeof options.css === 'object')
            {
                this.css(options.css);
            }

            options.callbackContainer.apply(
                this,
                arguments
            );
        }

        return this;
    },

    closeDialogue : function()
    {
        var options = dialogue.getOptions.apply(
            this,
            arguments
        );
        
        if (this.find('ul.hDialogueTabs').length)
        {
            this.find('ul.hDialogueTabs')
                .children('li.hDialogueTab')
                .first()
                    .select('hDialogueTab')
                    .click();
        }

        // Whether or not the dialogue is modal
        if (options.isModal)
        {
            modal.fadeOut();
        }

        var id = this.attr('id');

        hot.fire(id + 'Close');

        this.removeClass('hDialogueOpen');

        if (options.animated)
        {
            switch (options.animationType)
            {
                case 'fade':
                {
                    this.fadeOut(
                        options.animationSpeed,
                        options.callback
                    );

                    break;
                }
                case 'slide':
                {
                    this.slideUp(
                        options.animationSpeed,
                        options.callback
                    );

                    break;
                }
                case 'custom':
                {
                    this.animate(
                        options.animation,
                        options.animationSpeed,
                        options.callback
                    );

                    break;
                }
            }
        }
        else
        {
            this.hide();

            options.callback.apply(this, arguments);
        }

        return this;
    },

    toggleDialogue : function()
    {
        if (!arguments[0])
        {
            arguments[0] = null;
        }

        if (this.hasClass('hDialogueOpen'))
        {
            return this.closeDialogue(arguments[0]);
        }
        else
        {
            return this.openDialogue(arguments[0]);
        }
    },

    setFocus : function()
    {
        if (this.hasClass('hDialogueFullScreen') || this.hasClass('hDialogueDisableFocus'))
        {
            return this;
        }

        if (dialogue.focused)
        {
            dialogue.focused
                .css('z-index', 1000)
                .removeClass('hDialogueActive')
                .addClass('hDialogueInactive');
        }

        var inactive = dialogue.focused;

        dialogue.focused = this;

        this.css('z-index', 1200)
            .removeClass('hDialogueInactive')
            .addClass('hDialogueActive');

        return this;
    }
});

$.extend(
    dialogue, {
        dragging : false,
        onClose : [],
        onCloseContext : [],

        options : {

        },

        getOptions : function()
        {
            var options = {};

            if (typeof arguments[0] !== 'undefined')
            {
                switch (typeof arguments[0])
                {
                    case 'object':
                    {
                        options = arguments[0];

                        break;
                    }
                    case 'string':
                    {
                        options.title = arguments[0];
                        this.setTitlebar(options.title);

                        break;
                    }
                    case 'boolean':
                    {
                        options.isModal = true;

                        break;
                    }
                }
            }

            if (!options || typeof options != 'object')
            {
                options = {};
            }

            if (typeof arguments[1] !== 'undefined' && typeof arguments[1] == 'function')
            {
                options.callback = arguments[1];
            }

            if (typeof arguments[2] !== 'undefined')
            {
                options.callbackContext = arguments[2];
            }

            if (!options.callback || typeof options.callback === 'undefined')
            {
                options.callback = function()
                {

                };
            }

            if (typeof options.callbackContext === 'undefined')
            {
                options.callbackContext = null;
            }

            var node = this;

            options.callbackContainer = function()
            {
                if (options.callbackContext)
                {
                    options.callback.apply(
                        options.callbackContext,
                        arguments
                    );
                }
                else
                {
                    options.callback.apply(
                        node,
                        arguments
                    );
                }
            };

            if (typeof options.animated === 'undefined' && typeof hot.animationEnabled !== 'undefined')
            {
                options.animated = hot.animationEnabled;
            }

            options.animated = options.animated ? options.animated : false;

            if (typeof options.animationType === 'undefined')
            {
                options.animationType = 'fade';
            }

            if (typeof options.animationSpeed === 'undefined')
            {
                options.animationSpeed = 'normal';
            }

            if (typeof options.css === 'undefined')
            {
                options.css = {};
            }

            return options;
        },

        ready : function()
        {
            var counter = 0;

            $(document)
                .on(
                    'mouseenter.dialogueTab',
                    'li.hDialogueTab',
                    function()
                    {
                        $(this).addClass('hDialogueTabOn');
                    }
                )
                .on(
                    'mouseleave.dialogueTab',
                    'li.hDialogueTab',
                    function()
                    {
                        $(this).removeClass('hDialogueTabOn');
                    }
                )
                .on(
                    'click.dialogueTab',
                    'li.hDialogueTab',
                    function()
                    {
                        $(this).selectTab();
                    }
                );

            if (typeof hot.dialogueAutoSelect === 'undefined')
            {
                $('li.hDialogueTab:first')
                    .selectTab();
            }

            $(document)
                .on(
                    'mousedown.dialogue',
                    'form.hDialogue',
                    function()
                    {
                        $(this).setFocus();
                    }
                )
                .on(
                    'mousedown.dialogueTitlebar',
                    'h4.hDialogueTitlebar, ' +
                        '.hDialogueTitlebarLeft, ' +
                        '.hDialogueTitlebarRight',
                    function(event)
                    {
                        $(this)
                            .parents('.hDialogue:first')
                            .setFocus();

                        var offset = dialogue.focused.offset();

                        dialogue.dragging = true;

                        dialogue.coordinates = drag.getMouseCoordinates(event);

                        dialogue.position = {
                            x : offset.left,
                            y : offset.top
                        };

                        event.preventDefault();
                    }
                )
                .on(
                    'click.dialogueClose',
                    'form.hDialogue span.hDialogueClose',
                    function()
                    {
                        if (!$(this).hasClass('hDialogueDisabled'))
                        {
                            if (modal && modal.overlayed)
                            {
                                $(this)
                                    .parents('.hDialogue:first')
                                    .closeDialogue(true);
                            }
                            else
                            {
                                $(this)
                                    .parents('.hDialogue:first')
                                    .closeDialogue();
                            }
                        }
                    }
                );

            $(document).bind(
                'mousemove.dialogueDrag',
                function(event)
                {
                    dialogue.drag(event);
                },
                true
            );
        },

        drag : function(event)
        {
            if (this.dragging && this.focused)
            {
                var mouse = drag.getMouseCoordinates(event);

                this.focused.css({
                    top : (
                        this.position.y + (
                            mouse.y - this.coordinates.y
                        )
                    ) + 'px',
                    left : (
                        this.position.x + (
                            mouse.x - this.coordinates.x
                        )
                    ) + 'px',
                    margin : 0
                });
            }
        }
    }
);

/*
$(window).on(
    'load.dialogueTouchScroll',
    function()
    {
        if (hot.userAgentOS == 'iOS')
        {
            setTimeout(
                function()
                {
                    $('div.hApplicationUIButtonSegmentedAquaContentWrapper')
                        .touchScroll();
                },
                100
            );
        }
    }
);
*/

$(document).on(
    'ready.dialogue',
    function()
    {
        dialogue.ready();
    }
);

$(document).on(
    'selectstart.dialogue',
    function(event)
    {
        if (dialogue.dragging)
        {
            event.preventDefault();
        }
    }
);

$(document).on(
    'mouseup.dialogueDragging',
    function()
    {
        if (dialogue.dragging)
        {
            dialogue.dragging = false;
            dialogue.coordinates = 0;
        }
    }
);
