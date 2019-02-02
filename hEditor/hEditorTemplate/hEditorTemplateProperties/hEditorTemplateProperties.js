editor.properties = {
    node : null,
    
    ready : function()
    {
        $('div#hEditorTemplateProperties').draggable();
        
        hot.event(
            'hEditorTemplateNodeSelected',
            function()
            {
                editor.properties.node = this;
                editor.properties.activate();
            }
        );
    },
    
    activate : function()
    {
    
    },
    
    deactivate : function()
    {
    
    }
};

$(document).ready(
    function()
    {
        editor.properties.ready();
    }
);