if (typeof(contact) == 'undefined')
{
    var contact = {};
}

contact.dialogue = {

    list : {},
    dimensions : {},

    ready : function()
    {
        spotlight.setPath(
            hot.path('/hContact/query', {
                    hContactAddressBookId : 1,
                    hContactConf : null
                }
            )
        );

        spotlight.setSortColumn('`hContacts`.`hContactLastName`', 'ASC');
        
        hot.event(
            'spotlightSearch',
            function(data)
            {
                hot.unselect('hUserGroup'); 

                //var response = parseInt(data.json);

                $('div#hContactDialogueUsers').html(data.json);
            },
            this
        );

        $('div#hContactRolodex ul li').click(
            function()
            {
                $('input#hSpotlightSearchInput-Contacts').search($(this).find('span').text());
            }
        );

        hot.event(
            'hUserGroupSelected',
            function()
            {
                contact.dialogue.queryGroup();
            }
        );

        $('div#hContactDialogueGroups > ul > li').on(
            'click',
            function()
            {
                $(this).select('hUserGroup');
            }
        );
        
        $(document).on(
            'click',
            'div#hContactDialogueUsers div.hContactRecord',
            function()
            {
                $(this).select('hContactRecord');
            }
        );
        
        $('input#hContactDialogueCancel').click(
            function(e)
            {
                e.preventDefault();
                window.close();
            }
        );
        
        $('input#hContactDialogueSelect').click(
            function(e)
            {
                e.preventDefault();

                if (window.opener)
                {
                    var userName, userId, contactId, isGroup;

                    switch (true)
                    {
                        case select.ed('hContactRecord').length > 0:
                        {
                            var record = select.ed('hContactRecord');

                            userName = record.find('li.hContactRecordUserName').text();
                            userId = parseInt(record.find('span.hContactRecordUserId').text());
                            contactId = parseInt(record.find('span.hContactRecordId').text());
                            isGroup = record.hasClass('hContactGroupRecord');
                            
                            break;
                        }
                        case select.ed('hUserGroup').length > 0:
                        {
                            var record = select.ed('hUserGroup');

                            userName = record.attr('title');
                            userId = parseInt(record.attr('data-userId'));
                            contactId = parseInt(record.attr('data-contactId'));
                            isGroup = true;
                            break;
                        }
                    }

                    if (get.onChooseContact)
                    {
                        if (userId && contactId)
                        {
                            eval(
                                'window.opener.' + get.onChooseContact + '({' + 
                                    'userId : ' + userId + ', ' +
                                    'userName : "' + userName + '", ' + 
                                    'contactId : ' + contactId + ', ' + 
                                    'isGroup : ' + (isGroup? 'true' : 'false') +
                                '})'
                            );
                            
                            window.close();
                        }
                    }
                    else
                    {
                        hot.console.error('Unable to send contact data back to the parent window, because no callback function was specified in "get.onChooseContact"');
                    }
                }
                else
                {
                    hot.console.error("Unable to send contact data back to the parent window, because there isn't one.");
                }
            }
        );
        
        $('div#hContactDialogueToggle')
            .hover(
                function()
                {
                    $(this).addClass('hContactDialogueToggleOver');
                },
                function()
                {
                    $(this).removeClass('hContactDialogueToggleOver');
                }
            );
            
        $(document).mousedown(
            function()
            {
                if (!$('div#hContactDialogueToggle').hasClass('hContactDialogueToggleOver'))
                {
                    $('div#hContactDialogueToggle').removeClass('hContactDialogueToggleActive');
                    $('div#hContactDialogueToggle ul').css({
                        left : contact.dialogue.list.left ? contact.dialogue.list.left : 0,
                        top : contact.dialogue.list.top ? contact.dialogue.list.top : 0
                    });
                }
            }
        );

        $('div#hContactDialogueToggle li').click(
            function(e)
            {
                e.stopPropagation();
                
                var div = $('div#hContactDialogueToggle');
                
                if (div.hasClass('hContactDialogueToggleActive'))
                {
                    $(this).select('hContactDialogueToggle');
    
                    if ($(this).is(':first-child'))
                    {
                        $(this).parents('ul:first').css({
                            left : 0,
                            top : 0
                        });
                        
                        contact.dialogue.toggleToUsers();
                    }
                    else
                    {
                        $(this).parents('ul:first').css({
                            left : 0,
                            top : '-25px'
                        });
                        
                        contact.dialogue.toggleToGroups();
                    }

                    contact.dialogue.list = {
                        left : div.children('ul').css('left'),
                        top : div.children('ul').css('top')
                    };

                    div.removeClass('hContactDialogueToggleActive');
                }
                else
                {
                    div.addClass('hContactDialogueToggleActive');

                    contact.dialogue.list = {
                        left : div.children('ul').css('left'),
                        top : div.children('ul').css('top')
                    };

                    div.children('ul').css({
                       left : 0,
                       top : 0 
                    });
                }
            }
        );
        
        $('div#hContactDialogueToggle li:first-child').select('hContactDialogueToggle');
        
        $(document)
            .on(
                'mousedown',
                'div.hContactRecord, div#hContactDialogueGroups > ul > li.hUserGroup',
                function(e)
                {
                    e.stopPropagation();

                    this.draggable = true;

                    if (hot.userAgent == 'webkit')
                    {
                        this.style.WebkitUserDrag = 'element';
                    }

                    if (this.dragDrop)
                    {
                        this.dragDrop();
                    }
                }
            )
            .on(
                'dragstart',
                'div.hContactRecord, div#hContactDialogueGroups > ul > li.hUserGroup',
                function(e)
                {    
                    if (hot.userAgent == 'ie' && hot.userAgentVersion < 9)
                    {
                        // In IE, the click event is not called if dragDrop() has been
                        // activated, and dragStart is called every time a click occurs, 
                        // whether or not the user is actually starting a drag.
                    }

                    contact.dialogue.dragging = true;

                    e.stopPropagation();
                    e.originalEvent.dataTransfer.effectAllowed = 'copy';

                    var html = $(this).outerHTML();

                    e.originalEvent.dataTransfer.setData(hot.userAgent == 'ie'? 'Text' : 'text/html', html);

                    if (hot.userAgent != 'ie')
                    {
                        e.originalEvent.dataTransfer.setData('text/plain', html);
                    }
                }
            )
            .on(
                'dragend',
                'div.hContactRecord, div#hContactDialogueGroups > ul > li.hUserGroup',
                function(e)
                {
                    contact.dialogue.dragging = false;
                }
            )
            .on(
                'dragover',
                'div.hContactRecord, div#hContactDialogueGroups > ul > li.hUserGroup',
                function(e)
                {
                    e.preventDefault();
                    e.stopPropagation();
                    e.originalEvent.dataTransfer.dropEffect = 'copy';
                }
            )
            .on(
                'dragleave',
                'div.hContactRecord, div#hContactDialogueGroups > ul > li.hUserGroup',
                function(e)
                {
                    e.preventDefault();
                }
            )
            .on(
                'drop',
                'div.hContactRecord, div#hContactDialogueGroups > ul > li.hUserGroups',
                function(e)
                {   
                    e.preventDefault();
                    e.stopPropagation();
                }
            );
    },
    
    toggleToGroups : function()
    {
        this.dimensions = {
            width : $('div#hContactDialogueGroups').width()
        };
        
        $('div#hContactDialogueUsers').animate({
            width : 0
        }, 'slow');
        
        $('div#hContactDialogueGroups').animate({
            width : '100%'
        }, 'slow');
        
        $('div#hContactRolodex').slideUp();
    },
    
    toggleToUsers : function()
    {
        $('div#hContactRolodex').slideDown();
        
        $('div#hContactDialogueUsers').animate({
                width : 'auto'
            }, 'slow',
            function()
            {
                $(this).css('width', 'auto');
            }
        );
        
        $('div#hContactDialogueGroups').animate({
            width : this.dimensions.width
        }, 'slow');
    },

    queryGroup : function()
    {
        $('div#hContactDialogueUsers').html('');

        var args = {
            operation : 'Query Group',
            hContactAddressBookId : 1,
            hContactGroupId : hot.selected('hUserGroup').splitId()
        };

/*
        if (spotlight.sortColumn)
        {
            args.hSpotlightSortColumn = spotlight.sortColumn;
            args.hSpotlightSortOrientation = spotlight.sortOrientation;
        }
*/

        if (args.hContactGroupId)
        {
            http.get(
                '/hContact/queryGroup', 
                args,
                function(html)
                {
                    $('div#hContactDialogueUsers').html(html);
                }
            );
        }
    }
};

$(document).ready(
    function()
    {
        contact.dialogue.ready();
    }
);
