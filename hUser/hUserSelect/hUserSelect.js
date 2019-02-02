if (!user)
{
    var user = {};
}

$.fn.extend({
    getUsersByLetter : function(letter) 
    {
        user.select.selected = this.parents('.hUserSelect');

        isGroup = (user.select.selected.attr('id').indexOf('Groups') != -1);

        var params = {
            letter : letter,
            contactAddressBook : user.select.contactAddressBook
        };

        if (isGroup)
        {
            params.hUserGroups = 1;
        }

        params.operation = 'Get Users By Letter';

        http.get(
            '/hUser/hUserSelect/getUsersByLetter', 
            params,
            function(html)
            {
                user.select.selected.find('div.hUserSelectTableWrapper').html(html);
            }
        );
    }
});

user.select = {
    selected : null,
    
    contactAddressBook : 1,

    ready : function()
    {
        $('li.hUserSelectLetter').hover(
            function()
            {
                $(this).addClass('hUserSelectLetterOver');
            },
            function()
            {
                $(this).removeClass('hUserSelectLetterOver');
            }
        );
    
        $('li.hUserSelectLetter').click(
            function()
            {
                $(this).getUsersByLetter($(this).find('span').text());
            }
        );
        
        $(document).on(
            'mousedown',
            'div.hUserSelectTableWrapper tr',
            function()
            {
                $(this).toggleClass('hUserSelected');
            }
        );
    }
};

$(document).ready(
    function()
    {
        user.select.ready();
    }
);
