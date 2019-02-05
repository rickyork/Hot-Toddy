$.fn.extend({
    selectData : function()
    {
        if (contact.record.selectedData)
        {
            contact.record.selectedData.removeClass('hContactDataOn');
        }

        contact.record.selectedData = $(this);
        contact.record.selectedData.addClass('hContactDataOn');
    },

    selectAddressData : function()
    {
        if (contact.record.selectedAddressData)
        {
            contact.record.selectedAddressData.removeClass('hContactAddressDataOn');
        }

        contact.record.selectedAddressData = $(this);
        contact.record.selectedAddressData.addClass('hContactAddressDataOn');
    },

    deleteData : function()
    {
        var li = $(this).parents('li:first');
        var id = li.splitNumericId();

        if (id <= 0)
        {
            li.removeData();
        }
        else
        {
            var type = '';
            var label = '';

            switch (true)
            {
                case li.hasClass('hContactAddress'):
                {
                    label = 'Address';
                    break;
                };
                case li.hasClass('hContactPhoneNumber'):
                {
                    label = 'Phone Number';
                    break;
                };
                case li.hasClass('hContactEmailAddress'):
                {
                    label = 'Email Address';
                    break;
                };
            }

            contact.record.selectedDataNode = li;
            contact.record.selectedDataLabel = label;

            dialogue.confirm({
                title : 'Confirm Deletion',
                label : "<p>Are you certain you want to <b>PERMANENTLY</b> delete this <i>" + label + "</i>?</p>",
                ok : "Delete " + label,
                cancel : "Don't Delete " + label,
                callback : {
                    fn : function(confirm)
                    {
                        if (confirm)
                        {
                            http.get(
                                '/hContact/deleteData', {
                                    operation : 'Delete ' + this.selectedDataLabel,
                                    hContactId : $('div.hContact').splitId(),
                                    hContactAddressBookId : $('div.hContactAddressBookId').text(),
                                    data : this.selectedDataLabel,
                                    dataId : this.selectedDataNode.splitId()
                                },
                                function(json)
                                {
                                    this.selectedDataNode.removeData();
                                },
                                this
                            );
                        }
                    },
                    context : contact.record
                }
            });
        }
    },

    removeData : function()
    {
        if (!this.siblings().not('li.hContactDataTemplate, li.hContactNoData').length)
        {
            this.siblings('li.hContactNoData').removeClass('hContactNoDataOff');
        }

        this.remove();
    },

    addDataModule : function()
    {
        var plural, singular, fieldCount;

        switch (true)
        {
            case this.hasClass('hContactEmailAddressAdd'):
            {
                 plural = 'hContactEmailAddresses';
                 singular = 'hContactEmailAddress';

                 fieldCount = contact.record.hContactEmailAddressNewCount;
                 contact.record.hContactEmailAddressNewCount--;
                 break;
            };
            case this.hasClass('hContactPhoneNumberAdd'):
            {
                plural = 'hContactPhoneNumbers';
                singular = 'hContactPhoneNumber';

                fieldCount = contact.record.hContactPhoneNumberNewCount;
                contact.record.hContactPhoneNumberNewCount--;
                break;
            };
            case this.hasClass('hContactAddressAdd'):
            {
                plural = 'hContactAddresses';
                singular = 'hContactAddress';

                fieldCount = contact.record.hContactAddressNewCount;
                contact.record.hContactAddressNewCount--;
                break;
            };
        }

        if (plural && singular)
        {
            var template = $('ul.' + plural + ' li.hContactDataTemplate').clone();

            template.addClass('hContactDataTemplateApplied');

            $('ul.' + plural).append(template);

            var template = $('ul.' + plural).find('li.hContactDataTemplateApplied');

            if (template.length)
            {
                template.attr('id', singular + '-' + fieldCount);

                $(['hContactField', 'hLocationState', 'hLocationCountry']).each(
                    function()
                    {
                        template.find('select.' + this).change(contact.record.onChangeSelect);
                    }
                );

                template
                    .removeClass('hContactDataTemplateApplied')
                    .removeClass('hContactDataTemplate');

                if ($('ul.' + plural + ' li.hContactNoData').length)
                {
                    $('ul.' + plural + ' li.hContactNoData')
                        .addClass('hContactNoDataOff');
                }

                if (singular == 'hContactAddress')
                {
                    //contact.record.addAddressDataEvents.call(template);
                }
                else
                {
                    //contact.record.addDataEvents.call(template);
                }

                // IE loses the input field defaults...
                var hContactFieldId = '';

                switch (singular)
                {
                    case 'hContactEmailAddress':
                    {
                        hContactFieldId = contact.emailAddressFieldId;
                        break;
                    }
                    case 'hContactPhoneNumber':
                    {
                        hContactFieldId = contact.phoneNumberFieldId;
                        break;
                    }
                    case 'hContactAddress':
                    {
                        hContactFieldId = contact.addressFieldId;
                        break;
                    }
                }

                var select = template.find('select.hContactField').get(0);

                for (var i = 0; i < select.options.length; i++)
                {
                    if (parseInt(select.options[i].value) == parseInt(hContactFieldId))
                    {
                        select.options[i].selected = true;
                    }
                }

                if (singular == 'hContactAddress')
                {
                    template.find('select.hLocationCountry')
                            .val($('li.hContactDataTemplate select.hLocationCountry').val());
                }

                template.find('.hContactData').editData();
            }
        }

        //this.save();
    },

    getFieldValue : function()
    {
        return (this.find('i').length? '' : this.text());
    },

    setEditData : function()
    {
        var data = this.find('span');

        var input = this.find('input, textarea, select');

        if (data.find('i').length)
        {
            input.val(this.attr('title'));
            input.addClass('hContactDataEditLabel');
        }
        else
        {
            input.val(data.text());
        }
    },

    prepareData : function()
    {
        this.find('span').addClass('hContactDataEdit');

        switch (true)
        {
            case this.find('select').length > 0:
            {
                this.find('select').addClass('hContactSelectEdit');
                break;
            };
            case this.hasClass('hContactAddressStreet'):
            {
                if (!this.find('textarea').length)
                {
                    var textarea = document.createElement('textarea');
                    textarea.cols = 40;
                    textarea.rows = 2;
                    this.append(textarea);
                    this.setEditData();
                }
                break;
            };
            case !this.find('input').length:
            {
                var input = document.createElement('input');
                input.type = 'text';
                this.append(input);
                this.setEditData();
                break;
            };
            case this.find('input').length > 0:
            {
                this.find('input').focus();
                break;
            };
        }
    },

    editData : function()
    {
        this.each(
            function()
            {
                $(this).prepareData();
            }
        )
    },

    dataEdited : function()
    {
        this.each(
            function()
            {
                var node = $(this);

                if (node.is('select'))
                {
                    node.parent().find('span').removeClass('hContactDataEdit');
                    node.removeClass('hContactSelectEdit');
                }
                else
                {
                    var span = node.parent().find('span');

                    if (node.val())
                    {
                        if (node.val() == node.parents('.hContactData').attr('title'))
                        {
                            node.val('');
                        }

                        span.text(node.val());
                    }
                    else
                    {
                        span.html("<i>" + (node.parents('.hContactData').attr('title')) + "</i>");
                    }

                    span.removeClass('hContactDataEdit');

                    //this.remove();
                }
            }
        );

        return this;
    }
});

