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
        var textDecoration = this.css('text-decoration');

        var underline = (
            textDecoration.indexOf('underline') !== -1
        );

        var lineThrough = (
            textDecoration.indexOf('line-through') !== -1
        );

        var overline = (
            textDecoration.indexOf('overline') !== -1
        );

        var blink = (
            textDecoration.indexOf('blink') !== -1
        );

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

        return this.css('text-decoration', values.length ? values.join(' ') : 'none');
    },

    toggleCommand : function(property, onState, offState)
    {
        var value = onState;

        if (this.css(property) && this.css(property) == onState)
        {
            value = offState;
        }

        return this.css(property, value);
    },

    setFloat : function(cssFloat)
    {
        return this.parents('div.hEditorTemplateNodeWrapper:first').css({
            'float' : cssFloat.toLowerCase(),
            'position' : 'relative',
            'z-index' : 200
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

        var isBold = false;

        var fontWeight = node.css('font-weight');

        if (fontWeight == 'bold' || fontWeight == 'bolder')
        {
            isBold = true;
        }
        else if (!isNaN(parseInt(fontWeight)) && parseInt(fontWeight) > 400)
        {
            isBold = true;
        }

        editor.$('li#hEditorTemplateBold').commandToggle(
            node.parents('b').length ||
            node.is('b') ||
            isBold
        );

        editor.$('li#hEditorTemplateItalic').commandToggle(
            node.parents('i').length ||
            node.is('i') ||
            node.css('font-style') == 'italic'
        );

        editor.$('li#hEditorTemplateUnderline').commandToggle(
            node.parents('u').length ||
            node.is('u') ||
            node.css('text-decoration').indexOf('underline') !== -1
        );

        editor.$('li#hEditorTemplateStrikeThrough').commandToggle(
            node.parents('s, strike').length ||
            node.is('s, strike') ||
            node.css('text-decoration').indexOf('line-through') !== -1
        );

        var verticalAlign = node.css('vertical-align');

        editor.$('li#hEditorTemplateSubscript').commandToggle(
            node.parents('sub').length ||
            node.is('sub') ||
            verticalAlign == 'bottom' ||
            verticalAlign == 'sub'
        );

        editor.$('li#hEditorTemplateSuperscript').commandToggle(
            node.parents('sup').length ||
            node.is('sup') ||
            verticalAlign == 'top' ||
            verticalAlign == 'super'
        );

        var textAlign = node.css('text-align');

        editor.$('li#hEditorTemplateAlignLeft').commandToggle(
            textAlign == 'start' ||
            textAlign == 'left' ||
            !textAlign ||
            textAlign == '-webkit-auto'
        );

        editor.$('li#hEditorTemplateAlignCenter').commandToggle(
            textAlign == 'center'
        );

        editor.$('li#hEditorTemplateAlignRight')
            .commandToggle(textAlign == 'right');

        editor.$('li#hEditorTemplateAlignJustify')
            .commandToggle(textAlign == 'justify');

        var cssFloat = node.getWrapperNode().css('float');

        editor.$('li#hEditorTemplateFloatNone')
            .commandToggle(
                cssFloat === 'none' ||
                !cssFloat ||
                typeof cssFloat === 'undefined'
            );

        editor.$('li#hEditorTemplateFloatLeft')
            .commandToggle(cssFloat == 'left');

        editor.$('li#hEditorTemplateFloatRight')
            .commandToggle(cssFloat == 'right');

        editor.$('li#hEditorTemplateP').commandToggle(
            node.parents('p').length ||
            node.is('p')
        );

        editor.$('li#hEditorTemplateUL').commandToggle(
            node.parents('ul:not(.hEditorTemplateNodeControls)').length ||
            node.is('ul')
        );

        editor.$('li#hEditorTemplateOL').commandToggle(
            node.parents('ol').length ||
            node.is('ol')
        );

        editor.$('li#hEditorTemplateH1').commandToggle(
            node.parents('h1').length ||
            node.is('h1')
        );

        editor.$('li#hEditorTemplateH2').commandToggle(
            node.parents('h2').length ||
            node.is('h2')
        );

        editor.$('li#hEditorTemplateH3').commandToggle(
            node.parents('h3').length ||
            node.is('h3')
        );

        editor.$('li#hEditorTemplateH4').commandToggle(
            node.parents('h4').length ||
            node.is('h4')
        );

        editor.$('li#hEditorTemplateH5').commandToggle(
            node.parents('h5').length ||
            node.is('h5')
        );

        editor.$('li#hEditorTemplateH6').commandToggle(
            node.parents('h6').length ||
            node.is('h6')
        );

        editor.$('li#hEditorTemplateLink').commandToggle(
            node.parents('a').length ||
            node.is('a')
        );

        return node;
    }
});

$.extend(

    editor, {

        commandReady : function()
        {
            $(document)
                .on(
                    'input.editorCommandState',
                    //'keydown.editorCommandState ' +
                    //'keypress.editorCommandState ' +
                    //'keyup.editorCommandState',
                    function(event)
                    {
                        var node = editor.getNodeAtCaretPosition();

                        if (node && node.length && node.parents('div#hEditorTemplateDocumentWrapper').length)
                        {
                            node.commandState();
                        }
                    }
                )
                //.on(
                //    'keyup.editorCommandState',
                //    function(event)
                //    {
                //        // keyup reports target node is the contentEditable div, rather than the immediate node
                //        // the user is typing within.  This is a work-around.
                //        var node = editor.getNodeAtCaretPosition();
                //
                //        if (node && node.length && node.parents('div#hEditorTemplateDocumentWrapper').length)
                //        {
                //            node.commandState();
                //        }
                //    }
                //)
                .on(
                    'mouseup.editorCommandState',
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
                this.title.on(
                    'mouseup.editorCommandState',
                    function()
                    {
                        $(this).commandState();
                    }
                );
            }

            editor.$('document').on(
                'click.editorCommand',
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
                        node.css('text-align', 'left');
                        break;
                    }
                    case 'justifyCenter':
                    {
                        node.css('text-align', 'center');
                        break;
                    }
                    case 'justifyRight':
                    {
                        node.css('text-align', 'right');
                        break;
                    }
                    case 'justifyFull':
                    {
                        node.css('text-align', 'justify');
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
                                    node.toggleCommand('font-weight', 'bold', 'normal');
                                    break;
                                };
                                case 'italic':
                                {
                                    node.toggleCommand('font-style', 'italic', 'normal');
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
                                    node.toggleCommand('vertical-align', 'sub', 'baseline');
                                    break;
                                };
                                case 'superscript':
                                {
                                    node.toggleCommand('vertical-align', 'super', 'baseline');
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
                    this.document
                        .find('ul:not(.hEditorTemplateNodeControls):first, ol:first')
                        .each(
                            function()
                            {
                                if ($(this).parent('p').length)
                                {
                                    $(this)
                                        .parent('p')
                                        .outerHTML($(this).outerHTML());
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

$(document).on(
    'ready.editorCommands',
    function()
    {
        editor.commandReady();
    }
);