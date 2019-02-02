var defaultTemplate = {
    ready : function()
    {
        $(document).on(
            'click',
            'a.HotToddyLoginLogout',
            function(e)
            {
                e.preventDefault();

                if (this.id == 'HotToddyLogin')
                {
                    defaultTemplate.login();
                }
                else if (this.id == 'HotToddyLogout')
                {
                    defaultTemplate.logout();
                }
            }
        );
    },

    login : function()
    {
        dialogue.login({
                title : "Login"
            },
            function(loggedIn, responseCode)
            {
                if (loggedIn === true || !isNaN(responseCode) && responseCode === 1)
                {
                    // Login was successful.
                    $('a#HotToddyLogin')
                        .attr('id', 'HotToddyLogout')
                        .text('Logout');

                    hot.fire('login');
                }
                
            },
            defaultTemplate
        );
    },

    logout : function()
    {
        http.get(
            '/hUser/hUserLogout/logout',
            function()
            {
                $('a#HotToddyLogout')
                    .attr('id', 'HotToddyLogin')
                    .text('Login');

                hot.fire('logout');
            }
        );
    }
};

$(document).ready(
    function()
    {
        defaultTemplate.ready();
    }
);