contact.record = {

    selectedDataNode : null,

    hContacts : [
        'hContactId',
        'hUserId',
        'hContactFirstName',
        'hContactLastName',
        'hContactDisplayName',
        'hContactNickName',
        'hContactWebsite',
        'hContactCompany',
        'hContactTitle',
        'hContactDepartment'
    ],

    hContactAddresses : [
        'hContactId',
        'hContactAddressId',
        'hContactFieldId',
        'hContactAddressStreet',
        'hContactAddressCity',
        'hLocationStateId',
        'hContactAddressPostalCode',
        'hLocationCountryId',
        'hContactAddressLatitude',
        'hContactAddressLongitude',
        'hContactAddressIsDefault'
    ],

    hContactEmailAddresses : [
        'hContactId',
        'hContactEmailAddressId',
        'hContactFieldId',
        'hContactEmailAddress'
    ],

    hContactPhoneNumbers : [
        'hContactId',
        'hContactPhoneNumberId',
        'hContactFieldId',
        'hContactPhoneNumber'
    ],

    hContactInternetAccounts : [
        'hContactId',
        'hContactInternetAccountId',
        'hContactFieldId',
        'hContactInternetAccount'
    ],

    hContactEmailAddressNewCount : 0,
    hContactPhoneNumberNewCount : 0,
    hContactAddressNewCount : 0,

    autoSave : true,

    selectedData : null,

    hContactId: 0,

    reenableEdit : false,

    newContact : function(isGroup)
    {
        contact.record.get(0, isGroup);
    },

    ready : function()
    {
        $('form#hContactSummaryDialogue fieldset:not(#hContactFieldset)')
            .addClass('hContactEditOff')
            .find('input, textarea, select')
            .attr('readonly', 'readonly');

        $('div#hContactSummaryDiv').hide();

        $('img#hContactEditGroup').click(
            function(event)
            {
                event.preventDefault();

                if (hot.selected('hContactGroup').length)
                {
                    http.get(
                        '/hContact/getContactIdByUserId', {
                            operation : 'Edit Group',
                            hUserId : hot.selected('hContactGroup').splitId()
                        },
                        function(json)
                        {
                            var contactId = parseInt(json);

                            if (contactId > 0)
                            {
                                contact.enableContactControls();
                                contact.record.reenableEdit = true;
                                contact.record.get(contactId, true);
                            }
                        }
                    );
                }
            }
        );

        $('img#hContactNew').click(
            function()
            {
                contact.record.newContact(false);
            }
        );

        $('img#hContactDelete').click(
            function(event)
            {
                event.preventDefault();
                contact.record.deleteContact();
            }
        );

        $('img#hContactNewGroup').click(
            function(event)
            {
                event.preventDefault();
                contact.record.newContact(true);
            }
        );

        $('input#hContactSave').click(
            function(event)
            {
                event.preventDefault();
                contact.record.disableEdit();
                contact.record.reenableEdit = true;
                contact.record.save();
            }
        );

        $('input#hContactEdit').click(
            function(event)
            {
                event.preventDefault();

                if ($(this).hasClass('hContactEditOn'))
                {
                    contact.record.reenableEdit = false;
                    contact.record.disableEdit();
                }
                else
                {
                    contact.record.enableEdit();
                }
            }
        );

        $(document).on(
            'click',
            'img.hContactDataDelete',
            function()
            {
                $(this).deleteData();
            }
        );

        $(document).on(
            'change',
            '.hContactData select',
            function()
            {
                if (this.value)
                {
                    var label = '';

                    for (var i = 0; i < this.options.length; i++)
                    {
                        if (this.options[i].selected)
                        {
                            label = this.options[i].text;
                        }
                    }

                    switch (true)
                    {
                        case ($(this).hasClass('hContactField')):
                        {
                            $(this).parent().find('span').text(label + ':');
                            break;
                        };
                        case ($(this).hasClass('hLocationState')):
                        {
                            $(this).parents('span').find('span').text(this.value.split(':').pop());
                            break;
                        };
                        case ($(this).hasClass('hLocationCountry')):
                        {
                            var country = this.value.split(':');
                            $(this).parent().find('span').text(label);
                            
                            var countryAbbr = country[1] && country[1].length? country[1].toLowerCase() : 'us';                                

                            $(this)
                                .parents('.hContactAddress')
                                .find('img.hContactAddressCountryFlag')
                                .attr({
                                    src: hot.path('/images/icons/48x48/flags/' + countryAbbr + '.png'),
                                    alt: countryAbbr.toUpperCase()
                                });

                            break;
                        };
                        default:
                        {
                            $(this)
                                .parents('.hContactData')
                                .find('span.hContactDataEdit')
                                .text(this.value);
                        };
                    }

                    // Set the label to be the label, and if there is no value, the
                    // value as well.
                    $(this).parents('.hContactData').find('span').each(
                        function()
                        {
                            if (!$(this).hasClass('hContactField'))
                            {
                                var i = $(this).find('span i');

                                if (i.length)
                                {
                                    i.text(label);
                                }
                            }
                        }
                    );
                }
            }
        );

        $(document)
            .on(
                'focus',
                '.hContactData input, .hContactData select, .hContactData textarea',
                function()
                {
                    $(this).addClass('hContactDataEditOn');

                    if ($(this).val() == $(this).parents('.hContactData').attr('title'))
                    {
                        $(this).val('');
                        $(this).removeClass('hContactDataEditLabel');
                    }
                }
            )
            .on(
                'blur',
                '.hContactData input, .hContactData select, .hContactData textarea',
                function()
                {
                    $(this).removeClass('hContactDataEditOn');

                    if (!$(this).val())
                    {
                        var label = $(this).parents('.hContactData').attr('title');
                        $(this).val(label);
                        $(this).addClass('hContactDataEditLabel');
                    }
                }
            );

        $(document).on(
            'click',
            '.hContactData a',
            function()
            {
                $(this).attr('target', '_blank');
            }
        );

        $(document)
            .on(
                'mousedown',
                'div.hContactControls img',
                function()
                {
                    $(this).sourceFile(this.alt.toLowerCase() + '_pressed.png');
                }
            )
            .on(
                'mouseup',
                'div.hContactControls img',
                function()
                {
                    $(this).sourceFile(this.alt.toLowerCase() + '.png');
                }
            )
            .on(
                'click',
                'div.hContactControls img',
                function()
                {
                    $(this).addDataModule();
                }
            );

        $(document)
            .on(
                'mouseenter',
                'li.hContactPhoneNumber, li.hContactEmailAddress',
                function()
                {
                    $(this).addClass('hContactDataOver');
                }
            )
            .on(
                'mouseleave',
                'li.hContactPhoneNumber, li.hContactEmailAddress',
                function()
                {
                    $(this).removeClass('hContactDataOver');
                }
            );

        $(document)
            .on(
                'mouseenter',
                'li.hContactAddress',
                function()
                {
                    $(this).addClass('hContactAddressDataOver');
                }
            )
            .on(
                'mouseleave',
                'li.hContactAddress',
                function()
                {
                    $(this).removeClass('hContactAddressDataOver');
                }
            );

        hot.event(
            'hContactSelected',
            function()
            {
                contact.record.disableEdit();
                contact.record.get(parseInt($(this).splitId()), $(this).hasClass('hContactGroupRecord'));
            }
        );
    },

    focusedColumn : 'contacts',

    getFocusItems : function()
    {
        if (this.focusedColumn == 'contacts')
        {
            return {
                selected : 'div.hContactSelected',
                item : 'div.hContactRecord',
                select : 'hContact'
            };
        }
        else
        {
            return {
                selected : 'li.hContactGroupSelected',
                item : 'li.hContactGroup',
                select : 'hContactGroup'
            };
        }
    },

    scrollPane : function()
    {

    },

    enableEdit : function()
    {
        $('input#hContactEdit').addClass('hContactEditOn');
        $('.hContactData').editData();

        $('.hContactDataDelete').removeClass('hContactDataDeleteOff');
        $('.hContactControls').removeClass('hContactControlsOff');

        $('form#hContactSummaryDialogue fieldset:not(#hContactFieldset)')
            .addClass('hContactEditOn')
            .removeClass('hContactEditOff')
            .find('input, textarea, select').removeAttr('readonly');

        $('.hContactData input, .hContactData textarea, .hContactData select').show();

        if (this.isGroup)
        {
            $('span.hContactCompany input').focus();
        }
        else
        {
            $('span.hContactFirstName input').focus();
        }

        hot.fire(
            'enableEdit', {
                isGroup : this.isGroup
            }
        );
    },

    disableEdit : function()
    {
        $('input.hContactEdit').removeClass('hContactEditOn');

        $('.hContactData input, .hContactData textarea, .hContactData select').dataEdited().hide();

        $('.hContactDataDelete').addClass('hContactDataDeleteOff');
        $('.hContactControls').addClass('hContactControlsOff');

        $('input#hContactEdit').removeClass('hContactEditOn');

        $('form#hContactSummaryDialogue fieldset:not(#hContactFieldset)')
            .removeClass('hContactEditOn')
            .addClass('hContactEditOff')
            .find('input, textarea, select')
                .attr('readonly', true);

        hot.fire(
            'disableEdit', {
                isGroup : this.isGroup
            }
        );
    },

    get : function(contactId, isGroup)
    {
        // Get contact information...
        this.hContactId = contactId;
        this.isGroup = isGroup;

        http.get(
            '/hContact/' + (contactId? 'get' : 'new') + 'Record', {
                operation : 'Get Record',
                hContactAddressBookId : contact.addressBookId,
                hContactId : contactId,
                hContactConf : contact.conf
            },
            function(json)
            {
                if (!$('form#hContactSummaryDialogue div.hDialogueContentWrapper').children(':visible').length)
                {
                    $('li#hDialogueTab-hContactSummaryDiv').click();
                    $('div#hContactSummaryDiv').show();
                }

                $('fieldset#hContactFieldset table tbody td').html(json);

                if (this.hContactId > 0)
                {
                    hot.fire(
                        'getContact', {
                            isGroup : this.isGroup
                        }
                    );
                }
                else
                {
                    hot.fire(
                        'newContact', {
                            isGroup : this.isGroup
                        }
                    );
                }

                if (this.hContactId == 0)
                {
                    $('.hContactEmailAddressAdd').addDataModule();
                    $('.hContactPhoneNumberAdd').addDataModule();
                    $('.hContactAddressAdd').addDataModule();
                }

                if (this.reenableEdit || !this.hContactId)
                {
                    this.enableEdit();
                    this.reenableEdit = false;
                }
            },
            contact.record
        );
    },

    save : function()
    {
        var addressBookId = parseInt($('div.hContactAddressBookId').text());
        var contactId = parseInt($('div.hContact').splitId());

        this.post =
            'hContactId=' + contactId +
            '&hUserId=' + $('div.hUserId').text();

        hot.fire(
            'saveContact', {
                isGroup : this.isGroup
            }
        );

        // Determine Column
        $([
            'hContactFirstName',
            'hContactLastName',
            'hContactTitle',
            'hContactDepartment',
            'hContactCompany',
            'hContactWebsite'
        ]).each(
            function()
            {
                var node = $('.' + this + ' span');
                var value = node.length? node.getFieldValue() : '';

                if (value == 'undefined')
                {
                    value = '';
                }

                contact.record.post += '&' + this + '=' + encodeURIComponent(value);
            }
        );

        // Email Addresses...
        this.getFieldData('hContactEmailAddress', 'es');

        // Phone Numbers
        this.getFieldData('hContactPhoneNumber', 's');

        // Addresses...
        var i = 0;

        $('li.hContactAddress').each(
            function()
            {
                if (!$(this).hasClass('hContactDataTemplate'))
                {
                    contact.record.appendToPost(i, 'hContactAddressId', $(this).splitNumericId());
                    contact.record.appendToPost(i, 'hContactFieldId', $(this).find('span.hContactField select').val());
                    contact.record.appendToPost(i, 'hContactAddressStreet', $(this).find('span.hContactAddressStreet span').getFieldValue());
                    contact.record.appendToPost(i, 'hContactAddressCity', $(this).find('span.hContactAddressCity span').getFieldValue());

                    var hLocationStateId = $(this).find('select.hLocationState').length?
                        $(this).find('select.hLocationState').val().split(':').shift() : 0;

                    contact.record.appendToPost(i, 'hLocationStateId', hLocationStateId);

                    var hContactAddressPostalCode = $(this).find('span.hContactAddressPostalCode').length?
                        $(this).find('span.hContactAddressPostalCode span').getFieldValue() : '';

                    contact.record.appendToPost(i, 'hContactAddressPostalCode', hContactAddressPostalCode);

                    if ($(this).find('select.hLocationCountry').length)
                    {
                        contact.record.appendToPost(i, 'hLocationCountryId', $(this).find('select.hLocationCountry').val().split(':').shift());
                    }
                    else
                    {
                        contact.record.appendToPost(i, 'hLocationCountryId', 223);
                    }

                    i++;
                }
            }
        );

        application.status.message('Saving Contact...');

        http.post({
                url : '/hContact/save',
                operation : 'Save Contact',
                onErrorCallback : function(json)
                {
                    hot.fire(
                        'saveContactError', {
                            json : json
                        }
                    );

                    application.status.message('Save Failed!', 'Fade');
                },
                onErrorCallbackContext : contact.record
            }, {
                hContactAddressBookId : addressBookId,
                hContactConf : contact.conf
            },
            this.post,
            function(json)
            {
                application.status.message('Contact Saved!', 'Fade');

                var hContactId = parseInt(json.hContactId);
                var hUserId = parseInt(json.hUserId);

                this.hUserId = hUserId;
                this.hContactId = hContactId;

                if (hContactId > 0)
                {
                    hot.fire('contactSaved');
                    this.get(hContactId, this.isGroup);
                }
            },
            contact.record
        );
    },

    deleteContact : function()
    {
        var contactId = parseInt($('div.hContact').splitId());

        if (contactId > 0)
        {
            dialogue.confirm({
                title : 'Confirm Deletion',
                label : "<p>Are you sure you want to <b>PERMANENTLY</b> delete this contact?</p>",
                ok : "Delete Contact",
                cancel : "Don't Delete Contact",
                callback : {
                    fn : function(confirm)
                    {
                        if (confirm)
                        {
                            http.get(
                                '/hContact/delete', {
                                    operation : 'Delete Contact',
                                    hContactAddressBookId : contact.addressBookId,
                                    hContactId : parseInt($('div.hContact').splitId()),
                                    hContactConf : contact.conf
                                },
                                function(json)
                                {
                                    var contactId = parseInt($('div.hContact').splitId());

                                    $('form#hContactSummaryDialogue div.hDialogueContentWrapper').children().hide();
                                    hot.unselect('hContact');

                                    var userId = $('div#hContactRecordId-' + contactId).find('span.hContactRecordUserId').text();

                                    $('li#hContactGroup-' + userId).remove();
                                    $('div#hContactRecordId-' + contactId).remove();

                                    hot.fire(
                                        'deleteContact', {
                                            hUserId : userId,
                                            hContactId : contactId
                                        }
                                    );
                                },
                                this
                            );
                        }
                    },
                    context : contact.record
                }
            });
        }
    },

    appendToPost : function(i, field, value)
    {
        this.post += '&hContactAddresses[' + i + '][' + field + ']=' + encodeURIComponent(value);
    },

    getFieldData : function(field, plural)
    {
        alert(field);
        
        plural = field + plural;

        var i = 0;

        $('ul.' + plural + ' li.' + field).each(
            function()
            {
                if (!$(this).hasClass('hContactDataTemplate'))
                {
                    contact.record.post +=
                        '&' + plural + '[' + i + '][hContactFieldId]=' + $(this).find('span.hContactField select').val() +
                        '&' + plural + '[' + i + '][' + field + 'Id]=' + $(this).splitNumericId() +
                        '&' + plural + '[' + i + '][' + field + ']=' + $(this).find('span.' + field + ' span').getFieldValue();
                }

                i++;
            }
        );
    }
};


