$.fn.extend({

    insertAtSelection : function()
    {
        editor.restoreSelection();

        if (!editor.selection)
        {
            hot.console.warning('Attempting to insert at selection, but no selection exists.');
            return;
        }

        if (editor.selectionExists())
        {
            editor.selection.deleteContents();
        }

        editor.selection.insertNode(this.get(0));
    },

    surroundSelection : function()
    {
        if (arguments[0] && !editor.selectionExists())
        {
            this.text(arguments[0]);
        }

        editor.restoreSelection();

        if (editor.selectionExists())
        {
            editor.selection.surroundContents(this.get(0));
        }
        else
        {
            editor.selection.insertNode(this.get(0));
        }
    },

    splitContentAtCaret : function()
    {
        editor.restoreSelection();

        var position = this.getCaretPosition();
        var html = this.html();
        var length = html.length;

        if (position == length)
        {
            return [];
        }

        var before = html.substring(0, position);
        var after = html.substring(position, length);

        //hot.console.log(
        //    "Position: " + position + "\n\n" +
        //    "Before:\n" + before + "\n\n" +
        //    "After:\n" + after + "\n\n"
        //);

        return [before, after];
    },

    // Move the text caret to the very beginning of the specified wrapper.
    moveCaretToNode : function()
    {
        var selection = window.getSelection(); //editor.getSelectionObject();
        var range = document.createRange();
        var node = this.getNodeWrapper();

        node = node.children(editor.blockNodes).get(0);

        if (node)
        {
            range.setStart(node.firstChild ? node.firstChild : node, 0);
            range.collapse(true);

            selection.removeAllRanges();
            selection.addRange(range);
        }

        return this;
    },

    getCaretPosition : function()
    {
        var node = null;
        var range = window.getSelection().getRangeAt(0); //editor.getSelectionObject().getRangeAt(0);

        if (this.hasClass('hEditorTemplateNodeWrapper'))
        {
            node = this.children(editor.blockNodes).get(0);
        }
        else
        {
            node = (
                this.parents('div.hEditorTemplateNodeWrapper:first')
                    .children(editor.blockNodes)
                    .get(0)
            );
        }

        var treeWalker = document.createTreeWalker(
            node,
            NodeFilter.SHOW_TEXT,
            function(node)
            {
                var nodeRange = document.createRange();

                nodeRange.selectNodeContents(node);

                if (nodeRange.compareBoundaryPoints(Range.END_TO_END, range) < 1)
                {
                    return NodeFilter.FILTER_ACCEPT;
                }
                else
                {
                    return NodeFilter.FILTER_REJECT;
                }
            },
            false
        );

        var charCount = 0;

        while (treeWalker.nextNode())
        {
            charCount += treeWalker.currentNode.length;
        }

        if (range.startContainer.nodeType == 3)
        {
            charCount += range.startOffset;
        }

        return charCount;
    },

    // In some situations, we might be working with a node inside of a node wrapper,
    // rather than the node wrapper itself.  In those situations, it's necessary to
    // move up the DOM to that element's node wrapper to ensure consistency.
    getNodeWrapper : function()
    {
        if (this.hasClass('hEditorTemplateNodeWrapper'))
        {
            return this;
        }

        return this.parents('div.hEditorTemplateNodeWrapper:first');
    },

    // Relative to the presently selected node, iterate to the closest preceding node,
    // whether that node be an adjacent sibling, a parent, etc, and select it.
    selectPreviousNode : function()
    {
        var removeCaret = true;

        if (arguments[0] !== undefined)
        {
            removeCaret = arguments[0];
        }

        return (
            this.getPreviousNode(removeCaret)
                .selectNode()
        );
    },

    /**
    * Return the closest preceding node.
    *
    */
    getPreviousNode : function()
    {
        var removeCaret = true;

        if (arguments[0] !== undefined)
        {
            removeCaret = arguments[0];
        }

        var prevNode = null;
        var node = this.getNodeWrapper();

        if (node.prev().find('div.hEditorTemplateNodeWrapper:last').length)
        {
            prevNode = (
                node.prev()
                    .find('div.hEditorTemplateNodeWrapper:last')
            );
        }
        else if (node.prev() && node.prev().length)
        {
            prevNode = node.prev();
        }
        else if (node.parents('div.hEditorTemplateNodeWrapper:first').length)
        {
            prevNode = node.parents('div.hEditorTemplateNodeWrapper:first');
        }

        if (prevNode && prevNode.length)
        {
            if (removeCaret)
            {
                editor.removeCaret();
            }

            return prevNode;
        }

        return node;
    },

    /**
    * Relative to the presently selected node, iterate to the closest proceeding node, whether
    * that element be a child or an adjacent sibling.
    *
    */
    selectNextNode : function()
    {
        var removeCaret = true;

        if (arguments[0] !== undefined)
        {
            removeCaret = arguments[0];
        }

        return (
            this.getNextNode(removeCaret)
                .selectNode()
        );
    },

    /**
    * Return the closest proceeding node.
    *
    */
    getNextNode : function()
    {
        var removeCaret = true;

        if (arguments[0] !== undefined)
        {
            removeCaret = arguments[0];
        }

        var nextNode = null;
        var node = this.getNodeWrapper();

        if (node.find('div.hEditorTemplateNodeWrapper:first').length)
        {
            nextNode = node.find('div.hEditorTemplateNodeWrapper:first');
        }
        else if (node.next() && node.next().length)
        {
            nextNode = node.next();
        }
        else
        {
            var parent = node.parents('div.hEditorTemplateNodeWrapper:first');

            while (true)
            {
                if (parent.next() && parent.next().length)
                {
                    nextNode = parent.next();
                    break;
                }
                else
                {
                    parent = parent.parents('div.hEditorTemplateNodeWrapper:first');

                    if (!parent.length)
                    {
                        break;
                    }
                }
            }
        }

        if (nextNode && nextNode.length)
        {
            if (removeCaret)
            {
               editor.removeCaret();
            }

            return nextNode;
        }

        return node;
    },

    selectNode : function()
    {
        // var selected = hot.selected('hEditorTemplateNode');
        //
        // if (selected && selected.length)
        // {
        //     if (selected.siblings().length)
        //     {
        //         selected.siblings().css('opacity', 1);
        //     }
        //
        //     selected.find('div.hEditorTemplateNodeWrapper').css('opacity', 1);
        //     selected.parents('div.hEditorTemplateNodeWrapper').css('opacity', 1);
        //     selected.parents('div.hEditorTemplateNodeWrapper:last').css('opacity', 0.3);
        //
        //     $('div#hEditorTemplateClone > div.hEditorTemplateDocument')
        //         .children('div.hEditorTemplateNodeWrapper')
        //         .css('opacity', 0.3);
        // }

        this.select('hEditorTemplateNode');

        this.children(editor.nodes)
            .setProperties();

        // this.css('opacity', 1);
        // this.parents().css('opacity', 1);
        //
        // if (this.siblings().length)
        // {
        //     this.siblings().css('opacity', 0.3);
        // }

        var node = editor.getNodeAtCaretPosition();

        if (node && node.length)
        {
            node.commandState();
        }
        else
        {
            this.commandState();
        }

        editor.$('span#hEditorContentSelected').css(
            'background-color',
            this.children('div.hEditorTemplateNodeHandle')
                .css('background-color')
        );

        editor.setSizeAndPosition();

        this.sanitizeContent();

        return this;
    }
});

