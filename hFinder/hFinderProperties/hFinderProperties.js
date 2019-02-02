if (typeof(finder) == 'undefined')
{
    var finder = {};
}

finder.properties = {
    ready : function()
    {
        $('a').attr('target', '_blank');
    }
};

$(document).ready(
    function()
    {
        finder.properties.ready();
    }
);
