$.fn.extend({
    editorDoubleClickEvents : function(event)
    {
        if (this.parents('a').length)
        {
            this.parents('a').setLink();
            editor.link.openDialogue();
        }

        if (this.is('a'))
        {
            this.setLink();
            editor.link.openDialogue();
        }

        if (this.is('img'))
        {
            this.setImage();
            editor.image.openDialogue();
        }

        if (this.is('video'))
        {
            this.setMovie();
            editor.movie.openDialogue();
        }

        hot.fire('editorDoubleClickEvents', this);
    },

    editorClickEvents : function(e)
    {
/*
        var node = '';
        var nodeName = this.get(0).nodeName.toLowerCase();

        console.log(nodeName);

        if (nodeName == 'li' || nodeName == 'button')
        {
            node = (nodeName == 'button')? this.parent() : this;

            if (node.hasClass('hEditorTemplateNodeControl'))
            {
                var value = node.find('button').text();

            }
        }

*/
        hot.fire('editorDocumentEvents', this);
    }
});

var editor = {
    nodes :
        "img, " +
        "video, " +
        "ul:not(.hEditorTemplateNodeControls, .hEditorTemplatePlaceholder), " +
        "ol, " +
        "h1, " +
        "h2, " +
        "h3, " +
        "h4, " +
        "h5, " +
        "h6, " +
        "blockquote, " +
        "table, " +
        "p, " +
        "div:not(.hEditorTemplateNodeControl, .hEditorTemplatePlaceholder)",

    notAllowedInParagraphElements : "ul, ol, h1, h2, h3, h4, h5, h6, blockquote, table, p, div",

    notAllowedInHeadingElements : "h1, h2, h3, h4, h5, h6, p, ol, ul, table, blockquote",

    headingElements : "h1, h2, h3, h4, h5, h6",

    blockNodes :
        "ul:not(.hEditorTemplateNodeControls, .hEditorTemplatePlaceholder), " +
        "ol, " +
        "h1, " +
        "h2, " +
        "h3, " +
        "h4, " +
        "h5, " +
        "h6, " +
        "blockquote, " +
        "table, " +
        "p, " +
        "div:not(.hEditorTemplateNodeControl, .hEditorTemplatePlaceholder)",

    transformNodes :
        "p, " +
        "ul:not(.hEditorTemplateNodeControls, .hEditorTemplatePlaceholder), " +
        "ol, " +
        "h1, " +
        "h2, " +
        "h3, " +
        "h4, " +
        "h5, " +
        "h6, " +
        "blockquote, " +
        "p, " +
        "div:not(.hEditorTemplateNodeControl, .hEditorTemplatePlaceholder)",

    transformByCaretPositionNodes :
        "p:first, " +
        "ul:not(.hEditorTemplateNodeControls, .hEditorTemplatePlaceholder):first, " +
        "ol:first, " +
        "h1:first, " +
        "h2:first, " +
        "h3:first, " +
        "h4:first, " +
        "h5:first, " +
        "h6:first, " +
        "blockquote:first, " +
        "div:not(.hEditorTemplateNodeControl, .hEditorTemplatePlaceholder):first",

    emptyNodes :
        "ul:not(.hEditorTemplateNodeControls, .hEditorTemplatePlaceholder):first li:empty, " +
        "ol:first li:empty, " +
        "h1:empty, " +
        "h2:empty, " +
        "h3:empty, " +
        "h4:empty, " +
        "h5:empty, " +
        "h6:empty, " +
        "p:empty, " +
        "blockquote:empty, " +
        "div:not(.hEditorTemplateNodeControl, .hEditorTemplatePlaceholder):empty",

    imgCounter : 0,

    titleSelector : '',
    documentSelector : '',
    documentContainerSelector : '',

    title : {},
    document : {},

    ready : function()
    {
        // if (hot.userAgent == 'ie')
        // {
        //     dialogue.alert({
        //             title : 'Error',
        //             label : "<p>Editor is not presently compatible with Internet Explorer.</p>" +
        //                     "<p>Please use either the Safari or Google Chrome browsers, or the Google Chrome Frame extension for Internet Explorer.</p>"
        //         },
        //         function()
        //         {
        //             if (hot.fileWildcardPath)
        //             {
        //                 location.href = hot.fileWildcardPath;
        //             }
        //             else
        //             {
        //                 location.href = hot.filePath;
        //             }
        //         }
        //     );
        //
        //     return;
        // }

        document.body.innerHTML =
            "<div id='hEditorTemplateBodyWrapper'>" +
                "<div id='hEditorTemplateBodyWrapperInner'>" +
                    document.body.innerHTML +
                "</div>" +
                "<div id='hEditorTemplateOverlay'></div>" +
                "<div id='hEditorTemplateClone'></div>" +
            "</div>";

        $('div#hEditorTemplateToolset').appendTo('body');

        document.body.innerHTML =
            "<div id='hEditorTemplateBodyWrapperOuter'>" + document.body.innerHTML + "</div>";

        if (this.documentSelector && this.titleSelector)
        {
            $(this.documentSelector + ', ' + this.titleSelector).focus(
                function()
                {
                    editor.selectedEditor = $(this);
                }
            );
        }

        if (this.documentSelector)
        {
            this.document = $(this.documentSelector);

            if (this.document.length && !this.document.text().length)
            {
                this.document.html(
                    $("<p/>").text("Document text goes here...")
                );
            }

            if (this.document.length)
            {
                $(document)
                    .on(
                        'click',
                        this.documentSelector,
                        function(event)
                        {
                            $(event.target).editorClickEvents(event);
                        }
                    )
                    .on(
                        'dblclick',
                        this.documentSelector,
                        function(event)
                        {
                            $(event.target).editorDoubleClickEvents(event);
                        }
                    );
            }

            this.document
                .addClass('hEditorTemplateDocument');

            if (this.documentContainerSelector)
            {
                $(this.documentContainerSelector)
                    .wrap("<div id='hEditorTemplateDocumentWrapper'/>");
            }
            else
            {
                this.document
                    .wrap("<div id='hEditorTemplateDocumentWrapper'/>");
            }
        }

        if (this.titleSelector)
        {
            this.title = $(this.titleSelector);

            if (this.title.length && !this.title.text().length)
            {
                this.title.text('No Title');
            }

            this.title
                .addClass('hEditorTemplateTitle')
                .wrap("<div id='hEditorTemplateTitleWrapper'/>");
        }
    }
};

$(document)
    .bind(
        'touchmove',
        function(event)
        {
            // Prevent elastic scrolling on iOS devices.
            event.preventDefault();
        }
    )
    .ready(
        function()
        {
            editor.ready();
        }
    );
