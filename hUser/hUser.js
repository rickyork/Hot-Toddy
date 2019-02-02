var user = {

    selected : null,

    ready : function()
    {
        if (contact.record)
        {
            hot.events({
                getContact : {
                    fn : this.getContact,
                    context : this
                },
                saveContact : {
                    fn : this.saveContact,
                    context : contact.record
                },
                contactSaved : {
                    fn : this.contactSaved,
                    context : this
                },
                newContact : {
                    fn : this.newContact,
                    context : this
                },
                enableEdit : {
                    fn : this.enableEdit,
                    context : this
                },
                disableEdit : {
                    fn : this.disableEdit,
                    context : this
                }
            });

            contact.record.autoSave = false;
        }

        hot.event('groupDrop', this.addToGroup, this);

        $('input#hUserGroupAdd').click(
            function(event)
            {
                event.preventDefault();
                user.addToGroups();
            }
        );

        $('input#hUserGroupRemove').click(
            function(event)
            {
                event.preventDefault();
                user.removeFromGroups();
            }
        );

        $(document).on(
            'click',
            'div#hUserActivityPagination a',
            function(event)
            {
                event.preventDefault();

                if (this.href && this.href.length)
                {
                    user.getActivity(this.href.split('=').pop());
                }
            }
        );

        $(document).on(
            'click',
            'div#hUserHistoryPagination a',
            function(event)
            {
                event.preventDefault();

                if (this.href && this.href.length)
                {
                    user.getHistory(this.href.split('=').pop());
                }
            }
        );

        this.disableEdit();
    },

    enableEdit : function(data)
    {
        $('input#hUserPassword').parents('tr:first').show();
        $('input#hUserPasswordConfirm').parents('tr:first').show();
        $('select#hUserGroups').parents('tr:first').show();
        $('input#hUserGroupAdd').parents('tr:first').show();
        $('ul#hUserMemberGroupsList').hide();
        $('select#hUserMemberGroups').show();
    },

    disableEdit : function(data)
    {
        $('input#hUserPassword').parents('tr:first').hide();
        $('input#hUserPasswordConfirm').parents('tr:first').hide();
        $('select#hUserGroups').parents('tr:first').hide();
        $('input#hUserGroupAdd').parents('tr:first').hide();

        this.listGroups();
    },

    listGroups : function()
    {
        var html = '';

        $('select#hUserMemberGroups option').each(
            function()
            {
                html += "<li>" + $(this).text() + "</li>";
            }
        );

        $('select#hUserMemberGroups').hide();

        if (!$('ul#hUserMemberGroupsList').length)
        {
            $('select#hUserMemberGroups').before(
                $("<ul/>")
                    .attr('id', 'hUserMemberGroupsList')
                    .html(html)
            );
        }
        else
        {
            $('ul#hUserMemberGroupsList').html(html);
        }

        $('ul#hUserMemberGroupsList').show();
    },

    getActivity : function(searchCursor)
    {
        http.get(
            '/hUser/getActivity', {
                operation : 'Get User Activity',
                hSearchCursor : searchCursor,
                hUserId : $('div.hUserId').text()
            },
            function(json)
            {
                $('div#hUserActivity tbody').html(json.activities);
                $('div#hUserActivityPagination').html(json.pagination);
            }
        );
    },

    getHistory : function(searchCursor)
    {
        http.get(
            '/hUser/getDocumentHistory', {
                operation : 'Get Document History',
                hSearchCursor : searchCursor,
                hUserId : $('div.hUserId').text()
            },
            function(json)
            {
                $('div#hUserHistory tbody').html(json.history);
                $('div#hUserHistoryPagination').html(json.pagination);
            }
        );
    },

    addToGroup : function(source, destination)
    {
        var userId = 0;

        // Contact / or / Group is present in $node
        if (source.hasClass('hContactRecord'))
        {
            var userId = parseInt(source.find('span.hContactRecordUserId').text());
        }

        if (userId > 0)
        {
            http.get(
                '/hUser/addUserToGroup', {
                    operation : 'Add User to Group',
                    hUserId : userId,
                    hUserGroupId : destination.splitId()
                },
                function(json)
                {

                }
            );
        }
    },

    addToGroups : function()
    {
         var userGroupsSelect = $('select#hUserGroups').get(0);
         var userMemberGroupsSelect = $('select#hUserMemberGroups').get(0);

         for (var n = 0; n < userGroupsSelect.options.length; n++)
         {
             if (userGroupsSelect.options[n].selected)
             {
                 var inGroup = false;

                 for (var i = 0; i < userMemberGroupsSelect.options.length; i++)
                 {
                     if (userMemberGroupsSelect.options[i].value == userGroupsSelect.options[n].value)
                     {
                         inGroup = true;
                         break;
                     }
                 }

                 if (!inGroup)
                 {
                     userMemberGroupsSelect.appendChild(userGroupsSelect.options[n].cloneNode(true));
                 }
             }
         }
    },

    removeFromGroups : function()
    {
         $('select#hUserMemberGroups option:selected').remove();
    },

    toggleGroupFields : function(isGroup)
    {
        var groupFields = [
            'hUserGroupOwner',
            'hUserGroupIsElevated',
            'hUserGroupPassword',
            'hUserGroupConfirmPassword',
            'hUserGroupLoginEnabled'
        ];

        if (isGroup)
        {
            $('input#hUserEmail').parents('tr:first').hide();
            $('input#hUserPassword').parents('tr:first').hide();
            $('input#hUserPasswordConfirm').parents('tr:first').hide();
            $('input#hUserName').parents('tr:first').find('label').text('Group Name:');
            $('span.hContactFirstName').parents('li:first').hide();
            $('li.hContactTitle').hide();
            $('li.hContactDepartment').hide();
            $('div#hContactSummaryDiv').addClass('hContactSummaryIsGroup');
            $('img.hContactUserIcon').attr('src', '/images/icons/128x128/group.png');

            if ($('input#hUserGroupOwner').attr('type').toLowerCase() == 'text')
            {
                $(groupFields).each(
                    function(key, value)
                    {
                        $('input#' + value).parents('tr:first').show();
                    }
                );
            }
        }
        else
        {
            $('input#hUserEmail').parents('tr:first').show();

            if ($('input.hContactEdit').hasClass('hContactEditOn'))
            {
                $('input#hUserPassword')
                    .parents('tr:first')
                    .show();

                $('input#hUserPasswordConfirm')
                    .parents('tr:first')
                    .show();
            }

            $('input#hUserName')
                .parents('tr:first')
                .find('label')
                .text('Username:');

            $('span.hContactFirstName')
                .parents('li:first')
                .show();

            $('li.hContactTitle').show();
            $('li.hContactDepartment').show();
            $('div#hContactSummaryDiv').removeClass('hContactSummaryIsGroup');

            $('img.hContactUserIcon').attr('src', '/images/icons/128x128/user.png');

            if ($('input#hUserGroupOwner').attr('type').toLowerCase() == 'text')
            {
                $(groupFields).each(
                    function(key, value)
                    {
                        $('input#' + value).parents('tr:first').hide();
                    }
                );
            }
        }
    },

    getContact : function(data)
    {
        //var $hContact = hot.selected('hContact');
        var contactId = contact.record.hContactId;

        this.toggleGroupFields(data.isGroup);

        if (contactId > 0)
        {
            http.get(
                '/hUser/getLoginInformation', {
                    operation : 'Get Login Information',
                    hContactId : contactId,
                    hContactConf: contact.conf
                },
                function(json)
                {
                    user.setLoginInformation(json);
                }
            );
        }
    },

    setLoginInformation : function(json)
    {
        $('input#hUserName').val(json.hUserName);
        $('input#hUserEmail').val(json.hUserEmail);

        $('select#hUserGroups option').removeAttr('selected');
        $('select#hUserMemberGroups option').remove();

        $(json.hUserGroups).each(
            function(key, value)
            {
                $('select#hUserMemberGroups').append(
                    $('select#hUserGroups')
                        .find('option[value="' + value + '"]')
                        .clone(true)
                );
            }
        );

        if (json.hUserGroupOwner)
        {
            $('input#hUserGroupOwner').val(json.hUserGroupOwner);
        }

        var groupInputType = $('input#hUserGroupOwner').attr('type').toLowerCase();

        if (typeof(json.hUserGroupIsElevated) != 'undefined')
        {
            if (groupInputType == 'text')
            {
                if (parseInt(json.hUserGroupIsElevated))
                {
                    $('input#hUserGroupIsElevated').attr('checked', true);
                }
                else
                {
                    $('input#hUserGroupIsElevated').removeAttr('checked');
                }
            }
            else
            {
                $('input#hUserGroupIsElevated').val(json.hUserGroupIsElevated);
            }
        }

        if (typeof(json.hUserGroupLoginEnabled) != 'undefined')
        {
            if (groupInputType == 'text')
            {
                if (parseInt(json.hUserGroupLoginEnabled))
                {
                    $('input#hUserGroupLoginEnabled').attr('checked', true);
                }
                else
                {
                    $('input#hUserGroupLoginEnabled').removeAttr('checked');
                }
            }
            else
            {
                $('input#hUserGroupLoginEnabled').val(json.hUserGroupLoginEnabled);
            }
        }

        if (json.hUserCreatedFormatted)
        {
            $('td#hUserLogId').text(json.hUserId);
            $('td#hUserCreated').text(json.hUserCreatedFormatted);
            $('td#hUserLoginCount').text(json.hUserLoginCount);
            $('td#hUserLastLogin').text(json.hUserLastLoginFormatted);
            $('td#hUserLastFailedLogin').text(json.hUserLastFailedLoginFormatted);
            $('td#hUserLastModified').text(json.hUserLastModifiedFormatted);
            $('td#hUserLastModifiedBy').text(json.hUserLastModifiedByName);
        }
        else
        {
            $('td#hUserLogId').text('<i>No Data</i>');
            $('td#hUserCreated').html('<i>No Data</i>');
            $('td#hUserLoginCount').html('<i>No Data</i>');
            $('td#hUserLastLogin').html('<i>No Data</i>');
            $('td#hUserLastFailedLogin').html('<i>No Data</i>');
            $('td#hUserLastModified').html('<i>No Data</i>');
            $('td#hUserLastModifiedBy').html('<i>No Data</i>');
        }

        if (json.hUserHistory)
        {
            $('div#hUserHistory tbody').html(json.hUserHistory);
        }
        else
        {
            $('div#hUserHistory tbody').html('');
        }

        if (json.hUserHistoryPagination)
        {
            $('div#hUserHistoryPagination').html(json.hUserHistoryPagination);
        }
        else
        {
            $('div#hUserHistoryPagination').html('');
        }

        if (json.hUserActivity)
        {
            $('div#hUserActivity tbody').html(json.hUserActivity);
        }
        else
        {
            $('div#hUserActivity tbody').html('');
        }

        if (json.hUserActivityPagination)
        {
            $('div#hUserActivityPagination').html(json.hUserActivityPagination);
        }
        else
        {
            $('div#hUserActivityPagination').html('');
        }

        this.listGroups();

        hot.fire(
            'getLoginInformation', {
                json : json
            }
        );
    },

    inGroup : function(group)
    {
        var inGroup = false;

        $('select#hUserMemberGroups option').each(
            function()
            {
                if ($(this).text() == group)
                {
                    inGroup = true;
                    return;
                }
            }
        );

        return inGroup;
    },

    updateInformation : function()
    {
        var hContactId = contact.record.hContactId;
        var hUserId = contact.record.hUserId;
        var hUserName = $('input#hUserName').val();
        var hUserEmail = $('input#hUserEmail').val();

        var div = $('div#hContactRecordId-' + hContactId);

        var newContactRecord = false;

        if (!div.length)
        {
            if (contact.record.isGroup)
            {
                div = $('div.hContactRecordTemplate.hContactGroupRecord').clone(true);
            }
            else
            {
                div = $('div.hContactRecordTemplate').not('.hContactGroupRecord').clone(true);
            }

            newContactRecord = true;
        }

        div.find('span.hContactRecordFirstName').text(
            $('span.hContactFirstName span').getFieldValue()
        );

        div.find('span.hContactRecordLastName').text(
            $('span.hContactLastName span').getFieldValue()
        );

        div.find('li.hContactRecordTitle').text(
            $('li.hContactTitle span').getFieldValue()
        );

        div.find('li.hContactRecordCompany').text(
            $('li.hContactCompany span').getFieldValue()
        );

        div.find('li.hContactRecordEmail').html(
            $("<a/>")
                .attr('href', 'mailto:' + hUserEmail)
                .text(hUserEmail)
        );

        div.find('li.hContactRecordUserName').text(hUserName);

        if (newContactRecord)
        {
            div.attr('id', 'hContactRecordId-' + hContactId);
            div.removeClass('hContactRecordTemplate');

            div.find('span.hContactRecordId').text(hContactId);
            div.find('span.hContactRecordUserId').text(hUserId);

            $('div#hContactResults').append(div);

            $('div.hContactNoResults').remove();
        }

        if (contact.record.isGroup)
        {
            var li = $('li#hContactGroup-' + hUserId);

            if (!hUserName.length)
            {
                hUserName = $('li.hContactCompany span').getFieldValue();
            }

            if (li.length)
            {
                $('li#hContactGroup-' + hUserId + ' span').text(hUserName);
            }
            else
            {
                var li = $('ul.hContactGroupPane li.hContactGroupTemplate').clone(true);

                li.attr('id', 'hContactGroup-' + hUserId);
                li.find('span').text(hUserName);
                li.removeClass('hContactGroupTemplate');
                li.Select('hContactGroup');
                $('ul.hContactGroupPane').append(li);

                $('select#hUserGroups').append(
                    $("<option/>")
                        .val('hUserId')
                        .text(hUserName)
                );
            }
        }
    },

    newContact : function(data)
    {
        this.toggleGroupFields(data.isGroup);

        var fields = [
            'hUserName',
            'hUserEmail',
            'hUserPassword',
            'hUserPasswordConfirm',
            'hUserGroupOwner',
            'hUserGroupPassword',
            'hUserGroupPasswordConfirm'
        ];

        $(fields).each(
            function(key)
            {
                var node = $('input#' + this);

                if (node && node.length)
                {
                    node.val('');
                }
            }
        );

        var groupInputType = $('input#hUserGroupOwner').attr('type').toLowerCase();

        if (groupInputType == 'text')
        {
            $('input#hUserGroupIsElevated').removeAttr('checked');
            $('input#hUserGroupLoginEnabled').removeAttr('checked');
        }
        else
        {
            $('input#hUserGroupIsElevated').val('');
            $('input#hUserGroupLoginEnabled').val('');
        }

        $('li#hDialogueTab-hContactSummaryDiv').click();

        $('select#hUserMemberGroups option').remove();
        $('select#hUserGroups option').removeAttr('selected');

        if (!data.isGroup && hot.selected('hContactGroup').length)
        {
            // Add the user to the selected group...
            $('select#hUserGroups option[value="' + hot.selected('hContactGroup').splitId() + '"]')
                .attr('selected', true);

            this.addToGroups();
        }
    },

    saveContact : function(data)
    {
        var hContactAddressBookId = parseInt($('div.hContactAddressBookId').text());
        var hContactId = parseInt($('div.hContact').splitId());

        var hContactCompany = $('li.hContactCompany span').getFieldValue();
        var hUserPassword = $('input#hUserPassword').val();
        var hUserPasswordConfirm = $('input#hUserPasswordConfirm').val();
        var hUserName = $('input#hUserName').val();
        var hUserEmail = $('input#hUserEmail').val();

        if (!hUserName.length && hContactCompany.length)
        {
            hUserName = hContactCompany;
            $('input#hUserName').val(hContactCompany)
        }

        var hUserGroupOwner = $('input#hUserGroupOwner').val();
        var hUserGroupPassword = $('input#hUserGroupPassword').val();
        var groupInputType = $('input#hUserGroupOwner').attr('type');

        if (groupInputType == 'text')
        {
            var hUserGroupIsElevated = $('input#hUserGroupIsElevated').is(':checked')? 1 : 0;
            var hUserGroupLoginEnabled = $('input#hUserGroupLoginEnabled').is(':checked')? 1 : 0;
            var hUserGroupConfirmPassword = $('input#hUserGroupConfirmPassword').val();

            if (hUserGroupPassword !== hUserGroupConfirmPassword && data.isGroup)
            {
                dialogue.alert({
                    title : 'Error',
                    label : 'Unable to save contact because the group password and confirm password do not match.'
                });

                return false;
            }
        }
        else
        {
            var hUserGroupIsElevated = $('input#hUserGroupIsElevated').val();
            var hUserGroupLoginEnabled = $('input#hUserGroupLoginEnabled').val();
        }

        if (!hContactId && !data.isGroup)
        {
            if (!hUserPassword || !hUserPasswordConfirm)
            {
                dialogue.alert({
                    title : 'Error',
                    label : 'Unable to save contact because no password entered.'
                });

                return false;
            }
        }

        if (hUserPassword !== hUserPasswordConfirm && !data.isGroup)
        {
            dialogue.alert({
                title : 'Error',
                label : 'Unable to save contact because password and confirmation do not match.'
            });

            return false;
        }
        else
        {
            this.post += '&hUserPassword=' + encodeURIComponent(hUserPassword);
        }

        if (!hUserName)
        {
            dialogue.alert({
                title : 'Error',
                label : 'Unable to save contact because no user name is entered.'
            });

            return false;
        }
        else
        {
            this.post += '&hUserName=' + encodeURIComponent(hUserName);
        }

        if (!hUserEmail && !data.isGroup)
        {
            dialogue.alert({
                title : 'Error',
                label : 'Unable to save contact because no email address is entered.'
            });

            return false;
        }
        else
        {
            this.post += '&hUserEmail=' + encodeURIComponent(hUserEmail);
        }

        if (data.isGroup)
        {
            this.post +=
                '&hUserGroupOwner=' + encodeURIComponent(hUserGroupOwner) +
                '&hUserGroupPassword=' + encodeURIComponent(hUserGroupPassword) +
                '&hUserGroupIsElevated=' + encodeURIComponent(hUserGroupIsElevated) +
                '&hUserGroupLoginEnabled=' + encodeURIComponent(hUserGroupLoginEnabled);
        }

        var hUserMemberGroups = $('select#hUserMemberGroups').get(0);

        for (var i = 0; i < hUserMemberGroups.options.length; i++)
        {
            this.post += '&hUserGroups[]=' + hUserMemberGroups.options[i].value;
        }

        this.post += '&hUserIsGroup=' + (data.isGroup? 1 : 0);

        return true;
    },

    contactSaved : function()
    {
        this.updateInformation();
    }
};

$(document).ready(
    function()
    {
        user.ready();
    }
);
