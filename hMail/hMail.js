
/*
//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
//\\\       \\\\\\\\|
//\\\ @@    @@\\\\\\| Hot Toddy Mail Plugin
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

var hMail = {

    icon : {
        on  : [],
        off : []
    },

    events : function()
    {
        // Get images and set the over and outs

        $nodes = cssQuery('img.hmail-icon');

        for ($i = 0; $i < $nodes.length; $i++)
        {
            hMail.icon.on[$i]  = $nodes[$i].lowsrc;
            hMail.icon.off[$i] = $nodes[$i].src;

            $nodes[$i].id = 'hmail-icon-' + $i;

            addEvent(
                $nodes[$i],
                'mouseover',
                function()
                {
                    this.src = hMail.icon.on[getId(this.id)];
                }
            );

            addEvent(
                $nodes[$i],
                'mouseout',
                function()
                {
                    this.src = hMail.icon.off[getId(this.id)];
                }
            );
        }

        addEvent(
            'hmail-user',
            'click',
            function()
            {
                hMail.closeOpenDialogues();
                getElement('hmail-urrd').style.display = 'block';
            }
        );

        addEvent(
            'hmail-user-template-cancel',
            'click',
            function($e)
            {
                getElement('hmail-urrd').style.display = 'none';
                $e.preventDefault();
            }
        );

        addEvent(
            'hmail-access-code',
            'click',
            function()
            {
                hMail.closeOpenDialogues();
                getElement('hmail-acd').style.display = 'block';
            }
        );

        addEvent(
            'hmail-access-code-cancel',
            'click',
            function($e)
            {
                getElement('hmail-acd').style.display = 'none';
                $e.preventDefault();
            }
        );
    },

    closeOpenDialogues : function()
    {
        $nodes = cssQuery('.hmail-popup-dialogue');

        for (var $i in $nodes)
        {
            if ($nodes[$i].style)
            {
                $nodes[$i].style.display = 'none';
            }
        }
    }
};

addEvent(window, 'load', hMail.events);