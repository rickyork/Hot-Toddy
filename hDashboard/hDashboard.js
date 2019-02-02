var dashboard = {
    ready : function()
    {
        $('td.hDashboardApplication').click(
            function(e)
            {
                e.stopPropagation();
                hot.window(
                    $(this).find('a').attr('href'),
                    null,
                    1050,
                    650,
                    '_blank', {
                        scrollbars: true,
                        resizable: true
                    }
                );
            }
        );
    
        $('td.hDashboardApplication a').click(
            function(e)
            {
                e.preventDefault();
            }
        );
        
        $('a#HotToddyLogin').click(
            function(e)
            {
                e.preventDefault();
                dialogue.login();
            }
        );
    }
};

$(document).ready(
    function()
    {
        dashboard.ready();
    }
);