$.extend(
    editor, {
        selection : '',

        selectionInDocument : false,

        removeCaret : function()
        {
            var selection = window.getSelection();
            selection.removeAllRanges();
        },

        // When a selection occurs, that selection is saved so that it might be
        // restored if it is needed for an operation.  This might be useful in the
        // event that a selection is lost as an editing operation is taking place
        // and the selection must be restored to complete the operation.
        selectionReady : function()
        {
            $(document)
                .on(
                    'mousedown.editorSelection',
                    function(event)
                    {
                        var target = $(event.target);

                        if (target && target.length && target.parents('div#hEditorTemplateClone').length)
                        {
                            editor.selectionInDocument = true;
                        }
                    }
                )
                .on(
                    'mousedown.editorSelection',
                    function(event)
                    {
                        if (editor.selectionInDocument && !editor.preventSelection)
                        {
                            editor.saveSelection();
                            editor.selectionInDocument = false;
                        }
                    }
                )
                .on(
                    'keyup.editorSelection',
                    function(event)
                    {
                        // keyup reports target node is the contentEditable div, rather
                        // than the immediate node the user is typing within.  This
                        // is a work-around.
                        var target = $(event.target);

                        if (target && target.length && target.parents('div#hEditorTemplateClone').length && !editor.preventSelection)
                        {
                            editor.saveSelection();
                            editor.selectionInDocument = false;
                        }
                    }
                );
        },

        // Determine whether or not a selection presently exists.
        selectionExists : function()
        {
            var selection = this.getSelectionObject();
            return (selection && selection.toString().length);
        },

        nodeInEditableNode : function(node)
        {
            if (!$(node).parents('.hEditorTemplateNodeSelected').length)
            {
                return false;
            }

            while (node = node.parentNode)
            {
                if (node.contenteditable == 'true' || node.contentEditable == 'true')
                {
                    return true;
                }

                if ($(node).hasClass('hEditorTemplateNodeWrapper'))
                {
                    return false;
                }
            }

            return false;
        },

        getSelectionObject : function()
        {
            return window.getSelection ? window.getSelection() : document.selection;
        },

        // Returns the node where the text editing caret is presently located inside of.
        //The node returned from this function is always the element the caret is located
        // within rather than a text node.
        getNodeAtCaretPosition : function()
        {
            if (!this.isEditable)
            {
                return;
            }

            var selection = this.getSelectionObject();

            if (selection && !selection.toString().length)
            {
                if (selection.baseNode)
                {
                    if (this.nodeInEditableNode(selection.baseNode))
                    {
                        if (selection.baseNode.nodeName == '#text')
                        {
                            return $(selection.baseNode.parentNode);
                        }
                        else
                        {
                            return $(selection.baseNode);
                        }
                    }
                    else
                    {
                        return null;
                    }
                }
                else if (selection.anchorNode)
                {
                    if (this.nodeInEditableNode(selection.anchorNode))
                    {
                        if (selection.anchorNode.nodeName == '#text')
                        {
                            return $(selection.anchorNode.parentNode);
                        }
                        else
                        {
                            return $(selection.anchorNode);
                        }
                    }
                    else
                    {
                        return null;
                    }
                }
            }

            return null;
        },

        // Don't use this, use the contextual getCaretPosition method instead
        // (written as a jQuery extension at the top of this file)
        getCaretPosition : function()
        {
            return window.getSelection().baseOffset; //this.getSelectionObject().baseOffset;
        },

        // Retains the present selection for potential future use.  This method takes
        // into account the different ways different browsers might handle the concept
        // of accessing a selection.
        saveSelection : function()
        {
            if (!this.isEditable)
            {
                return;
            }

            var selection = this.getSelectionObject();
            var range = null;

            if (selection)
            {
                if (selection.createRange)
                {
                    range = selection.createRange();
                }
                else if (selection.baseNode && selection.extentNode && document.createRange)
                {
                    range = document.createRange();
                    range.setStart(selection.baseNode, selection.baseOffset);
                    range.setEnd(selection.extentNode, selection.extentOffset);

                    if (range.collapsed)
                    {
                        range.setStart(selection.extentNode, selection.extentOffset);
                        range.setEnd(selection.baseNode, selection.baseOffset);
                    }
                }
                else if (selection.getRangeAt && selection.rangeCount)
                {
                    // This bit is called if there is only a text cursor, but no
                    // highlighted selection.
                    range = selection.getRangeAt(0);
                }
                else if (selection.anchorNode && selection.focusNode && document.createRange)
                {
                    // Older WebKit browsers
                    range = document.createRange();
                    range.setStart(selection.anchorNode, selection.anchorOffset);
                    range.setEnd(selection.focusNode, selection.focusOffset);

                    // Handle the case when the selection was selected backwards (from
                    // the end to the start in the document)
                    if (range.collapsed !== selection.isCollapsed)
                    {
                        range.setStart(selection.focusNode, selection.focusOffset);
                        range.setEnd(selection.anchorNode, selection.anchorOffset);
                    }
                }
            }

            // If a selection has been detected, save it.  If not,
            // preserve whatever, if anything, was last selected.
            if (range)
            {
                this.selection = range;
            }
        },

        // Takes an arbitrary x, y coordinate and creates a range from that point.
        createCaretAtPoint : function(x, y)
        {
            if (document.caretRangeFromPoint)
            {
                range = document.caretRangeFromPoint(x, y);

                if (range)
                {
                    this.selection = range;
                }

                this.restoreSelection();

                this.removeCustomCaret();

                var caret = $('span.hEditorCaretTemplate').clone();
                caret.removeClass('hEditorCaretTemplate');
                caret.insertAtSelection();
            }
        },

        removeCustomCaret : function()
        {
            $('span.hEditorCaret:not(.hEditorCaretTemplate)').remove();
        },

        // Restores a previously retained selection.  This method takes into account the
        // different methods that different browsers use to create a selection.
        restoreSelection : function()
        {
            if (!this.isEditable)
            {
                return;
            }

            var range = this.selection;

            if (range)
            {
                var selection = this.getSelectionObject();

                if (selection && range)
                {
                    if (range.select)
                    {
                        range.select();
                    }
                    else if (selection.removeAllRanges && selection.addRange)
                    {
                        selection.removeAllRanges();
                        selection.addRange(range);
                    }
                }
            }
        }
    }
);

$(document).on(
    'ready.editorSelection',
    function()
    {
        editor.selectionReady();
    }
);