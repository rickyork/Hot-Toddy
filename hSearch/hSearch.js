var search = {
    ready : function()
    {
        $('div.hSearchResultWrapper').click(
            function()
            {
                $(this).find('p.hFileLastModified').slideToggle('slow');
            }
        );
    }
};

$(document).ready(
    function()
    {
        search.ready();
    }
);
