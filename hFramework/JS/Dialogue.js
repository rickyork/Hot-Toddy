if (typeof(dialogue) == 'undefined')
{
    var dialogue = {};
}

/*
* This script provides custom replacements for browser modal dialogues,
* such as, confirm, alert, and prompt.  In addition to a login dialogue.
*
* With regards to confirm, alert, and prompt, this will allow both the
* look and feel of the dialogues and the button labels to be customized
* for better usability.  Unfortunately, these will not imitate the modal
* nature of these dialogues entirely.  They will not interupt the user
* while using a different browser window, for example.  But they will
* prevent the user from doing anything until they have responded to the
* dialogue.  Additionally, since JavaScript provides no wait/sleep
* to pause execution of script until the user provides a response, instead
* of being able to immediately receive a response such as
* value = window.confirm(), instead a callback function is provided that
* executes upon receiving an answer from the user.
*
* Options allowed for alert, confirm, and prompt dialogues:
*
* options = {
*   title :         // Optional dialogue window title
*   label :         // Text to present to the user
*   ok :            // Optional text label to use for "ok" button, button with true result.  Default is OK.
*   cancel :        // Optional text label to use for "cancel" button, button with false result.  Default is Cancel.
*   width :         // Optional dialogue width in pixels (unit of measurement not allowed).  If not set, default is 400.
*   height :        // Optional dialogue height in pixels (unit of measurement not allowed).  If not set, auto height is used.
*   beforeOpen :    // Callback function executed before a dialogue opens.  Use this to add or customize buttons or other things.
* }
*
*/
var modalDialogue = hot.factory();

