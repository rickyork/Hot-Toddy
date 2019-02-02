finder.buttons = {
    ready : function()
    {
        finder.addEvent({
            mousedown : function()
            {
                if ($(this).isDirectory())
                {
                    if (!finder.beginsPath('/Categories'))
                    {
                        if ($('input#hFinderButtonEditFile').length)
                        {
                            $('input#hFinderButtonEditFile').disabled();
                        }
                    }

                    if ($('input#hFinderButtonEditProperties').length)
                    {
                        $('input#hFinderButtonEditProperties').disabled();
                    }

                    if ($('input#hFinderButtonDelete').length)
                    {
                        $('input#hFinderButtonDelete').disabled();
                    }
                }
                else
                {
                    if (!finder.beginsPath('/Categories'))
                    {
                        if ($('input#hFinderButtonEditFile').length)
                        {
                            $('input#hFinderButtonEditFile').removeAttr('disabled');
                        }
                    }

                    if ($('input#hFinderButtonEditProperties').length)
                    {
                        $('input#hFinderButtonEditProperties').removeAttr('disabled');
                    }

                    if ($('input#hFinderButtonDelete').length)
                    {
                        $('input#hFinderButtonDelete').removeAttr('disabled');
                    }
                }
            }
        });

        $('input#hFinderButtonNewFolder').click(
            function(event)
            {
                event.preventDefault();
                finder.newDirectory();
            }
        );

        $('input#hFinderButtonUpload').click(
            function(event)
            {
                event.preventDefault();

                if (!finder.beginsPath('/Categories'))
                {
                    finder.upload.openPanel();
                }
            }
        );

        $('input#hFinderButtonEditFile').click(
            function(event)
            {
                event.preventDefault();

                if (!finder.beginsPath('/Categories'))
                {
                    finder.editFile.openPanel();
                }
            }
        );

        $('input#hFinderButtonEditProperties').click(
            function(event)
            {
                event.preventDefault();

                var selected = hot.selected('hFinder');

                if (selected && selected.length && !selected.isDirectory())
                {
                    finder.buttons.openPropertiesWindow(selected.getFilePath(), selected.splitId());
                }
            }
        );

        $('input#hFinderButtonReplace').click(
            function(event)
            {
                event.preventDefault();
            }
        );

        $('input#hFinderButtonProperties').click(
            function(event)
            {
                event.preventDefault();

                if (!hot.selected('hFinder').isDirectory())
                {
                    $('form#hFinderPropertiesDialogue').openDialogue();
                    finder.buttons.getProperties();
                }
            }
        );

        $('input#hFinderPropertiesDialogueCancel').click(
            function(event)
            {
                event.preventDefault();
                finder.buttons.closePropertiesDialogue();
            }
        );

        $('input#hFinderPropertiesDialogueSave').click(
            function(event)
            {
                event.preventDefault();
                finder.buttons.saveProperties();
            }
        );

        $('input#hFinderButtonDelete').click(
            function(event)
            {
                event.preventDefault();
                hot.selected('hFinder').deleteFile();
            }
        );

        hot.event('requestDirectory', this.onRequestDirectory, this);

        if (finder.beginsPath('/Categories'))
        {
            $('input#hFinderButtonUpload').disabled();
        }
    },

    onRequestDirectory : function()
    {
        if (finder.beginsPath('/Categories'))
        {
            $('input#hFinderButtonUpload').disabled();
            $('input#hFinderButtonEditFile').disabled();
            $('input#hFinderButtonDelete').disabled();
        }
        else
        {
            $('input#hFinderButtonUpload').removeAttr('disabled');
        }
    },

    openPropertiesWindow : function(filePath, fileId)
    {
        window.open(
            hot.path(
                '/Applications/Editor/Properties.html', {
                    path : filePath
                }
            ),
            'hFileId' + fileId,
            'width=900,height=650,scrollbars=no,resizable=yes'
        );
    },

    closePropertiesDialogue : function()
    {
        $('form#hFinderPropertiesDialogue').get(0).reset();
        $('form#hFinderPropertiesDialogue').closeDialogue();
    },

    getProperties : function()
    {
        http.get(
            '/hFile/getProperties', {
                path : hot.selected('hFinder').getFilePath()
            },
            function(xml)
            {
                $('input#hFileTitle').val($(xml).find('hFileTitle').text());
                $('textarea#hFileDescription').val($(xml).find('hFileDescription').text());
            }
        );
    },

    saveProperties : function()
    {
        http.post(
            '/hFile/saveProperties', {
                path : hot.selected('hFinder').getFilePath()
            },
            $('form#hFinderPropertiesDialogue').serialize(),
            function()
            {
                this.buttons.closePropertiesDialogue();
                this.refresh();
            },
            finder
        );
    }
};

$(document).ready(
    function()
    {
        finder.buttons.ready();
    }
);
