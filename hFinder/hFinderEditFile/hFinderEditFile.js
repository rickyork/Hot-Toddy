finder.editFile = {
    selected : null,

    debug : false,

    ready : function()
    {
        $('input#hFinderEditFileCancel').click(
            function(e)
            {
                e.preventDefault();
                finder.editFile.closePanel(); 
            }
        );

        $('input#hFinderEditFileSubmit').click(
            function()
            {
                $('div#hFinderEditFileActivity').addClass('hFinderEditFileActivityOn');

                if (finder.editFile.debug)
                {
                    $('iframe#hFinderUploadFrame').show();
                }

                $('input#hFinderEditFilePath').val(finder.path);
            }
        );

        $('form#hFinderEditFile').submit(
            function()
            {
                if (hot.userAgent == 'webkit')
                {
                    // This is a work-around for Safari occaisonally hanging when doing an file upload.
                    // This prevent you from having to click the submit button twice.
                    http.get('/hFile/blank');
                }
            }
        );
    },

    processResponse : function(response)
    {
        if (!http.responseHasErrors(response, "Edit File"))
        {
            finder.refresh();
        }

        $('div#hFinderEditFileActivity').removeClass('hFinderEditFileActivityOn');
        this.closePanel();
    },

    openPanel : function()
    {
        if (finder.upload)
        {
            finder.upload.closePanel();
        }
        
        var selected = hot.selected('hFinder');

        if (selected.length)
        {
            $('input#hFinderEditFileId').val(selected.splitId());

            this.selected = selected;

            http.get(
                '/hFile/getProperties', {
                    operation : 'Get Properties',
                    path : selected.getFilePath()
                },
                function(json)
                {
                    $('input#hFinderEditFileTitle').val(json.hFileTitle);
                    $('textarea#hFinderEditFileDescription').val(json.hFileDescription);

                    var input = $('input#hFinderEditFileWorldRead');

                    if (input.attr('type') == 'checkbox')
                    {
                        if (json.hUserPermissionsWorldRead == 'r')
                        {
                            input.attr('checked', 'checked');
                        }
                        else
                        {
                            input.removeAttr('checked');
                        }
                    }
                }
            );

            var height = $('div.hFinderEditFile').height();

            $('div.hFinderEditFile')
                .addClass('hFinderEditFileOn')
                .slideDown('slow');

            $('div.hFinderFiles')
                .addClass('hFinderFilesUploadOn')
                .animate({marginBottom: this.getBottomOffset(height + 33)}, 'slow');

        }
        else
        {
            alert('Error: Unable to edit file, no file is selected.');
        }
    },

    closePanel : function()
    {
        $('div.hFinderEditFile')
            .removeClass('hFinderEditFileOn')
            .slideUp('slow');

        $('div.hFinderFiles')
            .removeClass('hFinderFilesUploadOn')
            .animate({marginBottom: this.getBottomOffset(23)}, 'slow');

        $('div.hFinderEditFile form').get(0).reset();
        $('div#hFinderEditFileActivity').removeClass('hFinderEditFileActivityOn');
    },

    getBottomOffset : function(value)
    {
        return (value + ($('body').attr('id').indexOf('Dialogue') != -1? 19 : 0)) + 'px';
    }
};

$(document).ready(
    function()
    {
        finder.editFile.ready();
    }
);
