$.fn.extend({

    bindNodeEvents : function()
    {
        hot.fire('editorBindNodeEvents', this);
        return this;
    },

    unbindNodeEvents : function()
    {
        hot.fire('editorUnbindNodeEvents', this);
        return this;
    },

    /**
    * Add <div> wrappers containing editor controls to each editable node.
    *
    * See hEditorTemplate.js for the editor.nodes selector.
    */
    wrapNodes : function()
    {
        $('.hFinderNode').each(
            function()
            {
                // Convert Finder Nodes into links...
                $(this).finderNodeToLink();
            }
        );

        // Provide each editable node a wrapper.  The wrapper contains markup that
        // adds handles to resize, rearrange and remove each editable node.
        this.find(editor.nodes).each(
            function()
            {
                $(this).wrapNode();
            }
        );

        // On the first pass, find() seems only to work on the imediate children and
        // does not wrap children nested within children.  This function therefore,
        // has been modifed to look recursively at the DOM and make sure that nested
        // children receive wrappers.
        var missingWrappers = 0;

        this.find(editor.nodes).each(
            function()
            {
                if (!$(this).parent('div.hEditorTemplateNodeWrapper:first').length)
                {
                    missingWrappers += 1;
                }
            }
        );

        if (missingWrappers > 0)
        {
            return this.wrapNodes();
        }
        else
        {
            return this;
        }
    },

    finderNodeToLink : function()
    {
        var title = this.find('h4.hFinderFileTitle').text();

        if (!title)
        {
            title = this.find('span.hFinderFileName span').text();
        }

        var link = this.attr('data-file-path');

        this.outerHTML(
            $("<a/>")
                .attr('href', link)
                .text(title)
        );

        return this;
    },

    /**
    * Add a <div> wrapper containing editor controls to an individual editable node.
    *
    */
    wrapNode : function()
    {
        if (!this.parent('div.hEditorTemplateNodeWrapper').length)
        {
            var nodeName = this.get(0).nodeName.toLowerCase();

            if (this.is('img') || this.is('video'))
            {
                if (!this.get(0).style.width && this.width() > editor.document.innerWidth())
                {
                    this.width(editor.document.innerWidth());
                    this.css('height', 'auto');
                }
            }

            this.wrap($('div.hEditorTemplateNodeWrapper.hEditorTemplate'));

            this.html($.trim(this.html()));

            var div = this.parents('div.hEditorTemplateNodeWrapper:first');

            div.removeClass('hEditorTemplate');

            if (div.find(editor.blockNodes).length)
            {
                div.css('display', 'block');
            }

            var position = this.css('position');
            var cssFloat = this.css('float');

            if (cssFloat != 'none')
            {
                div.css('float', cssFloat);
                this.css('float', 'none');
            }

            if (position != 'static')
            {
                div.css('position', position);
                this.css('position', 'static');
            }

            var node = this;

            if (this.is(editor.blockNodes))
            {
                this.attr('contenteditable', 'true');
            }

            $(editor.autoProperties).each(
                function()
                {
                    var value = node.css(this);

                    if (value != 'auto')
                    {
                        div.css(this, value);
                        node.css(this, 'auto');
                    }
                }
            );

            if (this.hasClass('hMovie'))
            {
                this.outerHTML(
                    $('<video/>')
                        .attr({
                            src : this.attr('title'),
                            poster : this.attr('src'),
                            controls : 'controls',
                            style : this.attr('style')
                        })
                        .data('file-path', this.data('file-path'))
                );
            }

            div.bindNodeEvents();
        }

        return this;
    },

    /**
    * Iterate through the selection of elements removing the <div> wrapper containing editor controls
    * from each selected element.
    *
    */
    unwrapNodes : function()
    {
        this.each(
            function()
            {
                if ($(this).find('div.hEditorTemplateNodeWrapper').length)
                {
                    $(this).find('div.hEditorTemplateNodeWrapper').unwrapNodes();
                }

                $(this).unwrapNode();
            }
        );

        return this;
    },

    /**
    * Remove the <div> wrapper containing editor controls from the editable node and return it
    * to the normal, pre-editor state.
    *
    */
    unwrapNode : function()
    {
         var node = $(this);

         this.children('ul.hEditorTemplateNodeControls, div.hEditorTemplateNodeControl')
             .remove();

         this.children()
             .attr('contenteditable', 'false');

         var node     = this.find(editor.nodes);
         var cssFloat = this.css('float');
         var position = this.css('position');
         var top      = this.css('top');
         var right    = this.css('right');
         var bottom   = this.css('bottom');
         var left     = this.css('left');

         var itShouldBeStatic = (
            position == 'relative' &&
            top == 'auto' &&
            right == 'auto' &&
            bottom == 'auto' &&
            left == 'auto'
         );

         if (itShouldBeStatic)
         {
             position = 'static';
         }

         if (position != 'static')
         {
            node.css('position', position);
         }

         if (cssFloat != 'none')
         {
            node.css('float', cssFloat);
         }

         var div = this;

         $(editor.autoProperties).each(
             function()
             {
                 var value = div.css(this);

                 if (value != 'auto')
                 {
                     node.css(this, value);
                 }
             }
         );

         this.unbindNodeEvents();

         this.outerHTML($.trim(this.html()));

         return this;
    }
});

$.extend(

    editor, {

        autoProperties : [
            'top',
            'right',
            'bottom',
            'left',
            'zIndex'
        ],

        editableNodeReady : function()
        {
            $(document).click(
                function(event)
                {
                    var node = $(event.target);

                    if (!node.hasClass('hEditorTemplateNodeWrapper'))
                    {
                        node = node.parents('div.hEditorTemplateNodeWrapper:first');
                    }

                    if (node && node.length && node.hasClass('hEditorTemplateNodeWrapper'))
                    {
                        node.selectNode();
                    }
                }
            );
        }
    }
);

$(document).ready(
    function()
    {
        editor.editableNodeReady();
    }
);
