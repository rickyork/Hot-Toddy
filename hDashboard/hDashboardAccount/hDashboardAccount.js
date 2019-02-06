if (typeof(dashboard) == 'undefined')
{
    var dashboard = {};
}

dashboard.account = {

    ready : function()
    {
        $('div#hDashboardAccountEditLogin').click(
            function()
            {
                $('form#hDashboardAdminUserDialogue').openDialogue();  
            }
        );
        
        $('input#hDashboardAdminUserDialogueCancel').click(
            function(event)
            {
                event.preventDefault();
                $('form#hDashboardAdminUserDialogue').closeDialogue();
            }
        );
    }
};

$(document).ready(
    function()
    {
        dashboard.account.ready();
    }
);