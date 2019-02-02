if (typeof(user) == 'undefined')
{
    var user = {};
}

user.register =
{
    ready : function()
    {
        $('div#hUserRegister input').blur(
            function()
            {
                user.register.save();
            }
        );
    },

    wave : function()
    {
        var post = '';

        $('div#hUserRegister input').each(
            function()
            {
                post += '&' + this.name + '=' + encodeURIComponent(this.value);
            }
        );

        $('div#hUserRegister textarea').each(
            function()
            {
                post += '&' + this.name + '=' + encodeURIComponent(this.value);
            }
        );

        $('div#hUserRegister select').each(
            function()
            {
                post += '&' + this.name + '=' + encodeURIComponent(this.value);
            }
        );

        // Save data as it is entered, so that it doesn't have to be entered again if 
        // the user leaves the current location and returns.
        http.post(
            '/hUser/hUserRegister/save',
            post,
            function()
            {
                
            }
        );
    }
};

$(document).ready(
    function()
    {
        user.register.ready();
    }
);
