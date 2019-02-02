<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework
#//\\ @@@@  @@@@\\\\\|
#//\\\@@@@| @@@@\\\\\|
#//\\\ @@ |\\@@\\\\\\| https://github.com/rickyork/Hot-Toddy
#//\\\\  ||   \\\\\\\| © Copyright 2019 Richard York, All rights Reserved
#//\\\\  \\_   \\\\\\|
#//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#//\\\\\  ----  \@@@@| https://github.com/rickyork/Hot-Toddy/blob/master/License
#//@@@@@\       \@@@@|
#//@@@@@@\     \@@@@@|
#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

class hDialogueService extends hService {

    private $hDialogue;
    private $hForm;

    public function hConstructor()
    {
        # @return void

        # @description
        # <h2>Dialogue Listener Constructor</h2>
        # <p>
        # Initiates a <var>hFormLibrary</var> object and a <var>hDialogueLibrary</var> object,
        # and passes the form to the dialogue object.
        # </p>
        # @end
        $this->hForm = $this->library('hForm');
        $this->hDialogue = $this->library('hDialogue');

        $this->hDialogue->setForm($this->hForm);
    }

    public function getModalDialogue()
    {
        # @return HTML

        # @description
        # <h2>Getting a Modal Dialogue</h2>
        # <p>
        #
        # </p>
        # @end

        $this->hDialogueClose = true;
        $this->hDialogueModal = true;

        $this->hDialogue->newDialogue('hFrameworkModal');

        $this->hDialogueAction = '#';

        $this->hForm->addDiv('hFrameworkModalDialogueDiv');
        $this->hForm->addFieldset('', '100%', '100%');

        $this->hForm->addTableCell('');

        $this->hDialogue->addButtons('OK', 'Cancel');

        $this->HTML(
            $this->hDialogue->getDialogue(null, '')
        );

        $this->hDialogue->reset();
    }

    public function getAlertDialogue()
    {
        # @return HTML

        # @description
        # <h2>Getting an Alert Dialogue</h2>
        # <p>
        # This function returns the HTML template for Hot Toddy's replacement <var>alert()</var>
        # dialogue.
        # </p>
        # <p>
        # Hot Toddy provides a replacement for a browser's built-in JavaScript <var>alert()</var>
        # dialogue.  Hot Toddy's dialogue is modeled after a Mac OS X application window,
        # can have customized title bar text, customized button text (instead of being stuck with
        # a button saying 'OK'), and can have HTML used in the body of the dialogue's content.
        # </p>
        # <p>
        # Hot Toddy's replacement <var>alert()</var> dialogue can be called using the following JavaScript:
        # </p>
        # <code>dialogue.alert({
        #       title  : "Titlebar Text",
        #       ok     : "What you want the button to say",
        #       label  : "&lt;p&gt;The body of the dialogue's &lt;b&gt;message&lt;/b&gt;.&lt;/p&gt;",
        #       width  : <i>width</i>, <i># Optional</i>
        #       height : <i>height</i> <i># Optional</i>
        #    },
        #    function()
        #    {
        #       <i># A callback function that's executed after the user presses the
        #       # alert dialogue's 'OK' button.</i>
        #    },
        #    <i>optional</i> callbackFunctionContext
        # );
        # </code>
        # @end

        $this->hDialogueClose = false;
        $this->hDialogueModal = true;

        $this->hDialogue->newDialogue('hFrameworkAlert');

        $this->hDialogueAction = '#';

        $this->hForm->addDiv('hFrameworkAlertDiv');
        $this->hForm->addFieldset('Alert', '100%', '100%');

        $this->hForm->addTableCell($this->getTemplate('Alert'));

        $this->hDialogue->addButtons('OK');

        $this->HTML(
            $this->hDialogue->getDialogue(null, 'Alert')
        );
    }

    public function getConfirmDialogue()
    {
        # @return HTML

        # @description
        # <h2>Getting a Confirm Dialogue</h2>
        # <p>
        # This function returns the HTML template for Hot Toddy's replacement <var>confirm()</var>
        # dialogue.
        # </p>
        # <p>
        # Hot Toddy provides a replacement for a browser's built-in JavaScript <var>confirm()</var>
        # dialogue.  Hot Toddy's dialogue is modeled after a Mac OS X application window,
        # can have customized title bar text, customized button text (instead of being stuck with
        # confirm buttons saying 'OK' and 'Cancel'), and can have HTML used in the body of the
        # dialogue's content.
        # </p>
        # <p>
        # Hot Toddy's replacement <var>confirm()</var> dialogue can be called using the following JavaScript:
        # </p>
        # <code>dialogue.confirm({
        #       title  : "Titlebar Text", <i># Optional</i>
        #       ok     : "What you want the OK button to say", <i># Optional</i>
        #       cancel : "What you want the Cancel button to say", <i># Optional</i>
        #       label  : "&lt;p&gt;The body of the dialogue's &lt;b&gt;message&lt;/b&gt;.&lt;/p&gt;",
        #       width  : <i>width</i>, <i># Optional</i>
        #       height : <i>height</i> <i># Optional</i>
        #    },
        #    function(confirm)
        #    {
        #       <i># A callback function that's executed after the user presses the
        #       # confirm dialogue's 'OK' or 'Cancel' buttons.
        #       # The confirm argument is true if the user pressed the 'OK' button,
        #       # and false if the user pressed the 'Cancel' button.</i>
        #    },
        #    <i>optional</i> callbackFunctionContext
        # );
        # </code>
        # @end

        $this->hDialogueClose = false;
        $this->hDialogueModal = true;

        $this->hDialogue->newDialogue('hFrameworkConfirm');

        $this->hDialogueAction = '#';

        $this->hForm->addDiv('hFrameworkConfirmDiv');
        $this->hForm->addFieldset('Confirm', '100%', '100%');

        $this->hForm->addTableCell($this->getTemplate('Confirm'));

        $this->hDialogue->addButtons('OK', 'Cancel');

        $this->HTML(
            $this->hDialogue->getDialogue(null, 'Confirmation')
        );
    }

