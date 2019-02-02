finder.passwords = {

    selected : null,

    ready : function()
    {
        $('li#hFinderContextPassword').click(
            function()
            {
                var node = hot.selected('hFinder');

                $('form#hFinderPasswordsDialogue').openDialogue('Passwords for: ' + node.getFileName());

                finder.passwords.get();
                finder.contextMenu.close();
            }
        );

        $('input#hFinderPasswordsDialogueClose').click(
            function(e)
            {
                $('form#hFinderPasswordsDialogue').closeDialogue();
                finder.passwords.closeDialogue();
                e.preventDefault();
            }
        );
        
        hot.event('hFinderPasswordsDialogueClose', finder.passwords.closeDialogue);

        $('li#hFinderPasswordAdd').click(
            function()
            {
                if (hot.selected('hFinder').length)
                {
                    $('form#hFinderPasswordDialogue').openDialogue('New Password For: ' + hot.selected('hFinder').getFileName());
                }
            }
        );

        $('li#hFinderPasswordEdit').click(
            function()
            {
                finder.passwords.select();
                finder.passwords.edit();
            }
        );

        $('li#hFinderPasswordEmail').click(
            function()
            {
                finder.passwords.select();

                var node    = hot.selected('hFinder');
                var path = 'http://' + server.host + escape(escape(hot.path(node.getFilePath())));

                $('span#hFinderPasswordEmailTemplatePath').text(path);
                $('span#hFinderPasswordEmailTemplatePassword').text(hot.selected('hFinderPassword').find('td.hFinderPassword var').text());

                location.href = 'mailto:?body=' + encodeURIComponent($('p#hFinderPasswordEmailTemplate').text());
            }
        );

        $('li#hFinderPasswordRemove').click(
            function()
            {
                if (hot.selected('hFinderPassword').length)
                {
                    http.get(
                        '/hFinder/hFinderPasswords/delete', {
                            operation : 'Delete File Password',
                            path : hot.selected('hFinder').getFilePath(),
                            hFilePasswordCreated : hot.selected('hFinderPassword').splitId()
                        },
                        function(json)
                        {
                            hot.unselect('hFinderPassword').remove();
                        }
                    );
                }
                else
                {
                    alert('No password is selected.');
                }
            }
        );

        $('input#hFinderPasswordDialogueCancel').click(
            function(e)
            {
                finder.passwords.reset();
                $('form#hFinderPasswordDialogue').toggleDialogue();
                e.preventDefault();
            }
        );

        $('input#hFinderPasswordDialogueSave').click(
            function(e)
            {
                http.post(
                    '/hFinder/hFinderPasswords/save', {
                        path : hot.selected('hFinder').getFilePath()
                    }, {
                        operation : 'Save File Password',
                        hFilePasswordCreated : (hot.selected('hFinderPassword').length? hot.selected('hFinderPassword').splitId() : 0),
                        hFilePassword : $('input#hFilePassword').val(),
                        hFilePasswordLifetime : $('select#hFilePasswordLifetime').val(),
                        hFilePasswordExpirationAction : $('select#hFilePasswordExpirationAction').val(),
                        hFilePasswordRequired : $('select#hFilePasswordRequired').val()
                    },
                    function(json)
                    {
                        this.reset();
                        $('form#hFinderPasswordDialogue').toggleDialogue();
                        this.get();
                    },
                    finder.passwords
                );
            }
        );

        $('input#hFinderPasswordReveal').click(
            function()
            {
                var isChecked = this.checked;

                $('div#hFinderPasswordWrapper td.hFinderPassword').each(
                    function()
                    {
                        var node = $(this).find('var');

                        if (node && node.length)
                        {
                            if (isChecked)
                            {
                                $(this).html(node.text() + node.outerHTML());
                            }
                            else
                            {
                                $(this).html('&bull;&bull;&bull;&bull;&bull;&bull;&bull;' + node.outerHTML());
                            }
                        }
                    }
                );
            }
        );

        $(document)
            .on(
                'click',
                'form#hFinderPasswordsDialogue div.hApplicationUIListBody tr',
                function()
                {
                    $(this).select('hFinderPassword');
                }
            )
            .on(
                'dblclick',
                'form#hFinderPasswordsDialogue div.hApplicationUIListBody tr',
                function()
                {
                    finder.passwords.edit();
                }
            );
    },
    
    closeDialogue : function()
    {
        $('form#hFinderPasswordsDialogue div.hApplicationUIListBody tbody tr:not(.hFinderPasswordTemplate)').remove();
    },

    select : function()
    {
        if (!hot.selected('hFinderPassword').length)
        {
            $('form#hFinderPasswordDialogue div.hApplicationUIListBody tbody tr:not(.hFinderPasswordTemplate)').eq(0).select('hFinderPassword');
        }
    },

    get : function()
    {
        http.get(
            '/hFinder/hFinderPasswords/get', {
                operation : 'Get File Passwords',
                path : hot.selected('hFinder').getFilePath()
            },
            function(json)
            {
                $(json).each(
                    function (key, value)
                    {
                        finder.passwords.set(value);
                    }
                );
            }                
        );
    },

    reset : function()
    {
        $(this.fields).each(
            function()
            {
                $('#' + this).val('');
            }
        );
    },

    set : function(obj)
    {
        var tr = $('tr.hFinderPasswordTemplate').clone(true);
        
        tr.removeClass('hFinderPasswordTemplate');
        
        tr.attr('id', 'hFilePassword-' + obj.hFilePasswordCreated);

        var password = '';

        if ($('input#hFinderPasswordReveal:checked').length)
        {
            password = obj.hFilePassword;
        }
        else
        {
            password = '&bull;&bull;&bull;&bull;&bull;&bull;&bull;';
        }

        tr.find('td.hFinderPassword').html(password + "<var>" + obj.hFilePassword + "</var>");

        tr.find('td.hFinderPasswordExpires').html(
            parseInt(obj.hFilePasswordLifetime) > 0? "<span>" + obj.hFilePasswordLifetime + '</span> Hours' : 'Never'
        );

        var action = '';

        switch (obj.hFilePasswordExpirationAction)
        {
            case 1:
            {
                action = 'Delete this password';
                break;
            };
            case 2:
            {
                action = 'Delete the protected file';
                break;
            };
            default:
            {
                action = 'Do nothing';
            }
        }

        tr.find('td.hFinderPasswordExpirationAction').html(action + "<var>" + obj.hFilePasswordExpirationAction + "</var>");

        var required = '';

        if (!parseInt(obj.hFilePasswordRequired))
        {
            required = "Only if it's needed";
        }
        else
        {
            required = 'Every time';
        }

        tr.find('td.hFinderPasswordRequired').html(required + "<var>" + obj.hFilePasswordRequired + "</var>");

        $('form#hFinderPasswordsDialogue div.hApplicationUIListBody table tbody').append(tr);
    },

    edit : function()
    {
        $('form#hFinderPasswordDialogue').ToggleDialogue();

        var obj = hot.selected('hFinderPassword');
        
        var type = $('input#hFinderPasswordReveal:checked').length? 'text' : 'password';

        $('input#hFilePassword').outerHTML(
            "<input type='" + type + "' id='hFilePassword' size='25' maxlength='25' name='hFilePassword' class='hFormPasswordInput' />"
        );

        $('input#hFilePassword').val(
            obj.find('td.hFinderPassword var').text()
        );

        $('select#hFilePasswordLifetime').val(
            obj.find('td.hFinderPasswordExpires span').text()
        );

        $('select#hFilePasswordExpirationAction').val(
            obj.find('td.hFinderPasswordExpirationAction var').text()
        );

        $('select#hFilePasswordRequired').val(
            obj.find('td.hFinderPasswordRequired var').text()
        );
    }
};

$(document).ready(
    function()
    {
        finder.passwords.ready();
    }
);