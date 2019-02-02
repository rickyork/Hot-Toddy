$.fn.extend({
    getPhotos : function()
    {
        http.get(
            '/hPhoto/getPhotos', {
                operation : 'Get Photos',
                path : this.attr('data-file-path')
            },
            function(html)
            {
                $('div#hPhotoView ul').html(html);
                photo.resize(photo.slider);
                photo.events();
            }
        );
    }
});

if (typeof(photo) == 'undefined')
{
    var photo = {};
}

$.extend(
    photo, {
        lastPath : null,
        
        slider: 0,
      
        panelReady : function()
        {
            if (typeof(finder) != 'undefined' && typeof(finder.tree) != 'undefined')
            {
                finder.tree.addEvent(
                    'click',
                    function()
                    {
                        $(this).getPhotos();
                    },
                    $('div#hPhotoTree'),
                    true
                );
            }
    
            $('div#hPhotoThumbnailSlider input').change(
                function()
                {
                    photo.resize(this.value, true);
                }
            );
            
            if (this.slider)
            {
                this.resize(this.slider);
                $('div#hPhotoThumbnailSlider input').attr('title', this.slider).val(this.slider);
            }
            
            if ($('div#hPhotoTree div.hFinderTreeRoot').length)
            {
                setTimeout("$('div#hPhotoTree div.hFinderTreeRoot').getPhotos();", 3000);
            }
        },
    
        resize : function(size)
        {
            $('li.hPhoto').width(size);
             
            if (arguments[1])
            {
                this.saveSliderPosition(size);
            }
        },
    
        saveSliderPosition : function(size)
        {
            http.get(
                '/hPhoto/saveSliderPosition', {
                    operation : 'Save Photo Slider Position',
                    slider : size
                },
                function(json)
                {
    
                }
            );
        },
    
        events : function()
        {
            $('li.hPhoto')
                .click(
                    function()
                    {
                        $(this).select('hPhoto');
                    }
                )
                .mousedown(
                    function()
                    {
                        if (this.dragDrop)
                        {
                            // IE won't come out to play without this method call.
                            this.dragDrop();
                        }
                    }
                )
                .bind(
                    'dragstart',
                    function(e)
                    {
                        e.originalEvent.stopPropagation();
                        e.originalEvent.dataTransfer.effectAllowed = 'all';
    
                        var path = $(this).attr('data-file-path');
                        var caption = $(this).find('div.hPhotoCaption span').text();
    
                        photo.lastPath = path;
    
                        var img = document.createElement('img');
                        img.src = path;
                        img.title = path;
                        img.alt = caption;
    
                        var html = $(img).outerHTML();
    
                        hot.fire('photoDragStart', $(img));
    
                        // Data is passed this way for two reasons
                        //   1. IE only supports a precious few types of data, one of them being text.
                        //   2. The relevant event data needs to be passed this way in order to 
                        //      facilitate drag and drop between multiple instances of the browser.
                        e.originalEvent.dataTransfer.setData('text/html', html);
                    }
                )
                .bind(
                    'dragend',
                    function(e)
                    {
                        hot.fire('photoDragEnd', $(this));
                    }
                )
                .each(
                    function()
                    {
                        this.draggable = true;
                    }
                );
        }
    }
);

$(document).ready(
    function()
    {
        photo.panelReady();
    }
);