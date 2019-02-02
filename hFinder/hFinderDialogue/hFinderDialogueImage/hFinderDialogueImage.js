finder.dialogue.image = {
    ready : function()
    {
        $('input#hFinderDialogueChooseButton').click(
            function(e)
            {
                e.preventDefault();

                var selected = hot.selected('hFinder');

                if (selected.length && !selected.isDirectory())
                {
                    window.opener.SetUrl(selected.getFilePath());
                    self.close();
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
        finder.dialogue.image.ready();
    }
);
