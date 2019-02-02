$.fn.extend({

    selectSortColumn : function()
    {
        if (contact.selectedSortColumn && contact.selectedSortColumn.attr('id') != $(this).attr('id'))
        {
            contact.selectedSortColumn.removeClass('hContactSortASC');
            contact.selectedSortColumn.removeClass('hContactSortDESC');
        }

        var column = this.find('span.hContactSortColumn').text();

        if (!this.hasClass('hContactSortASC'))
        {
            this.addClass('hContactSortASC');
            spotlight.setSortColumn(column, 'ASC');
        }
        else if (this.hasClass('hContactSortDESC'))
        {
            this.addClass('hContactSortDESC');
            spotlight.setSortColumn(column, 'DESC');
        }
        else
        {
            spotlight.setSortColumn('', '');
            this.removeClass('hContactSortASC');
            this.removeClass('hContactSortDESC');
        }

        contact.selectedSortColumn = this;
        return this;
    },

    selectContact : function()
    {
        return this.select('hContact');
    },

    selectGroup : function()
    {
        return this.select('hContactGroup');
    },

    touchGroup : function()
    {
        if (this.hasClass('hContactGroupSelected'))
        {
            contact.enableContactControls();

            contact.record.get(
                this.find('ul.hContactGroupAddress').splitId(),
                true
            );
        }
        else
        {
            contact.disableContactControls();
            this.selectGroup(this);
            contact.queryGroup();
        }
    },

    touchContactRecord : function()
    {
        contact.enableContactControls();
        this.selectContact();
    },

    selectGroupPane : function()
    {
        this.select('hContactTab');
        $('ul#hContact' + this.splitId()).select('hContactGroupPane');
        return this;
    }
});

