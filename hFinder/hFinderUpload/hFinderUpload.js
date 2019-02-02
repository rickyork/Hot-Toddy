finder.upload = {
    duplicatePath : null,

    ready : function()
    {
        $('li#hFinderUploadFile').click(
            function()
            {
                if (!finder.beginsPath('/Categories'))
                {
                    finder.toolbar.menuTracker['Action'] = false;
                    finder.toolbar.closeMenu('Action');
                    finder.upload.openPanel();
                }
            }
        );

        $('input#hFinderUploadCancel').click(
            function(e)
            {
                e.preventDefault();
                finder.upload.closePanel();
            }
        );

        $('iframe#hFinderUploadFrame').click(
            function()
            {
                $(this).hide();
            }
        );

        $('form#hFinderUpload').submit(
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
        
        $('input#hFinderUploadFile-0').attr('multiple', 'multiple');

        $('input#hFinderUploadSubmit').click(
            function(e)
            {
                this.disabled = true;
                $('input#hFinderUploadCancel').attr('disabled', 'disabled');
            
                // Having this first seems to address a bug in Safari where setting the
                // value of this field was failing.
                if (finder.upload.debug)
                {
                    $('iframe#hFinderUploadFrame').show();
                }

                var error = false;

                $('input.hFinderUploadFile').each(
                    function()
                    {
                        var node = $(this);

                        // Look at the value of the file input...
                        if (this.value.indexOf('\\') != -1)
                        {
                            // IE exposes the whole path
                            fileName = this.value.split('\\').pop();
                        }
                        else if (this.value.indexOf('/') != -1)
                        {
                            fileName = this.value.split('/').pop();
                        }
                        else
                        {
                            // Firefox exposes only the document name
                            fileName = this.value;
                        }

                        if (fileName && !finder.hasTabooCharactersInName(fileName))
                        {
                            extension = fileName.split('.').pop();

                            if (extension == 'hot')
                            {
                                if (!confirm("You are about to install an Application.\n\nThis application may harm your website, are you sure you want to install it?"))
                                {
                                    error = true;
                                }
                            }

                            var file = finder.fileExists(fileName);
                            var matchPlus = new RegExp('/\+/g');

                            $('input#hFinderUploadPath').val(file.path.replace(matchPlus, ' '));

                            if (file.exists)
                            {
                                // e.preventDefault();
                                // 
                                // dialogue.confirm({
                                //         title : "Confirm File Replacement",
                                //         label : "<p>A file with the name <i>" + fileName + "</i> already exists, would you like to replace it?</p>" +
                                //                 "<p>This cannot be undone.</p>",
                                //         ok : "Replace File",
                                //         cancel : "Don't Replace File"
                                //     },
                                //     function(confirm)
                                //     {
                                //         if (confirm)
                                //         {
                                //             $('input#hFinderUploadReplaceFile-' + node.splitId()).val(1);
                                //         }
                                //         else
                                //         {
                                //             error = true;
                                //         }
                                //     }
                                // );
                            
                            
                                if (confirm('A file with the name, ' + fileName + ' already exists, would you like to replace it?'))
                                {
                                    $('input#hFinderUploadReplaceFile-' + node.splitId()).val(1);
                                }
                                else
                                {
                                    error = true;
                                }
                            }
                        }
                        else
                        {
                            // Can't upload if there is no file to upload
                            dialogue.alert({
                                title : 'Error',
                                label : "<p><b>Upload Failed:</b> No file selected for upload.</p>"
                            });
                            error = true;
                        }
                    }
                );

                e.preventDefault();

                if (!error)
                {
                    $('div#hFinderUploadActivity').addClass('hFinderUploadActivityOn');
                    $('form#hFinderUpload').submit();
                }
            }
        );
    },

    processResponse : function(response)
    {
        if (!finder.hasErrors(response, "File Upload"))
        {
            finder.refresh();
        }

        $('input#hFinderUploadSubmit').removeAttr('disabled');
        $('input#hFinderUploadCancel').removeAttr('disabled');

        $('div#hFinderUploadActivity').removeClass('hFinderUploadActivityOn');
        this.closePanel();
    },

    openPanel : function()
    {
        if (finder.editFile)
        {
            finder.editFile.closePanel();
        }

        var height = $('div.hFinderUpload').height();

        $('div.hFinderUpload')
            .addClass('hFinderUploadOn')
            .slideDown('slow');

        $('div.hFinderFiles')
            .addClass('hFinderFilesUploadOn')
            .animate({marginBottom: this.getBottomOffset(height + 33)}, 'slow');
    },

    closePanel : function()
    {
        $('div.hFinderUpload')
            .removeClass('hFinderUploadOn')
            .slideUp('slow');

        $('div.hFinderFiles')
            .removeClass('hFinderFilesUploadOn')
            .animate({marginBottom: this.getBottomOffset(23)}, 'slow');

        $('div.hFinderUpload form').each(
            function()
            {
                this.reset();
            }
        );

        this.removeFields();
    },

    getBottomOffset : function(value)
    {    
        return (value + ($('body').attr('id').indexOf('Dialogue') != -1? 19 : 0)) + 'px';
    },

    addFields : function(count)
    {
        $('div.hFinderUpload form').each(
            function()
            {
                this.reset();
            }
        );

        for (var i = 1; i < count; i++)
        {
            var table = $('div#hFinderUploadFieldWrapper table#hFinderUpload-0').clone(true);

            table.attr('id', 'hFinderUpload-' + i);

            table.find('label').each(
                function()
                {
                    var labelFor = this.getAttribute('for');
                    var id = labelFor.split('-');

                    $(this).attr('for', id[0] + '-' + i); 
                }
            );

            table.find('input').each(
                function()
                {
                    var id = this.id.split('-');
                    this.id = id[0] + '-' + i;
                }
            );

            $('div#hFinderUploadFieldWrapper').append(table);
        }
    },

    removeFields : function()
    {
        $('div#hFinderUploadFieldWrapper table').each(
            function()
            {
                if (parseInt($(this).splitId()) > 0)
                {
                    $(this).remove();
                }
            }
        );
    }
};

$(document).ready(
    function()
    {
        finder.upload.ready();
    }
);
