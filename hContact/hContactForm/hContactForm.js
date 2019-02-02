
/**
//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
//\\\       \\\\\\\\|
//\\\ @@    @@\\\\\\| Hot Toddy Contact Form
//\\ @@@@  @@@@\\\\\|
//\\\@@@@| @@@@\\\\\|
//\\\ @@ |\\@@\\\\\\| http://www.hframework.com
//\\\\  ||   \\\\\\\| © Copyright 2015 Richard York, All rights Reserved
//\\\\  \\_   \\\\\\|
//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
//\\\\\  ----  \@@@@| http://www.hframework.com/license
//@@@@@\       \@@@@|
//@@@@@@\     \@@@@@|
//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
*/

$.fn.extend({
    swapStates : function()
    {  
        if (parseInt($(this).val()) > 0)
        {
            var states = contact.form.states;
        
            states.html('');

            http.get(
                '/hLocation/getStates', { 
                    countryId : $(this).val()
                },
                function(json)
                {
                    states.removeAttr('disabled');

                    var path  = '/images/icons/32x32/flags/' + json.iso2.toLowerCase() + '.png';
                    var alt   = json.iso2 + ' Flag';
                
                    if (!hot.IE6)
                    {
                        $('span#hContactFormFlag img').attr({
                            src: path,
                            alt: alt
                        });
                    }
                    else
                    {
                        $('span#hContactFormFlag span').replaceWith(
                            "<img src='" + path + "' alt='" + alt + "' />"
                        );
                    }

                    $('td#hContactFormStateLabel span').text(json.stateLabel + ':');

                    if (typeof(json.states) != 'undefined' && json.states && json.states.length  > 1)
                    {
                        states.append("<option value='0'>Please select a " + json.stateLabel + "</option>");

                        $(json.states).each(
                            function(key, value)
                            {
                                states.append("<option value='" + value[0] + "'>" + value[1] + "</option>");
                            }
                        );
                    }
                    else
                    {
                        states.attr('disabled', 'disabled');
                    }
                }
            );
        }
    }
});

if (typeof(contact) == 'undefined')
{
    var contact = {};
}

contact.form = {

    states : null,
    country : null,
    
    ready : function()
    {  
        if ($('select#hLocationStateId').length)
        {
            this.states  = $('select#hLocationStateId'); 
            this.country = $('select#hLocationCountryId');

            if (!this.states.find('option').length)
            {
                this.states.attr('disabled', 'disabled');
            }

            this.country.change(
                function()
                {
                    $(this).swapStates();
                }
            );
        }

        if ($('input#hContactDateOfBirth').length && typeof($.datepicker) != 'undefined')
        {
            $('input#hContactDateOfBirth').datepicker({
                changeMonth: true,
                changeYear: true,
                yearRange: '-150:+1'
            });
        } 
    }
};

$(document).ready(
    function()
    {
        contact.form.ready();
    }
);
