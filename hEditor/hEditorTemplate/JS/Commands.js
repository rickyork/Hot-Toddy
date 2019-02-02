$.fn.extend({

    commandEvent : function(fn, event)
    {
        if (this.parents('div.hEditorTemplateIslandWrapper').hasClass('hEditorTemplateDisabledButtons'))
        {
            return;
        }

        return fn.call(this, event);
    },

    commandOn : function()
    {
        return this.addClass('hEditorTemplateButtonActive');
    },

    commandOff : function()
    {
        return this.removeClass('hEditorTemplateButtonActive');
    },

    commandToggle : function(condition)
    {
        return condition? this.commandOn() : this.commandOff();
    },

    toggleTextDecoration : function(style)
    {
        var textDecoration = this.css('textDecoration');

        var underline   = (textDecoration.indexOf('underline') != -1);
        var lineThrough = (textDecoration.indexOf('line-through') != -1);
        var overline    = (textDecoration.indexOf('overline') != -1);
        var blink       = (textDecoration.indexOf('blink') != -1);

        var values = [];

        if (style == 'underline' && !underline || style != 'underline' && underline)
        {
            values.push('underline');
        }

        if (style == 'line-through' && !lineThrough || style != 'line-through' && lineThrough)
        {
            values.push('line-through');
        }

        if (style == 'overline' && !overline || style != 'overline' && overline)
        {
            values.push('overline');
        }

        if (style == 'blink' && !blink || style != 'blink' && blink)
        {
            values.push('blink');
        }

        return this.css('textDecoration', values.length? values.join(' ') : 'none');
    },

    toggleCommand : function(property, onState, offState)
    {
        return this.css(property, this.css(property) && this.css(property) == onState? offState : onState);
    },

    setFloat : function(float)
    {
        return this.parents('div.hEditorTemplateNodeWrapper:first').css({
            float : float.toLowerCase(),
            position : 'relative',
            zIndex : 200
        });
    },

    getWrapperNode : function()
    {
        if (this.hasClass('hEditorTemplateNodeWrapper'))
        {
            return this;
        }
        else
        {
            return this.parents('.hEditorTemplateNodeWrapper:first');
        }
    },

    commandState : function()
    {
        var node = this;

        if (this.hasClass('hEditorTemplateNodeWrapper'))
        {
            node = this.children(editor.nodes);
        }

        var nodeName = node.get(0).nodeName.toLowerCase();

        $('li#hEditorTemplateBold').commandToggle(
            node.parents('b').length ||
            nodeName == 'b' ||
            node.css('fontWeight') == 'bold'
        );

        $('li#hEditorTemplateItalic').commandToggle(
            node.parents('i').length ||
            nodeName == 'i' ||
            node.css('fontStyle') == 'italic'
        );

        $('li#hEditorTemplateUnderline').commandToggle(
            node.parents('u').length ||
            nodeName == 'u' ||
            node.css('textDecoration').indexOf('underline') != -1
        );

        $('li#hEditorTemplateStrikeThrough').commandToggle(
            node.parents('s, strike').length ||
            nodeName == 's' ||
            nodeName == 'strike' ||
            node.css('textDecoration').indexOf('line-through') != -1
        );

        var verticalAlign = node.css('verticalAlign');

        $('li#hEditorTemplateSubscript').commandToggle(
            node.parents('sub').length ||
            nodeName == 'sub' ||
            verticalAlign == 'bottom' ||
            verticalAlign == 'sub'
        );

        $('li#hEditorTemplateSuperscript').commandToggle(
            node.parents('sup').length ||
            nodeName == 'sup' ||
            verticalAlign == 'top' ||
            verticalAlign == 'super'
        );

        var textAlign = node.css('textAlign');

        $('li#hEditorTemplateAlignLeft').commandToggle(
            textAlign == 'left' ||
            !textAlign ||
            textAlign == '-webkit-auto'
        );

        $('li#hEditorTemplateAlignCenter').commandToggle(
            textAlign == 'center'
        );

        $('li#hEditorTemplateAlignRight')
            .commandToggle(textAlign == 'right');

        $('li#hEditorTemplateAlignJustify')
            .commandToggle(textAlign == 'justify');

        var float = node.getWrapperNode().css('float');

        $('li#hEditorTemplateFloatNone')
            .commandToggle(float == 'none' || !float || float == 'undefined');

        $('li#hEditorTemplateFloatLeft')
            .commandToggle(float == 'left');

        $('li#hEditorTemplateFloatRight')
            .commandToggle(float == 'right');

        $('li#hEditorTemplateP').commandToggle(
            node.parents('p').length ||
            nodeName == 'p'
        );

        $('li#hEditorTemplateUL').commandToggle(
            node.parents('ul:not(.hEditorTemplateNodeControls)').length ||
            nodeName == 'ul'
        );

        $('li#hEditorTemplateOL').commandToggle(
            node.parents('ol').length ||
            nodeName == 'ol'
        );

        $('li#hEditorTemplateH1').commandToggle(
            node.parents('h1').length ||
            nodeName == 'h1'
        );

        $('li#hEditorTemplateH2').commandToggle(
            node.parents('h2').length ||
            nodeName == 'h2'
        );

        $('li#hEditorTemplateH3').commandToggle(
            node.parents('h3').length ||
            nodeName == 'h3'
        );

        $('li#hEditorTemplateH4').commandToggle(
            node.parents('h4').length ||
            nodeName == 'h4'
        );

        $('li#hEditorTemplateH5').commandToggle(
            node.parents('h5').length ||
            nodeName == 'h5'
        );

        $('li#hEditorTemplateH6').commandToggle(
            node.parents('h6').length ||
            nodeName == 'h6'
        );

        $('li#hEditorTemplateLink').commandToggle(
            node.parents('a').length ||
            nodeName == 'a'
        );

        return node;
    }
});

