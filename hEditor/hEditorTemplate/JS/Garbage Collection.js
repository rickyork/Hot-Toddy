$.fn.extend({

    /**
    * Remove garbage content from the editor in an ongoing fashion.  Get rid of
    * <font> tags, get rid of superfluous nodes and empty nodes, get rid of comments and
    * conditional comments.  Get rid of things stuffed in by MS Office.
    *
    * Superfluous defined as being utterly pointless and serving no other purpose than
    * to add bloat to the content.
    *
    * Presentational tags and attributes that might conflict with stylesheet rules also
    * falls under the categorization of "superfluous".  Taking a preemptive approach to
    * presentational tags and attributes by automatically stripping them preserves the
    * philosophical separation of structure and style.
    *
    * Finally, after the "bad" content is removed, the integrity of editor controls is
    * then checked, and each editable node is ensured to have been wrapped with editor
    * controls.  i.e., handles for resizing, an "X" button to delete the node, etc.
    */
    sanitizeContent : function()
    {
        if (!editor.isEditable)
        {
            return;
        }

        var removeProperties = [
            'mso-ansi-language',
            'mso-fareast-font-family',
            'mso-fareast-theme-font',
            'mso-fareast-language',
            'mso-bidi-font-family',
            'mso-bidi-language',
            'font-size',
            'font-family'
        ];

        this.find('*').each(
            function()
            {
                var object = this;

                $(removeProperties).each(
                    function(key, value)
                    {
                        if ($(object).css(value))
                        {
                            object.style.removeProperty(value);
                        }
                    }
                );

                if (!$(object).attr('style'))
                {
                    $(object).removeAttr('style');
                }
            }
        );

        //this.find(editor.emptyNodes).remove();

        // Clean up pasted content...
        // No <font> tags.
        this.find('font')
            .remove();

        // Detect paste of MS Office content
        // If the "MsoNormal" class name is found, or o:p elements are found, chances are
        // good that content has been added that originated in an MS Office document.
        if (this.find('.MsoNormal, o\\\:p').length)
        {
            // Automatically strip all superflusous markup and styling inserted by MS Office
            this.find('h1, h2, h3, h4, h5, h6, p, table, tr, td').each(
                function()
                {
                    if (typeof($(this).attr('style')) != 'undefined')
                    {
                        if ($(this).attr('style').indexOf('mso') != -1)
                        {
                            $(this).removeAttr('style');
                        }
                    }
                }
            );

            // Remove the valign attribute.
            this.find('td').removeAttr('valign');

            // Remove the width attribute from cells.
            this.find('td').each(
                function()
                {
                    if ($(this).attr('width') !== undefined)
                    {
                        $(this).css('width', $(this).attr('width') + 'px');
                        $(this).removeAttr('width');
                    }
                }
            );

            // Get rig of the align attribute.
            this.find('div')
                .removeAttr('align');

            // Get rid of the MsoNormalTable classname.
            this.find('table.MsoNormalTable')
                .removeAttr('class');

            // No border, cellspacing or cellpadding attributes.  All of this
            // should be controlled in the style sheet.
            this.find('table')
                .removeAttr('border')
                .removeAttr('cellspacing')
                .removeAttr('cellpadding');

            // Office adds a style attribute to lots of things, get rid of them.
            this.find('span')
                .removeAttr('style');

            this.find('b')
                .removeAttr('style');

            this.find('i')
                .removeAttr('style');

            this.find('u')
                .removeAttr('style');

            // Office also sometimes adds vector shapes, other browsers can't deal
            // with these, so out they go.
            this.find('v\\\:shapetype')
                .remove();

            // Now the markup will be analyzed in text form instead of working with the DOM.
            var html = this.html();

            // Strip out comments and conditional comments.
            var regEx = /<(?:!(?:--[\s\S]*?--\s*)?(>)\s*|(?:script|style)[\s\S]*?<\/(?:script|style)>)/gi;

            html = html.replace(
                regEx,
                function(m, $1)
                {
                    return $1? '' : m;
                }
            );

            // Remove superfluous <span> elements.  This will 86 all instances of <span> and </span>
            html = html.replace(/\<span\>/gmi, '');
            html = html.replace(/\<\/span\>/gmi, '');

            // Get rid of all extra spacing.
            html = html.replace(/\&nbsp\;/gmi, '');

            // Restore the cleansed HTML
            this.html(html);

            // The first pass gets rid of most empty nodes.  This pass may or may not be
            // unnecessary.  I can't recall why this bit exists, hence this note.
            this.find('i:empty').remove();
            this.find('u:empty').remove();
            this.find('b:empty').remove();
            this.find('p:empty').remove();
            this.find('a:empty').remove();
            this.find('strong:empty').remove();
            this.find('em:empty').remove();
            this.find('o\\\:p').remove();
        }

        // Now all elements bearing the class "MsoNormal" are stripped of their style
        // attribute.
        this.find('.MsoNormal').each(
            function()
            {
                $(this).removeAttr('style');
            }
        );

        // Finally all elements bearing the class "MsoNormal" are removed of the
        // "MsoNormal" class name.
        this.find('.MsoNormal')
            .removeAttr('class');

        // At this point, if the editor is in source mode we'll stop cleaning up,
        // since we don't want to add editor markup to the source.
        if (editor.isSource)
        {
            return;
        }

        // The newly sanitized content is made "editable" by wrapping each new
        // node with editor controls.
        this.wrapNodes();

        this.find('div.hEditorTemplateNodeWrapper').each(
            function()
            {
                // This hack gets rid of extra <br /> elements inserted
                // in some scenarios around image and video elements.
                if ($(this).find('img, video').length)
                {
                    $(this).find('br').remove();
                }

                var nodeCount = $(this).find(editor.nodes).length;

                // If there aren't any editable nodes with the controls,
                // the editor controls should be deleted.
                if (!nodeCount)
                {
                    $(this).remove();
                }
            }
        );

        return this;
    },

    /**
    * This method cleans up content for either saving or when converting between
    * WYSIWYG mode and source mode.
    *
    * Like the sanitize content method, this method gets ride of superfluous
    * styles, nodes, and attributes, such as redundant styles, empty nodes and
    * attributes.
    */
    garbageCollection : function()
    {
        // The "hEditorTemplateAutoTarget" class name is added to elements where
        // another script has been put in place to automatically force links
        // to open in a new window depending on the link.  For example, external
        // sites and PDF documents can be detected based on the contents of the link,
        // and then forced to open in a new window.  When that content is detected, the
        // attribute target="_blank" is added to those links dynamically at load time.
        //
        // Since this editor is designed to edit content in place, with no intervention,
        // the target="_blank" attribute that was added dynamically, can now inadvertently
        // be made a permanent part of the content.
        //
        // Therefore, to prevent the target="_blank" attribute from becoming permanently
        // added to the content, the links to which target="_blank" is dynamically applied
        // are also given the class name "hEditorTemplateAutoTarget".  This class name
        // then is used here to prevent the auto-added "target" attribute from being saved
        // with the content by calling up all links with it applied and stripping
        // both the "target" attribute and the "hEditorTemplateAutoTarget" class name.
        this.find('a.hEditorTemplateAutoTarget').each(
            function()
            {
                $(this).removeAttr('target')
                       .removeClass('hEditorTemplateAutoTarget');
            }
        );

        // Webkit adds the "webkit-indent-blockquote" class name to all <blockquote> elements.
        // Since the editor deals with <blockquote> indention in its own way, the class name
        // and the margin, border, and padding CSS properties are stripped in addition to the
        // "webkit-indent-blockquote" class name.
        this.find('.webkit-indent-blockquote').each(
            function()
            {
                if (this.nodeName.toLowerCase() != 'blockquote')
                {
                    this.style.removeProperty('margin');
                }

                if (this.style.border == 'none')
                {
                    this.style.removeProperty('border');
                }

                if (this.style.padding == '0px' || this.style.padding == '0')
                {
                    this.style.removeProperty('padding');
                }

                $(this).removeAttr('class');
            }
        );

        // Make sure <p> elements aren't placed inside of <div> containers.
        this.find(this.transformNodes)
            .each(
                function()
                {
                    if (this.nodeName.toLowerCase() == 'p')
                    {
                        if ($(this).parents('div.hEditorTemplateNodeWrapper').length)
                        {
                            $(this).parents('div.hEditorTemplateNodeWrapper').unwrapNode();
                        }
                    }
                }
            );

        // Remove empty nodes
        this.find('div.hEditorTemplateNodeWrapper').each(
            function()
            {
                // If there are no editable nodes in this editor node, 86 it.
                if (!$(this).find(editor.nodes).length)
                {
                    $(this).remove();
                }

                $(this).find(editor.nodes).each(
                    function()
                    {
                        var nodeName = this.nodeName.toLowerCase();

                        switch (nodeName)
                        {
                            // img and video elements are empty by nature, and
                            // should not be removed.
                            case 'img':
                            case 'video':
                            {
                                break;
                            };
                            default:
                            {
                                // If there is no content after trimming space or the element is
                                // empty, delete it.
                                if (!$.trim($(this).text()).length || $(this).is(':empty'))
                                {
                                    $(this).parents('div.hEditorTemplateNodeWrapper:first').remove();
                                }
                            };
                        }
                    }
                );
            }
        );

        // Remove a predefined selection of empty nodes.
        this.find(this.emptyNodes)
            .remove();

        // <p> elements in particular can be used to add extra space by the user by tapping
        // return or enter.  These elements are automatically stripped in an effort to
        // force the user into creating spacing in the correct way (height, padding, border,
        // or margin).
        this.find('p').each(
            function()
            {
                if (!$(this).children().length && !$.trim($(this).text()).length)
                {
                    $(this).remove();
                }
            }
        );

        // Finally, examine all descendant elements and remove default
        this.find('*').each(
            function()
            {
                var node = $(this);

                var parent = node.parent();
                var parentAlignment = parent.css('textAlign');

                // Remove pointless insertions of text-align: left;
                if (node.css('textAlign') == 'left' && (parentAlignment != 'right' || parentAlignment != 'center' && parentAlignment != 'justify' || parentAlignment == 'left'))
                {
                    this.style.removeProperty('textAlign');
                }

                if (node.css('textDecoration') == 'none')
                {
                    this.style.removeProperty('textDecoration');
                }

                // If the parent element's fontWeight is normal, than the present element
                // being set to normal is pointless.
                if (node.css('fontWeight') == 'normal' && parent.css('fontWeight') == 'normal')
                {
                    this.style.removeProperty('fontWeight');
                }

                if (node.css('position') == 'static')
                {
                    this.style.removeProperty('position');
                }

                node.removeAttr('contenteditable');

                // Remove pointless insertions of empty class name attributes.
                if (!node.attr('class'))
                {
                    node.removeAttr('class');
                }

                // Remove pointless insertions of empty style attributes.
                if (!node.attr('style'))
                {
                    node.removeAttr('style');
                }
            }
        );

/*
        // Now lets get rid of empty nodes serving no purpose other than to bloat,
        // This is done recursively.
        while (this.find(':empty:not(td, th)').length)
        {
            this.find(':empty:not(td th)').remove();
        }
*/
    }
});

$.extend(
    editor, {
        garbageCollectionReady : function()
        {
            if (this.title && this.title.length)
            {
                //this.title.sanitizeEvents();
            }
        },

        garbageCollection : function()
        {
            if (this.title && this.title.length)
            {
                this.title.garbageCollection();
            }

            this.document.garbageCollection();
        }
    }
);

$(document).ready(
    function()
    {
        editor.garbageCollectionReady();
    }
);
