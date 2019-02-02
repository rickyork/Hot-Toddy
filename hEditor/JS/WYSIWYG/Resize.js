$.extend(

    editor, {

        resizeReady : function()
        {
            editor.resize();

            $(window).resize(
                function()
                {
                    editor.resize();
                }
            );
        },

        resize : function()
        {
            if ($(window).width() < 1280)
            {
                $('body').addClass('hEditorTemplateLowRes');
            }
            else
            {
                $('body').removeClass('hEditorTemplateLowRes');
            }

            this.setSizeAndPosition();
        },

        setSizeAndPosition : function()
        {
            $('div#hEditorTemplateDocumentWrapper').height(
                $('div#hEditorTemplateClone')
                    .outerHeight(true)
            );

            $('div#hEditorTemplateOverlay').css({

                width : (
                    $('div#hEditorTemplateBodyWrapperInner')
                        .outerWidth() + 20
                ) + 'px',

                height : (
                    $('div#hEditorTemplateBodyWrapperInner')
                        .outerHeight() + 20
                ) + 'px'

            });

            var offset = $('div#hEditorTemplateDocumentWrapper').offset();

            $('div#hEditorTemplateClone')
                .height('auto')
                .css({
                    top : offset.top + $('div#hEditorTemplateBodyWrapper').scrollTop(),
                    left : offset.left + $('div#hEditorTemplateBodyWrapper').scrollLeft(),
                    width : $('div#hEditorTemplateDocumentWrapper').width()
                });
        }
    }
);

$(document).ready(
    function()
    {
        editor.resizeReady();
    }
);