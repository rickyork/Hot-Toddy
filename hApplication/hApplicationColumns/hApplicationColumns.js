var application = {
    ready : function()
    {
        $(document).on(
            'click',
            'div#hApplicationColumn li',
            function()
            {
                $(this).select('hApplicationColumnItem');
            }
        );
    }
};

$(document).ready(
    function()
    {
        application.ready();
    }
);