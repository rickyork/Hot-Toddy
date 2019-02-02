finder.dialogue.saveAs = {
    ready: function()
    {
        if ($('input#hFinderDialogueSaveAsFileName').val())
        {
            $('input#hFinderDialogueSaveAsButton').removeAttr('disabled');
        }

        $(document).on(
            'click',
            'div.hFinderNode',
            function()
            {
                if (!$(this).isDirectory())
                {
                    $('input#hFinderDialogueSaveAsFileName').val($(this).getFileName());
                    $('input#hFinderDialogueSaveAsButton').removeAttr('disabled');
                }
            }
        );

        $('input#hFinderDialogueSaveAsFileName').keyup(
            function()
            {
                if (!$(this).val())
                {
                    $('input#hFinderDialogueSaveAsButton').attr('disabled', 'disabled');
                }
                else
                {
                    $('input#hFinderDialogueSaveAsButton').removeAttr('disabled');
                }
            }
        );

        $('input#hFinderDialogueSaveAsButton').click(
            function(e)
            {
                e.preventDefault();
                
                var fileName = $('input#hFinderDialogueSaveAsFileName').val();
                var replace = false;
                var error = false;

                if (fileName.length)
                {
                    var file = finder.fileExists(fileName);

                    if (file.exists)
                    {
                        if (confirm("A file or folder with the name, " + fileName + ", already exists.\n\nWould you like to replace it?"))
                        {
                            replace = true;
                        }
                        else
                        {
                            error = true;
                        }
                    }

                    if (!error)
                    {
                        if (get.onSaveAs && window.opener)
                        { 
                            var onSaveAs = eval(hot.ensureValidCallbackFunction("window.opener." + get.onSaveAs));

                            if (typeof(onSaveAs) == 'function')
                            {
                                onSaveAs(file.path, fileName, replace? true : false);
                            }
                            else if (console && console.error)
                            {
                                console.error("Unable to pass saved file to the callback function, because the callback function is not a function.");
                            }

                            self.close();
                        }
                        else
                        {
                            dialogue.alert({
                                title : 'Error',
                                label : 'Save failed because no "onSaveAs" event callback is defined.'
                            });
                        }
                    }
                }
                else
                {
                    
                }
            }
        );

        $('input#hFinderDialogueCancelButton').click(
            function(e)
            {
                e.preventDefault();
                self.close();
            }
        );
    }
};


$(document).ready(
    function()
    {
        finder.dialogue.saveAs.ready();
    }
);
