<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Dialogue Library
#//\\ @@@@  @@@@\\\\\|
#//\\\@@@@| @@@@\\\\\|
#//\\\ @@ |\\@@\\\\\\| http://www.hframework.com
#//\\\\  ||   \\\\\\\| © Copyright 2015 Richard York, All rights Reserved
#//\\\\  \\_   \\\\\\|
#//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#//\\\\\  ----  \@@@@| http://www.hframework.com/license
#//@@@@@\       \@@@@|
#//@@@@@@\     \@@@@@|
#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
# @description
# <h1>Dialogue API</h1>
# <p>
#   Hot Toddy's <var>hDialogueLibrary</var> object provides an API for generating HTML
#   dialogues. These are floating windows that appear above the content of a browser
#   window.
# </p>
# @end

class hDialogueLibrary extends hPlugin {

    private $dialogue;
    private $dialogues;
    private $tabs = array();
    private $hForm;
    private $title;
    private $buttons;

    public function hConstructor()
    {
        # @return void

        # @description
        # <h2>Dialogues</h2>
        # <p>
        # Initializing an <var>hDialogueLibrary</var> object includes
        # <a href='/System/Framework/Hot Toddy/hDialogue/hDialogue.css' target='_blank' class='code'>hDialogue.css</a>,
        # <a href='/System/Framework/Hot Toddy/hDialogue/hDialogue.js' target='_blank' class='code'>hDialogue.js</a>.
        # If the browser is IE6,
        # <a href='/System/Framework/Hot Toddy/hDialogue/hDialogue.ie6.css' target='_blank' class='code'>hDialogue.ie6.css</a>.
        # If the browser is IE7,
        # <a href='/System/Framework/Hot Toddy/hDialogue/hDialogue.ie7.css' target='_blank' class='code'>hDialogue.ie7.css</a>.
        # Finally,
        # <a href='/System/Framework/Hot Toddy/hDHTML/hDrag/hDrag.js' target='_blank' class='code'>hDHTML/hDrag/hDrag.js</a>
        # is included, which provides a few reusable JS functions.
        # </p>
        # @end

        if ($this->userAgent->iOS)
        {
            $this->touchScroll();
        }

        $this->getPluginFiles();
        $this->getPluginCSS('ie6');
        $this->getPluginCSS('ie7');
        $this->getPluginJavaScript('hDHTML/hDrag');

        //$this->hForm = $this->library('hForm');
    }

    private function &reset()
    {
        # @return hDialogueLibrary

        # @description
        # <h2>Resetting a Dialogue</h2>
        # <p>
        #   When you're working with dialogues, the dialogue object is automatically
        #   reset after each call to <a href='#getDialogue' class='code'>getDialogue()</a>.
        #   Resetting the dialogue object will remove all stored values from internal
        #   properties, so that the dialogue object behaves the same way it did when it
        #   was created.
        # </p>
        # @end

        $this->dialogue = nil;
        $this->dialogues = nil;
        $this->tabs = array();

        if ($this->hForm && $this->checkFormObject('reset'))
        {
            $this->hForm->resetForm();
        }

        $this->hForm = nil;
        $this->title = nil;
        $this->buttons = array();

        $this->unsetVariables('hDialogue');

        return $this;
    }

    public function &setForm(hFormLibrary &$form)
    {
        # @return hDialogueLibrary

        # @description
        # <h2>Setting the Form Object</h2>
        # <p>
        #   The dialogue object can work with the
        #   <a href='/Hot Toddy/Documentation?hForm/hForm.library.php' class='code'>hFormLibrary</a>
        #   object, and
        #   automatically create dialogues containing forms, and dialogues containing
        #   tabbed navigation. This is done by providing the
        #   <a href='/Hot Toddy/Documentation?hForm/hForm.library.php' class='code'>hFormLibrary</a>
        #   object to the dialogue object.  This should be done any time you need the
        #   <a href='/Hot Toddy/Documentation?hForm/hForm.library.php' class='code'>hFormLibrary</a>
        #   and <var>hDialogueLibrary</var> objects to work together.
        # </p>
        # @end

        $this->hForm = $form;

        return $this;
    }

