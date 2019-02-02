$.fn.extend({

    inlineEvent : function()
    {
        return this
            .dblclick(
                function(event)
                {
                    event.stopPropagation();
                    calendar.eventFileId = $(this).splitId();

                    $('div#hCalendarEvent').fadeIn(
                        'slow',
                        calendar.form.openEventCallback
                    );
                }
            )
            .each(
                function()
                {
                    this.draggable = true;
                    this.style.WebkitUserDrag = 'element';
                }
            )
            .mousedown(
                function()
                {
                    if (this.dragDrop)
                    {
                        // IE won't come out to play without this method call.
                        this.dragDrop();
                    }
                }
            )
            .on(
                'dragstart.calendar',
                function(event)
                {
                    event.originalEvent.dataTransfer.effectAllowed = 'copyMove';

                    var html = $(this).outerHTML();

                    // Data is passed this way for two reasons
                    //     1. IE only supports a precious few types of data, one of them being text.
                    //     2. The relevant event data needs to be passed this way in order to
                    //            facilitate drag and drop between multiple instances of the browser.
                    event.originalEvent.dataTransfer.setData(
                        (hot.userAgent == 'ie')? 'html' : 'text/html',
                        html
                    );

                    // @todo - Update this with a plain text representation of the event.
                    event.originalEvent.dataTransfer.setData(
                        (hot.userAgent == 'ie')? 'text' : 'text/plain',
                        html
                    );
                }
            )
            .on(
                'dragend.calendar',
                function(event)
                {
                    // If the value is 'none' a drop was not successful.
                    if (event.originalEvent.dataTransfer.dropEffect != 'none')
                    {
                        if (event.originalEvent.dataTransfer.dropEffect == 'move')
                        {
                            $(this).remove();
                        }
                    }
                }
            );

        return this;
    },

    addEventToDay : function(file, calendarDate)
    {
        var fragment = false;

        if (file && file instanceof Array && file.length)
        {
            var id = 'hCalendarEvent-' + calendarDate + '-' + file.hFileId;

            var event = $('div.hCalendarEventTemplate')
                .clone()
                .removeClass('hCalendarEventTemplate')
                .addClass('hCalendarEventCalendarId-' + file.hCalendarId)
                .addClass('hCalendarEventCalendarCategoryId-' + file.hCalendarCategoryId)
                .addClass('hCalendarEventFileId-' + file.hFileId)
                .attr('id', id)
                .inlineEvent();

            event.find('div.hCalendarEventTitle')
                 .html(file.hFileTitle);

            event.find('div.hCalendarEventDescription')
                 .html(file.hFileDescription);

            event.find('div.hCalendarEventPath')
                 .text(file.hFilePath);
        }
        else if (typeof file == 'string')
        {
            fragment = true;

            var fileId = (!arguments[2]? calendar.getEventFileIdFromHTML() : arguments[2]);

            var id = 'hCalendarEvent-' + calendarDate + '-' + fileId;

            var event = calendar.eventHTML.replace(
                new RegExp("/hCalendarEvent\-(\d*)\-(\d*)/"),
                function()
                {
                    return 'hCalendarEvent-' + calendarDate + '-' + fileId;
                }
            );

            event = event.replace(new RegExp('/style\="(.*?)"/'), '');
        }

        if (!$('div#' + id).length)
        {
            this.append(event);

            if (fragment)
            {
                $('div#' + id).InlineEvent();
            }
        }

        return this;
    },

    toggleCalendar : function()
    {
        if (calendar.selectedCalendar)
        {
            calendar.selectedCalendar.removeClass('on');
        }

        var calendarId = this.splitId();

        calendar.selectedCalendar = this;
        calendar.selectedCalendar.addClass('on');

        var checkbox = this.find('input').get(0);

        checkbox.checked = !(checkbox.checked);

        if (checkbox.checked)
        {
            $('div.hCalendarEventCalendarId-' + calendarId).fadeIn('slow');
        }
        else
        {
            $('div.hCalendarEventCalendarId-' + calendarId).fadeOut('slow');
        }

        // Remember toggle state
        http.get(
            '/hCalendar/saveCalendarToggleState', {
                operation : 'Save Calendar Toggle State',
                hCalendarId : calendarId,
                toggle : checkbox.checked ? 1 : 0
            },
            function(json)
            {
                calendar.getSidebarEvents();
            }
        );

        return this;
    },

    seperators : function(state)
    {
        $(this)
            .prev()
            .sourceFile('sep' + state + '.png');

        $(this)
            .next()
            .sourceFile('sep' + state + '.png');
    }
});

