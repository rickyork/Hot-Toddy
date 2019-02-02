$.fn.extend({

    toggleLocationOptions : function()
    {
        var options =
            this.parents('.hSpotlightSearch')
                .find('div.hSpotlightExtendedLocationOptions')
                .slideToggle('slow');

        return this;
    },

    toggleTimeRange : function()
    {
        var div =
            this.parents('.hSpotlightSearch')
                .find('div.hSpotlightExtendedTimeRange');

        !parseInt(this.val())? div.slideDown('slow') : div.slideUp('slow');

        return this;
    },

    toggleTimeOptions : function()
    {
        var search = this.parents('div.hSpotlightSearch');

        var time = search.find('div.hSpotlightExtendedTimeOptions');

        if (time.hasClass('hSpotlightExtendedTimeOptionsOn'))
        {
            time.slideUp('slow');
            time.removeClass('hSpotlightExtendedTimeOptionsOn');
            search.find('div.hSpotlightExtendedTimeColumn').slideUp('slow');
            search.find('div.hSpotlightExtendedTimeRange').slideUp('slow');
        }
        else
        {
            time.slideDown('slow');
            time.addClass('hSpotlightExtendedTimeOptionsOn');

            search.find('div.hSpotlightExtendedTimeColumn').slideDown('slow');

            if (!parseInt(search.find('select.hSpotlightSearchExtendedTimeRange').val()))
            {
                search.find('div.hSpotlightExtendedTimeRange').slideDown('slow');
            }
        }

        return this;
    },

    openExtendedSearch : function()
    {
        if (arguments[0])
        {
            this.find('div.hSpotlightSearchExtendedWrapper').hide();
            this.find('div.hSpotlightSearchExtended').slideDown('slow');
        }
        else
        {
            this.addClass('hSpotlightSearchExtendedOn');
            this.find('div.hSpotlightSearchExtendedWrapper').slideDown('slow');

            var div = this.find('div.hSpotlightSearchExtendedToggleOptions');
            div.addClass('hSpotlightSearchExtendedHideOptions');
            div.find('span').text('Hide Options');
        }

        return this;
    },

    openAdvancedSearch : function()
    {
        var search = this.parents('.hSpotlightSearch');
        var id = search.splitId();

        $('form#hSpotlightSearchAdvanced' + id + 'Dialogue').toggleDialgoue();

        // Prepare the initial fields...
        spotlight.getAdvancedSearchFields(search.splitId());

        return this;
    },

    closeExtendedSearch : function()
    {
        if (arguments[0])
        {
            this.find('div.hSpotlightSearchExtended').slideUp(
                'slow',
                function()
                {
                    var div = $(this).parents('div.hSpotlightSearch');

                    div.find('div.hSpotlightSearchExtendedWrapper').hide();
                    div.removeClass('hSpotlightSearchExtendedOn');

                    div = div.find('div.hSpotlightSearchExtendedToggleOptions');
                    div.removeClass('hSpotlightSearchExtendedHideOptions');
                    div.find('span').text('Show Options');
                }
            );
        }
        else
        {
            this.find('div.hSpotlightSearchExtendedWrapper')
                .slideUp('slow');

            this.removeClass('hSpotlightSearchExtendedOn');

            var div = this.find('div.hSpotlightSearchExtendedToggleOptions');

            div.removeClass('hSpotlightSearchExtendedHideOptions');
            div.find('span').text('Show Options');
        }

        return this;
    },

    openBasicSearch : function()
    {
        if (!this.hasClass('hSpotlightSearchExtendedOn'))
        {
            this.openExtendedSearch(true);
        }

        return this;
    },

    closeBasicSearch : function()
    {
        this.closeExtendedSearch(true);
        return this;
    },

    activateSearch : function()
    {
        this.parents('.hSpotlightSearch')
            .addClass('hSpotlightSearchOn')
            .openBasicSearch();

        return this;
    },

    search : function()
    {
        var node = this.parents('.hSpotlightSearch');

        // Columns to search...
        time = node.find('input.hSpotlightSearchExtendedToggleTime');

        var toggleTime = time.length? time.is(':checked') : false;

        var post = 'hSpotlightSearchToggleTime=' + (toggleTime? 1 : 0);

        if (toggleTime)
        {
            post +=
                '&hSpotlightSearchTimeRange=' + node.find('select.hSpotlightSearchExtendedTimeRange').val() +
                '&hSpotlightSearchDateStart=' + node.find('input.hSpotlightSearchExtendedDateStart').val() +
                '&hSpotlightSearchDateEnd=' + node.find('input.hSpotlightSearchExtendedDateEnd').val() +
                '&hSpotlightSearchTimeColumn=' + node.find('select.hSpotlightSearchExtendedTimeColumn').val();
        }

        var location = node.find('input.hSpotlightSearchExtendedToggleLocation');
        var toggleLocation = location.length? location.is(':checked') : false;

        post += '&hSpotlightSearchToggleLocation=' + (toggleLocation? 1: 0);

        if (toggleLocation)
        {
            post +=
                '&hSpotlightSearchCountryId=' + node.find('select.hSpotlightSearchExtendedCountryId').val() +
                '&hSpotlightSearchStateId=' + node.find('select.hSpotlightSearchExtendedStateId').val() +
                '&hSpotlightSearchCity=' + encodeURIComponent(node.find('input.hSpotlightSearchExtendedCity').val()) +
                '&hSpotlightSearchPostalCode=' + encodeURIComponent(node.find('input.hSpotlightSearchExtendedPostalCode').val());
                //'&hSpotlightSearchCounty=' + encodeURIComponent(node.find('input.hSpotlightSearchExtendedCounty').val());
        }

        var select = node.find('select.hSpotlightSearchExtendedColumn').get(0);
        var columnsSelected = 0;

        if (select && select.options && select.options.length)
        {
            for (var optionCounter = 0; optionCounter < select.options.length; optionCounter++)
            {
                if (select.options[optionCounter].selected)
                {
                    post += '&hSpotlightSearchColumns[]=' + encodeURIComponent(select.options[optionCounter].value);
                    columnsSelected = 1;
                }
            }
        }

        if (!columnsSelected)
        {
            post += '&hSpotlightSearchColumns=';
        }

        post += '&hSpotlightSearchQuery=' + encodeURIComponent(arguments[0]? arguments[0] : node.find('input.hSpotlightSearchInput').val());

        var uri = spotlight.path;

        if (spotlight.sortColumn)
        {
            uri +=
                (uri.indexOf('?') != -1? '&' : '?') +
                'hSpotlightSortColumn=' + encodeURIComponent(spotlight.sortColumn) +
                '&hSpotlightSortOrientation=' + spotlight.sortOrientation;
        }

        spotlight.active = node;

        if (!arguments[0])
        {
            node.find('img.hSpotlightActivity')
                .addClass('hSpotlightActivityOn');
        }

        if (uri)
        {
            http.post(
                {
                    url : uri,
                    operation : 'Spotlight Search'
                },
                post,
                function(json)
                {
                    spotlight.active
                        .find('img.hSpotlightActivity')
                        .removeClass('hSpotlightActivityOn');

                     hot.fire(
                         'spotlightSearch', {
                             json : json
                         }
                     );
                }
            );
        }
        else
        {
            hot.console.error('Spotlight: Unable to perform search, no path is defined.');
        }

        return this;
    }
});

