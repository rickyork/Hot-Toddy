if (typeof(mail) == 'undefined')
{
    var mail = {};
}

mail.editor = {
    ready : function()
    {
        $(document).on(
            'click',
            'ul#hMailEditorTemplates li',
            function()
            {
                $(this).select('hMailEditorTemplate');
                mail.editor.get($(this).splitId());
            }
        );
        
        hot.event(
            'hDialogueSelectTab',
            function(e)
            {
                if (e.label == 'Mailer')
                {
                    $('div#hMailHTMLWrapper').show();
                }
                else if (e.label == 'Properties')
                {
                    $('div#hMailHTMLWrapper').hide();
                }
            }
        );
    },

    get: function(mailTemplateId)
    {
        $('iframe#hMailHTML').attr('src', '/Applications/Mail Editor/Document.html?mailTemplateId=' + mailTemplateId);
    
        http.get(
            '/hMail/hMailEditor/get', {
                operation : 'Get mailer',
                mailTemplateId: mailTemplateId
            },
            function(json)
            {
                mail.editor.setForm(json);
            }
        );
    },
    
    setForm : function(json)
    {
        $('input#hMailTo').val(json.hMailTo);
        $('input#hMailCc').val(json.hMailCc);
        $('input#hMailBcc').val(json.hMailBcc);
        $('input#hMailFrom').val(json.hMailFrom);
        $('input#hMailReplyTo').val(json.hMailReplyTo);
        $('input#hMailSubject').val(json.hMailSubject);
        $('td#hMailTemplateId').html(json.hMailTemplateId);
        $('input#hMailTemplateName').val(json.hMailTemplateName);
        $('input#hMailTemplateDescription').val(json.hMailTemplateDescription);    
    }
};

$(document).ready(
    function()
    {
        mail.editor.ready();
    }
);