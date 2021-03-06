$.fn.extend({
    dragAndDropEvents : function()
    {
        this
            .bind(
                'dragover',
                function(event)
                {
                    if (event.target.nodeName.toLowerCase() == 'img')
                    {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                }
            )
            .bind(
                'dragleave',
                function(event)
                {
                    if (event.target.nodeName.toLowerCase() == 'img')
                    {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                }
            )
            .bind(
                'drop',
                function(event)
                {
                    var node = $(event.target);

                    if (event.target.nodeName.toLowerCase() == 'img')
                    {
                        if (event.originalEvent && event.originalEvent.dataTransfer && event.originalEvent.dataTransfer.getData)
                        {
                            // Draging on top of an image should replace the image.
                            event.preventDefault();
                            event.stopPropagation();

                            var html  = event.originalEvent.dataTransfer.getData('text/html');
                            var src   = $(html);
                            var path  = src.attr('src');
                            var title = src.attr('title');
                            var alt   = src.attr('alt');

                            if (typeof(path) != 'undefined' && path)
                            {
                                node.attr('src', path);

                                if (typeof(title) != 'undefined' && title)
                                {
                                    node.attr('title', title);
                                }

                                if (typeof(alt) != 'undefined' && alt)
                                {
                                    node.attr('alt', alt);
                                }

                                node.removeAttr('width');
                                node.removeAttr('height');

                                node.css({
                                    width: 'auto',
                                    height: 'auto'
                                });
                            }
                        }
                    }
                }
            );
    },

    unbindNodeDragAndDropEvents : function()
    {
        if (this.hasClass('hEditorTemplateNodeWrapper'))
        {
            this.unbind('dragstart.node')
                .unbind('dragover.node')
                .unbind('dragenter.node')
                .unbind('dragleave.node')
                .unbind('drop.node')
                .unbind('dragend.node');

            this.children('table')
                .children('tbody, thead')
                .children('tr')
                .children('td, th')
                .unbindOtherNodeDragAndDropEvents();

            this.children('ul, ol')
                .children('li')
                .unbindOtherNodeDragAndDropEvents();

            this.children('div.hEditorTemplatePlaceholder')
                .unbind('dragenter.nodePlaceholder')
                .unbind('dragleave.nodePlaceholder')
                .unbind('drop.nodePlaceholder');
        }

        var nodes = this.is('img')? this : this.find('img');

        if (nodes.length)
        {
            nodes.removeClass('hEditorTemplateNodeDraggable')
                 .unbind('mousedown.nodeChild')
                 .unbind('dragstart.nodeChild')
                 .unbind('dragend.nodeChild')
                 .unbind('dragenter.nodeChild')
                 .unbind('dragover.nodeChild')
                 .unbind('dragleave.nodeChild')
                 .unbind('drop.nodeChild');
        }

        return this;
    },

    nodeDragAndDropEvents : function()
    {
        this.unbindNodeDragAndDropEvents();

        if (this.hasClass('hEditorTemplateNodeWrapper'))
        {
            //this.attr('draggable', 'true');

            this.bind(
                'dragstart.node',
                function(event)
                {
                    event.stopPropagation();

                    editor.dropCompleted = false;
                    editor.sourceCoordinates = null;

                    if (!$(this).hasClass('hEditorTemplateNodeSelected'))
                    {
                        $(this).selectNode();
                    }

                    if ($(this).children('div.hEditorTemplateNodeHandle').hasClass('hEditorTemplateNodeHandleActive'))
                    {
                        editor.dropEffect = (editor.altKey)? 'copy' : 'move';

                        editor.sourceCoordinates = $(this).offset();

                        event.originalEvent.dataTransfer.effectAllowed = 'copyMove';
                        event.originalEvent.dataTransfer.setData('text/html', $(this).outerHTML());
                    }
                    else
                    {
                        event.preventDefault();
                    }
                 }
            )
            .bind(
                'dragover.node',
                function(event)
                {
                    event.stopPropagation();
                    event.preventDefault();

                    event.originalEvent.dataTransfer.dropEffect = editor.dropEffect;

                    //if (editor.imageDrag)
                    //{
                        // This really slows things down.
                        editor.createCaretAtPoint(event.pageX, event.pageY);
                    //}

                    $(this).addClass('hEditorTemplateNodeDragover');

                    if (!$(this).hasClass('hEditorTemplateNodeSelected'))
                    {
                        $(this).selectNode();
                    }
                }
            )
            .bind(
                'dragenter.node',
                function(event)
                {
                    event.preventDefault();
                    event.stopPropagation();

                    event.originalEvent.dataTransfer.dropEffect = editor.dropEffect;
                }
            )
            .bind(
                'dragleave.node',
                function(event)
                {
                    event.preventDefault();
                    event.stopPropagation();
                }
            )
            .bind(
                'drop.node',
                function(event)
                {
                    event.stopPropagation();
                    $(this).drop(event);
                }
            )
            .bind(
                'dragend.node',
                function(event)
                {
                    event.preventDefault();
                    event.stopPropagation();

                    $('div.hEditorTemplateNodeDragover').removeClass('hEditorTemplateNodeDragover');

                    if (editor.dropEffect == 'move' && editor.dropCompleted)
                    {
                        $(this).remove();
                    }

                    editor.dropCompleted = false;
                    editor.sourceCoordinates = null;
                }
            );

            this.children('table')
                .children('tbody, thead')
                .children('tr')
                .children('td, th')
                .otherNodeDragAndDropEvents();

            this.children('ul, ol')
                .children('li')
                .otherNodeDragAndDropEvents();

            this.children('div.hEditorTemplatePlaceholder')
                .bind(
                    'dragenter.nodePlaceholder',
                    function(event)
                    {
                        event.preventDefault();
                        event.stopPropagation();

                        $(this).addClass('hEditorTemplatePlaceholderDragover');
                    }
                )
                .bind(
                    'dragleave.nodePlaceholder',
                    function(event)
                    {
                        event.preventDefault();
                        event.stopPropagation();

                        $(this).removeClass('hEditorTemplatePlaceholderDragover');
                    }
                )
                .bind(
                    'drop.nodePlaceholder',
                    function(event)
                    {
                        event.stopPropagation();
                        $(this).drop(event);
                    }
                );
        }

        var nodes = this.is('img')? this : this.find('img');

        if (nodes.length)
        {
            nodes.each(
                function()
                {
                    var node = $(this);

                    if (!node.hasClass('hEditorTemplateNodeDraggable'))
                    {
                        this.contenteditable = true;

                        node.addClass('hEditorTemplateNodeDraggable')
                            .bind(
                                'mousedown.nodeChild',
                                function(event)
                                {
                                    this.draggable = true;

                                    if (this.dragDrop)
                                    {
                                        this.dragDrop();
                                    }
                                }
                            )
                            .bind(
                                'dragstart.nodeChild',
                                function(event)
                                {
                                    editor.dragStart($(this));
                                    editor.imageDrag = true;
                                    event.originalEvent.dataTransfer.setData('text/html', $(this).outerHTML());
                                }
                            )
                            .bind(
                                'dragend.nodeChild',
                                function(event)
                                {
                                    editor.imageDrag = false;

                                    if (/*editor.dropTarget.get(0) != this &&*/ $(this).is('a, img'))
                                    {
                                        $(this).remove();
                                    }
                                }
                            )
                            .bind(
                                'dragenter.nodeChild',
                                function(event)
                                {
                                    event.stopPropagation();
                                    event.preventDefault();
                                }
                            )
                            .bind(
                                'dragover.nodeChild',
                                function(event)
                                {
                                    event.stopPropagation();
                                    event.preventDefault();

                                    $(this).addClass('hEditorTemplateNodeDragover');
                                    $(document).css('cursor', 'move');
                                }
                            )
                            .bind(
                                'dragleave.nodeChild',
                                function(event)
                                {
                                    $(this).removeClass('hEditorTemplateNodeDragover');
                                    $(document).css('cursor', 'auto');
                                }
                            )
                            .bind(
                                'drop.nodeChild',
                                function(event)
                                {
                                    event.stopPropagation();
                                    event.preventDefault();

                                    if (event.originalEvent && event.originalEvent.dataTransfer && event.originalEvent.dataTransfer.getData)
                                    {
                                        var node = $(this);
                                        var drop = $(event.originalEvent.dataTransfer.getData('text/html'));
                                        var dropTarget = $(this);

                                        if (drop.is('img'))
                                        {
                                            node.attr('href', drop.attr('src'));
                                        }
                                        else if (drop.is('a'))
                                        {
                                            node.attr('href', drop.attr('href'));
                                        }
                                        else if (drop.hasClass('hFinderNode'))
                                        {
                                            node.attr('href', drop.attr('data-file-path'));
                                        }
                                    }
                                }
                            );
                    }
                }
            );
        }

        return this;
    },

    otherNodeDragAndDropEvents : function()
    {
        return(
            this.bind(
                'dragenter.otherNodes',
                function(event)
                {
                    event.preventDefault();
                    event.stopPropagation();

                    $(this).addClass('hEditorTemplateOtherDragover');
                }
            )
            .bind(
                'dragover.otherNodes',
                function(event)
                {
                    event.stopPropagation();
                    event.preventDefault();
                }
            )
            .bind(
                'dragleave.otherNodes',
                function(event)
                {
                    event.preventDefault();
                    event.stopPropagation();

                    $(this).removeClass('hEditorTemplateOtherDragover');
                }
            )
            .bind(
                'drop.otherNodes',
                function(event)
                {
                    event.stopPropagation();
                    $(this).drop(event);
                }
            )
        );
    },

    unbindOtherNodeDragAndDropEvents : function()
    {
        return(
            this.unbind('dragenter.otherNodes')
                .unbind('dragover.otherNodes')
                .unbind('dragleave.otherNodes')
                .unbind('drop.otherNodes')
        );
    },

    drop : function(event)
    {
        var placeholder = '';
        var target = this;

        if (this.hasClass('hEditorTemplatePlaceholder'))
        {
            if (this.hasClass('hEditorTemplatePlaceholderPrevious'))
            {
                placeholder = 'previous';
            }
            else if (this.hasClass('hEditorTemplatePlaceholderNext'))
            {
                placeholder = 'next';
            }

            target = this.parent();
        }

        if (event.originalEvent && event.originalEvent.dataTransfer && event.originalEvent.dataTransfer.getData)
        {
            event.originalEvent.dataTransfer.dropEffect = editor.dropEffect;
            event.preventDefault();

            var html = event.originalEvent.dataTransfer.getData('text/html');

            $('.hEditorTemplateOtherDragover').removeClass('hEditorTemplateOtherDragover');

            if (typeof(html) != 'undefined')
            {
                var block = $(html);

                if (block.hasClass('hEditorTemplateNodeDraggable'))
                {
                    block.removeClass('hEditorTemplateNodeDraggable');
                }

                var replace = html;

                switch (true)
                {
                    case block.is('div.hFinderNode'):
                    {
                        var a = document.createElement('a');
                        var title = block.find('h4').text();

                        if (!title)
                        {
                            title = block.find('span.hFinderFileName span').text();
                        }

                        a.href = block.attr('data-file-path');
                        a.title = title;

                        a.appendChild(document.createTextNode(title));

                        block = $(a);
                        $('span.hEditorCaret').not('span.hEditorCaretTemplate').replaceWith(block);
                        block.nodeDragAndDropEvents();
                        break;
                    };
                    case block.is('img, a'):
                    {
                        $('span.hEditorCaret').not('span.hEditorCaretTemplate').replaceWith(block);
                        block.nodeDragAndDropEvents();
                        break;
                    };
                    case block.is('div.hEditorTemplateNodeWrapper'):
                    {
                        block.removeAttr('draggable');
                        block.removeClass('hEditorTemplateNodeSelected');
                        block.removeClass('hEditorTemplateNodeDragover');

                        if (editor.sourceCoordinates)
                        {
                            var targetCoordinates = target.offset();

                            // Source is the same as the target, ignore.
                            if (editor.sourceCoordinates.top == targetCoordinates.top && editor.sourceCoordinates.left == targetCoordinates.left)
                            {
                                return;
                            }
                        }

                        if (placeholder)
                        {
                            switch (placeholder)
                            {
                                case 'previous':
                                {
                                    target.before(block);
                                    break;
                                }
                                case 'next':
                                {
                                    target.after(block);
                                    break;
                                }
                            }
                        }
                        else
                        {
                            var node = target.children(editor.nodes);
                            var sourceNode = block.children(editor.nodes);

                            if ($(this).is('td, th, li'))
                            {
                                if ($(this).find('span.hEditorCaret').length)
                                {
                                    $(this).find('span.hEditorCaret').not('span.hEditorCaretTemplate').replaceWith(block);
                                }
                                else
                                {
                                    $(this).append(block);
                                }
                            }
                            else if (node && node.length)
                            {
                                // Keep the user from doing whacky things with the HTML structure.
                                if (node.notAllowedElement(sourceNode))
                                {
                                    target.after(block);
                                }
                                else
                                {
                                    node.append(block);
                                }
                            }
                        }

                        block.nodeDragAndDropEvents();
                        editor.dropCompleted = true;
                        break;
                    };
                    case block.is('div.hEditorTemplatePreview'):
                    {
                        block = block.wrapNodes();
                        block = $(block.html());

                        block.removeAttr('draggable');
                        block.removeClass('hEditorTemplateNodeSelected');
                        block.removeClass('hEditorTemplateNodeDragover');

                        if (placeholder)
                        {
                            switch (placeholder)
                            {
                                case 'previous':
                                {
                                    target.before(block);
                                    break;
                                }
                                case 'next':
                                {
                                    target.after(block);
                                    break;
                                }
                            }
                        }
                        else
                        {
                            var node = target.children(editor.nodes);
                            var sourceNode = block.children(editor.nodes);

                            if ($(this).is('td, th, li'))
                            {
                                if ($(this).find('span.hEditorCaret').length)
                                {
                                    $(this).find('span.hEditorCaret').not('span.hEditorCaretTemplate').replaceWith(block);
                                }
                                else
                                {
                                    $(this).append(block);
                                }
                            }
                            else if (node && node.length)
                            {
                                // Keep the user from doing whacky things with the HTML structure.
                                if (node.notAllowedElement(sourceNode))
                                {
                                    target.after(block);
                                }
                                else
                                {
                                    node.append(block);
                                }
                            }
                        }

                        block.nodeDragAndDropEvents();
                        editor.dropCompleted = true;

                        break;
                    };
                }
            }
            else
            {
               var text = event.originalEvent.dataTransfer.getData('text/plain');
            }
        }

        return target;
    },

    notAllowedElement : function(sourceNode)
    {
        return(
            this.is('p') && sourceNode.is(editor.notAllowedInParagraphElements) ||
            this.is(editor.headingElements) && sourceNode.is(editor.notAllowedInHeadingElements)
        );
    }
});

$.extend(
    editor, {
        dropTarget : null,

        dragging : false,

        dragAndDropReady: function()
        {
            hot.event(
                'hEditorTemplateNodeUnselected',
                function()
                {
                    // I tried doing this in the dragleave event, but found it was really twitchy.
                    // This makes it smooth as butta
                    $(this).removeClass('hEditorTemplateNodeDragover');
                }
            );

            $(document)
                .on(
                    'mousedown',
                    'div.hEditorTemplateNodeHandle',
                    function()
                    {
                        $(this).addClass('hEditorTemplateNodeHandleActive');
                        $(this).parent().attr('draggable', 'true');
                    }
                )
                .on(
                    'mouseout',
                    'div.hEditorTemplateNodeHandle',
                    function()
                    {
                        $(this).removeClass('hEditorTemplateNodeHandleActive');
                        $(this).parent().removeAttr('draggable');
                    }
                );

            $(document).mouseup(
                function()
                {
                    $('div.hEditorTemplateNodeHandle').removeClass('hEditorTemplateNodeHandleActive');
                    $('div.hEditorTemplateNodeWrapper').removeAttr('draggable');
                }
            );

            if (this.title && this.title.length)
            {
                this.title.dragAndDropEvents();
            }

            $('.hEditorTemplateDrop').dragAndDropEvents();

            hot.event('photoDragStart', this.dragStart);
            hot.event('photoDragEnd', this.dragEnd);
            hot.event('editorBindNodeEvents', this.nodeDragAndDropEvents);
            hot.event('editorUnbindNodeEvents', this.unbindNodeDragAndDropEvents);

            $('div#hEditorTemplateBodyWrapper')
                .bind(
                    'dragenter',
                    function(event)
                    {
                        event.preventDefault();
                        editor.dragIsActive = true;
                    }
                )
                .bind(
                    'dragleave',
                    function(event)
                    {
                        event.preventDefault();
                        editor.dragIsActive = false;
                    }
                )
                .bind(
                    'dragover',
                    function(event)
                    {
                        event.preventDefault();
                        editor.dragIsActive = true;
                    }
                )
                .bind(
                    'drop',
                    function(event)
                    {
                        event.preventDefault();
                        editor.dragIsActive = false;
                    }
                );
        },

        removePlaceholders : function()
        {
            $('div.hEditorTemplateNodeWrapper').each(
                function()
                {
                    if (!(this).hasClass('hEditorTemplateNodeSelected'))
                    {
                        $(this).prev('div.hEditorTemplatePlaceholder').remove();
                        $(this).next('div.hEditorTemplatePlaceholder').remove();
                    }
                }
            );
        },

        dragStart : function(node)
        {
            $('.hEditorTemplateNodeDragover').removeClass('hEditorTemplateNodeDragover');
            $('span.hEditorCaret').not('span.hEditorCaretTemplate').remove();
            editor.dragging = true;
        },

        dragEnd : function()
        {
            editor.dragging = false;
            $('a.hEditorTemplateNodeLinkDragover').removeClass('hEditorTemplateNodeLinkDragover');
        },

        nodeDragAndDropEvents : function(node)
        {
            node.nodeDragAndDropEvents();
        },

        unbindNodeDragAndDropEvents : function(node)
        {
            node.unbindNodeDragAndDropEvents();
        },

        autoScrollDocument : function(event)
        {
            if (editor.dragIsActive)
            {
                var height = $(document).height();

                if (event.pageY >= height - 75)
                {
                    // This bit of math will cause the momentum of scrolling to increase as the cursor gets
                    // closer to the bottom of the screen.
                    var x = (height - event.pageY) - 75;

                    // Number is negative, need a positive number, so flip it.
                    x = -x;

                    // Add the positive number, thus forward scrolling.
                    $('div#hEditorTemplateBodyWrapper').scrollTop(
                        $('div#hEditorTemplateBodyWrapper').scrollTop() + x
                    );
                }
                else if (event.pageY <= 75)
                {
                    // Same thing but for top scrolling. Momentum of scrolling is increased as the cursor gets
                    // closer to the top edge of the screen.  Result is a negative number, which is fine.
                    var x = (event.pageY - 75);

                    // Add the negative number to subtract, thus reverse scrolling.
                    $('div#hEditorTemplateBodyWrapper').scrollTop(
                        $('div#hEditorTemplateBodyWrapper').scrollTop() + x
                    );
                }
            }
        }
    }
);

$(document)
    .ready(
        function()
        {
            editor.dragAndDropReady();
        }
    )
    .keydown(
        function(event)
        {
            // Work-around for drag-n-drop bug.
            editor.altKey = event.altKey;
        }
    )
    .keypress(
        function(event)
        {
            // Work-around for drag-n-drop bug.
            editor.altKey = event.altKey;
        }
    )
    .keyup(
        function(event)
        {
            // Work-around for drag-n-drop bug.
            editor.altKey = false;
        }
    )
    .bind(
        'dragenter',
        function(event)
        {
            // This is used to detect when a drag comes into the document from outside of the browser window,
            // and it also handles drags that occur from within the browser window.
            editor.dragIsActive = true;
            editor.autoScrollDocument(event);
        }
    )
    .bind(
        'dragover',
        function(event)
        {
            editor.dragIsActive = true;
            editor.autoScrollDocument(event);
        }
    )
    .bind(
        'dragleave',
        function(event)
        {
            editor.dragIsActive = false;
        }
    );