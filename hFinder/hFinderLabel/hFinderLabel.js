if (typeof(finder) == 'undefined')
{
    var finder = {};
}

finder.label = {
    ready : function()
    {
        $('span.hFileLabel').hover(
            function()
            {
                $('div.hFinderLabelName div').show().find('span').text($(this).attr('title'));
            },
            function()
            {
                $('div.hFinderLabelName div').hide();
            }                
        );
    },

    select : function(color)
    {
        $('span.hFileLabel')
            .removeClass('hFinderLabelNoneOn')
            .removeClass('hFinderLabelRedOn')
            .removeClass('hFinderLabelOrangeOn')
            .removeClass('hFinderLabelYellowOn')
            .removeClass('hFinderLabelGreenOn')
            .removeClass('hFinderLabelBlueOn')
            .removeClass('hFinderLabelPurpleOn')
            .removeClass('hFinderLabelGrayOn');

        if (color == '0' || !color)
        {
            color = 'None';
        }

        $('span.hFinderLabel' + color).addClass('hFinderLabel' + color + 'On');
    },

    set : function(path, color)
    {
        $('span.hFileLabel').each(
            function()
            {
                $(this).removeClass('hFinderLabel' + $(this).attr('title') + 'On');
            }
        );

        $('span.hFinderLabel' + color).addClass('hFinderLabel' + color + 'On');

        http.get(
            '/hFile/setLabel', {
                operation : 'Set File Label',
                path : path,
                hFileLabel : color.toLowerCase() 
            },
            function(json)
            {
                if (finder && finder.refresh)
                {
                    finder.refresh();
                }

                if (opener && opener.finder && opener.finder.refresh)
                {
                    opener.finder.refresh();
                }
            }
        );
    }
};

$(document).ready(
    function()
    {
        finder.label.ready();
    }
);
