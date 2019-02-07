if (typeof(dashboard) == 'undefined')
{
    var dashboard = {};
}

dashboard.user = {
    id : 0,
    deleteId : 0,

    ready : function()
    {
        $('ul#hDashboardUserRolodex li').click(
            function()
            {
                dashboard.letter = $(this).find('span').text();
                dashboard.user.get(dashboard.letter);
            }
        );
        
        $('ul#hDashboardUserRolodex li:first-child').click();

        $('div#hDashboardUserAccountNew').click(
            function()
            {
                dashboard.user.reset();
                $('form#HotToddyAdminUserDialogue').openDialogue(true);
            }
        );

        $('input#HotToddyAdminUserDialogueCancel').click(
            function(event)
            {
                event.preventDefault();
                $('form#HotToddyAdminUserDialogue').closeDialogue(true);
            }
        );

        $('input#HotToddyAdminUserDialogueSave').click(
            function(event)
            {
                event.preventDefault();
                dashboard.user.save();
            }
        );

        $('input#HotToddyAdminGroupDialogueCancel').click(
            function(event)
            {
                event.preventDefault();
                $('form#HotToddyAdminGroupDialogue').closeDialogue(true);
            }
        );
        
        $('input#HotToddyAdminGroupDialogueSave').click(
            function(event)
            {
                event.preventDefault();
            }
        );
        
        $('div#hDashboardUserGroupNew').click(
            function()
            {
                $('input#hUserGroupName').val('');
                $('input#hUserGroupEmail').val('');
                $('form#HotToddyAdminGroupDialogue').openDialogue(true);
            }
        );

        $(document)
            .on(
                'click',
                'ul.hPaginationNavigation a',
                function(event)
                {
                    event.preventDefault();

                    dashboard.user.get(
                        dashboard.letter,
                        this.href.split('=').pop()
                    );
                }
            )
            .on(
                'click',
                'span.hDashboardUserAccountEdit',
                function(event)
                {
                    event.stopPropagation();
                    event.preventDefault();

                    dashboard.user.getUser(
                        $(this).parents('tr:first').attr('data-userId')
                    );
                }
            )
            .on(
                'click',
                'span.hDashboardUserAccountEnable',
                function(event)
                {
                    event.preventDefault();
                    event.stopPropagation();
                    dashboard.user.enable($(this).parents('tr:first').attr('data-userId'));
                }
            )
            .on(
                'click',
                'span.hDashboardUserAccountDisable',
                function(event)
                {
                    event.preventDefault();
                    event.stopPropagation();
                    dashboard.user.disable($(this).parents('tr:first').attr('data-userId'));
                }
            )
            .on(
                'click',
                'span.hDashboardUserAccountDelete',
                function(event)
                {
                    event.stopPropagation();
                    event.preventDefault();
                    dashboard.user.deleteUser($(this).parents('tr:first').attr('data-userId'));
                }
            )
            .on(
                'click',
                'div.hDashboardUserAccountsWrapper table tbody tr',
                function()
                {
                    $(this).select('hDashboardUser');
                }
            )
            .on(
                'dblclick',
                'div.hDashboardUserAccountsWrapper table tbody tr',
                function()
                {
                    dashboard.user.getUser(
                        $(this).attr('data-userId')
                    );
                }
            )
            .on(
                'click',
                'ul#hDashboardUserAccountGroups li',
                function()
                {
                    $(this).siblings('li').removeClass('hDashboardUserAccountGroupSelected');
                
                    if ($(this).hasClass('hDashboardUserAccountGroupSelected'))
                    {
                        $(this).removeClass('hDashboardUserAccountGroupSelected');
                    }
                    else
                    {
                        $(this).addClass('hDashboardUserAccountGroupSelected');
                        dashboard.user.get(null, null, $(this).attr('data-userId'));
                    }
                }
            )
            .on(
                'dblclick',
                'ul#hDashboardUserAccountGroups li',
                function()
                {
                    $('form#HotToddyAdminGroupDialogue').openDialogue(true);

                    dashboard.user.getGroup(
                        $(this).attr('data-userId')
                    );
                }
            );
            
        $('input#hUserGroupsAdd').click(
            function(event)
            {
                event.preventDefault();
            }
        );

        $('input#hUserGroupsRemove').click(
            function(event)
            {
                event.preventDefault();
                dashboard.user.removeGroups();
            }
        );
    },
    
    removeGroups : function()
    {
        $('select#hUserGroups option').each(
            function()
            {
                if (this.selected)
                {
                    alert(this.value);
                }
            }
        );
    },

    get : function(letter)
    {
        this.letter = letter;
    
        http.get(
            '/hDashboard/hDashboardUser/get', {
                letter : letter,
                cursor : arguments[1]? arguments[1] : null,
                group : arguments[2]? arguments[2] : null,
                operation : "Get Users"
            },
            function(json)
            {
                if (this.letter)
                {
                    $('li.hDashboardUserAccountGroupSelected')
                        .removeClass('hDashboardUserAccountGroupSelected');
                }

                if (json.users)
                {
                    $('div#hDashboardUsers')
                        .html(json.users);
                }

                if (json.pagination)
                {
                    $('div#hDashboardUserPagination')
                        .html(json.pagination);
                }

                if (!$('ul.hPaginationNavigation a').length)
                {
                    $('div#hDashboardUserPagination').hide();
                }
                else
                {
                    $('div#hDashboardUserPagination').show();
                }

                this.toggleEnabledDisabledVisability();
            },
            this
        );    
    },

    getUser : function(userId)
    {
        this.id = userId;

        $('form#HotToddyAdminUserDialogue').openDialogue(true);

        http.get(
            '/hDashboard/hDashboardUser/getUser', {
                userId : userId,
                operation : "Get User Data"
            },
            function(json)
            {
                hot.console.log(json);
                
                $('input#hContactFirstName').val(json.hContactFirstName);
                $('input#hContactLastName').val(json.hContactLastName);
                $('input#hContactCompany').val(json.hContactCompany);
                $('input#hContactTitle').val(json.hContactTitle);
                $('input#hContactDepartment').val(json.hContactDepartment);
                $('input#hContactWebsite').val(json.hContactWebsite);

                $('input[name="hContactGender"]').removeAttr('checked');

                switch (parseInt(json.hContactGender))
                {
                    case -1: // No Response
                    {
                        $('input#hContactGender--1').attr('checked', 'checked');
                        break;
                    }
                    case 0: // Female
                    {
                        $('input#hContactGender-0').attr('checked', 'checked');
                        break;
                    }
                    case 1: // Male
                    {
                        $('input#hContactGender-1').attr('checked', 'checked');
                        break;
                    }
                }

                $('input#hUserName').val(json.hUserName);
                $('input#hUserEmail').val(json.hUserEmail);

                for (var offset in json.hContactPhoneNumbers)
                {    
                    var phone = json.hContactPhoneNumbers[offset];

                    switch (parseInt(phone.hContactFieldId))
                    {
                        case 4:
                        {
                            $('input#hContactPhoneNumberHome').val(
                                phone.hContactPhoneNumber
                            );
                            
                            break;
                        }
                        case 6:
                        {
                            $('input#hContactPhoneNumberWork').val(
                                phone.hContactPhoneNumber
                            );
                            
                            break;
                        }
                        case 9:
                        {
                            $('input#hContactPhoneNumberFax').val(
                                phone.hContactPhoneNumber
                            )
                            
                            break;
                        }
                    }   
                }

                $('textarea#hContactAddressStreet').html('');
                $('input#hContactAddressCity').val('');
                
                $('select#hLocationStateId option')
                    .removeAttr('selected')
                    .first()
                        .attr('selected', 'selected');
                    
                $('input#hContactAddressPostalCode').val('');
                
                $('select#hLocationCountryId option')
                    .removeAttr('selected')
                    .first()
                        .attr('selected', 'selected');
                    
                for (var offset in json.hContactAddresses)
                {
                    var address = json.hContactAddresses[offset];
                    
                    $('textarea#hContactAddressStreet').html(address.hContactAddressStreet);
                    $('input#hContactAddressCity').val(address.hContactAddressCity);
                    $('select#hLocationStateId option').each(
                        function()
                        {
                            if ($(this).val() == address.hLocationStateId)
                            {
                                $(this).attr('selected', 'selected');
                            }
                        }
                    );
                    
                    $('input#hContactAddressPostalCode').val(address.hContactAddressPostalCode);
                    
                    $('select#hLocationCountryId option').each(
                        function()
                        {
                            if ($(this).val() == address.hLocationCountryId)
                            {
                                $(this).attr('selected', 'selected');
                            }
                        }
                    );
                }

                $('select#hUserGroups').html('');
                
                if (json.hUserGroups && json.hUserGroups.length)
                {
                    $(json.hUserGroups).each(
                        function(offset, group)
                        {
                            $('select#hUserGroups').append(
                                "<option value='" + group.hUserGroupId + "'>" + group.hUserName + "</option>"
                            );
                        }
                    );
                }
            }
        );
    },
    
    getGroup : function(userId)
    {
        this.id = userId;

        http.get(
            '/hDashboard/hDashboardUser/getGroup', {
                userId : userId,
                operation : "Get Group"
            },
            function(json)
            {
                $('input#hUserGroupName').val(json.hUserName);
                $('input#hUserGroupEmail').val(json.hUserEmail);
            },
            this
        );    
    },
    
    reset : function()
    {
        this.id = 0;

        $('input#hContactFirstName').val('');
        $('input#hContactLastName').val('');
        $('input#hContactCompany').val('');
        $('input#hContactTitle').val('');
        $('input#hContactDepartment').val('');
        $('input#hContactWebsite').val('');

        $('input[name="hContactGender"]').removeAttr('checked');

        $('input#hUserName').val('');
        $('input#hUserEmail').val('');
        $('input#hUserPassword').val('');
        $('input#hUserPasswordConfirm').val('');
        
        $('textarea#hContactAddressStreet').val('');
        $('input#hContactAddressCity').val('');
        $('select#hLocationStateId').val('0');
        $('input#hContactAddressPostalCode').val('');
    },
    
    save : function()
    {
        $('select#hUserGroups option').attr('selected', 'selected');
        
        var post = $('form#HotToddyAdminUserDialogue').serialize();

        application.status.message('Saving User Account...');

        http.post(
            '/hDashboard/hDashboardUser/save', {
                userId : this.id  
            }, {
                hContactFirstName : $('input#hContactFirstName').val()
            },
            function(json)
            {
                application.status.message('User Account Saved!', true);
            }
        );
    },
    
    deleteNodes : function(userId)
    {
        $('table#hDashboardUserAccountsEnabled tbody tr, table#hDashboardUserAccountsDisabled tbody tr, ul#hDashboardUserAccountGroups li').each(
            function()
            {
                if (parseInt(userId) == parseInt($(this).attr('data-userId')))
                {
                    $(this).remove();
                }
            }
        );
    },
    
    getNode : function(userId)
    {
        var node = null;
    
        $('table#hDashboardUserAccountsEnabled tbody tr, table#hDashboardUserAccountsDisabled tbody tr').each(
            function()
            {
                if (parseInt(userId) == parseInt($(this).attr('data-userId')))
                {
                    node = $(this);
                    return false;
                }
            }
        );

        return node;
    },

    getAccountName : function(userId)
    {
        var node = this.getNode(userId);
        var name = $.trim(node.find('td.hDashboardUserAccountFullName').text());
        var userName = node.find('td.hDashboardUserAccountUserName').text();

        return (name && name.length)? name : userName;
    },
    
    toggleEnabledDisabledVisability : function()
    {
        if ($('div#hDashboardUserAccountsEnabledWrapper table tbody tr').length)
        {
            $('div#hDashboardUserAccountsEnabledWrapper').show();
        }
        else
        {
            $('div#hDashboardUserAccountsEnabledWrapper').hide();
        }

        if ($('div#hDashboardUserAccountsDisabledWrapper table tbody tr').length)
        {
            $('div#hDashboardUserAccountsDisabledWrapper').show();
            $('div#hDashboardUserAccountsDisabledWrapper').prev().show();
        }
        else
        {
            $('div#hDashboardUserAccountsDisabledWrapper').hide();
            $('div#hDashboardUserAccountsDisabledWrapper').prev().hide();
        }
    },
    
    enable : function(userId)
    {
        http.get(
            '/hDashboard/hDashboardUser/enable', { 
                userId : userId,
                operation : "Enable User Account"
            },
            function(json)
            {
                $('div#hDashboardUserAccountsEnabledWrapper').show();
                var tr = this.getNode(userId).detach();

                tr.find('span.hDashboardUserAccountEnable')
                    .addClass('hDashboardUserAccountDisable')
                    .removeClass('hDashboardUserAccountEnable')
                    .text('Disable');
        
                $('div#hDashboardUserAccountsEnabledWrapper tbody').append(tr);

                this.toggleEnabledDisabledVisability();
            },
            this
        );
    },

    disable : function(userId)
    {
        http.get(
            '/hDashboard/hDashboardUser/disable', { 
                userId : userId,
                operation : "Disable User Account"
            },
            function(json)
            {    
                $('div#hDashboardUserAccountsDisabledWrapper').show();
                var tr = this.getNode(userId).detach();

                tr.find('span.hDashboardUserAccountDisable')
                    .addClass('hDashboardUserAccountEnable')
                    .removeClass('hDashboardUserAccountDisable')
                    .text('Enable');

                $('div#hDashboardUserAccountsDisabledWrapper tbody').append(tr);

                this.toggleEnabledDisabledVisability();
            },
            this
        );
    },

    deleteUser : function(userId)
    {
        this.deleteId = userId;
    
        var name = this.getAccountName(userId);

        dialogue.confirm({
               label : "<p>Are you sure you want to <b>PERMANENTLY</b> delete <i>" + name + "</i>?</p>",
                ok : "Delete User",
                cancel : "Don't Delete User"
            },
            function(confirm)
            {
                if (confirm)
                {
                    http.get(
                        '/hDashboard/hDashboardUser/delete', {
                            userId : this.deleteId
                        },
                        function(json)
                        {
                            this.deleteNodes(this.deleteId);
                        },
                        this
                    );
                }
            },
            this
        );
    }
};

$(document).ready(
    function()
    {
        dashboard.user.ready();
    }
);