$.extend(
    editor, {
        commandReady : function()
        {
            $(document)
                .keypress(
                    function(event)
                    {
                        var node = editor.getNodeAtCaretPosition();

                        if (node && node.length && node.parents('div#hEditorTemplateDocumentWrapper').length)
                        {
                            node.commandState();
                        }
                    }
                )
                .keyup(
                    function(event)
                    {
                        // keyup reports target node is the contentEditable div, rather than the immediate node
                        // the user is typing within.  This is a work-around.
                        var node = editor.getNodeAtCaretPosition();

                        if (node && node.length && node.parents('div#hEditorTemplateDocumentWrapper').length)
                        {
                            node.commandState();
                        }
                    }
                )
                .mouseup(
                    function(event)
                    {
                        if ($(event.target).parents('div#hEditorTemplateDocumentWrapper').length)
                        {
                            $(event.target).commandState(event);
                        }
                    }
                );

            if (this.title.length)
            {
                this.title.mouseup(
                    function()
                    {
                        $(this).commandState();
                    }
                );
            }

            $(document).on(
                'click',
                'div.hEditorContentEditableButtons button, .hEditorTemplateNodeControl button',
                function(event)
                {
                    event.preventDefault();

                    var islandWrapper = $(this).parents('div.hEditorTemplateIslandWrapper');

                    if (islandWrapper.length && !islandWrapper.hasClass('hEditorTemplateDisabledButtons') || !islandWrapper.length)
                    {
                        editor.executeCommand(this.value, $(this));
                    }
                }
            );
        },

        executeCommand : function(command, eventNode)
        {
            if (!this.isEditable)
            {
                return;
            }

            switch (command)
            {
                case 'remove':
                {
                    this.eventNode = eventNode;

                    dialogue.confirm(
                        {
                            title : "Confirm Remove Content",
                            label : "<p>Are you sure you want to remove this content?</p>" +
                                    "<p>This cannot be undone.</p>",
                            ok : "Remove Content",
                            cancel : "Don't Remove Content"
                        },
                        function(confirm)
                        {
                            if (confirm)
                            {
                                hot.unselect('hEditorTemplateNode');
                                editor.setPreviewLabel('Nothing');

                                editor.eventNode
                                      .parents('div.hEditorTemplateNodeWrapper:first')
                                      .remove();
                            }
                        }
                    );

                    return;
                };
            }

            var argument = null;

            // If the text caret is in an editable area, this method will return the present node the caret
            // resides within.
            var node = this.getNodeAtCaretPosition();

            // If there is no node at the caret position, perhaps there is an editor node the user has selected
            // by clicking on it.
            if (!node && hot.selected('hEditorTemplateNode').length)
            {
                node = hot.selected('hEditorTemplateNode').children(this.nodes);
            }

            var nodeName = '';

            if (node && node.length)
            {
                nodeName = node.get(0).nodeName.toLowerCase();
            }

            switch (command)
            {
                case 'h1':
                case 'h2':
                case 'h3':
                case 'h4':
                case 'h5':
                case 'h6':
                case 'p':
                case 'ul':
                case 'ol':
                {
                    this.transformBlock(command);
                    command = '';
                    break;
                };
                case 'outdent':
                {
                    if (node && node.length)
                    {
                        var parentCount = node.parents('blockquote').length;

                        if (nodeName == 'blockquote' && !parentCount || nodeName != 'blockquote' && parentCount == 1)
                        {
                            this.transformBlock('p');
                            command = '';
                        }
                    }
                    break;
                };
                case 'link':
                {
                    editor.link.openDialogue();
                    break;
                };
                case 'image':
                {
                    editor.image.openDialogue();
                    break;
                };
                case 'movie':
                {
                    editor.movie.openDialogue();
                    break;
                };
                default:
                {

                };
            }

            var commandState = true;

            if (command)
            {
                switch (command)
                {
                    case 'justifyLeft':
                    {
                        node.css('textAlign', 'left');
                        break;
                    }
                    case 'justifyCenter':
                    {
                        node.css('textAlign', 'center');
                        break;
                    }
                    case 'justifyRight':
                    {
                        node.css('textAlign', 'right');
                        break;
                    }
                    case 'justifyFull':
                    {
                        node.css('textAlign', 'justify');
                        break;
                    }
                    case 'floatLeft':
                    {
                        node.setFloat('left');
                        break;
                    }
                    case 'floatRight':
                    {
                        node.setFloat('right');
                        break;
                    }
                    case 'floatNone':
                    {
                        node.setFloat('none');
                        break;
                    }
                    default:
                    {
                        // If a selection exists, i.e., there is a highlighted bit of text the user has
                        // selected with their mouse, that should take precedence over other types of
                        // selection.
                        if (this.selectionExists())
                        {
                            commandState = false;
                            document.execCommand(command, false, argument);
                        }
                        else
                        {
                            switch (command)
                            {
                                case 'bold':
                                {
                                    node.toggleCommand('fontWeight', 'bold', 'normal');
                                    break;
                                };
                                case 'italic':
                                {
                                    node.toggleCommand('fontStyle', 'italic', 'normal');
                                    break;
                                };
                                case 'underline':
                                {
                                    node.toggleTextDecoration('underline');
                                    break;
                                };
                                case 'strikeThrough':
                                {
                                    node.toggleTextDecoration('line-through');
                                    break;
                                };
                                case 'subscript':
                                {
                                    node.toggleCommand('verticalAlign', 'sub', 'baseline');
                                    break;
                                };
                                case 'superscript':
                                {
                                    node.toggleCommand('verticalAlign', 'super', 'baseline');
                                    break;
                                };
                            }
                        }
                    }
                }

                // Webkit places the <ul> and <ol> elements inside of <p> elements, this hack gets rid of the
                // <p> element wrapper.
                if (this.document.find('ul:not(.hEditorTemplateNodeControls):first, ol:first').length)
                {
                    this.document.find('ul:not(.hEditorTemplateNodeControls):first, ol:first').each(
                        function()
                        {
                            if ($(this).parent('p').length)
                            {
                                $(this).parent('p').outerHTML($(this).outerHTML());
                            }
                        }
                    );
                }

                // This hack clears up some odd markup nesting, and makes sure news element have
                // editing controls enabled.
                this.toWYSIWYG();

                if (!commandState)
                {
                    if (!document.queryCommandState(command))
                    {
                        $(this).parent().commandOff();

                    }
                    else
                    {
                        $(this).parent().commandOn();
                    }
                }
                else
                {
                    node.commandState();
                }
            }
        }
    }/*,

    command : function(command)
    {
        document.execCommand(command, false, null);
    }
    */
);

$(document).ready(
    function()
    {
        editor.commandReady();
    }
);