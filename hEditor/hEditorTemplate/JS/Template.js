$.fn.extend({

    /**
    * Sets the label in the floating Editor panel to reflect in plain English
    * what the item selected is.  For example, "A paragraph of copy" might be
    * the label for a selected <p> element.
    *
    * This method sets the label based on the node itself, the editor.setPreviewLabel
    * method sets the text label itself without any node context.
    */
    setPreviewLabel : function()
    {
        // The node passed is expected to be a div.hEditorTemplateNodeWrapper
        // Look at the immediate children of the div and select only the original
        // editable node.  See hEditorTemplate.js for the selector used in
        // editor.blockNodes.
        var editable = this.children(editor.blockNodes);

        // Iterate through the previewLabels object defined in the template,
        // this will have selectors and labels defined for each editable
        // node.  For example, preview.selector might be "h1", which matches
        // any <h1> node.  This will accompany a plain English description in
        // preview.label that might be "Title", instead of slightly harder to
        // parse geekspeak like Level 1 Heading or some such.
        for (var i in editor.previewLabels)
        {
            var preview = editor.previewLabels[i];

            // Does the editable node match the selector?
            if (editable.is(preview.selector))
            {
                // Set the associated label.
                editor.setPreviewLabel(preview.label);

                // Break iteration.
                break;
            }
        }

        return this;
    },

    selectTemplate : function()
    {
        var editable = this.children(editor.blockNodes);

        var foundMatch = false;

        $('ul#hEditorTemplates').scrollTop(0);

        $('ul#hEditorTemplates > li').each(
            function()
            {
                var i = parseInt($(this).attr('data-template-offset'));
                var matches = editor.templates[i].matches;

                if (editable.is(matches))
                {
                    $(this).select('hEditorTemplate');
                    $('ul#hEditorTemplates').scrollTop($(this).position().top - $(this).innerHeight());
                    foundMatch = true;
                    return false;
                }
            }
        );

        if (!foundMatch)
        {
            hot.unselect('hEditorTemplate');
        }
    },

    transformTemplate : function(toTemplate)
    {
        fromTemplate = this.getTemplate();

        var node = this.children(editor.transformByCaretPositionNodes);

        if (node && node.length && toTemplate.matches && toTemplate.matches.length)
        {
            node.outerHTML(editor.rewriteBlock(node.outerHTML(), toTemplate.matches));
            node.commandState();
        }

        return this;
    },

    getTemplate : function()
    {
        // Be consistent...
        var node = this.hasClass('hEditorTemplateNodeWrapper')? this : this.parents('div.hEditorTemplateNodeWrapper:first');

        var editable = node.children(editor.blockNodes);

        var foundMatch = false;

        var template = {};

        $('ul#hEditorTemplates > li').each(
            function()
            {
                var i = parseInt($(this).attr('data-template-offset'));
                var matches = editor.templates[i].matches;

                if (editable.is(matches))
                {
                    template = editor.templates[i];
                    return false;
                }
            }
        );

        return template;
    }
});

