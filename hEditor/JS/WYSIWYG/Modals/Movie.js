$.fn.extend({
    setMovie : function()
    {
        editor.movie.video = this;
    }
});

editor.movie = {
    ready : function()
    {
        $('li#hEditorTemplateMovieSave button').click(
            function()
            {
                editor.movie.save({
                    path : $('input#hEditorMovie').val()
                });

                editor.movie.closeDialogue();
            }
        );

        $('li#hEditorTemplateMovieCancel button').click(
            function()
            {
                editor.movie.closeDialogue();
            }
        );

        $('li#hEditorTemplateMovieRemove button').click(
            function()
            {
                $(this).commandEvent(
                    function()
                    {
                        editor.movie.removeMovie();
                    }
                );
            }
        );

        $('li#hEditorTemplateMovieBrowse').click(
            function()
            {
                editor.movie.openFinder();
            }
        );
    },

    openFinder : function()
    {
        this.chooseDialogue = hot.window(
            '/Applications/Finder/index.html',
            {
                dialogue : 'Choose',
                onChooseFile : 'editor.movie.onChooseFile'
            },
            600, 400,
            'hFinderChoose',
            {
                scrollbars : false,
                resizable : true
            }
        );
    },

    removeMovie : function()
    {
        if (this.video && this.video.length)
        {
            if (this.video.parents('div.hEditorTemplateNodeWrapper').length)
            {
                this.video
                    .parents('div.hEditorTemplateNodeWrapper')
                    .remove();
            }
            else
            {
                this.video
                    .remove();
            }
        }

        this.closeDialogue();
    },

    onChooseFile : function(id, path)
    {
        $('input#hEditorMovie')
            .val(path);
    },

    getNode : function()
    {
        if (this.video)
        {
            return this.video;
        }
        else
        {
            var node = editor.getNodeAtCaretPosition();

            if (node && node.prop('nodeName').toLowerCase() == 'video')
            {
                this.video = node;
                return this.video;
            }
            else
            {
                return $('<video/>');
            }
        }
    },

    save : function(obj)
    {
        var video = this.getNode();

        video.attr('src', obj.path);

        if (!this.video)
        {
            video.insertAtSelection();
        }
    },

    openDialogue : function()
    {
        var video = this.getNode();

        if (video.length)
        {
            $('input#hEditorMovie')
                .val(video.attr('src'));
        }

        if (this.video && this.video.length)
        {
            $('div#hEditorTemplateMovieRemoveWrapper')
                .removeClass('hEditorTemplateDisabledButtons');
        }

        editor.openModal();

        $('div#hEditorTemplateMovie')
            .slideDown('slow');

        $('li#hEditorTemplateMovie')
            .commandOn();
    },

    closeDialogue : function()
    {
        editor.closeModal();

        $('div#hEditorTemplateMovie')
            .slideUp('slow');

        $('li#hEditorTemplateMovie')
            .commandOff();

        this.video = null;

        $('div#hEditorTemplateMovieRemoveWrapper')
            .addClass('hEditorTemplateDisabledButtons');

        $('input#hEditorMovie')
            .val('');

        $('input#hEditorMovieTarget')
            .removeAttr('checked');
    }
};

$(document).ready(
    function()
    {
        editor.movie.ready();
    }
);