    public function getLoginDialogue()
    {
        # @return HTML

        # @description
        # <h2>Getting a Login Dialogue</h2>
        # <p>
        # This function returns the HTML template for Hot Toddy's <var>login()</var>
        # dialogue, which can be used in place of a login form.
        # </p>
        # <p>
        # Hot Toddy's <var>login()</var> dialogue can be called using the following JavaScript:
        # </p>
        # <code>dialogue.login({
        #       title  : "Titlebar Text", <i># Optional</i>
        #       width  : <i>width</i>, <i># Optional</i>
        #       height : <i>height</i> <i># Optional</i>
        #    },
        #    function(responseCode)
        #    {
        #       <i># A callback function that's executed after the user attempts to
        #       # login, and is executed whether a login is successful or fails.
        #       # The responseCode argument contains a response code that identifies
        #       # how the login failed, if it failed, and will contain a number less than or
        #       # equal to zero if that is the case.  If the response code is one, login
        #       # was successful.</i>
        #    },
        #    <i>optional</i> callbackFunctionContext
        # );
        # </code>
        # @end

        $this->hDialogueClose = false;
        $this->hDialogueModal = true;

        $this->hDialogue->newDialogue('hFrameworkLogin');

        $this->hDialogueAction = '#';

        $this->hForm->addDiv('hFrameworkLoginDiv');
        $this->hForm->addFieldset('Login', '100%', '185px,');

        $this->hForm->setAttributes(
            array(
                'autocapitalize' => 'off',
                'autocorrect' => 'off',
                'autofocus' => 'autofocus'
            )
        );

        $this->hForm->addTextInput('hFrameworkLoginUserName', $this->hUserLoginUserNameLabel('User Name or Email Address:'), 45);
        $this->hForm->addPasswordInput('hFrameworkLoginPassword', 'Password:', 45);

        $this->hForm->addTableCell('');

        $this->hForm->addCheckboxInput('hFrameworkLoginCookie', 'Remember this login on this computer?');

        $this->hForm->addTableCell($this->getTemplate('Login'), 2);

        $this->hDialogue->addButtons('Login', 'Cancel');

        $this->HTML(
            $this->hDialogue->getDialogue(null, 'Login')
        );
    }

    public function getPromptDialogue()
    {
        # @return HTML

        # @description
        # <h2>Getting a Prompt Dialogue</h2>
        # <p>
        # This function returns the HTML template for Hot Toddy's replacement <var>prompt()</var>
        # dialogue.
        # </p>
        # <p>
        # Hot Toddy provides a replacement for a browser's built-in JavaScript <var>prompt()</var>
        # dialogue.  Hot Toddy's dialogue is modeled after a Mac OS X application window,
        # can have customized title bar text, customized button text (instead of being stuck with
        # prompt buttons saying 'OK' and 'Cancel'), and can have HTML used in the body of the
        # dialogue's content.
        # </p>
        # <p>
        # Hot Toddy's replacement <var>prompt()</var> dialogue can be called using the following JavaScript:
        # </p>
        # <code>dialogue.prompt({
        #       title  : "Titlebar Text", <i># Optional</i>
        #       ok     : "What you want the OK button to say", <i># Optional</i>
        #       cancel : "What you want the Cancel button to say", <i># Optional</i>
        #       label  : "&lt;p&gt;The body of the dialogue's &lt;b&gt;message&lt;/b&gt;.&lt;/p&gt;",
        #       width  : <i>width</i>, <i># Optional</i>
        #       height : <i>height</i> <i># Optional</i>
        #    },
        #    function(message)
        #    {
        #       <i># A callback function that's executed after the user presses the
        #       # prompt dialogue's 'OK' or 'Cancel' buttons.
        #       # The message argument is a string if the user pressed the 'OK' button,
        #       # and false if the user pressed the 'Cancel' button.</i>
        #    },
        #    <i>optional</i> callbackFunctionContext
        # );
        # </code>
        # @end

        $this->hDialogueClose = false;
        $this->hDialogueModal = true;

        $this->hDialogue->newDialogue('hFrameworkPrompt');

        $this->hDialogueAction = '#';

        $this->hForm->addDiv('hFrameworkPromptDiv');
        $this->hForm->addFieldset('Prompt', '100%', '100%');

        $this->hForm->addTableCell($this->getTemplate('Prompt'));

        $this->hForm->addTextInput('hFrameworkPromptResponse', null, 25);

        $this->hDialogue->addButtons('OK', 'Cancel');

        $this->HTML(
            $this->hDialogue->getDialogue(null, 'Prompt')
        );
    }
}

?>