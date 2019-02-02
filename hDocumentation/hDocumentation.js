$(document).ready(
    function()
    {
        SyntaxHighlighter.config.clipboardSwf = '/Library/SyntaxHighlighter/Scripts/clipboard.swf';
        SyntaxHighlighter.config.tagName = 'code';
        SyntaxHighlighter.all();
    }
);

var documentation = {

    fileId : 0,
    methodId: 0,

    sourceWindowCounter : 1,

    ready : function()
    {
        $('input#hDocumentationSearch').keyup(
            function(event)
            {
                if (event.keyCode == 13)
                {
                    event.preventDefault();
                    $('p#hDocumentationActivity').slideDown();
                    documentation.search();
                }
            }
        );

        $('a').each(
            function()
            {
                if (this.href.indexOf('/System/Framework/') != -1)
                {
                    $(this).addClass('code');
                }
            }
        );

        $(document).on(
            'click',
            'a',
            function(event)
            {
                if (this.href.indexOf('/System/Framework/') != -1)
                {
                    event.preventDefault();

                    hot.window(
                        this.href, {},
                        'sourceWindow' + documentation.sourceWindowCounter,
                        800, 400, {
                            scrollbars : true,
                            resizable : true
                        }
                    );

                    documentation.sourceWindowCounter++;
                }
            }
        );

        $('input#hDocumentationSearchButton').click(
            function(event)
            {
                event.preventDefault();
                $('p#hDocumentationActivity').slideDown();
                documentation.search();
            }
        );

        $(document).on(
            'click',
            'a.hDocumentationMethodBack',
            function(event)
            {
                event.preventDefault();
                $(this).parents('div.hDocumentationFileMethods').prevAll('h4:first').click();
            }
        );

        $(document).on(
            'click',
            'li.hDocumentationFile h4.hDocumentationFileTitle',
            function()
            {
                if ($(this).find('a').length)
                {
                    return;
                }

                var hasMethods = false;

                if (!$(this).parents('li:first').find('div.hDocumentationFileMethods h4').length)
                {
                    documentation.getMethods($(this).parents('li:first').splitId());
                }
                else
                {
                    var methods = $(this).parents('li:first').find('div.hDocumentationFileMethods');

                    if (methods.is(':visible'))
                    {
                        hasMethods = true;
                        methods.slideUp();
                    }
                    else
                    {
                        methods.slideDown();
                    }
                }

                var description = $(this).siblings('div.hDocumentationFileDescription');
                var siblings = $(this).parents('li').siblings();

                if (!hasMethods)
                {
                    description.slideDown();
                    siblings.slideUp()
                }
                else
                {
                    description.slideUp();
                    siblings.slideDown();
                }
            }
        );

        $(document).on(
            'click',
            'td.hDocumentationMethodName',
            function()
            {
                var methodId = $(this).parents('tr:first').splitId();

                $(this).parents('div.hDocumentationMethodWrapper').siblings().slideToggle('slow');

                if (!$(this).parents('table:first').next().find('table').length)
                {
                    documentation.getMethodArguments(methodId);
                }
                else
                {
                    this.parents('table:first').next().slideToggle('slow');
                }
            }
        );

        $(document).on(
            'click',
            'div.hDocumentationMethodSignature',
            function()
            {
                $(this).slideUp();
                $(this).next().slideDown();
            }
        );

        $(document).on(
            'click',
            'div.hDocumentationMethodBody',
            function()
            {
                $(this).slideUp();
                $(this).prev().slideDown();
            }
        );

        $(document).on(
            'click',
            'h4.hDocumentationFileSourceHeading',
            function(event)
            {
                documentation.openSourceWindow($(this).attr('data-source-url'), $(this).attr('data-source-name'));
            }
        );

        $('ul#hDocumentationPluginNavigation ul li').click(
            function(event)
            {
                event.stopPropagation();
                event.preventDefault();
                location.href = $(this).children('a').attr('href');
            }
        );
    },

    sourceWindowCounter : 0,

    openSourceWindow : function(url, name)
    {
        hot.window(url, {}, 800, 640, name + this.sourceWindowCounter);
        this.sourceWindowCounter++;
    },

    search : function()
    {
        http.get(
            '/hDocumentation/search', {
                search : $('input#hDocumentationSearch').val(),
                option : $('select#hDocumentationSearchOption').val()
            },
            function(html)
            {
                $('p#hDocumentationActivity').slideUp();
                $('div#hDocumentationSearchResults').html(html);
            }
        );
    },

    getMethods : function(documentationFileId)
    {
        this.fileId = documentationFileId;

        http.get(
            '/hDocumentation/getMethods', {
                documentationFileId : documentationFileId
            },
            function(html)
            {
                $('li#hDocumentationFile-' + documentation.fileId + ' div.hDocumentationFileMethods').html(html);
                SyntaxHighlighter.all();
            }
        );
    },

    getMethodArguments : function(methodId)
    {
        this.methodId = methodId;

        http.get(
            '/hDocumentation/getMethodArguments', {
                methodId : methodId
            },
            function(html)
            {
                $('table#hDocumentationMethod-' + documentation.methodId)
                    .next()
                    .html(html)
                    .toggleDown('slow');
            }
        );
    }
};

$(document).ready(
    function()
    {
        documentation.ready();
    }
);
