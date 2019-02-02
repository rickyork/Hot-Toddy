finder.dialogue.directory = {
    ready : function()
    {
        $('input#hFinderDirectoryDialogueChoose').click(
            function(e)
            {
                e.preventDefault();

                var node = hot.selected('hFinderTree');

                if (node && node.length)
                {
                    if (get.onSelectDirectory && window.opener)
                    {
                        if (get.onSelectDirectory.indexOf("'") != -1 || get.onSelectDirectory.indexOf('"') != -1)
                        {
                            return;
                        }

                        var id = node.splitId();
                        var path = node.attr('data-file-path');
                        
                        if (isNaN(parseInt(id)))
                        {
                            var matches = id.match(/\d/g);    // Make sure Id is a number...    sometimes it's not.

                            if (matches && matches.length)
                            {
                                id = matches.join('');
                            } 
                        }

                        if (!id)
                        {
                            id = 0;
                        }

                        // hFinderTreeCategoriesRoot
                        if (path == '/Categories')
                        {
                            // Sorry chaps, you can't pick that one.
                            return;
                        }
                        
                        var onSelectDirectory = eval(hot.ensureValidCallbackFunction("window.opener." + get.onSelectDirectory));

                        if (typeof(onSelectDirectory) == 'function')
                        {
                            onSelectDirectory(id, path);
                        }
                        else if (console && console.error)
                        {
                            console.error("Unable to pass selected directory to the callback function, because the callback function is not a function.");
                        }

                        window.close();
                    }
                    else
                    {
                        dialogue.alert({
                            title : 'Error',
                            label : 'Unable to select a directory because no "onSelectDirectory" event callback is defined.'
                        });
                    }
                }
            }
        );

        $('input#hFinderDirectoryDialogueCancel').click(
            function(e)
            {
                e.preventDefault();
                window.close();
            }
        );

        $('input#hFinderDirectoryDialogueNew').click(
            function(e)
            {
                e.preventDefault();
                finder.tree.newFolder();
            }
        );
        
        $('input#hFinderDirectoryDialogueDelete').click(
            function(e)
            {
                e.preventDefault();
                finder.tree.deleteFolder();
            }
        );
    }
};

$(document).ready(
    function()
    {
        finder.dialogue.directory.ready();
    }
);