modalDialogue.prototype.init = function(dialogue, options, fn, context, dialogueCounter)
{
    this.onClickModalDialogueButton = function(event, input)
    {
        event.preventDefault();

        var validation = true;

        if (this.fn)
        {
            var data = null;

            switch (this.dialogueName)
            {
                case 'Confirm':
                {
                    data = (input.attr('id').indexOf('OK') != -1);
                    break;
                };
                case 'Prompt':
                {
                    data = $('input#hFrameworkPromptResponse').val();

                    if (input.attr('id').indexOf('OK') != -1 && (!data || !data.length))
                    {
                        validation = false;
                    }

                    break;
                };
            }

            if (validation)
            {
                this.fn.call(this.context? this.context : this, data);
            }
            else if (this.dialogueName == 'Prompt')
            {
                this.fn.call(
                    this.context ? this.context : this,
                    input.attr('id').indexOf('Cancel') != -1 ?  false : null
                );
            }
        }

        if (validation)
        {
            if ($('input#hFrameworkPromptResponse').length)
            {
                $('input#hFrameworkPromptResponse').val('');
            }

            $('form#hFramework' + this.dialogueName + 'Dialogue').closeDialogue(true);
        }
    };

    // When login is successful, the callback function is called and the argument passed to it is 'true',
    // meaning login happened successfully.  When login is attempted and fails, the callback function is
    // still called, but the argument is false and the failure or success code is supplied in the 2nd argument.
    //
    // Failure/Success code is zero or a negative number is login failed, it is '1' if login is successful,
    // and it is not set at all if login is canceled ('undefined').
    this.onClickLogin = function(event)
    {
        http.post(
            '/hUser/hUserLogin/login', {
                hUserName : $('input#hFrameworkLoginUserName').val(),
                hUserPassword : $('input#hFrameworkLoginPassword').val(),
                hUserLoginCookie : $('input#hFrameworkLoginCookie:checked').length ? 1 : 0
            },
            function(json)
            {
                var code = parseInt(json);

                if (isNaN(code))
                {
                    code = 1;
                }

                if (typeof this.fn === 'function')
                {
                    this.fn.call(this.context? this.context : this, (code > 0), (code <= 0? code : 1));
                }

                switch (code)
                {
                    case -26:
                    {
                        $('div#hFrameworkLoginMessage').html(
                            "<p>" +
                                "<b>Login Failed:</b> The user name or email address " +
                                "that you provided does not exist." +
                            "</p>"
                        );

                        break;
                    };
                    case -27:
                    {
                        $('div#hFrameworkLoginMessage').html(
                            "<p>" +
                                "<b>Login Failed:</b> The password you provided was incorrect." +
                            "</p>"
                        );

                        break;
                    };
                    case -28:
                    {
                        $('div#hFrameworkLoginMessage').html(
                            "<p>" +
                                "<b>Login Failed:</b> Your account has not been activated. Please " +
                                "activate your account to complete the registration process." +
                            "</p>"
                        );

                        break;
                    };
                    case -29:
                    {
                        $('div#hFrameworkLoginMessage').html(
                            "<p>" +
                                "<b>Login Failed:</b> Your account has been disabled by an " +
                                "administrator." +
                            "</p>"
                        );

                        break;
                    };
                    case -30:
                    {
                        $('div#hFrameworkLoginMessage').html(
                            "<p>" +
                                "<b>Login Failed:</b> There have been too many failed login " +
                                "attempts for this account." +
                            "</p>" +
                            "<p>" +
                                "For security reasons, you are only allowed 3 failed login " +
                                "attempts within a 10 minute time frame." +
                            "</p>" +
                            "<p>" +
                                "Please try again in 10 minutes." +
                            "</p>"
                        );

                        break;
                    };
                    case 1:
                    {
                        $('input#hUserName').val('');
                        $('input#hUserPassword').val('');

                        $('form#hFrameworkLoginDialogue')
                            .closeDialogue(true);

                        // The existence of this form could cause conflicts with other things,
                        // so, it's removed from the document after it has faded out.
                        // If it is needed again, it can again be dynamically loaded.
                        setTimeout(
                            function()
                            {
                                $('form#hFrameworkLoginDialogue').remove();
                            },
                            3000
                        );

                        if (this.loginRequest)
                        {
                            var request = this.loginRequest;

                            if (request.method && request.url)
                            {
                                // Resend the last request.
                                http.request(
                                    request.method,
                                    request.url,
                                    request.post,
                                    request.fn,
                                    request.context,
                                    request.synchronous,
                                    request.options
                                );
                            }

                            this.loginRequest = {};
                        }

                        break;
                    };
                }

                if (code <= 0)
                {
                    $('div#hFrameworkLoginMessage').show();
                }

                var height =
                    $('form#hFrameworkLoginDialogue')
                        .find('div.hFormDivision')
                            .height() + 80;

                $('form#hFrameworkLoginDialogue').css({
                    height : height + 'px',
                    marginTop : -(height / 2) + 'px'
                });
            },
            dialogue
        );
    };

    // If login is canceled, the callback function is called, the argument is false and
    // no failure/success code is supplied at all (it is 'undefined').
    this.onCancelLogin = function()
    {
        if (typeof this.fn === 'function')
        {
            this.fn.call(this.context ? this.context : this, false);
        }

        $('input#hUserName').val('');
        $('input#hUserPassword').val('');
        $('form#hFrameworkLoginDialogue').closeDialogue(true);
    };

    this.openModalDialogue = function()
    {
        if (!this.options)
        {
            this.options = {};
        }

        if (!$('form#hFramework' + this.dialogueName + 'Dialogue').length)
        {
            var html = http.get({
                url : '/hDialogue/get' + this.dialogueName + 'Dialogue',
                synchronous : true
            });

            $('body').append(html);
        }

        var dialogueForm = null;
        var context = this;

        switch (this.dialogueName)
        {
            case 'Modal':
            {
                if ($('form#hFrameworkModalDialogue').length)
                {
                    dialogueForm = $('form#hFrameworkModalDialogue');
                    //this.setModalDialogue(true, true);
                }
                else
                {
                    hot.console.warning('form#hFrameworkModalDialogue does not exist.');
                }

                break;
            }
            case 'Alert':
            {
                if ($('form#hFrameworkAlertDialogue').length)
                {
                    dialogueForm = $('form#hFrameworkAlertDialogue');
                    this.setModalDialogue(true, false);
                }
                else
                {
                    hot.console.warning('form#hFrameworkAlertDialogue does not exist.');
                }

                break;
            };
            case 'Confirm':
            {
                if ($('form#hFrameworkConfirmDialogue').length)
                {
                    dialogueForm = $('form#hFrameworkConfirmDialogue');
                    this.setModalDialogue(true, true);
                }
                else
                {
                    hot.console.warning('form#hFrameworkConfirmDialogue does not exist.');
                }

                break;
            };
            case 'Login':
            {
                if ($('form#hFrameworkLoginDialogue').length)
                {
                    dialogueForm = $('form#hFrameworkLoginDialogue');

                    $('input#hFrameworkLoginDialogueLogin')
                        .off('click.modalDialogueLoginButton')
                        .on(
                            'click.modalDialogueLoginButton',
                            function(event)
                            {
                                event.preventDefault();
                                context.onClickLogin(event);
                            }
                        );

                    $('input#hFrameworkLoginDialogueCancel')
                        .off('click.modalDialogueLoginButton')
                        .on(
                            'click.modalDialogueLoginButton',
                            function(event)
                            {
                                event.preventDefault();
                                context.onCancelLogin();
                            }
                        );
                }
                else
                {
                    hot.console.warning('form#hFrameworkLoginDialogue does not exist.');
                }

                break;
            };
            case 'Prompt':
            {
                if ($('form#hFrameworkPromptDialogue').length)
                {
                    dialogueForm = $('form#hFrameworkPromptDialogue');
                    this.setModalDialogue(true, true);
                }
                else
                {
                    hot.console.warning('form#hFrameworkPromptDialogue does not exist.');
                }

                break;
            };
        }

        if (dialogueForm && dialogueForm.length)
        {
            // This hook can be used to further customize the dialogue,
            // add or remove buttons, etc.
            if (this.options.beforeOpen)
            {
                this.options.beforeOpen.call(dialogueForm, this.options);
            }

            if (this.options.title)
            {
                dialogueForm.setTitlebar(this.options.title);
            }

            if (this.options.width)
            {
                this.options.width = parseInt(this.options.width);

                if (!isNaN(this.options.width))
                {
                    dialogueForm.css({
                        width : this.options.width + 'px',
                        marginLeft : (-(this.options.width / 2)) + 'px'
                    });
                }
            }

            dialogueForm.openDialogue(
                {
                    isModal : true,
                    animated : true
                },
                function()
                {
                    $(this).find('div.hDialogueButtons input:first').focus();
                }
            );

            if (!this.options.height)
            {
                // Can't read the height until the element is visible.  Since I'm fading in
                // using openDialogue(), as soon as that is called I can get a value for
                // height.  Then, the division height is only part of it, the added value
                // to that compensates for the dialogue buttons and extra space between the
                // button(s) and the bottom of the dialogue content.
                this.options.height = dialogueForm.find('div.hFormDivision').height() + 80;
            }

            dialogueForm
                .find('div.hFormDivision:first')
                .css('display', 'block');

            this.options.height = parseInt(this.options.height);

            if (!isNaN(this.options.height))
            {
                dialogueForm.css({
                    height : this.options.height + 'px',
                    marginTop : (-(this.options.height / 2)) + 'px'
                });
            }

            //if (this.dialogueName == 'Modal')
            //{
            //    if (this.context !== undefined && this.context !== null && typeof this.context == 'object')
            //    {
            //        this.fn.call(this.context, this.options);
            //    }
            //    else
            //    {
            //        this.fn(this.options);
            //    }
            //
            //    return;
            //}
        }
        else
        {
            hot.console.warning(this.dialogueName + ' dialogue could not be loaded, because it does not exist.');
        }
    };

    this.setModalDialogue = function(hasOkButton, hasCancelButton)
    {
        if (this.options.label)
        {
            $('div#hFramework' + this.dialogueName + 'Text')
                .html(this.options.label)
                .parents('div.hFormDivision:first')
                .css('display', 'block');
        }

        var context = this;

        if (hasOkButton)
        {
            $('input#hFramework' + this.dialogueName + 'DialogueOK')
                .val(this.options.ok? this.options.ok : 'OK')
                .off('click.modalDialogueOKButton')
                .on(
                    'click.modalDialogueOKButton',
                    function(event)
                    {
                        context.onClickModalDialogueButton(event, $(this));
                    }
                );
        }

        if (hasCancelButton)
        {
            $('input#hFramework' + this.dialogueName + 'DialogueCancel')
                .val(this.options.cancel? this.options.cancel : 'Cancel')
                .off('click.modalDialogueCancelButton')
                .on(
                    'click.modalDialogueCancelButton',
                    function(event)
                    {
                        context.onClickModalDialogueButton(event, $(this));
                    }
                );
        }
    };

    this.resourcesLoaded = false;

    this.dialogueName = dialogue;
    this.options = options;
    this.fn = fn;
    this.context = context;

    if (dialogue == 'Login')
    {
        this.loginRequest = options;
    }

    // First load will have a wee, tiny delay to it, subsequent
    // dialogues will open instantly though.
    if (!this.resourcesLoaded && typeof $.fn.openDialogue === 'undefined')
    {
        include.js('/hDialogue/hDialogue.js');
        include.js('/hDHTML/hDrag/hDrag.js');
        include.css('/hDialogue/hDialogue.css', this.openModalDialogue, this);

        if (hot.userAgent == 'ie' && (hot.userAgentVersion == 6 || hot.userAgentVersion == 7))
        {
            include.css('/hDialogue/hDialogue.ie' + ((hot.userAgentVersion == 6)? 6 : 7) + '.css');
        }

        this.resourcesLoaded = true;
    }
    else
    {
        this.openModalDialogue();
    }
};

