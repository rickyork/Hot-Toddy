$.fn.extend({

    getPosition : function()
    {
        return (
            this.children('ul.hEditorTemplateNodeControls')
                .find('li[title="Position"] button')
                .text()
        );
    },

    positionNode : function(event)
    {
        $(this).css({

            top : (
                editor.position.top  + (
                    event.pageY - editor.positionCoordinates.y
                )
            ) + 'px',

            left : (
                editor.position.left + (
                    event.pageX - editor.positionCoordinates.x
                )
            ) + 'px'
        });
    },

    nodePosition : function(position)
    {
        var width = 'auto';

        if (position == 'absolute' || position == 'fixed')
        {
            width = editor.document.width() + 'px';
        }

        return this
            .parents('div.hEditorTemplateNodeWrapper:first')
            .css({
                position : position == 'static' ? 'relative' : position.toLowerCase(),
                top : 'auto',
                right : 'auto',
                bottom : 'auto',
                left : 'auto',
                width : width,
                height : 'auto',
                margin : 0
            });
    }
});

$.extend(

    editor, {

        positionNode : null,

        positionReady : function()
        {
            $(document)
                .mousemove(
                    function(event)
                    {
                        if (editor.positionNode && editor.positionNode.length)
                        {
                            editor.positionNode.positionNode(event);
                        }
                    }
                )
                .mouseup(
                    function(event)
                    {
                        if (editor.positionNode)
                        {
                            editor.positionNode = null;
                        }
                    }
                );

            $(document).on(
                'mousedown.editor',
                'div.hEditorTemplateNodeWrapper',
                function(event)
                {
                    var position = $(this).getPosition();

                    if (position == 'Relative' || position == 'Absolute' || position == 'Fixed')
                    {
                        event.preventDefault();
                        event.stopPropagation();

                        editor.positionNode = $(this);

                        editor.positionCoordinates = {
                            x : event.pageX,
                            y : event.pageY
                        };

                        var top, left;

                        top = parseInt($(this).css('top'));
                        left = parseInt($(this).css('left'));

                        if (isNaN(top))
                        {
                            top = 0;
                        }

                        if (isNaN(left))
                        {
                            left = 0;
                        }

                        editor.position = {
                            top : top,
                            left : left
                        };
                    }
                }
            );
        }
    }
);