keyboard
    .shortcut(
        {
            saveContact : "Command + S, Control + S"
        },
        function()
        {
            $('input#hContactSave').click();
        }
    )
    .shortcut(
        {
            newContact : "Command + N, Control + N"
        },
        function()
        {
            $('img#hContactNew').click();
        }
    )
    .shortcut(
        {
            editContact : "Command + E, Control + E"
        },
        function()
        {
            $('input#hContactEdit').click();
        }
    )
    .shortcut(
        {
            searchContacts : "Command + F, Control + F"
        },
        function()
        {
            $('input.hSpotlightSearchInput').focus();
        }
    )
    .shortcut(
         {
             getGroup : 'Return',
             disableShortcutOnInput : true
         },
         function(event)
         {
            if (contact.record.focusedColumn == 'groups' && hot.selected('hContactGroup').length)
            {
                contact.disableContactControls();
                contact.queryGroup();
            }
        }
    )
    .shortcut(
        {
            selectBelow : 'Down Arrow',
            disableShortcutOnInput : true
        },
        function(event)
        {
            var focus = contact.record.getFocusItems();

            if ($(focus.selected).length)
            {
                if ($(focus.selected).next(focus.item).length)
                {
                    $(focus.selected).next(focus.item).select(focus.select);
                }
            }
            else
            {
                $(focus.item + ':first').select(focus.select);
            }

            $(focus.selected).parent().scrollTop($(focus.selected).parent().scrollTop() + $(focus.selected).position().top - 20);
        }
    )
    .shortcut(
        {
            selectAbove : 'Up Arrow',
            disableShortcutOnInput : true
        },
        function(event)
        {
            var focus = contact.record.getFocusItems();

            if ($(focus.selected).length)
            {
                if ($(focus.selected).prev(focus.item).length)
                {
                    $(focus.selected)
                        .prev(focus.item)
                        .select(focus.select);
                }
            }
            else
            {
                $(focus.item + ':first').select(focus.select);
            }

            $(focus.selected).parent().scrollTop($(focus.selected).parent().scrollTop() + $(focus.selected).position().top - 20);
        }
    )
    .shortcut(
        {
            focusResultsColumn : 'Right Arrow',
            disableShortcutOnInput : true
        },
        function(event)
        {
            contact.record.focusedColumn = 'contacts';
            focus = contact.record.getFocusItems();
            $(focus.item + ':first').select(focus.select);
        }
    )
    .shortcut(
        {
            focusGroupsColumn : 'Left Arrow',
            disableShortcutOnInput : true
        },
        function(event)
        {
            contact.record.focusedColumn = 'groups';
            focus = contact.record.getFocusItems();
            $(focus.item + ':first').select(focus.select);
        }
    );

$(document).ready(
    function()
    {
        contact.record.ready();
    }
);
