var ticket = {

    ready : function()
    {
        $('input#hTicketDialogueSave').click(
            function(event)
            {
                event.preventDefault();
            }
        );

        $('input#hTicketDialogueCancel').click(
            function(event)
            {
                event.preventDefault();
                $('form#hTicketDialogue').closeDialogue(true);
            }
        );
    },

    save : function()
    {
        http.post(
            '/hTicket/save',
            $('form#hTicketDialogue').serialize(),
            function()
            {

            },
            this
        );
    }
};

$(document).ready(
    function()
    {
        ticket.ready();
    }
);