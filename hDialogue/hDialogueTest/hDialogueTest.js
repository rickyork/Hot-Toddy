if (typeof(dialogue) == 'undefined')
{
    var dialogue = {};
}

dialogue.test = {
    ready : function()
    {
        $('input#hDialogueTestAlert').click(
            function(e)
            {
                e.preventDefault();

                dialogue.alert({
                        title : "Alert",
                        label : "This is a custom alert dialogue.",
                        ok : "Dismiss"
                    },
                    function()
                    {
                        
                    }
                );
            }
        );
        
        $('input#hDialogueTestConfirm').click(
            function(e)
            {
                e.preventDefault();
                
                dialogue.confirm({
                        title : "Confirmation Dialogue",
                        label : "<p>This is a custom confirmation dialogue.</p><p>This is <b>another</b> line.</p>",
                        ok : "Delete File",
                        cancel : "Don't Delete File"
                    },
                    function(response)
                    {
                    
                    }
                );
            }
        );

        $('input#hDialogueTestPrompt').click(
            function(e)
            {
                e.preventDefault();
                
                dialogue.prompt({
                        title : "Prompt Dialogue",
                        label : "<p>This is a custom prompt dialogue.</p><p>This is <b>another</b> line.</p>",
                        ok : "Create File",
                        cancel : "Don't Create File"
                    },
                    function(response)
                    {
                        
                    }
                );
            }
        );
        
        $('input#hDialogueTestLogin').click(
            function(e)
            {
                e.preventDefault();
                
                dialogue.login({
                        
                
                    },
                    function()
                    {
                    
                    }
                );
            }
        );
    }
};

$(document).ready(
    function()
    {
        dialogue.test.ready();
    }
);