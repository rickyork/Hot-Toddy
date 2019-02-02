if (typeof(editor) == 'undefined')
{
    var editor = {};
}

editor.findReplace = {

    ready : function()
    {
        $('input#hEditorFindAndReplaceFind').click(
            function(e)
            {
                e.preventDefault();
                editor.findReplace.doIt();
            }
        );

        $(document).on(
            'click',
            'div.hEditorFindAndReplaceMatch',
            function(e)
            {
                if (e.shiftKey)
                {
                    var matchSelected = $(this).hasClass('hEditorFindAndReplaceMatchSelected');

                    $(this).prevAll().each(
                        function()
                        {
                            if (!$(this).hasClass('hEditorFindAndReplaceMatchSelected'))
                            {
                                if (!matchSelected)
                                {
                                    $(this).addClass('hEditorFindAndReplaceMatchSelected');    
                                }
                                else
                                {
                                    $(this).removeClass('hEditorFindAndReplaceMatchSelected');
                                }
                            }
                            else
                            {
                                return false;
                            }
                        }
                    );
                }

                if (!$(this).hasClass('hEditorFindAndReplaceMatchSelected'))
                {
                    $(this).addClass('hEditorFindAndReplaceMatchSelected');
                }
                else
                {
                    $(this).removeClass('hEditorFindAndReplaceMatchSelected');
                }
            }
        );
        
        $('input#hEditorFindAndReplaceReplaceClose').click(
            function(e)
            {
                e.preventDefault();
                $('div#hEditorFindAndReplaceMatches').hide('slow');
            }
        );
        
        $('input#hEditorFindAndReplaceReplaceSelectAll').click(
            function(e)
            {
                e.preventDefault();
                $('div#hEditorFindAndReplaceMatchesInner div.hEditorFindAndReplaceMatch').addClass('hEditorFindAndReplaceMatchSelected');
            }
        );

        $('input#hEditorFindAndReplaceReplaceSelectNone').click(
            function(e)
            {
                e.preventDefault();
                $('div#hEditorFindAndReplaceMatchesInner div.hEditorFindAndReplaceMatch').removeClass('hEditorFindAndReplaceMatchSelected');
            }
        );

        $('input#hEditorFindAndReplaceReplaceAll').click(
            function(e)
            {
                e.preventDefault();

                if (confirm("Replacement cannot be undone. Confirm?"))
                {
                    editor.findReplace.doIt('all');
                }
            }
        );

        $('input#hEditorFindAndReplaceReplaceSelected').click(
            function(e)
            {
                e.preventDefault();

                if (confirm("Replacement cannot be undone. Confirm?"))
                {
                    editor.findReplace.doIt('selected');
                }
            }
        );

        $('input#hEditorFindAndReplaceReplaceUnselected').click(
            function(e)
            {
                e.preventDefault();

                if (confirm("Replacement cannot be undone. Confirm?"))
                {
                    editor.findReplace.doIt('unselected');
                }
            }
        );
        
        $(document).on(
            'click',
            'ul#hEditorFindAndReplaceFolderList li',
            function(e)
            {
                $(this).select('hEditorFindAndReplaceFolder');
            }
        );
        
        $('input#hEditorFindAndReplaceChooseFolder').click(
            function(e)
            {
                e.preventDefault();
                editor.findReplace.chooseFolder();
            }
        );
        
        $('input#hEditorFindAndReplaceRemoveFolder').click(
            function(e)
            {
                e.preventDefault();

                var selected = select.ed('hEditorFindAndReplaceFolder');

                if (selected.length)
                {
                    selected.remove();
                    select.un('hEditorFindAndReplaceFolder');
                }
            }
        );
        
        window.resizeTo(800, 800);
    },

    isReplace : false,

    chooseFolder : function()
    {
        hot.window(
            '/Applications/Finder', {
                dialogue : 'Choose',
                types : 'folders',
                onChooseFile : 'editor.findReplace.onChooseFile',
                path : '/System/Framework'
            },
            800,
            600,
            'editorFindAndReplaceChooseFolder', {
                scrollable : false
            }
        );
    },
    
    onChooseFile : function(directoryId, directoryPath, directoryName)
    {
        var li = $('li.hEditorFindAndReplaceFolderTemplate').clone(true);
        
        li.removeClass('hEditorFindAndReplaceFolderTemplate');
        
        li.find('span.hEditorFindAndReplaceFolderTitle').text(directoryName);

        li.find('span.hEditorFindAndReplaceFolderPath a')
            .attr('target', '_blank')
            .attr('href', directoryPath)
            .text(directoryPath);
            
        $('ul#hEditorFindAndReplaceFolderList').append(li);
    },

    doIt : function()
    {
        var replaceMethod = '';
        var post = '';

        if (!$('input#hEditorFind').val())
        {
            alert("Error: Nothing provided to find.");
            return;
        }

        var folderSelected = false;

        $('ul#hEditorFindAndReplaceFolderList li:not(.hEditorFindAndReplaceFolderTemplate)').each(
            function()
            {
                folderSelected = true;
                post += '&scanFolders[]=' + encodeURIComponent($(this).find('span.hEditorFindAndReplaceFolderPath a').text());
            }
        );

        if (!folderSelected)
        {
            alert("Error: No target folder selected.");
            return;
        }

        if (arguments[0])
        {
            replaceMethod = arguments[0];
            
            this.isReplace = true;
            
            post += '&replaceMethod=' + arguments[0];
        }

        if (replaceMethod)
        {
            switch (replaceMethod)
            {
                case 'selected':
                {
                    $('div.hEditorFindAndReplaceMatchSelected').each(
                        function()
                        {
                            post += '&replaceFiles[]=' + 
                                encodeURIComponent(
                                    $(this).find('span.hEditorFindAndReplaceMatchFile').text() + ':' + 
                                    $(this).find('span.hEditorFindAndReplaceMatchLine').text()
                                );
                        }
                    );

                    break;
                };
                case 'unselected':
                {
                    $('div.hEditorFindAndReplaceMatch:not(.hEditorFindAndReplaceMatchSelected)').each(
                        function()
                        {
                            post += '&replaceFiles[]=' + 
                                encodeURIComponent(
                                    $(this).find('span.hEditorFindAndReplaceMatchFile').text() + ':' + 
                                    $(this).find('span.hEditorFindAndReplaceMatchLine').text()
                                );
                        }
                    );

                    break;
                };
                case 'all':
                default:
                {
                    // Do nothing.
                };
            }
        }

        $('input#hEditorFindAndReplaceFind, input.hEditorFindAndReplaceMatchButton').attr('disabled', 'disabled');

        $('div#hEditorFindAndReplaceMatchesInner').html('');

        $('div#hEditorFindAndReplaceActivity').fadeIn('slow');

        http.post(
            '/hEditor/hEditorFindAndReplace/' + (this.isReplace? 'replace' : 'find'),
            $('form').serialize() + post,
            function(json)
            {            
                $('input#hEditorFindAndReplaceFind, input.hEditorFindAndReplaceMatchButton').removeAttr('disabled');
                
                var label = null;
                
                if (this.isReplace)
                {
                    label = 'Replaced <b>' + json.length + '</b> Matches';
                }
                else
                {
                    label = 'Found <b>' + json.length + '</b> Matches';
                }

                $('div.hEditorFindAndReplaceMatchCount').html(label);

                $('div#hEditorFindAndReplaceActivity').fadeOut('slow');

                $('div#hEditorFindAndReplaceMatches').show('slow');

                $(json).each(
                    function(key, obj)
                    {
                        var template = $('div.hEditorFindAndReplaceMatchTemplate').clone();
                        
                        template.removeClass('hEditorFindAndReplaceMatchTemplate');
                        
                        template.find('span.hEditorFindAndReplaceMatchFile').text(obj.file);
                        template.find('span.hEditorFindAndReplaceMatchLine').text(obj.lineNumber);
                        template.find('div.hEditorFindAndReplaceSnippet').html(obj.matchesHighlighted);

                        $('div#hEditorFindAndReplaceMatchesInner').append(template);
                    }
                );
                
                this.isReplace = false;
            },
            this
        );
    }
};

$(document).ready(
    function()
    {
        editor.findReplace.ready();
    }
);