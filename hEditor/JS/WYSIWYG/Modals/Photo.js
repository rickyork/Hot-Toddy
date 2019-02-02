$.fn.extend({
    setImage : function()
    {
        editor.image.img = this;
    }
});

editor.image = {
    ready : function()
    {
        $('li#hEditorTemplateImageSave button').click(
            function()
            {
                editor.image.save({
                    path : $('input#hEditorImage').val()
                });

                editor.image.closeDialogue();
                //document.execCommand('createImage', false, $('input#hEditorImage').val());
            }
        );

        $('li#hEditorTemplateImageCancel button').click(
            function()
            {
               editor.image.closeDialogue();
            }
        );

        $('li#hEditorTemplateImageRemove button').click(
            function()
            {
                $(this).commandEvent(
                    function()
                    {
                        editor.image.removeImage();
                    }
                );
            }
        );

        $('li#hEditorTemplateImageBrowse').click(
            function()
            {
                editor.image.openFinder();
            }
        );
    },

    openFinder : function()
    {
        // Open a Finder Choose dialogue so that the user may select
        // an image from the file system.
        this.chooseDialogue = hot.window(
            '/Applications/Finder/index.html',
            {
                dialogue : 'Choose',
                onChooseFile : 'editor.image.onChooseFile'
            },
            600, 400,
            'hFinderChoose',
            {
                scrollbars : false,
                resizable : true
            }
        );
    },

    removeImage : function()
    {
        // When the "Remove" button is pressed, delete the img node
        // from the DOM.
        if (this.img && this.img.length)
        {
            if (this.img.parents('div.hEditorTemplateNodeWrapper').length)
            {
                this.img.parents('div.hEditorTemplateNodeWrapper').remove();
            }
            else
            {
                this.img.remove();
            }
        }

        this.closeDialogue();
    },

    onChooseFile : function(id, path)
    {
        $('input#hEditorImage').val(path);
    },

    getNode : function()
    {
        // If an img node is already set, return that one.
        if (this.img && this.img.length)
        {
            return this.img;
        }
        else
        {
            var node = editor.getNodeAtCaretPosition();

            if (node && node.length && node.get(0).nodeName.toLowerCase() == 'img')
            {
                // If no img node is set, look at the node residing
                // where the text caret presently resides.  If that
                // node is an img node, set that to be the img node
                // and return that one.
                this.img = node;
                return this.img;
            }
            else
            {
                // As a last resort, simply create a new img node
                // and return the new one.
                return $('<img/>');
            }
        }
    },

    save : function(obj)
    {
        var img = this.getNode();

        img.attr('src', obj.path);

        // If there is a selection, replace it with the img node.
        // If there is just a caret position, insert the node at
        // that position.
        img.insertAtSelection();
    },

    openDialogue : function()
    {
        var img = this.getNode();

        if (img && img.length)
        {
            $('input#hEditorImage').val(img.attr('src'))
        }

        if (this.img && this.img.length)
        {
            $('div#hEditorTemplateImageRemoveWrapper')
                .removeClass('hEditorTemplateDisabledButtons');
        }

        editor.openModal();

        $('div#hEditorTemplateImage')
            .slideDown('slow');

        $('li#hEditorTemplateImage')
            .commandOn();
    },

    closeDialogue : function()
    {
        editor.closeModal();

        $('div#hEditorTemplateImage')
            .slideUp('slow');

        $('li#hEditorTemplateImage')
            .commandOff();

        this.img = null;

        $('div#hEditorTemplateImageRemoveWrapper')
            .addClass('hEditorTemplateDisabledButtons');

        $('input#hEditorImage').val('');

        $('input#hEditorImageTarget')
            .removeAttr('checked');
    }
};

$(document).ready(
    function()
    {
        editor.image.ready();
    }
);