$.extend(
    editor, {

        templates : null,
        templatePath : null,
        previewLabels : null,

        templateReady : function()
        {
            // Is there a templatePath set?
            //
            // templatePath will be set in the framework configuration as a framework variable,
            // hEditorTemplatePath.  The path set references a JSON file that defines the
            // template elements that can be presented to the user.
            if (this.templatePath)
            {
                setTimeout('editor.retrieveTemplateConfiguration();', 1);
            }

            hot.event(
                'hEditorTemplateNodeSelected',
                function()
                {
                    this.setPreviewLabel();
                    this.selectTemplate();
                }
            );

            hot.event(
                'hEditorTemplateNodeUnselected',
                function()
                {
                    editor.setPreviewLabel('');
                }
            );

            $(document).on(
                'click',
                'ul#hEditorTemplates > li',
                function()
                {
                    // If the transformation fails or is rejected, save the previously selected
                    // item so that it may be restored.
                    var previousSelection = hot.selected('hEditorTemplate');

                    $(this).select('hEditorTemplate');

                    var template = editor.templates[$(this).attr('data-template-offset')];
                    var selected = hot.selected('hEditorTemplateNode');

                    if (selected && selected.length)
                    {
                        var transformed = selected.transformTemplate(template);

                        // The template transformation can fail or be otherwise rejected, if the
                        // transformTemplate method returns false, this is the case and the
                        // previously selected template should be selected again.
                        if (false === transformed)
                        {
                            previousSelection.select('hEditorTemplate');
                        }
                    }
                }
            );

            hot.event(
                'hEditorTemplateSelected',
                function()
                {
                    $(this).find('input[type="radio"]').attr('checked', 'checked');
                }
            );

            hot.event(
                'hEditorTemplateUnselected',
                function()
                {
                    $(this).find('input[type="radio"]').removeAttr('checked');
                }
            );

            // Prevent links in templates from being opened.
            $(document).on(
                'click',
                'ul#hEditorTemplates a',
                function(event)
                {
                    event.preventDefault();
                }
            );
        },

        templatesLoaded : false,

        retrieveTemplateConfiguration : function()
        {
            // This won't load without setting a timeout, but when a timeout is set
            // it loads multiple times.  This dirty hack prevents the template
            // configuration from being loaded multiple times, though I should
            // learn the true origin
            if (!this.templatesLoaded)
            {
                http.get(
                    this.templatePath,
                    function(json)
                    {
                        editor.templatesLoaded = true;

                        if (typeof(json) == 'object')
                        {
                            if (typeof(json.templates) != 'undefined')
                            {
                                editor.templates = json.templates;
                            }

                            if (typeof(json.previewLabels) != 'undefined')
                            {
                                editor.previewLabels = json.previewLabels;
                            }

                            editor.addTemplates();
                        }
                    }
                );
            }
        },

        setPreviewLabel : function(label)
        {
            $('span#hEditorContentSelectedLabel span').text(label);
        },

        addTemplates : function()
        {
            $('ul#hEditorTemplates').html('');

            if (this.templates)
            {
                for (var i in this.templates)
                {
                    var template = this.templates[i];

                    $('ul#hEditorTemplates').append(
                        "<li data-template-offset='" + i + "'>" +
                            "<input type='radio' name='hEditorTemplateRadio' class='hEditorTemplateRadio' value='" + i + "' />" +
                            "<h4 class='hEditorTemplateName'>" + template.name + "</h4>" +
                            "<div class='hEditorTemplatePreview " + template.preview + "'>" +
                                template.template +
                            "</div>" +
                        "</li>\n"
                    );
                }

                $('div.hEditorTemplatePreview')
                    .attr('draggable', 'true')
                    .bind(
                        'dragstart.template',
                        function(event)
                        {
                            event.stopPropagation();

                            editor.dropCompleted = false;
                            editor.sourceCoordinates = null;

                            editor.dropEffect = 'copy' ;

                            editor.sourceCoordinates = $(this).offset();

                            event.originalEvent.dataTransfer.effectAllowed = 'copy';
                            event.originalEvent.dataTransfer.setData('text/html', $(this).outerHTML());
                        }
                    );


/*
                $('div.hEditorTemplatePreview').draggable({
                    connectToSortable : 'div#hEditorTemplateClone',
                    containment : 'document',
                    appendTo : 'body',
                    helper : 'clone',
                    zIndex : 9999,
                    stop : function(event, ui)
                    {
                        // Find and remove the template preview wrapper.
                        $(editor.documentSelector).find('div.hEditorTemplatePreview').each(
                            function()
                            {
                                $(this).outerHTML($(this).html());
                            }
                        );

                        editor.toWYSIWYG();
                    }
                });
*/

            }
        },

        rewriteBlock : function(fragment, matches)
        {
            if (!matches)
            {
                return;
            }

            var bits, to, className;

            if (matches.indexOf('.') != -1)
            {
                bits = matches.split('.');
                to = bits[0];
                className = bits[1];
            }
            else
            {
                to = matches;
            }

            $('div#hEditorTemplateTemporary').html(fragment);

            if (!$('div#hEditorTemplateTemporary').children(':not(p)').length && $('div#hEditorTemplateTemporary').children('p').length >= 1 && (to == 'ul' || to == 'ol'))
            {
                html = $('div#hEditorTemplateTemporary').html();

                html = html.replace(/\<(p)/gmi, '<li');
                html = html.replace(/\<\/(p)\>/gmi, '</li>');

                $('div#hEditorTemplateTemporary').html("<" + to + ">" + html + "</" + to + ">");
            }
            else
            {
                $('div#hEditorTemplateTemporary').find(this.transformNodes).each(
                    function()
                    {
                        $(this).removeAttr('class');

                        var nodeName = this.nodeName.toLowerCase();

                        if ((nodeName == 'ul' || nodeName == 'ol') && to != 'ul' && to != 'ol')
                        {
                            var html = $.trim($(this).outerHTML());

                            html = html.replace(/\<li/gmi, '<' + to);
                            html = html.replace(/\<\/li\>/gmi, '</' + to);

                            html = html.replace(/\<(ul|ol).*?\>/gmi, '');
                            html = html.replace(/\<\/(ul|ol)\>/gmi, '');

                            $(this).outerHTML($.trim(html));
                        }
                        else
                        {
                            $(this).outerHTML($("<" + to + "/>").html($(this).html()).outerHTML());
                            //html = html.replace(/^\<(p|ul|ol|h\d|blockquote|div)/gmi, '<' + to);
                            //html = html.replace(/\<\/(p|ul|ol|h\d|blockquote|div)\>$/gmi, '</' + to + '>');
                        }
                    }
                );
            }

            $('div#hEditorTemplateTemporary').find('ul, ol').each(
                function()
                {
                    if (!$(this).find('li').length)
                    {
                        $(this).wrapInner("<li />");
                    }
                }
            );

            if (to != 'ol' && to != 'ul')
            {
                $('div#hEditorTemplateTemporary').find('li').each(
                    function()
                    {
                        $(this).outerHTML($(this).html());
                    }
                );
            }

            if (className)
            {
                $('div#hEditorTemplateTemporary')
                    .find(to)
                    .addClass(className);
            }

            $('div#hEditorTemplateTemporary')
                .find('li')
                .removeAttr('contenteditable');

            $('div#hEditorTemplateTemporary')
                .find(to)
                .attr('contenteditable', 'true');

            return $('div#hEditorTemplateTemporary').html();
        },

        transformBlock : function(matches)
        {
            var node = hot.selected('hEditorTemplateNode');
            var editable = node.children(this.transformByCaretPositionNodes);

            if (editable && editable.length)
            {
                editable.outerHTML(this.rewriteBlock(editable.outerHTML(), matches));
                editable.commandState();
            }

            this.garbageCollection();
        }
    }
);

$(document).ready(
    function()
    {
        editor.templateReady();
    }
);