    public function &newDialogue($id)
    {
        # @return hDialogueLibrary

        # @description
        # <h2>Starting a New Dialogue</h2>
        # <p>
        #   When starting a new dialogue, the very first call should be to <var>newDialogue()</var>.
        #   You should provide the <var>$id</var> name you want to use, which will be appended with
        #   the word 'Dialogue'.  So, if you provide the <var>$id</var> as <var>Something</var>,
        #   <var>Something</var> becomes <var>SomethingDialogue</var>, and this is used in the <var>id</var>
        #   and <var>name</var> attributes of the <var>&lt;form&gt;</var> element used to contain the
        #   dialogue.
        # </p>
        # @end

        $this->dialogue = $id;

        return $this;
    }

    public function &addTabs(array $tabs = array())
    {
        # @argument $tabs array
        #

        # @return hDialogueLibrary

        # @description
        # <h2>Adding Multiple Dialogue Tabs</h2>
        # <p>
        #   Dialogue tabs are modeled after the Segemented Aqua Buttons UI in Mac OS X Human Interface
        #   Guidelines.  Dialogue tabs can be added one of two ways:
        # </p>
        # <ol>
        #   <li>By pulling <var>&lt;div&gt;</var> groupings from an <a href='/Hot Toddy/Documentation?hForm/hForm.library.php#addDiv' class='code'>hFormLibrary</a> object</li>
        #   <li>Manually, by providing a properly structured array.</li>
        # </ol>
        # <p>
        #   See the <a href='/Hot Toddy/Documentation?hForm/hForm.library.php#addDiv' class='code'>hFormLibrary</a>
        #   object's documentation for how to create <var>&lt;div&gt;</var>
        #   groupings that will represent dialogue tabs.
        # </p>
        # <p>
        #   To create an array and provide it directly to the dialogue object, follow this pattern:
        # </p>
        # <code>
        #   array(
        #       'TabId1' =&gt; 'Tab Label 1',
        #       'TabId2' =&gt; 'Tab Label 2',
        #       'TabId3' =&gt; 'Tab Label 3',
        #   )
        # </code>
        # <p>
        #   If no array is provided in the <var>$tabs</var> argument, <var>addTabs()</var> will attempt to
        #   pull <var>&lt;div&gt;</var> groupings from the
        #   <a href='/Hot Toddy/Documentation?hForm/hForm.library.php#addDiv' class='code'>hFormLibrary</a> object, if an
        #   <a href='/Hot Toddy/Documentation?hForm/hForm.library.php#addDiv' class='code'>hFormLibrary</a> object has been provided.
        # </p>
        # @end

        if (!count($tabs))
        {
            if ($this->checkFormObject('addTabs'))
            {
                // Get div id names and field set legends automatically from $hForm
                $form = $this->hForm->getLegends();

                if (is_array($form))
                {
                    foreach ($form as $legend)
                    {
                        $this->addTab($legend['id'], $legend['value']);
                    }
                }
            }
        }
        else if (is_array($tabs))
        {
            foreach ($tabs as $id => $label)
            {
                $this->addTab($id, $label);
            }
        }

        return $this;
    }

    public function &addTab($id, $label)
    {
        # @return hDialogueLibrary

        # @description
        # <h2>Adding a Single Dialogue Tab</h2>
        # <p>
        #   Adds a single dialogue tab, passing the <var>id</var> attribute in the <var>$id</var>
        #   argument and the tab's label in the <var>$label</var> argument.
        # </p>
        # @end

        $this->tabs['hDialogueTabLabel'][] = $this->translate($label);
        $this->tabs['hDialogueTabId'][] = $id;

        return $this;
    }

