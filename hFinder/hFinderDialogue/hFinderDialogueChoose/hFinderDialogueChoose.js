finder.dialogue.choose = {

    ready: function() 
    {
        if (get.types)
        {
            if (get.types.indexOf(',') != -1)
            {
                finder.types = get.types.split(',');
            }
            else
            {
                finder.types = [get.types];
            }
        }
        else
        {
            finder.types = ['files'];
        }

        if (finder.types.length)
        {        
            $('div.hFinderNode').disableByType();

            hot.event(
                'requestDirectory',
                function(event)
                {
                    event.nodes.disableByType();
                },
                this
            );
        }

        hot.event(
            'hFinderSelected',
            function(event)
            {            
                if ($(this).hasClass('hFinderDisabled'))
                {
                    $('input#hFinderDialogueChooseButton').attr('disabled', 'disabled');
                }
                else
                {
                    $('input#hFinderDialogueChooseButton').removeAttr('disabled');
                }
            }
        );

        $('input#hFinderDialogueChooseButton').click(
            function(event)
            {
                event.preventDefault();

                var selected = hot.selected('hFinder');

                if (selected && selected.length)
                {
                    if (get.onChooseFile && window.opener)
                    {
                        var onChooseFile = eval(hot.ensureValidCallbackFunction("window.opener." + get.onChooseFile));

                        if (typeof(onChooseFile) == 'function')
                        {
                            onChooseFile(selected.splitId(), selected.getFilePath(), selected.getFileName());
                        }
                        else if (console && console.error)
                        {
                            console.error("Unable to pass selected file to the callback function, because the callback function is not a function.");
                        }

                        self.close();
                    }
                    else
                    {
                        dialogue.alert({
                            title : 'Error',
                            label : 'Unable to select a file because no "onChooseFile" event callback is defined.'
                        });
                    }
                }
            }
        );

        $('input#hFinderDialogueCancelButton').click(
            function(event)
            {
                event.preventDefault();
                self.close();
            }
        );
    }
};

$(document).ready(
    function()
    {
        finder.dialogue.choose.ready();
    }
);
