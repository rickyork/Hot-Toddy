if (typeof(user) == 'undefined')
{
    var user = {};
}

$.fn.extend({
    setPermissions : function()
    {
        var isGroup = $(this).isGroup();
        var group = isGroup? 'Groups' : 'Users';
        var source = $('div#hUserSelectPermissions' + group);
        var prefix = 'hUserPermissions' + group;

        switch (true)
        {
            case $(this).attr('id').indexOf('Both') != -1:
            {
                var read = $('select#' + prefix + 'Read').get(0);
                var write = $('select#' + prefix + 'Write').get(0);
                break;
            };
            case $(this).attr('id').indexOf('Read') != -1:
            {
                var read = $('select#' + prefix + 'Read').get(0);
                var write = false;
                break;
            };
            case $(this).attr('id').indexOf('Write') != -1:
            {
                var read = false;
                var write = $('select#' + prefix + 'Write').get(0);
                break;
            };
            default:
            {
                hot.console.error('Permissions: Unable to remove permissions, an error has occurred.');
            };
        }

        if ($(this).attr('id').indexOf('Remove') != -1)
        {
            user.permissions.removeSelected(read);
            user.permissions.removeSelected(write);
        }
        else
        {
            user.permissions.copySelected(source, read);
            user.permissions.copySelected(source, write);
        }

        return this;
    },
    
    selectTab : function()
    {
        if (user.permissions.selectedTab)
        {
            $('div#hUserPermissions' + user.permissions.selectedTab.splitId()).removeClass('hUserPermissionsOn');
        }

        user.permissions.selectedTab = this;
        $('div#hUserPermissions' + user.permissions.selectedTab.splitId()).addClass('hUserPermissionsOn');

        return this;
    },

    isGroup : function()
    {
        return ($(this).attr('id').indexOf('Groups') != -1);
    }
});

user.permissions = {

    isPosted : false,
    selectedTab : null,

    fields : [
        'hUserPermissionsUsersRead',
        'hUserPermissionsUsersWrite',
        'hUserPermissionsGroupsRead',
        'hUserPermissionsGroupsWrite'
    ],

    ready : function()
    {
        if (this.isPosted)
        {
            window.close();
        }

        this.selectedTab = $('li#hUserPermissionsTab-OwnerWorld');

        $('ul#hUserPermissionsTabs li')
            .mousedown(
                function()
                {
                    $(this).selectTab();
                }
            )
            .hover(
                function()
                {
                    $(this).addClass('hUserPermissionsTabOver');
                },
                function()
                {
                    $(this).removeClass('hUserPermissionsTabOver');
                }
            );

        $('input#hUserPermissionsDialogueCancel').click(
            function()
            {
                this.disabled = true;
                self.close();
            }
        );

        $('input#hUserPermissionsDialogueSave').click(
            function(e)
            {
                this.disabled = true;
                user.permissions.selectAll();
                $(this).parents('form').submit();
            }
        );

        $('input.hUserPermissionsAction').click(
            function(e)
            {
                e.preventDefault();
                $(this).setPermissions();
            }
        );
    },

    selectAll : function()
    {
        $(this.fields).each(
            function()
            {
                var select = $('select#' + this).get(0);

                for (var n = 0; n < select.length; n++)
                {
                    select.options[n].selected = true;
                }
            }
        );
    },

    removeSelected : function(select)
    {
        if (select && select.length)
        {
            for (var i = 0; i < select.length; i++)
            {
                if (select.options[i] && select.options[i].selected)
                {
                    $(select.options[i]).remove();
                }
            }
        }
    },

    copySelected : function(source, destination)
    {
        if (source && destination)
        {
            source.find('tr.hUserSelected').each(
                function()
                {
                    var userId = $(this).find('td.hUserSelectUserId').text();
                    var userName = $(this).find('td.hUserSelectUserName').text();

                    if (!user.permissions.optionExists(destination, userId))
                    {
                        user.permissions.appendOption(destination, userName, userId, true);
                    }
                }
            );
        }
    },

    getAllOptions : function(select, field)
    {
        var select = getElement(select);
        var rtn = '';

        if (select.length)
        {
            for (var i = 0; i < select.length; i++)
            {
                if (select.options[i] && select.options[i].value)
                {
                    rtn += '&' + field + '[]=' + select.options[i].value;
                }
            }
        }

        return rtn;
    },

    getSelected : function(select)
    {
        var options = [];

        for (var i = 0; i < select.length; i++)
        {
            if (select.options[i] && select.options[i].selected)
            {
                options[select.options[i].value] = select.options[i].text;
            }
        }

        return options;
    },

    appendOption : function(select, text, value, selected)
    {
        var option = document.createElement('option');
        option.value = value;
        option.text = text;
        option.selected = selected;

        (hot.userAgent == 'ie')? select.add(option) : select.add(option, null);
    },

    optionExists : function(select, value)
    {
        for (var i = 0; i < select.length; i++)
        {
            if (select.options[i] && select.options[i].value == value)
            {
                return true;
            }
        }

        return false;
    }
};

$(document)
    .ready(
        function()
        {
            user.permissions.ready();
        }
    )
    .bind(
        'touchmove',
         function(e)
         {
             e.preventDefault();
         }
    );