    public function &addButtons()
    {
        # @return hDialogueLibrary

        # @description
        # <h2>Adding Dialogue Buttons</h2>
        # <p>
        #   Dialogue buttons are the buttons that will appear in the lower, right-hand side
        #   of the dialogue window.  These buttons typically are to close the window, print
        #   something, or whatever other action you might want the user to take as a result
        #   of seeing the dialogue window.
        # </p>
        # <p>
        #   Buttons are provided by passing one or more labels in the arguments to this
        #   method. For example:
        # </p>
        # <code>
        # $this-&gt;hDialogue-&gt;addButtons('Save', 'Cancel');
        # </code>
        # <p>
        #   The preceding will automatically create two buttons, 'Save' and 'Cancel', each
        #   button will be labeled correctly, and will have <var>id</var> attributes added
        #   to each button, assuming a dialogue named 'hFile' the buttons would have
        #   <var>id</var> attributes <var>hFileDialogueSave</var> and <var>hFileDialogueCancel</var>.
        #   You will be responsible for creating and supplying the JavaScript code that makes
        #   each button's action work.
        # </p>
        # <p>
        #   In terms of the order that you add buttons, the 1st argument will be located furthest
        #   to the right, the last argument will be located furthest to the left.  Additionally,
        #   if the user hits the 'Return' or 'Enter' buttons on their keyboard, the default action
        #   will be the button closest to the input field where the user hit the 'return' or 'enter'
        #   buttons.  If there are no buttons other than dialogue buttons, then the first
        #   dialogue button provided will be the default action when 'Return' or 'Enter' are
        #   pressed.
        # </p>
        # @end

        $arguments = func_get_args();

        foreach ($arguments as $key => $value)
        {
            $this->buttons['hDialogueButtonLabel'][] = $this->translate($value);
            $this->buttons['hDialogueButtonId'][] = str_replace(' ', '', $value);
        }

        return $this;
    }

    private function checkFormObject($caller)
    {
        # @argument $caller string
        # <p>
        #   The name of the method calling <var>checkFormObject()</var>, this is
        #   used for informational purposes in the error message thrown if the
        #   <a href='/Hot Toddy/Documentation?hForm/hForm.library.php' class='code'>hFormLibrary</a>
        #   object is invalid.
        # </p>
        # @end

        # @return boolean
        # <p>
        #   Whether or not the form object is a valid
        #   <a href='/Hot Toddy/Documentation?hForm/hForm.library.php' class='code'>hFormLibrary</a> object.
        # </p>
        # @end

        # @description
        # <h2>Validating the Form Object</h2>
        # <p>
        #   Whenever the <a href='/Hot Toddy/Documentation?hForm/hForm.library.php' class='code'>hFormLibrary</a>
        #   object is needed and called for, a call to this method is performed to make
        #   sure that the item specified in the <var>hForm</var> member property is:
        # </p>
        # <ol>
        #   <li>An Object.</li>
        #   <li>An instance of the <a href='/Hot Toddy/Documentation?hForm/hForm.library.php' class='code'>hFormLibrary</a> object.</li>
        # </ol>
        # <p>
        #   If either of those conditions is not met, an error is thrown, and
        #   functionality associated with <a href='/Hot Toddy/Documentation?hForm/hForm.library.php' class='code'>hFormLibrary</a> fails to work.
        # </p>
        # @end

        if (is_object($this->hForm) && get_class($this->hForm) == 'hFormLibrary')
        {
            return true;
        }
        else
        {
            $this->warning("The form provided to the dialogue is not an 'hFormLibrary' object.  Called from '{$caller}()'", __FILE__, __LINE__);
            return false;
        }
    }

    public function &setDialogueTitle($title)
    {
        # @argument $title string
        # <p>
        #   The text to be used in the dialogue's titlebar.
        # </p>
        # @end

        # @return hDialogueLibrary

        # @description
        # <h2>Setting a Dialogue's Title Bar</h2>
        # <p>
        # A call to this method will set the content of the dialogue's title bar to
        # the value provided in <var>$title</var>.
        # </p>
        # @end

        $this->title = $title;

        return $this;
    }

