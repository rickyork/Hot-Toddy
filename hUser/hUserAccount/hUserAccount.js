var account = {
    ready : function()
    {
        $('td#hUserAccountFinder .hUserAccountFinder').click(
            function()
            {
                hot.window(
                    '/Applications/Finder', {
                    
                    },
                    1200,
                    800,
                    'Finder', {
                        scrollbars : false,
                        resizable : true
                    }
                );
            }
        );
    }
};

$(document).ready(
    function()
    {
        account.ready();
    }
);