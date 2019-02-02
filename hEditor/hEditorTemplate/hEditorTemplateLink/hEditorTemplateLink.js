$.fn.extend({
    setLink : function()
    {
        editor.link.a = this;
    } 
});

editor.link = {
    ready : function()
    {
      $('li#hEditorTemplateLinkSave button').click(
            function()
            {
                editor.link.save({
                    path: $('input#hEditorLink').val(),
                    label: $('textarea#hEditorLinkLabel').val(),
                    newWindow: $('input#hEditorLinkTarget').is(':checked')
                });
          
                editor.link.closeDialogue();
            }
        );

        $('li#hEditorTemplateLinkCancel button').click(
            function()
            {
                editor.link.closeDialogue();
            }
        );
        
        $('li#hEditorTemplateLinkRemove button').click(
            function()
            {
                $(this).commandEvent(
                    function()
                    {
                        editor.link.removeLink();
                    }
                );
            }
        );

        $('li#hEditorTemplateLinkBrowse').click(
            function()
            {
                editor.link.openFinder();
            }
        );
    },
    
    openFinder : function()
    {
        this.chooseDialogue = hot.window(
            '/Applications/Finder/index.html', {
                dialogue: 'Choose',
                onChooseFile: 'editor.link.onChooseFile'
            },
            600, 400,
            'hFinderChoose', {
                scrollbars: false,
                resizable: true
            }
        );
    },

    removeLink : function()
    {
        if (this.a && this.a.length)
        {      
            this.a.outerHTML(this.a.text());
        }
        
        this.closeDialogue();
    },

    onChooseFile : function(id, path)
    {
        $('input#hEditorLink').val(path);
    },
   
    getNode : function()
    {
        if (this.a)
        {
            return this.a;
        }
        else
        {
            var node = editor.getNodeAtCaretPosition();

            if (node && node.get(0).nodeName.toLowerCase() == 'a')
            {
                this.a = node;
                return this.a;
            }
            else if (node && node.parent().get(0).nodeName.toLowerCase() == 'a')
            {
                this.a = node.parent();
                return this.a;
            }
            else
            {
                var a = $(document.createElement('a'));
                return a;
            }
        }
    },
   
    save : function(obj)
    {
        editor.restoreSelection();

        var a = this.getNode();
     
        a.attr('href', obj.path);
        a.html(obj.label);
     
        if (obj.newWindow)
        {
            a.attr('target', '_blank');
        }
        else
        {
            a.removeAttr('target');
        }

        if (!this.a)
        {            
            a.surroundSelection(obj.path);
        }
    },

    openDialogue : function()
    {
        var a = this.getNode();

        if (a && a.length)
        {
            var href = decodeURIComponent(a.attr('href'));

            href = href.replace(/\?hFileLastModified\=(\d*)/, '');
            href = href.replace(/\+/gi, ' ');
            
            if (href == 'undefined')
            {
                href = '';                
            }

            $('input#hEditorLink').val(href);

            $('textarea#hEditorLinkLabel').val(a.html());

            if (a.attr('target') == '_blank')
            {
                $('input#hEditorLinkTarget').attr('checked', true);
            }
        }

        if (this.a && this.a.length)
        {
            $('div#hEditorTemplateLinkRemoveWrapper').removeClass('hEditorTemplateDisabledButtons');
        }

        editor.openModal();

        $('div#hEditorTemplateLink').slideDown('slow');
        $('li#hEditorTemplateLink').commandOn();
    },

    closeDialogue : function()
    {
        editor.closeModal();
        $('div#hEditorTemplateLink').slideUp('slow');
        $('li#hEditorTemplateLink').commandOff();
   
        this.a = null;

        $('div#hEditorTemplateLinkRemoveWrapper').addClass('hEditorTemplateDisabledButtons');

        $('input#hEditorLink').val('');
        $('input#hEditorLinkTarget').removeAttr('checked');
    }
};

$(document).ready(
    function()
    {
        editor.link.ready();
    }
);