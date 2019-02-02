$.fn.extend({

    resizeNode : function(event)
    {
        var pageX = editor.resizeCoordinates.x;
        var pageY = editor.resizeCoordinates.y;

        var x, y;

        var margin = editor.resizeMargin;
        var top, right, bottom, left;

        top    = margin.top;
        right  = margin.right;
        bottom = margin.bottom;
        left   = margin.left;

        switch (editor.resizeDirection)
        {
            case 'nw':
            {
                x = (pageX - event.pageX);
                y = (pageY - event.pageY);

                top  = margin.top + y;
                left = margin.left + x;
                break;
            };
            case 'n':
            {
                x = 0;
                y = (pageY - event.pageY);

                top = margin.top + y;
                break;
            };
            case 'ne':
            {
                x = -(pageX - event.pageX);
                y =  (pageY - event.pageY);

                top   = margin.top + y;
                right = margin.right + x;
                break;
            };
            case 'w':
            {
                x =  (pageX - event.pageX);
                y = 0;

                left = margin.left + x;
                break;
            };
            case 'e':
            {
                x = -(pageX - event.pageX);
                y = 0;

                right = margin.right + x;
                break;
            };
            case 'sw':
            {
                x =  (pageX - event.pageX);
                y = -(pageY - event.pageY);

                bottom = margin.bottom + y;
                left   = margin.left + x;
                break;
            };
            case 's':
            {
                x = 0;
                y = -(pageY - event.pageY);

                bottom = margin.bottom + y;
                break;
            };
            case 'se':
            {
                x = -(pageX - event.pageX);
                y = -(pageY - event.pageY);

                bottom = margin.bottom + y;
                right  = margin.right + x;
                break;
            };
        }

        if (event.altKey)
        {
            this.css(
                'margin',
                top + 'px ' + right + 'px ' + bottom + 'px ' + left + 'px'
            );
        }
        else if (event.metaKey)
        {
            this.css(
                'padding',
                ((top < 0)?    0 : top)    + 'px ' +
                ((right < 0)?  0 : right)  + 'px ' +
                ((bottom < 0)? 0 : bottom) + 'px ' +
                ((left < 0)?   0 : left)   + 'px'
            );
        }
        else
        {
            this.width(editor.resizeNodeDimensions.width + x);
            var height = event.shiftKey? 'auto' : (editor.resizeNodeDimensions.height + y) + 'px';
            this.css('height', height);
        }

        return this;
    },

    getResizeDirection : function()
    {
        switch (true)
        {
            case this.hasClass('hEditorTemplateResizeNW'): return 'nw';
            case this.hasClass('hEditorTemplateResizeN'):  return 'n';
            case this.hasClass('hEditorTemplateResizeNE'): return 'ne';
            case this.hasClass('hEditorTemplateResizeW'):  return 'w';
            case this.hasClass('hEditorTemplateResizeE'):  return 'e';
            case this.hasClass('hEditorTemplateResizeSW'): return 'sw';
            case this.hasClass('hEditorTemplateResizeS'):  return 's';
            case this.hasClass('hEditorTemplateResizeSE'): return 'se';
        }
    }
});

$.extend(
    editor, {
        resizeByGripReady : function()
        {
            $(document)
                .mousemove(
                    function(event)
                    {
                        if (editor.resizeNode && editor.resizeNode.length)
                        {
                            editor.resizeNode.resizeNode(event);
                        }
                    }
                )
                .mouseup(
                    function(event)
                    {
                        if (editor.resizeNode)
                        {
                            editor.resizeNode = null;
                        }
                    }
                );

            $(document).on(
                'mousedown',
                'div.hEditorTemplateResize',
                editor.resizeByGrip
            );
        },

        resizeNode : null,

        resizeDirection : null,

        resizeNodeDimensions: {},

        resizeCoordinates: {},

        resizeMargin: {},

        resizeByGrip : function(event)
        {
            event.preventDefault();
            event.stopPropagation();

            var node = $(this).parents('div.hEditorTemplateNodeWrapper:first').children(editor.nodes);

            editor.resizeNode = node;

            editor.resizeDirection = $(this).getResizeDirection();

            editor.resizeNodeDimensions = {
                width : node.width(),
                height : node.height()
            };

            //var coordinates = $(this).offset();
            editor.resizeCoordinates = {
                x: event.pageX,
                y: event.pageY
            };

            var top    = parseInt(node.css('marginTop'));
            var right  = parseInt(node.css('marginRight'));
            var bottom = parseInt(node.css('marginBottom'));
            var left   = parseInt(node.css('marginLeft'));

            editor.resizeMargin = {
                top:    isNaN(top)?    0 : top,
                right:  isNaN(right)?  0 : right,
                bottom: isNaN(bottom)? 0 : bottom,
                left:   isNaN(left)?   0 : left
            };
        }
    }
);

$(document).ready(
    function()
    {
        editor.resizeByGripReady();
    }
);