var contact = {

    selectedSortColumn : null,

    sortMenuTracker : null,

    ready : function()
    {
        spotlight.setPath(
            hot.path(
                '/hContact/query', {
                    hContactAddressBookId : this.addressBookId,
                    hContactConf : contact.conf
                }
            )
        );

        spotlight.setSortColumn(
            '`hContacts`.`hContactLastName`',
            'ASC'
        );

        hot.event(
            'spotlightSearch',
            function(data)
            {
                contact.disableContactControls();
                hot.unselect('hContactGroup');

                var response = parseInt(data.json);

                $('div#hContactResults').html(data.json);
            },
            this
        );

        $('div#hContactRolodex ul li').click(
            function()
            {
                $('input#hSpotlightSearchInput-Contacts').search(
                    $(this).find('span').text()
                );
            }
        );

        $('div#hContactGroupTabs li, div#hContactSortButton').hover(
            function()
            {
                $(this).addClass('hContactTabOver');
            },
            function()
            {
                $(this).removeClass('hContactTabOver');
            }
        );

        $('div#hContactSortButton').click(
            function()
            {
                contact.toggleSortMenu();
            }
        );

        $('div#hContactSort').hover(
            function()
            {
                contact.sortMenuTracker = true;
            },
            function()
            {
                contact.sortMenuTracker = false;
            }
        );

        $('ul#hContactSortColumns li')
            .hover(
                function()
                {
                    $(this).addClass('hContactSortItemOn');
                },
                function()
                {
                    $(this).removeClass('hContactSortItemOn');
                }
            )
            .click(
                function()
                {
                    $(this).selectSortColumn();
                }
            );

        $('div#hContactGroupTabs li').click(
            function()
            {
                $(this).selectGroupPane();
            }
        );

        $('body').droppable({
            accept : 'div.hContactRecord',
            tolerance : 'pointer',
            drop : function(event, ui)
            {

            }
        });

        $('ul#hContactGroups').bind(
            'touchstart',
            function(event)
            {
                if ($(event.target).parents('li:first').length)
                {
                    $(event.target).parents('li:first').touchGroup();
                }
            }
        );

        $(document).on(
            'click',
            'ul#hContactGroups > li',
            function()
            {
                $(this).touchGroup();
            }
        );

        $('ul#hContactGroups > li').droppable({
            accept : 'div.hContactRecord',
            hoverClass : 'hContactGroupDragover',
            tolerance : 'pointer',
            greedy : true,
            drop : function(event, ui)
            {
                event.stopPropagation();

                // ui.draggable
                hot.fire(
                    'groupDrop', {
                        draggable : ui.draggable
                    }
                );
            }
        });

        $(document).mouseup(
            function()
            {
                contact.menuTracker();
            }
        );

        $('img#hContactNew').click(
            function(event)
            {
                event.preventDefault();
                contact.enableContactControls();
                hot.unselect('hContact');
            }
        );

        $('img#hContactNewGroup').click(
            function(event)
            {
                event.preventDefault();
                hot.unselect('hContactGroup');
                $('div#hContactResults').html('');
                contact.enableContactControls();
            }
        );

        $('img#hContactDeleteGroup').click(
            function(event)
            {
                event.preventDefault();

                var group = hot.selected('hContactGroup');

                if (group.length)
                {
                    contact.deleteGroup(group.splitId());
                }
            }
        );

        $('img.hContactButton')
            .mousedown(
                function()
                {
                    $(this).sourceFile(this.alt + '+Pressed.png');
                }
            )
            .mouseup(
                function()
                {
                    $(this).sourceFile(this.alt + '.png');
                }
            );

        //$('li#hContact-Groups').SelectGroupPane();
        $('div#hContactResults').bind(
            'touchstart',
            function(event)
            {
                if ($(event.target).parents('div.hContactRecord').length)
                {
                    $(event.target)
                        .parents('div.hContactRecord')
                        .touchContactRecord();
                }
            }
        );

        $(document).on(
            'click',
            'div.hContactRecord',
             function()
             {
                 $(this).touchContactRecord();
             }
        );

        $('div#hContactInstructions').click(
            function()
            {
                $(this).fadeOut('slow');
            }
        );

        $('div#hContactRolodex ul li:first').click();

        $(document).dblclick(
            function()
            {
                if ($('div#hContactInstructions').is(':visible'))
                {
                     $('div#hContactInstructions').fadeOut('slow');
                }
            }
        );

        $('div#hContactGroupResizeGrip').mousedown(
            function(event)
            {
                contact.groupResizeActive = true;

                contact.coordinates = {
                    x : event.pageX,
                    y : event.pageY,
                    groupWidth : $('div#hContactGroupWrapper').width(),
                    resultsWidth : $('div#hContactResultsWrapper').width()
                };
            }
        );

        $('div#hContactResultsResizeGrip').mousedown(
            function(event)
            {
                var groupWidth = 0;

                if (!$('body').hasClass('hContactAddressBookNoGroups'))
                {
                    groupWidth = $('div#hContactGroupWrapper').width();
                }

                contact.resultsResizeActive = true;

                contact.coordinates = {
                    x : event.pageX,
                    y : event.pageY,
                    groupWidth : groupWidth,
                    resultsWidth : $('div#hContactResultsWrapper').width()
                };
            }
        );

        $(document)
            .mousemove(
                function(event)
                {
                    if (contact.groupResizeActive || contact.resultsResizeActive)
                    {
                        contact.onResize(event);
                    }
                }
            )
            .mouseup(
                function(event)
                {
                    if (contact.groupResizeActive || contact.resultsResizeActive)
                    {
                        contact.groupResizeActive = false;
                        contact.resultsResizeActive = false;
                        contact.saveColumnDimensions();
                    }
                }
            );

        if (this.groupWidth || this.resultsWidth)
        {
            this.resize(
                this.groupWidth,
                this.resultsWidth
            );
        }

        $('input#hContactInstructionsDefault').click(
            function()
            {
                http.get(
                    '/hContact/saveInstructionsDefault',
                    function(json)
                    {

                    }
                );
            }
        );

        if (this.width > screen.width)
        {
            this.width = screen.width;
        }

        if (this.height > screen.height)
        {
            this.height = screen.height;
        }

        window.resizeTo(this.width, this.height);
        window.moveTo((screen.width - this.width) / 2, (screen.height - this.height) / 2);

        contact.firstLoad = true;

        $(window).resize(
            function()
            {
                http.get(
                    '/hContact/saveWindowDimensions', {
                        operation : 'Save Window Dimensions',
                        width : $(window).width(),
                        height : $(window).height()
                    },
                    function(json)
                    {

                    }
                );
            }
        );
    },

    onResize : function(event)
    {
        var groupWidth = this.coordinates.groupWidth;

        if (contact.groupResizeActive)
        {
            groupWidth = this.coordinates.groupWidth - (this.coordinates.x - event.pageX);
        }

        var resultsWidth = this.coordinates.resultsWidth;

        if (contact.resultsResizeActive)
        {
            resultsWidth = this.coordinates.resultsWidth - (this.coordinates.x - event.pageX);
        }

        this.resize(groupWidth, resultsWidth);
    },

    resize : function(groupWidth, resultsWidth)
    {
        if ($('body').hasClass('hContactAddressBookNoGroups'))
        {
            groupWidth = 0;
        }
        else if (groupWidth < 200)
        {
            groupWidth = 200;
        }
        else if (groupWidth > 400)
        {
            groupWidth = 400;
        }

        if (resultsWidth < 200)
        {
            resultsWidth = 200;
        }
        else if (resultsWidth > 500)
        {
            resultsWidth = 500;
        }

        this.groupResizedTo = groupWidth;
        this.resultsResizedTo = resultsWidth;

        if (groupWidth)
        {
            $('div#hContactGroupWrapper').width(groupWidth + 'px');
            $('div#hContactGroupTabs').width(groupWidth + 'px');

            $('div#hContactGroupResizeGrip').css(
                'left',
                groupWidth + 'px'
            );

            $('div#hContactResultsWrapper').css(
                'left',
                groupWidth? (groupWidth + 1) + 'px' : 0
            );
        }

        $('div#hContactResultsWrapper').css({
            width : resultsWidth + 'px'
        });

        $('form#hContactSummaryDialogue').css(
            'left',
            ((groupWidth? groupWidth : 0) + resultsWidth + 2) + 'px'
        );

        $('div#hContactResultsResizeGrip').css(
            'left',
            ((groupWidth? groupWidth : 0) + resultsWidth + 1) + 'px'
        );

        $('div#hContactSortButton').css(
            'left',
            (((groupWidth? groupWidth : 0) + resultsWidth) - 62) + 'px'
        );  // 488

        $('ul#hContactSortColumns').css(
            'left',
            (((groupWidth? groupWidth : 0) + resultsWidth) - $('ul#hContactSortColumns').width()) + 'px'
        );
    },

    saveColumnDimensions : function()
    {
        if (contact.groupResizedTo || contact.resultsResizedTo)
        {
            http.get(
                '/hContact/saveColumnDimensions', {
                    groupWidth : this.groupResizedTo,
                    resultsWidth : this.resultsResizedTo
                },
                function(json)
                {
                    contact.groupResizedTo = 0;
                    contact.resultsResizedTo = 0;

                    switch (parseInt(json))
                    {
                        case 1:
                        {

                        }
                    }
                }
            );
        }
    },

    enableContactControls : function()
    {
        $('input#hContactEdit, input#hContactSave')
            .removeAttr('disabled');

        $('form#hContactSummaryDialogue li.hDialogueTab')
            .show();
    },

    disableContactControls : function()
    {
        hot.unselect('hContact');

        $('li#hDialogueTab-hContactSummaryDiv')
            .click();

        $('form#hContactSummaryDialogue div.hDialogueContentWrapper')
            .children()
            .hide();

        $('input#hContactEdit, input#hContactSave')
            .attr('disabled', true);

        $('form#hContactSummaryDialogue li.hDialogueTab')
            .hide();
    },

    menuTracker : function()
    {
        if (!this.sortMenuTracker && $('ul#hContactSortColumns').hasClass('hContactSortColumnsOn'))
        {
            this.toggleSortMenu();
        }
    },

    toggleSortMenu : function()
    {
        $('div#hContactSortButton')
            .toggleClass('hContactTabSelected');

        $('ul#hContactSortColumns')
            .toggleClass('hContactSortColumnsOn');
    },

    queryGroup : function()
    {
        $('div#hContactResults')
            .html('');

        $('form#hContactSummaryDialogue div.hDialogueContentWrapper')
            .children()
            .hide();

        var args = {
            operation : 'Query Group',
            hContactAddressBookId : this.addressBookId,
            hContactGroupId : hot.selected('hContactGroup').splitId(),
            hContactConf : contact.conf
        };

        if (spotlight.sortColumn)
        {
            args.hSpotlightSortColumn = spotlight.sortColumn;
            args.hSpotlightSortOrientation = spotlight.sortOrientation;
        }

        if (args.hContactGroupId)
        {
            http.get(
                '/hContact/queryGroup',
                args,
                function(json)
                {
                    $('div#hContactResults').html(json);
                }
            );
        }
    },

    userId : 0,

    deleteGroup : function(userId)
    {
        this.userId = userId;

        var label = $('li#hContactGroup-' + userId + ' span').text();

        dialogue.confirm({
            label : "<p>Are you sure you want to <b>PERMANENTLY</b> delete group <i>" + label + "</i>?</p>",
            ok : "Delete Group",
            cancel : "Don't Delete Group",
            callback : {
                fn : function(confirm)
                {
                    if (confirm)
                    {
                        http.get(
                            '/hContact/delete', {
                                operation : 'Delete Group',
                                hUserId : contact.userId,
                                hContactAddressBookId : contact.addressBookId,
                                hContactConf : contact.conf
                            },
                            function(json)
                            {
                                $('li#hContactGroup-' + contact.userId).remove();
                            }
                        );
                    }
                },
                context : this
            }
        });
    }
};

keyboard.shortcut(
	{
        closeThings : 'escape'
    },
    function()
    {
        $('div#hContactInstructions')
            .fadeOut();

        spotlight.deactivate(true);

        $('input#hSpotlightSearchInput-Contacts')
            .blur();
    }
);

$(document)
    .ready(
        function()
        {
            contact.ready();
        }
    )
    .bind(
        'touchmove',
         function(event)
         {
             event.preventDefault();
         }
    );
