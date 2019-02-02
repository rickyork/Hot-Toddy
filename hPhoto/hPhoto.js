
if (typeof(photo) == 'undefined')
{
    var photo = {};
}

$.fn.extend({

    openLightbox : function()
    {
        $('div#hPhotoLightbox').fadeIn();
        $('div#hPhotoLightboxImage').html(
            $('<img/').attr(
                src : $(this).attr('data-file-path')
                alt : 'img'
            )
        );

        photo.selectedPath = $(this).attr('data-file-path');

        $('div#hPhotoLightboxImage img').Jcrop({
                onChange : photo.crop,
                onSelect : photo.crop
            },
            function()
            {
                photo.cropAPI = this;
            }
        );
    },

    selectNextFile : function()
    {
        if (this.next('li.hPhoto').length)
        {
            return (
                this.next('li.hPhoto')
                    .select('hPhoto')
                    .scrollToPhoto()
            );
        }
        else
        {
            return (
                this.siblings('li.hPhoto:first')
                    .select('hPhoto')
                    .scrollToPhoto()
            );
        }
    },

    selectPreviousFile : function()
    {
        if (this.prev('li.hPhoto').length)
        {
            return (
                this.prev('li.hPhoto')
                    .select('hPhoto')
                    .scrollToPhoto()
            );
        }
        else
        {
            return (
                this.siblings('li.hPhoto:last')
                    .select('hPhoto')
                    .scrollToPhoto()
            );
        }
    },

    selectBelowFile : function()
    {
        var photo = this;
        var position = this.position();
        var left = position.left;
        var top = position.top;

        if (this.next('li.hPhoto').length)
        {
            this.nextAll('li.hPhoto').each(
                function()
                {
                    var position = $(this).position();

                    if (position.top > top && position.left == left)
                    {
                        photo = $(this);
                        return false;
                    }
                }
            );
        }
        else
        {
            photo = this.siblings('li.hPhoto:first');
        }

        return (
            photo.select('hPhoto')
                 .scrollToPhoto()
        );
    },

    selectAboveFile : function()
    {
        var photo = this;
        var position = this.position();
        var left = position.left;
        var top = position.top;

        if (this.prev('li.hPhoto').length)
        {
            this.prevAll('li.hPhoto').each(
                function()
                {
                    var position = $(this).position();

                    if (position.top < top && position.left == left)
                    {
                        photo = $(this);
                        return false;
                    }
                }
            );
        }
        else
        {
            photo = this.siblings('li.hPhoto:last');
        }

        return (
            photo.select('hPhoto')
                 .scrollToPhoto()
        );
    },

    scrollToPhoto : function()
    {
        /*
var offset = this.offset();

        hot.console.log(
            'Photo position top: ' + offset.top +
            ' left: ' + offset.left +
            ' scroll top: ' + $('div#hPhotoViewOuter').scrollTop() +
            ' scroll left: ' + $('div#hPhotoViewOuter').scrollLeft()
        );
*/

        return this;
    }
});

$.extend(
    photo, {

        cropAPI : null,

        crop : function(coordinates)
        {
            http.get(
                '/hPhoto/crop', {
                    x : coordinates.x,
                    y : coordinates.y,
                    x2 : coordinates.x2,
                    y2 : coordinates.y2,
                    width : coordinates.w,
                    height : coordinates.h,
                    path : photo.selectedPath
                },
                function(json)
                {

                }
            );
        },

        ready : function()
        {
            $(document).on(
                'dblclick',
                'li.hPhoto',
                function()
                {
                    $(this).openLightbox();
                }
            );

            $('input#hPhotoLightboxCancel').click(
                function(event)
                {
                    event.preventDefault();
                    photo.closeLightbox();
                }
            );

/*
            $('input#hPhotoLightboxControlZoom').change(
                function()
                {
                    $('div#hPhotoLightboxImage img').css({
                        width: this.value + '%',
                        height: 'auto'
                    });
                }
            );
*/
        },

        closeLightbox : function()
        {
            $('div#hPhotoLightbox').fadeOut();
        },

        directional : function(event)
        {
            var node = hot.selected('hPhoto');

            if (node && node.length)
            {
                return node;
            }

            $('li.hPhoto:first').select('hPhoto');
            return false;
        }
    }
);

keyboard
    .shortcut(
        {
            selectAboveFile : 'Up Arrow',
            disableShortcutOnInput : true
        },
        function(event)
        {
            var node = photo.directional(event);

            if (node !== false)
            {
                node.selectAboveFile();
            }
        }
    )
    .shortcut(
        {
            selectNextFile : 'Right Arrow',
            disableShortcutOnInput: true
        },
        function(event)
        {
            var node = photo.directional(event);

            if (node !== false)
            {
                node.selectNextFile();
            }
        }
    )
    .shortcut(
        {
            selectBelowFile : 'Down Arrow',
            disableShortcutOnInput : true
        },
        function(event)
        {
            var node = photo.directional(event);

            if (node !== false)
            {
                node.selectBelowFile();
            }
        }
    )
    .shortcut(
        {
            selectPreviousFile : 'Left Arrow',
            disableShortcutOnInput : true
        },
        function(event)
        {
            var node = photo.directional(event);

            if (node !== false)
            {
                node.selectPreviousFile();
            }
        }
    )
    .shortcut(
        {
            openLightbox : 'Space',
            disableShortcutOnInput : true
        },
        function(event)
        {
            if (!$('div#hPhotoLightbox:visible').length)
            {
                if (hot.selected('hPhoto').length)
                {
                    hot.selected('hPhoto')
                       .openLightbox();
                }
            }
            else
            {
                photo.closeLightbox();
            }
        }
    );

$(document).ready(
    function()
    {
        photo.ready();
    }
);