$.extend(

    dialogue, {

        dialogueCounter : 0,

        objects : {},

        modal : function(options)
        {
            this.dialogueCounter++;
            
            var callback = hot.getCallbackAndContext(arguments, options);

            this.objects['Modal' + this.dialogueCounter] = new modalDialogue(
                'Modal',
                options,
                callback.fn,
                callback.context,
                this.dialogueCounter
            );

            return this;
        },

        alert : function(options)
        {
            this.dialogueCounter++;
            
            var callback = hot.getCallbackAndContext(arguments, options);

            this.objects['Alert' + this.dialogueCounter] = new modalDialogue(
                'Alert',
                options,
                callback.fn,
                callback.context,
                this.dialogueCounter
            );

            return this;
        },

        confirm : function(options)
        {
            this.dialogueCounter++;
            
            var callback = hot.getCallbackAndContext(arguments, options);

            this.objects['Confirm' + this.dialogueCounter] = new modalDialogue(
                'Confirm',
                options,
                callback.fn,
                callback.context,
                this.dialogueCounter
            );

            return this;
        },

        login : function()
        {
            this.dialogueCounter++;
            
            var options = arguments[0] ? arguments[0] : {};
            
            var callback = hot.getCallbackAndContext(arguments, options);

            this.objects['Login' + this.dialogueCounter] = new modalDialogue(
                'Login',
                options,
                callback.fn,
                callback.context,
                this.dialogueCounter
            );

            return this;
        },

        prompt : function(options)
        {
            this.dialogueCounter++;
            
            var callback = hot.getCallbackAndContext(arguments, options);

            this.objects['Prompt' + this.dialogueCounter] = new modalDialogue(
                'Prompt',
                options,
                callback.fn,
                callback.context,
                this.dialogueCounter
            );

            return this;
        }
    }
);