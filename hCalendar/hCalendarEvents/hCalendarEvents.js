$$.extend({
    GetFiles : function()
    {
        var $files = [];

        this.find('span.hCalendarEventFileId').each(
            function() {
                $files.push($(this).text());
            }
        );

        return $files;
    },

    CalendarEvents : {
        Ready : function()
        {
            $('div#hCalendarControlPrevious').click(
                function() {
                    $$.CalendarEvents.GetCalendar('previous');
                }
            );

            $('div#hCalendarControlNext').click(
                function() {
                    $$.CalendarEvents.GetCalendar('next');
                }
            );

            $('div#hCalendarControls span').click(
                function() {
                    $$.CalendarEvents.GetCalendar('current');
                }
            );

            this.CalendarReady();
        },

        GetCalendar : function($navigate)
        {     
            $.get(
                $$.Path(
                    '/calendar.php', {
                        action : 'getCalendar',
                        date: $('td.hCalendarFirst').SplitId(),
                        navigate: $navigate
                    }
                ),
                function(html) {
                    //$$.Debug(html);
                    $('table.hCalendar').replaceWith(html);
                    $$.CalendarEvents.CalendarReady();

                    $.get(
                        $$.Path(
                            '/calendar.php', {
                                action : 'getEvents',
                                date: $('td.hCalendarFirst').SplitId()
                            }
                        ),
                        function(html) {
                            $('div#hCalendarEvents').replaceWith(html);
                        },
                        'html'
                    );
                },
                'html'
            );
        },

        CalendarReady : function()
        {
            $('td.hCalendarEvent').hover(
                function() {
                    if (!$(this).find('div.hCalendarEvents div.hCalendarEventLayer').length) {
                        $(this).find('div.hCalendarEvents').load(
                            $$.Path(
                                '/calendar.php', {
                                    action : 'getLayer',
                                    hFiles : $(this).GetFiles().join(',')
                                }
                            ),
                            function() {
                                $(this).find('div.hCalendarEventLayer').fadeIn('fast');
                            }
                        );
                    } else {
                        $(this).find('div.hCalendarEvents div.hCalendarEventLayer').fadeIn('fast');
                    }
                },
                function() {
                    $(this).find('div.hCalendarEvents div.hCalendarEventLayer').fadeOut('fast');
                }
            );
        }
    }
});

$(document).ready(
    function() {
        $$.CalendarEvents.Ready();
    }
);