var calendar = {
    images : [],
    states : [],
    miniOffset : 0,
    selectedOffset : 0,
    cursor : '1/1/1',
    animating : false,

    navigationActive : false,

    getCalendarViewInnerWidth : function()
    {
        var width = 0;

        $('div#hCalendarView table.hCalendar').each(
            function()
            {
                width += $(this).outerWidth()
            }
        );

        return width;
    },

    ready : function()
    {
        $('td.hCalendarLastMonth:last').addClass('hCalendarLastMonthLastDay');

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

        $(window).resize(
            function()
            {
                http.get(
                    '/hCalendar/saveWindowDimensions', {
                        width : $(window).width(),
                        height : $(window).height()
                    },
                    function(json)
                    {

                    }
                );

                calendar.resize();
            }
        );

        $('div.hCalendarOptionPermissions').click(
            function(event)
            {
                event.stopPropagation();
                calendar.openPermissionDialogue(
                    6,
                    $(this)
                        .parents('div.hCalendarOption:first')
                        .splitId()
                );
            }
        );

        this.watchForResize();

        $.datepicker.setDefaults({
            changeMonth: true,
            changeYear: true
        });

        this.preloadImages();

        this.calendarSelect = $('select#hCalendarSelect');
        this.categorySelect = $('select#hCalendarCategory');

        $('img.hCalendarQuadStateControl')
            .mousedown(
                function()
                {
                    if (calendar.states[this.id])
                    {
                        this.src = calendar.images[this.id]['on_active'].src;
                        calendar.states[this.id] = false;
                    }
                    else
                    {
                        this.src = calendar.images[this.id]['active'].src;
                        calendar.states[this.id] = true;
                    }
                }
            )
            .mouseup(
                function()
                {
                    this.src = calendar.images[this.id][(calendar.states[this.id]? 'on' : 'off')].src;
                }
            );

        $('#hCalendarControlNew')
            .click(
                function()
                {
                    calendar.newCalendarEvent();
                }
            );

        // Show / Hide Mini Calendar
        $('img#hCalendarControlMini').click(
            function()
            {
                // @todo - Move style to the stylesheet.
                if (calendar.states['hCalendarControlMini'])
                {
                    $('div#hCalendarMini').show('slow');

                    $('div#hCalendar').animate(
                        {
                            bottom : '200px'
                        },
                        'slow'
                    );

                    var toggle = 1;
                }
                else
                {
                    $('div#hCalendarMini').hide('slow');

                    $('div#hCalendar').animate(
                        {
                            bottom : '32px'
                        },
                        'slow'
                    );

                    var toggle = 0;
                }

                // Remember the state
                http.get(
                    '/hCalendar/saveMiniCalendarState', {
                        operation : 'Save Mini Calendar State',
                        toggle : toggle
                    },
                    function(json)
                    {

                    }
                );
            }
        );

        this.info = $('div#hCalendarEventInfo');
        this.list = $('div#hCalendarEventList');
        this.selected = $('div#hCalendarView');

        $('img#hCalendarMiniUp').active(
            function()
            {
                $(this).sourceFile('up_on.png');
                $(this).next().sourceFile('sep_on.png');
            },
            function()
            {
                $(this).sourceFile('up.png');
                $(this).next().sourceFile('sep.png');
            },
            function()
            {
                calendar.miniOffset--;
                calendar.getMiniCalendar();
            }
        );

        $('img#hCalendarMiniCurrent').active(
            function()
            {
                $(this).prev().sourceFile('sep_on.png');
                $(this).sourceFile('current_on.png');
                $(this).next().sourceFile('sep_on.png');
            },
            function()
            {
                $(this).prev().sourceFile('sep.png');
                $(this).sourceFile('current.png');
                $(this).next().sourceFile('sep.png');
            },
            function()
            {
                calendar.miniOffset = 0;
                calendar.selectedOffset = 0;
                calendar.getCalendar();
            }
        );

        // Mini down arrow (next month)
        $('img#hCalendarMiniDown').active(
            function()
            {
                $(this).sourceFile('down_on.png');

                $(this).prev()
                       .sourceFile('sep_on.png');
            },
            function()
            {
                $(this).sourceFile('down.png');

                $(this).prev()
                       .sourceFile('sep.png');
            },
            function()
            {
                calendar.miniOffset++;
                calendar.getMiniCalendar();
            }
        );

        // Go back in calendar
        $('li#hCalendarNavigationPrevious').click(
            function()
            {
                if (!calendar.animating)
                {
                    calendar.animating = true;
                    calendar.selectedOffset--;
                    calendar.miniOffset = calendar.selectedOffset;
                    calendar.getCalendar();
                }
            }
        );

        $('li#hCalendarNavigationPrevious, li#hCalendarNavigationNext')
            .on(
                'dragenter.calendar',
                function(event)
                {
                    event.preventDefault();
                }
            )
            .on(
                'dragover.calendar',
                function(event)
                {
                    event.preventDefault();

                    if (!calendar.navigationActive)
                    {
                        $(this).active();
                        calendar.navigationActive = true;
                        setTimeout('calendar.resetNavigation();', 500);
                    }
                }
            )
            .on(
                'dragleave.calendar',
                function(event)
                {
                    event.preventDefault();
                }
            );

        $('li#hCalendarNavigationToday').click(
            function()
            {
                calendar.miniOffset = 0;
                calendar.selectedOffset = 0;
                calendar.getCalendar();
            }
        );

        // Go forward in calendar
        $('li#hCalendarNavigationNext').click(
            function()
            {
                if (!calendar.animating)
                {
                    calendar.animating = true;
                    calendar.selectedOffset++;
                    calendar.miniOffset = calendar.selectedOffset;
                    calendar.getCalendar();
                }
            }
        );

        this.miniEvents();
        this.calendarEvents();
        this.eventToggleEvents();

        this.selectedCalendar = $('div#hCalendar div.on');

        $('ul#hCalendarEventTabs li')
            .click(
                function()
                {
                    if (hot.selected('hCalendarEventForm').length)
                    {
                        hot.selected('hCalendarEventForm').hide();
                    }

                    switch ($(this).splitId())
                    {
                        case 'Content':
                        {
                            $('input#hCalendarEventFormContinue').show();
                            $('input#hCalendarEventFormSave').hide();
                            $('input#hCalendarEventFormBack').hide();
                            break;
                        }
                        case 'Properties':
                        {
                            $('input#hCalendarEventFormContinue').hide();
                            $('input#hCalendarEventFormSave').show();
                            $('input#hCalendarEventFormBack').show();
                            break;
                        }
                    }

                    $('div#hCalendarEventForm' + $(this).splitId())
                        .show()
                        .select('hCalendarEventForm');

                    $(this).select('hCalendarEventTab');
                    $(this).select('hApplicationUIButtonSegmentedAqua');
                }
            );

        $('ul#hCalendarEventTabs li:first-child').click();

        $('input#hCalendarDate, input#hCalendarBegin, input#hCalendarEnd, input#hCalendarBeginTime, input#hCalendarEndTime')
            .datepicker({
                dayNamesMin : ['S', 'M', 'T', 'W', 'T', 'F', 'S'],
                onSelect : function(date)
                {
                    switch ($(this).attr('id'))
                    {
                        case 'hCalendarBeginTime':
                        {
                            $('input#hCalendarDate').val(date);
                            $('input#hCalendarEndTime').val(date);
                            break;
                        };
                        case 'hCalendarEndTime':
                        {
                            $('input#hCalendarDate').val(date);
                            $('input#hCalendarBeginTime').val(date);
                            break;
                        };
                    }
                }
            });

        $('input#hCalendarEndTime').hide();

        $('img.hCalendarControlAdd')
            .button('add_pressed.png', 'add.png');

        $('img.hCalendarControlRemove')
            .button('remove_pressed.png', 'remove.png');

        $('img#hCalendarAdd').click(
            function()
            {
                calendar.newCalendarEvent();
            }
        );

        $('img#hCalendarRemove').click(
            function()
            {
                var calendars = $('select#hCalendarId').val();

                $(calendars).each(
                    function()
                    {
                        calendar.deleteCalendar(parseInt(this));
                    }
                );
            }
        );

        $('img#hCalendarCategoryAdd').click(
            function()
            {
                calendar.newCategory();
            }
        );

        $('img#hCalendarCategoryRemove').click(
            function()
            {
                calendar.deleteCategory();
            }
        );

        $('#hCalendarEventNew').click(
            function() {
                calendar.newEvent();
            }
        );

        $(document).on(
            'click',
            'div.hCalendarEventModify',
            function()
            {
                calendar.editEvent(
                    $(this).parents('li:first').splitId()
                );
            }
        );

        $(document).on(
            'click',
            'div#hCalendarEventNavigation a',
            function(event)
            {
                event.preventDefault();

                calendar.getSidebarEvents(
                    this.href.split('=').pop()
                );
            }
        );

        $(document)
            .on(
                'click',
                'ul#hCalendarEventsList li:not(#hCalendarRecentEvents)',
                function()
                {
                    $(this).select('hCalendarEvent');
                }
            )
            .on(
                'dblclick',
                'ul#hCalendarEventsList li:not(#hCalendarRecentEvents)',
                function()
                {
                    calendar.editEvent($(this).splitId());
                }
            );

        $('input#hCalendarSelectCategoryDialogueContinue')
            .attr('disabled', true)
            .click(
                function(event)
                {
                    event.preventDefault();

                    if (hot.selected('hCalendarCategoryId').length)
                    {
                        var categoryId = hot.selected('hCalendarCategoryId').splitId();
                        $('#hCalendarCategoryId').val(categoryId);

                        calendar.form.toggleDateInputsByCategory(categoryId);
                        calendar.closeCategorySelectDialogue();
                        calendar.openEventForm();
                    }
                }
            );

         $('input#hCalendarSelectCategoryDialogueCancel').click(
             function(event)
             {
                 event.preventDefault();
                 calendar.closeCategorySelectDialogue();
             }
         );

        $('ul#hCalendarSelectCategoryId li').click(
            function()
            {
                $(this).select('hCalendarCategoryId');

                $('input#hCalendarSelectCategoryDialogueContinue')
                    .removeAttr('disabled');
            }
        );

        $('li#hCalendarEventDelete').click(
            function()
            {
                var event = hot.selected('hCalendarEvent');

                if (event.length)
                {
                    calendar.deleteEvent(event.splitId());
                }
                else
                {
                    dialogue.alert({
                        title : 'Error',
                        label : 'Unable to delete an event because no event is selected.'
                    });
                }
            }
        );

        $('div#hCalendarsResizeGrip, div#hCalendarEventsResizeGrip').mousedown(
            function(event)
            {
                if (this.id == 'hCalendarEventsResizeGrip')
                {
                    calendar.resizeEventsActive = true;
                }
                else
                {
                    calendar.resizeCalendarsActive = true;
                }

                calendar.coordinates = {
                    x : event.pageX,
                    y : event.pageY,
                    calendarsWidth : $('div#hCalendar').width(),
                    eventsWidth : $('div#hCalendarEvents').width()
                };
            }
        );

        $(document)
            .mousemove(
                function(event)
                {
                    if (calendar.resizeCalendarsActive || calendar.resizeEventsActive)
                    {
                        calendar.onResize(event);
                    }
                }
            )
            .mouseup(
                function(event)
                {
                    if (calendar.resizeCalendarsActive || calendar.resizeEventsActive)
                    {
                        calendar.resizeCalendarsActive = false;
                        calendar.resizeEventsActive = false;
                        calendar.saveColumnDimensions();
                    }
                }
            );

        if (this.calendarsWidth || this.eventsWidth)
        {
            this.resizeColumns(this.calendarsWidth, this.eventsWidth);
        }
    },

    resizeCalendarsActive : false,
    resizeEventsActive : false,
    calendarsResizedTo : 0,
    eventsResizedTo : 0,

    onResize : function(event)
    {
        var calendarsWidth = this.coordinates.calendarsWidth;

        if (this.resizeCalendarsActive)
        {
            calendarsWidth = this.coordinates.calendarsWidth - (this.coordinates.x - event.pageX);
        }

        var eventsWidth = this.coordinates.eventsWidth;

        if (this.resizeEventsActive)
        {
            eventsWidth = this.coordinates.eventsWidth + (this.coordinates.x - event.pageX);
        }

        this.resizeColumns(calendarsWidth, eventsWidth);
    },

    resizeColumns : function(calendarsWidth, eventsWidth)
    {
        if (calendarsWidth < 150)
        {
            calendarsWidth = 150;
        }
        else if (calendarsWidth > 300)
        {
            calendarsWidth = 300;
        }

        if (eventsWidth < 200)
        {
            eventsWidth = 200;
        }
        else if (eventsWidth > 500)
        {
            eventsWidth = 500;
        }

        this.calendarsResizedTo = calendarsWidth;
        this.eventsResizedTo = eventsWidth;

        $('div#hCalendar').width(calendarsWidth + 'px');
        $('div#hCalendarMini').width(calendarsWidth + 'px');

        $('div#hCalendarsResizeGrip').css({
            left : (calendarsWidth - 1) + 'px'
        });

        $('div#hCalendarView').css({
            left : calendarsWidth + 'px',
            right : eventsWidth + 'px'
        });

        $('div#hCalendarEvent').css({
            left : calendarsWidth + 'px',
            right : eventsWidth + 'px'
        });

        $('div#hCalendarEvents').width(eventsWidth + 'px');

        $('div#hCalendarEventsResizeGrip').css({
            right : (eventsWidth - 1) + 'px'
        });

        this.resize();
    },

    saveColumnDimensions : function()
    {
        if (this.calendarsResizedTo || this.eventsResizedTo)
        {
            http.get(
                '/hCalendar/saveColumnDimensions', {
                    calendarsWidth : this.calendarsResizedTo,
                    eventsWidth : this.eventsResizedTo
                },
                function(json)
                {
                    this.calendarsResizedTo = 0;
                    this.eventsResizedTo = 0;

                    switch (parseInt(json))
                    {
                        case 1:
                        {

                        }
                    }
                },
                this
            );
        }
    },

    closeCategorySelectDialogue : function()
    {
        if (hot.selected('hCalendarCategoryId').length)
        {
            hot.unselect('hCalendarCategoryId');
        }

        $('form#hCalendarSelectCategoryDialogue')
            .closeDialogue(true);

        $('input#hCalendarSelectCategoryDialogueContinue')
            .attr('disabled', true);
    },

    newEvent : function()
    {
        calendar.form.reset();

        $('input#hFileId').val(0);

        if ($('form#hCalendarSelectCategoryDialogue').length)
        {
            this.closeEventForm();

            if (arguments[0])
            {
                $('input#hCalendarDate')
                    .val(arguments[0]);

                $('input#hCalendarBeginTime')
                    .val(arguments[0]);
            }

            $('form#hCalendarSelectCategoryDialogue')
                .openDialogue(true);
        }
        else
        {
            this.openEventForm();
        }
    },

    openEventForm : function()
    {
        $('div#hCalendarEvent').fadeIn(
            'slow',
            calendar.form.openEventCallback
        );
    },

    closeEventForm : function()
    {
        $('div#hCalendarEvent').fadeOut('slow');
    },

    openPermissionDialogue : function(frameworkResourceId, frameworkResourceKey)
    {
        hot.window(
            '/System/Applications/permissions.html', {
                hFrameworkResourceId : frameworkResourceId,
                hFrameworkResourceKey : frameworkResourceKey
            },
            800,
            600,
            'hUserPermissions', {
                menubar : false,
                location : false,
                statusbar : false,
                titlebar : false,
                toolbar : false,
                scrollbars : true,
                resizable : false,
                alwaysraised : true,
                "z-lock" : true
            }
        );
    },

    deleteEvent : function(fileId)
    {
        this.selectedEventId = fileId;

        dialogue.confirm({
            ok : 'Delete Event',
            cancel : 'Do Not Delete Event',
            title : 'Confirm Delete Event',
            label : "<p>" +
                        'Are you sure you want to PERMANENTLY delete <i>' + calendar.getEventName(fileId) + '</i>?' +
                    "</p>" +
                    "<p>" +
                        "This operation cannot be undone." +
                    "</p>",
            callback : {
                fn : function(confirm)
                {
                    if (confirm)
                    {
                        http.get(
                            '/hCalendar/deleteEvent', {
                                operaton : 'Delete Event',
                                hFileId : this.selectedEventId
                            },
                            function(json)
                            {
                                $('li#hCalendarEvent-' + this.selectedEventId)
                                    .remove();

                                $('div.hCalendarEventFileId-' + this.selectedEventId)
                                    .remove();
                            },
                            this
                        );
                    }
                },
                context : this
            }
        });
    },

    onload : function()
    {
        setTimeout('calendar.getEvents();', 1);
    },

    resetNavigation : function()
    {
        calendar.navigationActive = false;
    },

    updateFileDate : function(fileId, calendarDate)
    {
        http.get(
            '/hCalendar/updateFileDate', {
                operation : 'Update File Date',
                hCalendarDate : calendarDate,
                hFileId : fileId
            },
            function(json)
            {
                $('td#hCalendarThisMonth-hCalendar-' + calendar.eventDate).addEventToDay(
                    calendar.eventHTML,
                    calendar.eventDate
                );

                calendar.getSidebarEvents(calendar.cursor);
            }
        );
    },

    duplicate : function()
    {
        // Check path settings...
        if ($('input#hCalendarPathEnabled').val() != '0')
        {
            if (!arguments[0])
            {
                this.saveAs('Duplicate');
                return;
            }
            else
            {
                // function($hDirectoryPath, $hFileName, $replaceExisting)
                var directoryPath = arguments[0];
                var fileName = arguments[1];
                var replaceExisting = arguments[2];
            }
        }
        else
        {
            var directoryPath = '';
            var fileName = '';
            var replaceExisting = 0;
        }

        http.get(
            '/hCalendar/duplicateEvent', {
                operation : 'Duplicate Event',
                hFileId : this.getEventFileIdFromHTML(),
                hCalendarId : this.getEventCalendarIdFromHTML(),
                hCalendarDate : this.eventDate,
                hDirectoryPath : directoryPath,
                hFileName : fileName,
                replaceExisting : replaceExisting? 1 : 0
            },
            function(json)
            {
                var fileId = json;

                $('td#hCalendarThisMonth-hCalendar-' + calendar.eventDate).addEventToDay(
                    calendar.eventHTML, calendar.eventDate, fileId
                );

                calendar.getSidebarEvents(calendar.cursor);
            }
        );
    },

    editEvent : function(fileId)
    {
        calendar.eventFileId = fileId;

        $('div#hCalendarEvent').fadeIn(
            'slow',
            calendar.form.openEventCallback
        );
    },

    newCalendarEvent : function()
    {
        dialogue.prompt({
            ok : "Create Calendar",
            cancel : "Don't Create Calendar",
            title : "Create a New Calendar",
            label : "Calendar:",
            callback : {
                fn : function(calendarName)
                {
                    if (calendarName && calendarName.length)
                    {
                        this.newCalendarName = calendarName;

                        http.get(
                            '/hCalendar/newCalendar', {
                                operation : 'Create New Event',
                                hCalendarName : calendarName
                            },
                            function(json)
                            {
                                this.newCalendar(
                                    parseInt(json),
                                    this.newCalendarName
                                );
                            },
                            this
                        );
                    }
                },
                context : this
            }
        });
    },

    newCalendar : function(calendarId, calendarName)
    {
        if (calendarId > 0)
        {
            if (!$('select#hCalendarId option[value="' + calendarId + '"]').length)
            {
                $('select#hCalendarId').append(
                    $("<option/>")
                        .val(calendarId)
                        .text(calendarName)
                );
            }

            if (!$('div#hCalendarEvents-' + calendarId).length)
            {
                var div = $('div.hCalendarOptionTemplate').clone(true)
                    .attr('id', 'hCalendarEvents-' + calendarId)
                    .removeClass('hCalendarOptionTemplate');

                div.find('input').attr('id', 'hCalendarEventsCheckbox-' + calendarId);
                div.find('span').text(calendarName);

                $('div#hCalendarOwner').append(div);
            }
        }
    },

    deleteCalendar : function(calendarId)
    {
        this.selectedCalendarId = calendarId;

        dialogue.confirm({
            ok : "Delete Calendar",
            cancel : "Don't Delete Calendar",
            title : "Delete Calendar",
            label : "<p>\n" +
                        "Are you sure you want to PERMANENTLY delete <i>" + this.getCalendarName(calendarId) + "</i> " +
                        "and ALL events associated with it?\n" +
                    "</p>\n" +
                    "<p>\n" +
                        "This operation cannot be undone.\n" +
                    "</p>",
            callback : {
                fn : function(confirm)
                {
                    if (confirm)
                    {
                        http.get(
                            '/hCalendar/deleteCalendar', {
                                operation : 'Delete Calendar',
                                hCalendarId : this.selectedCalendarId
                            },
                            function(json)
                            {
                                $('div.hCalendarEventCalendarId-' + this.selectedCalendarId).remove();
                                $('select#hCalendarId option[value=' + this.selectedCalendarId + ']').remove();
                            },
                            this
                        );
                    }
                },
                context : this
            }
        });
    },

    getEventName : function(fileId)
    {
        return $('li#hCalendarEvent-' + fileId + ' h5').text();
    },

    getCalendarName : function(calendarId)
    {
        return $('div#hCalendarEvents-' + calendarId + ' span').text();
    },

    getCategoryName : function(calendarCategoryId)
    {
        return $('select#hCalendarCategoryId option[value=' + calendarCategoryId + ']').text();
    },

    newCategory : function()
    {
        //var category = prompt('New Type of Event:', '');

        dialogue.prompt({
            ok : "Create Category",
            cancel : "Don't Create Category",
            title : "Create a New Calendar Category",
            label : "New Type of Event:",
            callback : {
                fn : function(categoryName)
                {
                    if (categoryName && categoryName.length)
                    {
                        this.newCategoryName = categoryName;

                        http.get(
                            '/hCalendar/newCategory', {
                                operation : 'New Calendar Category',
                                hCalendarCategoryName : categoryName
                            },
                            function(json)
                            {
                                var calendarCategoryId = parseInt(json);

                                if (calendarCategoryId > 0)
                                {
                                    if (!$('select#hCalendarCategoryId option[value=' + calendarCategoryId + ']').length)
                                    {
                                        $('select#hCalendarCategoryId').append(
                                            $("<option/>")
                                                .val(calendarCategoryId)
                                                .text(this.newCategoryName)
                                        );
                                    }
                                }
                            },
                            this
                        );
                    }
                },
                context : this
            }
        });

        // if (category)
        // {
        //     http.get(
        //         '/hCalendar/newCategory', {
        //             operation : 'New Calendar Category',
        //             hCalendarCategoryName : category
        //         },
        //         function(json)
        //         {
        //             var calendarCategoryId = parseInt(json);
        //
        //             if (calendarCategoryId > 0)
        //             {
        //                 if (!$('select#hCalendarCategoryId option[value=' + calendarCategoryId + ']').length)
        //                 {
        //                     $('select#hCalendarCategoryId').append(
        //                         $("<option/>")
        //                             .val(calendarCategoryId)
        //                             .text(calendar.newCategoryName)
        //                     );
        //                 }
        //             }
        //         }
        //     );
        // }
    },

    deleteCategory : function()
    {
        var calendarCategoryId = $('select#hCalendarCategoryId').val();

        if (calendarCategoryId)
        {
            this.selectedCalendarCategoryId = calendarCategoryId;

            dialogue.confirm({
                ok : "Delete Category",
                cancel : "Don't Delete Category",
                title : "Delete Calendar Category",
                label :
                    "<p>" +
                        "Are you sure you want to PERMANENTLY delete <i>" + this.getCategoryName(calendarCategoryId) + "</i> " +
                        "and ALL events associated with it?" +
                    "</p>" +
                    "<p>" +
                        "This operation cannot be undone." +
                    "</p>",
                callback : {
                    fn : function(confirm)
                    {
                        if (confirm)
                        {
                            http.get(
                                '/hCalendar/deleteCategory', {
                                    operation : 'Delete Calendar Category',
                                    hCalendarCategoryId : this.selectedCalendarCategoryId
                                },
                                function(json)
                                {
                                    $('select#hCalendarCategoryId option[value=' + this.selectedCalendarCategoryId + ']').remove();
                                },
                                this
                            );
                        }
                    },
                    context : this
                }
            });
        }
    },

    miniEvents : function()
    {
        $('div#hCalendarMini table.hCalendarMini td.hCalendarThisMonth').click(
            function()
            {
                $(this).select('hCalendarDay');
                calendar.selectDayFromMini();
            }
        );
    },

    resize : function()
    {
        $('div#hCalendarView table.hCalendar').css({
            width : $('div#hCalendarView').innerWidth() + 'px',
            height : ($('div#hCalendarView').innerHeight() + 3) + 'px'
        });

        $('div#hCalendarViewInner').css({
            //width: this.GetCalendarViewInnerWidth()
            width : 'auto',
            height : 'auto'
        });

        this.resizeActive = true;

        //$('div#hCalendarView').get(0).scrollTop = calendar.GetScrollTopPosition();
    },

    watchForResize : function()
    {
        // Brutal hack here!
        //
        // This is needed because the resize event fires too often for this
        // code to keep up. This hack limits the scollTop position to being
        // set once a second (during a resize) at most.
        //
        // @todo : Research the resize event further, perhaps there is an
        // onresizeend event or something like that.
        if (this.resizeActive)
        {
            $('div#hCalendarView').get(0).scrollTop = calendar.getScrollTopPosition();
            this.resizeActive = false;
        }

        setTimeout('calendar.watchForResize();', 1000);
    },

    eventToggleEvents : function()
    {
        $('div.hCalendarOption').click(
            function() {
                $(this).toggleCalendar();
            }
        );

        $('input.hCalendar, input.hCalendar + label').click(
            function(event)
            {
                event.stopPropagation();
                this.checked = !this.checked;
                $(this).parent().toggleCalendar();
            }
        );
    },

    getSelectedCalendarMonth : function()
    {
        return this.getSelectedCalendar().splitId();
    },

    getEvents : function()
    {
        var request = '';

        $('div.hCalendarOption input').each(
            function()
            {
                if (this.checked)
                {
                    request += '&hCalendars[]=' + $(this).splitId();
                }
            }
        );

        var calendarDate = this.getSelectedCalendarMonth();

        // Remove this month's events only
        $('table#hCalendar-' + calendarDate).find('div.hCalendarEvent').remove();

        http.get(
            '/hCalendar/getEvents',
            'hCalendarDate=' + calendarDate + request,
            function(json)
            {
                calendar.getSidebarEvents(calendar.cursor);

                $(json).each(
                    function(i, file)
                    {
                        if (file.hCalendarDate && file.hCalendarDate instanceof Array && file.hCalendarDate.length)
                        {
                            $(file.hCalendarDate).each(
                                function(n, hCalendarDate)
                                {
                                    $('td#hCalendarThisMonth-hCalendar-' + hCalendarDate)
                                        .addEventToDay(
                                            file,
                                            hCalendarDate
                                        );
                                }
                            );
                        }
                        else
                        {
                            $('td#hCalendarThisMonth-hCalendar-' + file.hCalendarDate)
                                .addEventToDay(
                                    file,
                                    file.hCalendarDate
                                );
                        }
                    }
                );
            }
        );
    },

    getSidebarEvents : function()
    {
        var cursor = '';

        if (arguments[0])
        {
            this.cursor = arguments[0];
            cursor = arguments[0];
        }

        http.get(
            '/hCalendar/getSidebarEvents', {
                operation : 'Get Sidebar Events',
                hCalendarDate : this.getSelectedCalendarMonth(),
                hSearchCursor : cursor
            },
            function(json)
            {
                $('ul#hCalendarEventsList').html(json.events);

                if ($('div#hCalendarEventNavigation').length)
                {
                    $('div#hCalendarEventNavigation')
                        .html(json.navigation);
                }
            }
        );
    },

    getView : function()
    {
        var views = ['day', 'week', 'month'];

        for (viewCounter = 0; i < views.length; viewCounter++)
        {
            if (this.states['hCalendarControl' + views[viewCounter]])
            {
                return views[viewCounter];
            }
        }

        return 'month';
    },

    adjustOffsetToMatchMini : function()
    {
        if (this.miniOffset > this.selectedOffset)
        {
            this.selectedOffset++;
        }
        else if (this.miniOffset < this.selectedOffset)
        {
            this.selectedOffset--;
        }
    },

    getCalendar : function()
    {
        // See if the calendar already exists...
        if (this.getSelectedCalendar().length)
        {
            // Calendar exists...
            this.scrollToOffset();
            this.getMiniCalendar();
        }
        else
        {
            // Calendar does not exist
            http.get(
                '/hCalendar/hCalendarView/get', {
                    hCalendarOffset : this.selectedOffset,
                    hCalendarUniqueId : 'hCalendar'
                },
                function(html)
                {
                    if (this.selectedOffset > 0)
                    {
                        $('div#hCalendarViewInner').append(html);
                        this.resize();
                    }
                    else
                    {
                        $('div#hCalendarViewInner').prepend(html);
                        this.resize();
                        $('div#hCalendarView').get(0).scrollTop = this.getScrollTopPosition(this.selectedOffset + 1);
                    }

                    $('table.hCalendar').each(
                        function()
                        {
                            var td = $(this).find('td.hCalendarLastMonth:last');

                            if (!td.hasClass('hCalendarLastMonthLastDay'))
                            {
                                td.addClass('hCalendarLastMonthLastDay');
                            }
                        }
                    );

                    this.scrollToOffset();

                    this.calendarEvents();
                    this.getMiniCalendar();

                    if (this.getCalendarRunPost)
                    {
                        this.getCalendarRunPost();
                        this.getCalendarRunPost = '';
                    }
                },
                calendar
            );
        }
    },

    getMiniCalendar : function()
    {
        $('#hCalendarMiniInner').load(
            hot.path(
                '/hCalendar/hCalendarView/get', {
                    hCalendarOffset : this.miniOffset,
                    hCalendarUniqueId : 'hCalendarMini',
                    hCalendarWeekday : 'initial'
                }
            ),
            function()
            {
                calendar.miniEvents();
                calendar.getEvents();
            }
        );
    },

    getScrollTopPosition : function()
    {
        var offset = this.getOffsetString(typeof arguments[0] !== 'undefined' ? arguments[0] : this.selectedOffset);

        return(
            $('div#hCalendarView table.hCalendar caption[title="' + offset + '"]')
                .parent()
                .position().top +
            $('div#hCalendarView').scrollTop()
        );
    },

    getOffsetString : function()
    {
        var offset = typeof arguments[0] !== 'undefined' ? arguments[0] : this.selectedOffset;

        return (offset > 0? '+' : '') + offset;
    },

    scrollToOffset : function()
    {
        $('div#hCalendarView').animate(
            {
                scrollTop : this.getScrollTopPosition()
            },
            this.matchMiniOffset? 'fast' : 'slow',
            function()
            {
                if (calendar.matchMiniOffset)
                {
                    if (calendar.miniOffset != calendar.selectedOffset)
                    {
                        calendar.adjustOffsetToMatchMini();
                        calendar.getCalendar();
                    }
                    else
                    {
                        calendar.matchMiniOffset = false;
                    }
                }

                calendar.animating = false;
            }
        );
    },

    getSelectedCalendar : function()
    {
        var offset = (this.selectedOffset > 0? '+' : '') + this.selectedOffset;
        return $('div#hCalendarView table.hCalendar caption[title="' + offset + '"]').parent();
    },

    altKey : false,

    calendarEvents : function()
    {
        this.getSelectedCalendar().find('td.hCalendarThisMonth')
            .click(
                function()
                {
                    $(this).select('hCalendarDay');
                }
            )
            .dblclick(
                function()
                {
                    var year = $(this).parents('table:first').attr('data-year');
                    var month = $(this).parents('table:first').attr('data-month');
                    var day = $(this).attr('data-day');

                    calendar.newEvent(month + '/' + day + '/' + year);
                }
            )
            .on(
                'dragover.calendar',
                function(event)
                {
                    event.preventDefault();
                    $(this).addClass('hCalendarDateDroppable');

                    //event.dataTransfer.dropEffect = (!event.altKey)? 'move' : 'copy';
                    event.originalEvent.dataTransfer.dropEffect = (!calendar.altKey)? 'move' : 'copy';
                }
            )
            .on(
                'dragenter.calendar',
                function(event)
                {
                    event.preventDefault();

                    // I haven't yet figured out whether it's a browser bug,
                    // jQuery, or a bug in my code, but meta keys like event.altKey
                    // don't seem to get passed, hence the work-around.
                    //alert(calendar.altKey);
                    event.originalEvent.dataTransfer.dropEffect = (!calendar.altKey)? 'move' : 'copy';
                }
            )
            .on(
                'dragleave.calendar',
                function(event)
                {
                    event.preventDefault();
                    $(this).removeClass('hCalendarDateDroppable');
                    //event.dataTransfer.dropEffect = 'none';
                }
            )
            .on(
                'drop.calendar',
                function(event)
                {
                    event.preventDefault();
                    event.stopPropagation();

                    $(this).removeClass('hCalendarDateDroppable');

                    calendar.eventHTML = event.originalEvent.dataTransfer.getData(
                        (hot.userAgent == 'ie')? 'html' : 'text/html'
                    );

                    calendar.eventDate = $(this).splitId();

                    // IE needs this set in the drop event too, otherwise pulling the value
                    // in the dragend event will report 'none'
                    event.originalEvent.dataTransfer.dropEffect = (!calendar.altKey)? 'move' : 'copy';

                    if (!calendar.altKey)
                    {
                        // Move event from one date to another
                        calendar.updateFileDate(
                            calendar.getEventFileIdFromHTML(),
                            $(this).splitId()
                        );
                    }
                    else
                    {
                        // Duplicate the original event...
                        calendar.duplicate();
                    }
                }
            );
    },

    getEventFileIdFromHTML : function()
    {
         return new RegExp("/hCalendarEventFileId\-(\d*)/").exec(this.eventHTML)[1];
    },

    getEventCalendarIdFromHTML : function()
    {
         return new RegExp("/hCalendarEventCalendarId\-(\d*)/").exec(this.eventHTML)[1];
    },

    preloadImages : function()
    {
        var states = ['off', 'on', 'active', 'on_active'];
        var images = ['mini', 'notifications', 'search', 'events', 'info'];

        for (var m = 0; m < images.length; m++)
        {
            var path = '/images/themes/aqua/calendar/controls/';
            var id = 'hCalendarControl' + images[m].charAt(0).toUpperCase() + images[m].substr(1).toLowerCase();

            this.images[id] = new Array();

            for (var i = 0; i < states.length; i++)
            {
                this.images[id][states[i]] = new Image();
                this.images[id][states[i]].src = path + images[m] + (states[i] == 'off'? '' : '_' + states[i]) + '.png';
            }
        }
    },

    selectDayFromMini : function()
    {
        if (this.selectedOffset == this.miniOffset)
        {

        }
        else
        {
            //this.SelectedOffset = this.MiniOffset;
            this.getCalendarRunPost = this.selectDayFromMini;

            this.matchMiniOffset = true;

            this.adjustOffsetToMatchMini();

            this.getCalendar();
        }
    },

    getOrdinalSuffix : function(number)
    {
        var hundredRemainder = number % 100;
        var tenRemainder = number % 10;

        if (hundredRemainder - tenRemainder == 10)
        {
            return 'th';
        }

        switch (tenRemainder)
        {
            case 1: return 'st';
            case 2: return 'nd';
            case 3: return 'rd';
            default: return 'th';
        }
    },

    saveAs : function()
    {
        this.saveAsDialogue = window.open(
            hot.path(
                '/Applications/Finder/index.html', {
                    dialogue: 'SaveAs',
                    onSaveAs: (arguments[0]? 'calendar.' + arguments[0] : 'calendar.form.onSaveAs'),
                    path: $('input#hDirectoryPath').val(),
                    hFileName: $('input#hFileName').val()
                }
            ),
            '_blank',
            'width=600,height=400,scrollbars=no,resizable=yes'
        );

        this.saveAsDialogue.moveTo((window.screen.width    - 600) / 2, (window.screen.height - 400) / 2);
        this.saveAsDialogue.focus();
    }
};

/* Has to be "onload" because of the *@#$&$% WYSIWYG editor! */
$(window)
    .on(
        'load',
        function()
        {
            calendar.onload();
        }
    );

$(document)
    .ready(
        function()
        {
            calendar.ready();
        }
    )
    .on(
        'touchmove',
        function(event)
        {
            event.preventDefault();
        }
    )
    .keydown(
        function(event)
        {
            // Work-around for drag-n-drop bug.
            calendar.altKey = event.altKey;
        }
    )
    .keypress(
        function(event)
        {
            // Work-around for drag-n-drop bug.
            calendar.altKey = event.altKey;
        }
    )
    .keyup(
        function(event)
        {
            // Work-around for drag-n-drop bug.
            calendar.altKey = false;

            if (event.keyCode)
            {
                if (event.keyCode == 8 || event.keyCode == 46)
                {
                    // Delete or backspace keys.
                    event.preventDefault();
                }
            }
        }
    );