    public function getDialogue($content = nil, $title = nil)
    {
        # @argument $content string
        # <p>
        #   The content to be used in the body of the dialogue window.
        # </p>
        # @end

        # @argument $title string
        # <p>
        #   The text to be used in the dialogue's titlebar.
        # </p>
        # @end

        # @return string
        # <p>
        #   The HTML of the dialogue ready to be inserted in the document.
        # </p>
        # @end

        # @description
        # <h2>Getting a Dialogue</h2>
        # <p>
        # A call to the <var>getDialogue()</var> method generates and returns HTML for a dialogue.
        # The dialogue's content can optionally be provided in the <var>$content</var> argument.
        # The dialogue's titlebar can optionally be set in the <var>$title</var> argument.
        # </p>
        # <h3>All Dialogues Are Forms</h3>
        # <p>
        # All dialogues created by the dialogue object are created as forms.
        # </p>
        # <h3>Configuring Dialogues</h3>
        # <p>
        # The following is a list of framework variables that can be set to configure a
        # dialogue:
        # </p>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>Variable</th>
        #           <th>Default Value</th>
        #           <th>Description</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td class='code'>hDialogueAction</td>
        #           <td class='code'>javascript:void(0);</td>
        #           <td>The URL the form should post to.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDialogueAppend</td>
        #           <td class='code'>nil</td>
        #           <td>A string added after the closing <var>&lt;/form&gt;</var> tag.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDialogueAutoTabs</td>
        #           <td class='code'>true</td>
        #           <td>
        #               If a <a href='/Hot Toddy/Documentation?hForm/hForm.library.php' class='code'>hFormLibrary</a> object is provided to the dialogue object, the
        #               dialogue object will automatically attempt to created tabs from the
        #               form's <var>&lt;div&gt;</var> groupings.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDialogueButtons</td>
        #           <td class='code'>nil</td>
        #           <td>If specified, this content can be used as custom dialogue buttons.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDialogueClass</td>
        #           <td class='code'>nil</td>
        #           <td>A class name added to the dialogue's <var>&lt;/form&gt;</var> element.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDialogueClose</td>
        #           <td class='code'>true</td>
        #           <td>Whether or not the close button should be enabled.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDialogueContentAppend</td>
        #           <td class='code'>nil</td>
        #           <td>
        #               If provided, the specified content will appear within the dialogue, after the
        #               dialogue's content.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDialogueContentPrepend</td>
        #           <td class='code'>nil</td>
        #           <td>
        #               If provided, the specified content will appear within the dialogue, before the
        #               dialogue's content.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDialogueDisableFocus</td>
        #           <td class='code'>false</td>
        #           <td>
        #               Whether or not the dialogue should be focus-able.  When focus is enabled,
        #               the dialogue's <var>z-index</var> is modified to place the dialogue on top of
        #               everything else when the dialogue has focus.  Additionally, the dialogue is
        #               made inactive or active by adding or removing the <var>hDialogueActive</var> and
        #               <var>hDialogueInactive</var> class names.  When focus is disabled, <var>z-index</var>
        #               is never modified, along with the concept of active and inactive.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDialogueEnableTabs</td>
        #           <td class='code'>true</td>
        #           <td>
        #               Whether or not tabs should be enabled.
        #           </td>
        #       </tr>

        #       <tr>
        #           <td class='code'>hDialogueEnctype</td>
        #           <td class='code'>false</td>
        #           <td>
        #               Whether or not the <var>enctype</var> attribute should be added to the
        #               dialogue's form element.  If added, <var>enctype</var> has a value of
        #               <var>multipart/form-data</var>, for file uploads.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDialogueFullScreen</td>
        #           <td class='code'>false</td>
        #           <td>
        #               Whether or not the dialogue should be created as a popup dialogue that is
        #               layered over content, or if it is intended to be full screen.  Full screen
        #               removes drop shadows, titlebar, and titlebar buttons.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDialogueMinimize</td>
        #           <td class='code'>false</td>
        #           <td>Whether or not the minimize button should be enabled.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDialoguePrepend</td>
        #           <td class='code'>nil</td>
        #           <td>Content that's added before the dialogue's opening <var>&lt;form&gt;</var> tag.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDialogueShadow</td>
        #           <td class='code'>true</td>
        #           <td>If <var>false</var>, the drop shadow is removed from the dialogue.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDialogueTarget</td>
        #           <td class='code'>nil</td>
        #           <td>
        #               The value of the <var>target</var> attribute of the dialogue's
        #               <var>&lt;/form&gt;</var> element.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDialogueTargetFrame</td>
        #           <td class='code'>nil</td>
        #           <td>
        #               Whether or not an invisible <var>&lt;iframe&gt;</var> should be
        #               added to the dialogue, if <var>true</var>, the value of <var>hDialogueTarget</var>
        #               sets the iframe's <var>id</var> and <var>name</var> attributes.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDialogueTitlebar</td>
        #           <td class='code'>true</td>
        #           <td>Whether or not the dialogue should have a titlebar.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDialogueTitlebarId</td>
        #           <td class='code'>nil</td>
        #           <td>
        #               If specified, the value is made the <var>id</var> attribute value of
        #               the dialogue's titlebar, which is an <var>&lt;h4&gt;</var> element.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDialogueZoom</td>
        #           <td class='code'>false</td>
        #           <td>Whether or not the zoom button should be enabled.</td>
        #       </tr>
        #   </tbody>
        # </table>
        # <p class='hDocumentationWarning'>
        # <b>Warning:</b> Any framework variable with the prefix <var>hDialogue</var> will be unset after every
        # call to <var>getDialogue()</var>.
        # </p>
        # @end


        # Occaisonally a plugin resets the hFileCSS / hFileJavaScript strings
        # to avoid content inserted by a private plugin.  Placing these calls
        # here rather than in hConstructor() ensurse that CSS / JavaScript gets
        # added after a reset.
        $html = '';

        if (!empty($this->dialogue))
        {
            if ($this->hDialogueFullScreen(false))
            {
                $this->hTemplatePath = '/hDialogue/hDialogue.template.php';
            }

            if (!$this->hDialogueTitlebarId)
            {
                $this->hDialogueTitlebarId = $this->dialogue.'DialogueTitleBar';
            }

            if (!$this->hDialogueContentId)
            {
                $this->hDialogueContentId = $this->dialogue.'DialogueContent';
            }

            if (empty($content))
            {
                if ($this->hForm)
                {
                    if ($this->checkFormObject('getDialogue'))
                    {
                        $this->hForm->hFormIdentifier = $this->dialogue.'DialogueForm';
                        $this->hForm->hFormElement = false;
                        $content = $this->hForm->getForm();
                    }
                }
                else
                {
                    $content = $this->hFileDocument;
                }
            }

            if ($this->hForm && $this->hDialogueAutoTabs(true))
            {
                if ($this->checkFormObject('getDialogue'))
                {
                    $this->addTabs();
                }
            }

            if ($this->userAgent->isTridentLT9)
            {
                if (count($this->tabs))
                {
                    $tabCounter = 0;
                    $tabCount = count($this->tabs);

                    foreach ($this->tabs as $tab)
                    {
                        $this->tabs['hDialogueTabFirst'][] = (!$tabCounter);
                        $this->tabs['hDialogueTabLast'][]  = ($tabCounter == ($tabCount - 1));
                        $tabCounter++;
                    }
                }
            }

            if (!$this->hDialogueAction)
            {
                $this->hDialogueAction = 'javascript:void(0);';
            }

            $html = $this->getTemplate(
                'Dialogue',
                array(
                    'dialogueName' => $this->dialogue,
                    'title' => $this->translate($title),
                    'tabs' => $this->tabs,
                    'content' => $content,
                    'buttons' => $this->buttons
                )
            );

            $this->reset();

            return $html;
        }
        else
        {
            $this->warning('You did not set a dialogue Id with newDialogue().', __FILE__, __LINE__);
        }
    }
}

?>