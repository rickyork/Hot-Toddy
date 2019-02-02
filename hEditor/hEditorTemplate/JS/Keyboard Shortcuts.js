$.extend(
    editor, {
        keyboardShortcutsReady : function()
        {
            $(document)
                .keydown(
                    function(event)
                    {
                        editor.shortcuts(event);
                    }
                )
                .keypress(
                    function(event)
                    {
                        editor.shortcuts(event);
                        editor.setSizeAndPosition();
                    }
                )
                .keyup(
                    function(event)
                    {
                        editor.keyupShortcuts(event);
                    }
                );

            $(document).on(
                'keypress',
                this.documentSelector,
                function(event)
                {
                    editor.keyboard(event);
                }
            );
        },

        keyboard : function(event)
        {
            if (!this.isEditable)
            {
                return;
            }

            if (event.keyCode == 46 || event.keyCode == 8)
            {
                // 46 = Delete
                // 8  = Backspace
                //
                // If there are no "editable" nodes in an editor control, this forces deletion
                // of the control.
                this.document.find('div.hEditorTemplateNodeWrapper').each(
                    function()
                    {
                        if (!$(this).find(editor.nodes).length)
                        {
                            $(this).remove();
                        }
                    }
                );
            }
        },

        keyupShortcuts : function(event)
        {
            var node = null;

            switch (event.keyCode)
            {
                // Allow the browser to do the default action when pressing return,
                // which handles the nasty business of splitting up the content at
                // the caret position.
                //
                // In terms of webkit, when the user presses return, the browser
                // inserts a new <div> node.  I imagine this will be ugly in gecko,
                // and will need further revision.
                case 13: // Return / Enter
                {
                    // A new node has been inserted.  Let's find that lucky stiff,
                    // move it where it belongs and transform it to look like its
                    // parent.

                    // As luck would have it, the caret is inside of this shiny new
                    // DOM node.
                    if (event.metaKey || event.ctrlKey)
                    {
                        event.preventDefault();
                    }

                    var node = this.getNodeAtCaretPosition();

                    if (node)
                    {
                        if (node.parents('tr').length)
                        {
                            var tr = node.parents('tr:first');

                            var trClone = tr.clone(true);

                            if (trClone.attr('id'))
                            {
                                trClone.attr('id', '');
                            }

                            trClone.find('td, th').text('Content');

                            tr.after(trClone);
                            return;
                        }

                        if (node.is('li') || node.parents('li').length)
                        {
                            return;
                        }

                        if (!node.is('[contenteditable="true"]'))
                        {
                            node = node.parents('[contenteditable="true"]:first');
                        }

                        node = node.find('div');

                        var wrapper = node.parents('div.hEditorTemplateNodeWrapper:first');

                        var html = node.html();

                        node.remove();

                        var clone = wrapper.clone(true);

                        clone.children(this.blockNodes)
                             .html(html);

                        wrapper.after(clone);

                        clone.select('hEditorTemplateNode');

                        clone.moveCaretToNode();
                    }

                    break;
                };
                // Here again, the browser handles the default action when the backspace
                // key is pressed, which joins content together.
                case 8: // Backspace
                {
                    node = hot.selected('hEditorTemplateNode');

                    if (node && node.length && !node.children(this.blockNodes).text().length)
                    {
                        node.selectPreviousNode(false);
                        node.remove();

                        //hot.selected('hEditorTemplateNode').moveCaretToNode();
                    }

                    break;
                };
            }
        },

        shortcuts : function(event)
        {
            if (!this.isEditable || this.isSource)
            {
                return;
            }

            var node = this.getNodeAtCaretPosition();

            switch (event.keyCode)
            {
                case 39: // Right
                {
                    if (!node)
                    {
                        event.preventDefault();
                        hot.selected('hEditorTemplateNode').moveCaretToNode();
                        break;
                    }
                };
                case 37: // Left
                case 38: // Up
                case 40: // Down
                {
                    var byCaretPosition = true;

                    if (!node)
                    {
                        byCaretPosition = false;
                        node = hot.selected('hEditorTemplateNode');
                    }

                    break;
                };
                case 13: // Return / Enter - Insert a new item.
                {
                    return;
                };
            }

            if (node && node.length)
            {
                switch (event.keyCode)
                {
                    case 37: // Left Arrow
                    case 39: // Right Arrow
                    {
                        node.commandState();
                        break;
                    };
                    case 38: // Up Arrow
                    {
                        if (!byCaretPosition || !this.getCaretPosition())
                        {
                            node.selectPreviousNode();
                        }

                        break;
                    };
                    case 40: // Down Arrow
                    {
                        if (!byCaretPosition || this.getCaretPosition() == node.text().length)
                        {
                            node.selectNextNode();
                        }

                        break;
                    };
                }
            }

            if (event.metaKey || event.ctrlKey)
            {
                var execute = '';

                switch (event.keyCode)
                {
                    // Command + r or Control + r
                    case 221:
                    {
                        // ]
                        // Add <blockquote> element.
                        execute = 'indent';
                        break;
                    };
                    case 219:
                    {
                        // [
                        // Remove <blockquote> element.
                        execute = 'outdent';
                        break;
                    };
                    case 83:
                    {
                        // S
                        // Save the document.
                        event.preventDefault();
                        event.stopPropagation();
                        this.save();

                        break;
                    };
                    case 69:
                    {
                        // E
                        // Toggle the "Edit" button.
                        event.preventDefault();
                        event.stopPropagation();
                        this.toggleEditability();

                        break;
                    };
                    case 80:
                    {
                        // P
                        // Convert the selected element to a <p> element.
                        execute = 'p';
                        break;
                    };
                    case 76:
                    {
                        // L
                        // Convert the selected element to a <ul> element.
                        execute = 'ul';
                        break;
                    };
                    case 79:
                    {
                        // O
                        // Convert the selected element to an <ol> element.
                        execute = 'ol';
                        break;
                    };
                    case 49:
                    {
                        // 1
                        // Convert the selected element to an <h1> element.
                        execute = 'h1';
                        break;
                    };
                    case 50:
                    {
                        // 2
                        // Convert the selected element to an <h2> element.
                        execute = 'h2';
                        break;
                    };
                    case 51:
                    {
                        // 3
                        // Convert the selected element to an <h3> element.
                        execute = 'h3';
                        break;
                    };
                    case 52:
                    {
                        // 4
                        // Convert the selected element to an <h4> element.
                        execute = 'h4';
                        break;
                    };
                    case 53:
                    {
                        // 5
                        // Convert the selected element to an <h5> element.
                        execute = 'h5';
                        break;
                    };
                    case 54:
                    {
                        // 6
                        // Convert the selected element to an <h6> element.
                        execute = 'h6';
                        break;
                    };
                }

                if (execute)
                {
                    event.preventDefault();
                    event.stopPropagation();

                    this.executeCommand(execute);
                }
            }
        }
    }
);

$(document).ready(
    function()
    {
        editor.keyboardShortcutsReady();
    }
);