var spotlight = {

    isActive : [],
    columnCounter     : 0,
    listener : '',

    handler : '',
    handlerContext : '',
    active : '',
    sortColumn : '',
    sortOrientation : '',

    ready : function()
    {
        $('ul.hSpotlightRolodex li').hover(
            function()
            {
                $(this).addClass('hSpotlightRoldexOver');
            },
            function()
            {
                $(this).removeClass('hSpotlightRoldexOver');
            }
        );

        $('input.hSpotlightSearchInput')
            .focus(
                function()
                {
                    $(this).activateSearch();
                }
            )
            .keyup(
                function(event)
                {
                    if (this.value && this.value.length > 1)
                    {
                        $(this).search();
                    }
                }
            );

        $('div.hSpotlightSearch').hover(
            function()
            {
                spotlight.isActive[this.id] = true;
            },
            function()
            {
                spotlight.isActive[this.id] = false;
            }
        );

        $('div.hSpotlightSearchExtendedToggleOptions').click(
            function()
            {
                if ($(this).find('span').text() == 'Show Options')
                {
                    $(this)
                        .parents('div.hSpotlightSearch')
                        .openExtendedSearch();
                }
                else
                {
                    $(this)
                        .parents('div.hSpotlightSearch')
                        .closeExtendedSearch();
                }
            }
        );

        $('input.hSpotlightSearchExtendedButton, div.hSpotlightIcon').click(
            function(event)
            {
                event.preventDefault();
                $(this).search();
            }
        );

        $('div.hSpotlightSearchExtendedMoreOptions').click(
            function()
            {
                $(this).openAdvancedSearch();
            }
        );

        $(document).mousedown(
            function()
            {
                spotlight.deactivate();
            }
        );

        $('select.hSpotlightSearchExtendedTimeRange').change(
            function()
            {
                $(this).toggleTimeRange();
            }
        );

        $('input.hSpotlightSearchExtendedToggleTime').click(
            function()
            {
                $(this).toggleTimeOptions();
            }
        );

        $('input.hSpotlightSearchExtendedToggleLocation').click(
            function()
            {
                $(this).toggleLocationOptions();
            }
        );
    },

    setSortColumn : function(column, orientation)
    {
        this.sortColumn = column;
        this.sortOrientation = orientation;
    },

    getAdvancedSearchFields : function(id)
    {
        if (!arguments[1])
        {
            var table = document.createElement('table');

            table.className = 'hSpotlightSearchAdvanced';

            var thead = document.createElement('thead');
            var tr = document.createElement('tr');

            $(['', 'Table', 'Column', 'Options', 'Query', 'Add/Remove']).each(
                function()
                {
                    var th = document.createElement('th');
                    th.appendChild(document.createTextNode(this));
                    tr.appendChild(th);
                }
            );

            thead.appendChild(tr);
            table.appendChild(thead);

            var tbody = document.createElement('tbody');
        }
        else
        {
            var tbody = $('form#hSpotlightSearchAdvanced' + id + 'Dialogue div.hSpotlightSearchAdvancedFields table tbody').get(0);
        }

        var tr = document.createElement('tr');

        // Boolean AND/OR
        var td = document.createElement('td');
        td.className = 'hSpotlightSearchAdvancedBoolean';

        if (arguments[1])
        {
            var select = $('select#hSpotlightSearchAdvancedBoolean-' + id).get(0)
                            .cloneNode(true);

            select.id += '-' + this.columnCounter;
            td.appendChild(select);
        }

        tr.appendChild(td);

        // Tables
        var td = document.createElement('td');
        td.className = 'hSpotlightSearchAdvancedTables';

        var select = $('select#hSpotlightSearchAdvancedTables-' + id).get(0)
                        .cloneNode(true);

        select.id += '-' + this.columnCounter;
        $(select).change(this.getTableColumns);

        td.appendChild(select);
        tr.appendChild(td);

        // Columns
        var td = document.createElement('td');
        td.className = 'hSpotlightSearchAdvancedColumns';

        var select = document.createElement('select');
        select.disabled = true;

        td.appendChild(select);
        tr.appendChild(td);

        // Options
        var td = document.createElement('td');
        td.className = 'hSpotlightSearchAdvancedOptions';

        var select = $('select#hSpotlightSearchAdvancedOptions-' + id).get(0)
                        .cloneNode(true);
        select.id += '-' + this.columnCounter;
        select.disabled = true;

        td.appendChild(select);
        tr.appendChild(td);

        // Query
        var td = document.createElement('td');
        td.className = 'hSpotlightSearchAdvancedQuery';

        var input = document.createElement('input');
        input.type = 'text';
        input.size = 25;
        input.disabled = true;
        td.appendChild(input);

        tr.appendChild(td);

        // More/Less
        var td = document.createElement('td');
        td.className = 'hSpotlightSearchAdvancedAddRemove';

        var input = $('input#hSpotlightSearchAdvancedAdd-' + id).get(0).cloneNode(true);
        input.id += '-' + this.columnCounter;

        $(input).click(
            function(event)
            {
                event.preventDefault();

                var tr = $(this).parents('tr');
                tr.find('td.hSpotlightSearchAdvancedAddRemove').html('');

                spotlight.getAdvancedSearchFields($(this).splitId(1), true);
            }
        );

        input.disabled = true;

        td.appendChild(input);

        if (arguments[1])
        {
            var input = $('input#hSpotlightSearchAdvancedRemove-' + id)
                            .get(0)
                            .cloneNode(true);

            input.id += '-' + this.columnCounter;

            $(input).click(
                function(event)
                {
                    event.preventDefault();
                }
            );

            td.appendChild(input);
        }

        tr.appendChild(td);
        tbody.appendChild(tr);

        if (!arguments[1])
        {
            table.appendChild(tbody);
            $('form#hSpotlightSearchAdvanced' + id + 'Dialogue div.hSpotlightSearchAdvancedFields').append(table);
        }

        this.columnCounter++;
    },

    getTableColumns : function()
    {
        var tr = $(this).parents('tr');
        var td = tr.find('td.hSpotlightSearchAdvancedColumns');
        td.html('');

        if (this.value)
        {
            var columnId = $(this).splitId();
            var search = $(this).splitId(1);

            var select =
                $('select#hSpotlightSearchAdvancedColumns-' + search + '-' + $(this).val())
                    .get(0)
                    .cloneNode(true);

            select.id += '-' + columnId;
            select.change(spotlight.toggleColumnFields);
        }
        else
        {
            var select = document.createElement('select');
            select.disabled = true;

            tr.find('input, select').each(
                function()
                {
                    if (!$(this).hasClass('hSpotlightSearchAdvancedTables'))
                    {
                        $(this).attr('disabled', 'disabled');
                    }
                }
            );
        }

        td.appendChild(select);
    },

    toggleColumnFields : function()
    {
        $(this).parents('tr').find('input, select').each(
            function()
            {
                if (!$(this).hasClass('hSpotlightSearchAdvancedBoolean hSpotlightSearchAdvancedTables hSpotlightSearchAdvancedColumns'))
                {
                    if ($(this).val())
                    {
                        $(this).removeAttr('disabled');
                    }
                    else
                    {
                        $(this).attr('disabled', true);
                    }
                }
            }
        );
    },

    deactivate : function()
    {
        var force = arguments[0]? true : false;

        $('div.hSpotlightSearch').each(
            function()
            {
                if (!$(this).find('img.hSpotlightActivity').hasClass('hSpotlightActivityOn') || force)
                {
                    $(this).find('img.hSpotlightActivity').removeClass('hSpotlightActivityOn');

                    if (!spotlight.isActive[this.id] || force)
                    {
                        $(this).removeClass('hSpotlightSearchOn');
                        $(this).closeExtendedSearch(true);
                    }
                }
            }
        );
    },

    setPath : function(path)
    {
        this.path = path;
    }
};

$(document).ready(
    function()
    {
        spotlight.ready();
    }
);
