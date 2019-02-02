if (typeof(calendar) == 'undefined')
{
    var calendar = {};
}

calendar.news = {
    ready : function()
    {
        $('li.hCalendarNewsStory a').click(
            function(e)
            {
                var ext = ['pdf', 'jpg', 'gif', 'doc', 'png', 'xls', 'tif', 'tiff', 'jpe', 'jpeg', 'jp2'];

                var hasExt = false;
                var link = this.href;

                $(ext).each(
                    function(index, item)
                    {
                        if (link.indexOf(item) != -1)
                        {
                            hasExt = true;
                            return false;
                        }
                    }
                );

                if (hasExt)
                {
                    e.preventDefault();
                    window.open(this.href, '_blank', '');
                }
            }
        );
    }
};

$(document).ready(
    function()
    {
        calendar.news.ready();
    }
);
