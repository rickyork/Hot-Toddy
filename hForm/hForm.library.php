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
# @description
# <h1>Generating and Validating HTML Forms</h1>
# <p>
#   The <var>hFormLibrary</var> object provides an API that makes it easy to generate
#   and validate HTML forms.
# </p>
# @end

class hFormLibrary extends hFrameworkApplication {

    const hFormAttributeId      = '/^([A-Z]|[a-z]|\_|\-){1}(\w|\-){1,}$/';
    const hFormAttributeInteger = '/^\-?\d{1,}$/';
    const hFormAttributeQuotes  = '/^((?!\')(?!\")(.)){1,}$/';

    const version = 1;

    # These are used to build a form
    private $index     = 0;                 # Track the field count.
    private $fields    = array();
    private $compiled  = array();
    private $table     = array();
    private $hForm;
    private $hFormSnapShot;

    # The following variables are set via options either for the entire form,
    # or for a specific field within a form.
    private $variables = array(
        'hFormElement',                     # Whether or not to include the opening and closing <form> tags. Defaults to true.
        'hFormOpeningTag',                  # Whether or not to include the opening form tag.  Defaults to true.
        'hFormPrependCallback',             # A function that is executed just after the opening form tag, results are prepended to the form. Default is none.
        'hFormPrependCallbackArguments',    # Arguments to pass to the prepend function.  Default is none.
        'hFormAppendCallback',              # A function that is executed just before the closing form tag, results are appended to the form.  Default is none.
        'hFormAppendCallbackArguments',     # Arguments to pass to the append function.  Default is none.
        'hFormAutoValues',                  # Whether or not to attempt to automatically fill in form values from superglobal arrays.  Default is true.
        'hFormDisplayOnForm',               # Whether or not to display the field this variable is attached to during the form rendering stage.  Default is true.
        'hFormDisplayOnVerify',             # Whether or not to display the field this variable is attached to during the verify rendering stage.  Default is true.
        'hFormIdentifier',                  # A hidden field that's included that's used to identify the form has been posted.  Default is none.
        'hFormRender',                      # Contains what stage the form renderer is set to one of form or verify.  Default is 'form'.
        'hFormRequiredIndicator',           # Whether or not to include the "this field is required" indicator.  Default is true.
        'hFormSessionId',                   # Whether or not to include the session id in a hidden field.  Default is false.
        'hFormClosingTag',                  # Whether or not to include the closing form tag.  Default is true.
        'hFormHasErrors',                   # Whether or not the form contains validation errors.  Default is false.
        'hFormFieldHasErrors',              # Whether or not the current field has validation errors.
        'hFormErrorText',                   # What error text to display if errors are present.  (See code for default)
        'hFormAutoLoadHeaders',             # Whether or not to automatically get headers for dynamic fields (such as a WYSIWYG) at the close of form compilation. Default is true.
        'hFormVerifyPasswordMessage',       # What text to display instead of a password field's value at the verify stage.
        'hFormUseLabelAsValue',             # Whether or not to use a checkbox or radio input's label as its value.  Default is false.
        'hFormBlockWrapper',                # Whether or not to wrap a checkbox or radio input with a <div> element.  Default is true.
        'hFormOptionLabelIsValue',          # Whether or not to use option labels as option values for select elements.  Default is false.
        'hFormPrepend',                     # A string to prepend before an input field.  Default is none.
        'hFormAppend',                      # A string to append after an input field.  Default is none.
        'hFormNL2BR',                       # Whether or not to apply the nl2br function to the value.  Default is false.
        'hFormValidate',                    # Whether or not to attempt validation on the field.  Default is false.
        'hFormPrependInput',                # HTML/Text to prepend to an input
        'hFormAppendInput',                 # HTML/Text to append to an input
        'hFormLabel',                       # Checkbox label
        'hFormStates',                      # Whether or not the <select> field is a state selection
        'hFormCheckboxReverseLabel',        #
        'hFormJoinInputToNext',             # For when you want to place multiple input elements in the same table cell
        'hFormJoinInputToLast',             # For when you want to place multiple input elements in the same table cell
        'hFormRenderOverride'
    );

    private $form = array(
        'attributes'        => array(
#            'enctype'    => '',            # multipart/form-data|application/x-www-form-urlencoded|text/plain (default to application/x-www-form-urlencoded)
#            'name'        => '',           # Name attribute of form
            'method'    => 'post'           # get|post
#            'target'    => '',             #
#            'id'        => '',             # id attribute of form
#            'class'        => '',          # class attribute of form
#            'style'        => '',          # style attribute of form
#            'action'    => ''              # Form action.  If null, default is hFilePath (self).
        )
    );

    private $formLegends = array();

    public $types = array(
        'heading'   => array('class' => 'hFormHeading'),
        'label -L'  => array('class' => 'hFormLabelLong'),
        'label'     => array('class' => 'hFormLabel'),
        'help'      => array('class' => 'hFormHelp'),
        'html'      => array('class' => 'hFormHTML'),
        'bbcode'    => array('class' => 'hFormBBCodeInput'),
        'plugin'    => array('class' => 'hFormPlugin'),
        'image'     => array('class' => 'hFormButton'),
        'text'      => array('class' => 'hFormInput'),
        'file'      => array('class' => 'hFormInput'),
        'password'  => array('class' => 'hFormInput'),
        'radio'     => array('class' => 'hFormInput'),
        'submit'    => array('class' => 'hFormButton'),
        'reset'     => array('class' => 'hFormButton'),
        'checkbox'  => array('class' => 'hFormInput'),
        'textarea'  => array('class' => 'hFormInput'),
        'wysiwyg'   => array('class' => 'hFormWYSIWYG'),
        'fckeditor' => array('class' => 'hFormWYSIWYG'),
        'select'    => array('class' => 'hFormInput'),
        'th'        => array('class' => 'hFormHeader'),
        'button'    => array('class' => 'hFormButton')
    );

    public $inputTypes = array(
        'image'     => array('class' => 'hFormImageInput'),
        'text'      => array('class' => 'hFormTextInput'),
        'file'      => array('class' => 'hFormFileInput'),
        'password'  => array('class' => 'hFormPasswordInput'),
        'radio'     => array('class' => 'hFormRadioInput'),
        'submit'    => array('class' => 'hFormSubmitInput'),
        'reset'     => array('class' => 'hFormResetInput'),
        'checkbox'  => array('class' => 'hFormCheckboxInput'),
        'textarea'  => array('class' => 'hFormTextareaInput'),
        'select'    => array('class' => 'hFormSelectInput'),
        'button'    => array('class' => 'hFormButton')
    );

    private $attributes = array(
        # Attribute syntax errors can be suppressed by leading the value with an at sign '@'.
        'validation' => array(
            'autocomplete' => array(
                'validation' => array(
                    '/^on|off$/' => 'The <em>autocomplete</em> attribute may only be "on" or "off".'
                )
            ),
            'autocorrect' => array(
                'validation' => array(
                    '/^on|off$/' => 'The <em>autocorrect</em> attribute may only be "on" or "off".'
                )
            ),
            'placeholder' => array(
                'validation' => array(
                    self::hFormAttributeQuotes => 'The <em>placeholder</em> attribute cannot contain literal quote characters. Entities must be used instead.'
                )
            ),
            'autocapitalize' => array(
                'validation' => array(
                    '/^on|off$/' => 'The <em>autocapitalize</em> attribute may only be "on" or "off".'
                )
            ),
            'autofocus' => array(
                'validation' => array(
                    '/^autofocus$/' => 'The <em>autofocus</em> attribute may only contain <em>autofocus</em> as its value.'
                )
            ),
            'accept' => array(
                'validation' => array(
                    '/^((application|audio|image|message|model|multipart|text|video){1}+(\/){1}+(\w|\-|\+){1,}+(\,)?+(\s)?){1,}$/' => 'The provided sequence of comma-separated MIME types is invalid'
                )
            ),
            # An accesskey must be a letter or number, and may only be one character long.
            'accesskey' => array(
                'validation' => array(
                    '/^([A-Z]|[a-z]|\d){1}$/' => 'The <em>accesskey</em> attribute must contain alpha characters.'
                )
            ),
            # alt cannot contain non-encoded quotations.
            'alt' => array(
                'validation' => array(
                    self::hFormAttributeQuotes => 'The <em>alt</em> attribute cannot contain literal quote characters, these must be encoded as entities.'
                )
            ),
            'checked' => array(
                'validation' => array(
                    '/^checked$/' => 'The <em>checked</em> attribute may only contain <em>checked</em> as its value.'
                )
            ),
            'cols' => array(
                'validation' => array(
                    self::hFormAttributeInteger => 'The <em>cols</em> attribute must contain a numeral value.'
                ),
                'default' => 25
            ),
            'disabled' => array(
                'validation' => array(
                    '/^disabled$/' => 'The <em>disabled</em> attribute may only contain <em>disabled</em> as its value.'
                )
            ),
            'formmethod' => array(
                'validation' => array(
                    '/^get|post$/' => 'The <em>formmethod</em> attribute may only be "get" or "post".'
                )
            ),
            'novalidate' => array(
                'validation' => array(
                    '/^novalidate$/' => 'The <em>novalidate</em> attribute may only be "novalidate".'
                )
            ),
            'formnovalidate' => array(
                'validation' => array(
                    '/^formnovalidate$/' => 'The <em>formnovalidate</em> attribute may only be "formnovalidate".'
                )
            ),
            'formtarget' => array(
                'validation' => array(
                    self::hFormAttributeQuotes => 'The <em>formtarget</em> attribute cannot contain literal quote characters, these must be encoded as entities.'
                )
            ),
            'list' => array(
                'validation' => array(
                    self::hFormAttributeId => 'The <em>list</em> value is not valid.'
                )
            ),
            'maxlength' => array(
                'validation' => array(
                    self::hFormAttributeInteger => 'The <em>maxlength</em> attribute may only contain numerals as its value.'
                )
            ),
            'multiple' => array(
                'validation' => array (
                    '/^multiple$/' => 'The <em>multiple</em> attribute may only contain <em>multiple</em> as its value.'
                )
            ),
            # A name cannot start with a number, and may only contain alpha-numeric characters and/or underscores.
            'name' => array(
                'validation' => array(
                    '/^([A-Z]|[a-z]|\_){1}(\w|\[|\]|\-){1,}$/' => 'The <em>name</em> attribute may only contain <em>alpha-numeric</em> characters, square brackets <em>[, ]</em> and underscores. It may not begin with a numeral.'
                )
            ),
            'readonly' => array(
                'validation' => array(
                    '/^readonly$/' => 'The <em>readonly</em> attribute may only contain <em>readonly</em> as its value.'
                )
            ),
            'required' => array(
                'validation' => array(
                    '/^required$/' => 'The <em>required</em> attribute may only be "required".'
                )
            ),
            'rows' => array(
                'validation' => array(
                    self::hFormAttributeInteger => 'The <em>rows</em> attribute must contain a numeral value.'
                ),
                'default' => 10
            ),
            'size' => array(
                'validation' => array(
                    self::hFormAttributeInteger => 'The <em>size</em> attribute may only contain numerals as its value.'
                ),
                'default' => 25
            ),
            'src' => array(
                'isPath'    => true,
                'pathError' => 'The file specified in <em>src</em> does not exist.'
            ),
            'step' => array(
                'validation' => array(
                    self::hFormAttributeInteger => 'The <em>step</em> attribute must be numerical.'
                )
            ),
            'tabindex' => array(
                'validation' => array(
                    self::hFormAttributeInteger => 'The <em>tabindex</em> attribute must be numerical.'
                )
            ),
            'type' => array(
                'validation' => array(
                    '/^image|button|checkbox|file|hidden|image|password|radio|reset|submit|text|email|url|number|range|date|month|week|time|datetime|datetime\-local|search|color$/' => 'The <em>type</em> attribute must contain one of the following values: <em>button, checkbox, file, hidden, image, password, radio, reset, submit or text</em>.'
                )
            ),
            'wrap' => array(
                'validation' => array(
                    '/^hard|off|soft$/' => 'The <em>wrap</em> attribute must contain one of the following values: <em>hard, soft, or off</em>'
                )
            ),
            'id' => array(
                'validation' => array(
                    self::hFormAttributeId => 'The <em>id</em> value is not valid.'
                )
            ),
            'for' => array(
                'validation' => array(
                    self::hFormAttributeId => 'The <em>for</em> value is not valid.'
                )
            ),
            'class' => array(
                'validation' => array(
                    '/^([A-Z]|[a-z]|\_|\-){1}(\w|\-|\s){1,}$/' => 'The class name is invalid'
                )
            ),
            # A valid inline CSS declaration sequence must be present
            # This is slightly sticter than browsers actually allow.
            'style' => array(
                'validation' => array(
                    '/^(([A-Z]|[a-z]|\-){1,}+(\:)+(\s)?([A-Z]|[a-z]|\d|\:|\(|\)|\.|\/|\'|\"|\s|\%){1,}(\;)(\s)?){1,}$/' => 'A syntax error has occured, the styles provided are invalid.'
                )
            ),
            # value cannot contain non-encoded quotations.
            'value' => array(
                'validation' => array(
                    self::hFormAttributeQuotes => 'The <em>value</em> attribute cannot contain literal quote characters. Entities must be used instead.'
                )
            ),
            'method' => array(
                'validation' => array(
                    '/^get|post$/' => 'The value of the <em>method</em> attribute must be <em>get or post</em>.'
                ),
                'default' => 'post'
            ),
            'width' => array(
                'validation' => array(
                    self::hFormAttributeInteger => 'The <em>width</em> attribute must be numerical.'
                )
            ),
            'height' => array(
                'validation' => array(
                    self::hFormAttributeInteger => 'The <em>height</em> attribute must be numerical.'
                )
            ),
            'enctype' => array(
                'validation' => array(
                    '/^multipart\/form\-data$/' => 'The <em>enctype</em> attribute is configured to only allow the value "multipart/form-data".'
                )
            ),
            'formenctype' => array(
                'validation' => array(
                    '/^multipart\/form\-data$/' => 'The <em>formenctype</em> attribute is configured to only allow the value "multipart/form-data".'
                )
            )
        )
    );

    private $tableColumn     = 0;
    private $divisionCounter = 0;
    private $fieldsetCounter = 0;
    private $div;
    private $fieldset;
    private $render = 'form';
    private $fieldsTemp       = array();
    private $defineCell       = array();
    private $defineLabelCell  = array();
    private $defineTable      = array();
    private $defineCols       = 2;
    private $defineAttributes = array();
    private $setVariable      = array();
    private $setValue         = null;
    private $callArguments    = array();
    private $setFormOptions   = true;
    private $typeFunctions    = array('array', 'float', 'int', 'bool', 'object', 'integer', 'string');
    private $hiddenFields = '';
    private $field;
    private $options;
    private $css;
    private $javascript;
    private $plugins;
    private $element;
    private $validation = array();
    private $fieldNames = array();

    private $hWYSIWYG;
    private $hLocation;

    public function hConstructor()
    {
        if ($GLOBALS['hFramework']->hFormLoadCSS(true))
        {
            $this->getPluginCSS();
        }
    }

    public function &setVariableSnapShot()
    {
        $this->hFormSnapShot = $this->hForm;
        return $this;
    }

    public function &setVariables(&$array)
    {
        if (isset($array) && is_array($array))
        {
            foreach ($array as $key => $value)
            {
                if (in_array($key, $this->variables))
                {
                    $this->$key = $value;
                }
                else
                {
                    $GLOBALS['hFramework']->warning("Form variable '{$key}' is not a defined variable.", __FILE__, __LINE__);
                }
            }
        }

        return $this;
    }

    public function &setVariable($variable, $value)
    {
        $this->setVariable[$variable] = $value;

        return $this;
    }

    public function &restoreVariableSnapShot()
    {
        $this->hForm = $this->hFormSnapShot;

        return $this;
    }

    public function &__call($method, $arguments)
    {
        # @argument $method string
        #  <p>Name of the method to call.</p>
        # @end

        # @argument $arguments array
        #  <p>The arguments passed to the method numerically indexed from zero.</p>
        # @end

        # @description
        #
        # <h4>Setting id, name, and class Attributes</h4>
        #
        # <p>
        #     By default, the <var>id</var> argument is used for both the <var>id</var> and
        #     <var>name</var> attributes.  When you pass a value to <var>id</var> that value
        #     is used for both the <var>id</var> and <var>name</var> attributes.
        # <p>
        # <p>
        #     The <var>id</var> argument can specify, optionally, <var>class</var>, <var>id</var>,
        #     and <var>name</var> attributes, and each attribute be given a unique value.  To do so,
        #     you specify the id argument using a syntax pattern matching one of the following in the
        #     table below.
        # </p>
        # <h4>Specifying class/id/name for Form Inputs</h4>
        # <table>
        #     <thead>
        #         <tr>
        #             <th>Argument Syntax</th>         <th>Resulting HTML</th>
        #         </tr>
        #     </thead>
        #     <tbody class='code'>
        #         <tr>
        #             <td>$id = "class:id"</td>        <td>class="class" id="id"</td>
        #         </tr>
        #         <tr>
        #             <td>$id = "class:id:name"</td>   <td>class="class" id="id" name="name"</td>
        #         </tr>
        #         <tr>
        #             <td>$id = ":id:name"</td>        <td>id="id" name="name"</td>
        #         </tr>
        #         <tr>
        #             <td>$id = "class::name"</td>     <td>class="class" name="name"</td>
        #         </tr>
        #     </tbody>
        # </table>
        # <p>
        #     When a value is empty, for example, in the syntax <var>:id:name</var>, the <var>class</var>
        #     value is empty, therefore, <var>class</var> attribute will be left out.  Similarly, if you
        #     do <var>class:id</var>, since <var>name</var> is not specified, <var>name</var> is left out.
        #     Consequently, if you were to do <var>class::name</var> the <var>id</var>
        #     attribute would be left out.
        # </p>
        # <p>
        #     Whenever a class name is specified, the custom class name replaces any default class name
        #     that would have been applied, if there is one.
        # </p>
        # <p>
        #     This is useful when you might need the name attribute to be an array using a name with square
        #     brackets like <var>name[]</var>.  The square brackets in the <var>name</var> attribute result
        #     in an array being created upon form submission.  In a scenario like this, having the
        #     <var>id</var> and <var>name</var> attributes identical is unwanted.
        # </p>
        # <p>
        #     This is also useful for giving certain fields extra class names beyond what might be applied
        #     by default.
        # </p>
        # <h4>Specifying <var>size/maxsize</var> and <var>cols/rows</var> for Form Inputs</h4>
        # <p>
        #     Methods accepting a <var>$size</var> argument, by default sets the <var>size</var>
        #     attribute.
        # </p>
        # <p>
        #     It may also be used to specify the <var>maxsize</var> attribute.  The following table
        #     reveals how <var>size</var> and <var>maxsize</var> attributes can be specified using
        #     the <var>$size</var> argument.
        # </p>
        # <table>
        #     <thead>
        #         <tr>
        #             <th>Argument Syntax</th>         <th>Resulting HTML</th>
        #         </tr>
        #     </thead>
        #     </thead>
        #     <tbody class='code'>
        #         <tr>
        #             <td>$size = "size,maxsize"</td>  <td>size="size" maxsize="maxsize"</td>
        #         </tr>
        #         <tr>
        #             <td>$size = ",maxsize"</td>      <td>maxsize="maxsize"</td>
        #         </tr>
        #     </tbody>
        # </table>
        # <p>With regards to the <var>textarea</var> element, <var>$size</var> specifies the
        # <var>cols</var> and <var>rows</var> attributes.</p>
        # <table>
        #     <thead>
        #         <tr>
        #             <th>Argument Syntax</th>         <th>Resulting HTML</th>
        #         </tr>
        #     </thead>
        #     </thead>
        #     <tbody class='code'>
        #         <tr>
        #             <td>$size = "cols"</td>          <td>cols="cols"</td>
        #         </tr>
        #         <tr>
        #             <td>$size = "cols,rows"</td>     <td>cols="cols" rows="rows"</td>
        #         </tr>
        #         <tr>
        #             <td>$size = ",rows"</td>         <td>rows="rows"</td>
        #         </tr>
        #     </tbody>
        # </table>
        # <p>With regards to the <var>select</var> element, <var>$size</var> specifies just the
        # <var>size</var> attribute.</p>
        # <h4>Setting Attributes</h4>
        # <p>
        #    If an attribute is not provided for in the argument list of a method, you may set them
        #    by calling the appropriate method prior to calling the <var>add*Input()</var>, <var>add*()</var>,
        #    or <var>add*Button()</var> methods.
        # </p>
        # <code>setAttribute($attribute, $value);</code>
        # <p>For example:</p>
        # <code>$this->hForm->setAttribute('placeholder', 'Some placeholder text');</code>
        # <h4>Setting Multiple Attributes</h4>
        # <code>$this->hForm->setAttributes(Array $attributes);</code>
        # <p>For example:</p>
        # <code>
        #     $this->hForm->setAttributes(
        #        array(
        #           'placeholder'    => 'Some placeholder text',
        #           'disabled'       => 'disabled'
        #           'autofocus'      => 'autofocus',
        #           'autocapitalize' => 'off',
        #           'autocorrect'    => 'off'
        #        )
        #     );
        # </code>
        # <p>
        #   Then call, for example:
        # </p>
        # <code>$this->hForm->addTextInput('someId', 'My Text Field:');</code>
        # <p>This will give you:</p>
        # <code>{EncodeHTML?
        #   <td class='hFormLabel'>
        #     <label for='someId'>My Text Field:</label>
        #   </td>
        #   <td class='hFormInput'>
        #     <input type='text' id='someId' name='someId' class='hFormTextInput' value='' placeholder='Some placeholder text' disabled='disabled' autofocus='autofocus' autocapitalize='off' autocorrect='off' />
        #   </td>
        # }</code>
        #
        # @end

        $isDisabled = in_array('disabled', $arguments, true);

        if ($isDisabled)
        {
            $key = array_search('disabled', $arguments);
            unset($arguments[$key]);
            $arguments = array_merge($arguments);
        }

        $isReadOnly = in_array('readonly', $arguments, true);

        if ($isReadOnly)
        {
            $key = array_search('readonly', $arguments);
            unset($arguments[$key]);
            $arguments = array_merge($arguments);
        }

        $attributes = array();

        if (isset($arguments[0]))
        {
            $attributes = $this->getAttributesFromId($arguments[0]);
        }

        $options = array();

        if (isset($this->setVariable['hFormCheckboxReverseLabel']) && $method == 'addCheckboxInput')
        {
            $this->addInputLabel($attributes['id'], $arguments[1]);
        }

        switch ($method)
        {
            case 'addTextInput':
            case 'addFileInput':
            case 'addRadioInput':
            case 'addPasswordInput':
            case 'addTextareaInput':
            case 'addWYSIWYGInput':
            case 'addSelectCountry':
            case 'addSelectState':
            case 'addSelectInput':
            case 'addData':
            case 'addHumanVerificationInput':
            case 'addEmailInput':
            case 'addPhoneInput':
            case 'addURLInput':
            case 'addNumberInput':
            case 'addRangeInput':
            case 'addDateInput':
            case 'addMonthInput':
            case 'addWeekInput':
            case 'addTimeInput':
            case 'addDatetimeInput':
            case 'addDatetimeLocalInput':
            case 'addSearchInput':
            case 'addColorInput':
            {
                if (!empty($arguments[1]))
                {
                    $this->addInputLabel(isset($attributes['id'])? $attributes['id'] : '', $arguments[1]);
                }

                break;
            }
        }

        switch ($method)
        {
            case 'addTextInput':
            # public function addTextInput($id, $label, $size, $value)
            #
            #   $id
            #

            case 'addPasswordInput':
            # public function addPasswordInput($id, $label, $size, $value)

            case 'addTextareaInput':
            case 'addFileInput':
            case 'addEmailInput':
            case 'addURLInput':
            case 'addNumberInput':
            case 'addRangeInput':
            case 'addDateInput':
            case 'addMonthInput':
            case 'addWeekInput':
            case 'addTimeInput':
            case 'addPhoneInput':
            case 'addDatetimeInput':
            case 'addDatetimeLocalInput':
            case 'addSearchInput':
            case 'addColorInput':
            {
                # $id, $label, $size, $value
                if ($method == 'addDatetimeLocalInput')
                {
                    $type = 'datetime-local';
                }
                else if ($method == 'addPhoneInput')
                {
                    $type = 'tel';
                }
                else
                {
                    $type = strtolower(str_replace(array('add', 'Input'), '', $method));
                }

                # Possible values for size
                # size || cols
                # size,maxsize || cols,rows
                # ,maxsize || ,rows
                if (isset($arguments[2]))
                {
                    $type == 'textarea'?
                            $this->getSize($arguments[2], $attributes['cols'], $attributes['rows'])
                        :
                            $this->getSize($arguments[2], $attributes['size'], $attributes['maxlength']);
                }

                if (isset($arguments[3]))
                {
                    $value = $arguments[3];
                }

                break;
            }
            case 'addData':
            {
                # $id, $label, $value
                $type = 'html';

                $this->defineCell(
                    array(
                        'id' => isset($attributes['id'])? $attributes['id'] : ''
                    )
                );

                unset($attributes['id']);

                if (isset($arguments[2]))
                {
                    $value = $arguments[2];
                }

                break;
            }
            case 'addRadioInput':
            {
                # $id, $label, $options, $value
                $type                  = 'radio';
                $attributes['options'] = isset($arguments[2])? $arguments[2] : array();
                $value                 = isset($arguments[3])? $arguments[3] : '';
                break;
            }
            case 'addCheckboxInput':
            {
                # $id, $label, $value
                $type  = 'checkbox';

                if (!empty($arguments[2]))
                {
                    $value = 1;
                }

                if (!isset($this->setVariable['hFormCheckboxReverseLabel']))
                {
                    $options['hFormLabel'] = $arguments[1];
                }

                break;
            }
            case 'addWYSIWYGInput':
            {
                if (empty($this->hWYSIWYG))
                {
                    $this->hWYSIWYG = $GLOBALS['hFramework']->library('hWYSIWYG');
                }

                # $id, $label, $value, $size, $dimensions, $plugins, $toolbar, $styles, $configuration
                $type = 'wysiwyg';

                if (isset($arguments[2]))
                {
                    $value = $arguments[2];
                }

                $size          = isset($arguments[3])? $arguments[3] : '';
                $dimensions    = isset($arguments[4])? $arguments[4] : '';
                $plugins       = isset($arguments[5])? $arguments[5] : '';
                $toolbar       = isset($arguments[6])? $arguments[6] : '';
                $css           = isset($arguments[7])? $arguments[7] : '';
                $configuration = isset($arguments[8])? $arguments[8] : '';

                $width = 0;
                $height = 0;

                $this->getSize($dimensions, $width, $height);
                $this->getSize($size, $attributes['cols'], $attributes['rows']);

                $this->hWYSIWYG->addEditor($attributes['id'], $width, $height, is_array($plugins)? $plugins : array(), $toolbar, $css, $configuration);
                break;
            }
            case 'addSelectCountry':
            {
                if (in_array('multiple', $arguments, true))
                {
                    $key = array_search('multiple', $arguments);

                    unset($arguments[$key]);
                    $attributes['multiple'] = 'multiple';

                    # Reindex the array so that the keys are continuous.
                    $arguments = array_merge($arguments);
                }

                # id, label, size, value
                $type = 'select';
                $attributes['id']      = empty($attributes['id'])? 'hLocationCountryId' : $attributes['id'];
                $attributes['options'] = $this->getCountries();
                $attributes['size']    = isset($arguments[2])? (int) $arguments[2] : 1;

                if (isset($arguments[3]))
                {
                    $value = $arguments[3];
                }

                break;
            }
            case 'addSelectState':
            {
                if (in_array('multiple', $arguments, true))
                {
                    $key = array_search('multiple', $arguments);

                    unset($arguments[$key]);
                    $attributes['multiple'] = 'multiple';

                    # Reindex the array so that the keys are continuous.
                    $arguments = array_merge($arguments);
                }

                # id, label, country(ies), size, $value = null
                $type = 'select';
                $attributes['id']      = empty($attributes['id'])? 'hLocationStateId' : $attributes['id'];
                $attributes['options'] = $this->getStates(isset($arguments[2])? $arguments[2] : 223);
                $attributes['size']    = isset($arguments[3])? (int) $arguments[3] : 1;

                $options['hFormStates'] = true;

                if (isset($arguments[4]))
                {
                    $value = $arguments[4];
                }

                break;
            }
            case 'addSelectInput':
            {
                $type = 'select';

                # $id, $label, $options, $size = 1, $value = null
                #
                # If any argument value is 'multiple', multiple switch is set, then
                #
                # 1st string   == id
                # 1st array    == options
                # 2nd string   == label
                # 1st interger == size
                if (in_array('multiple', $arguments, true))
                {
                    $key = array_search('multiple', $arguments);

                    unset($arguments[$key]);
                    $attributes['multiple'] = 'multiple';

                    # Reindex the array so that the keys are continuous.
                    $arguments = array_merge($arguments);
                }

                $attributes['options'] = $arguments[2];

                if (isset($arguments[3]) && $arguments[3] > 1)
                {
                    $attributes['size'] = (int) $arguments[3];
                }

                if (isset($arguments[4]))
                {
                    $value = $arguments[4];
                }

                break;
            }
            case 'addHiddenInput':
            {
                # id, value
                $type  = 'hidden';
                if (isset($arguments[1]))
                {
                    $value = $arguments[1];
                }

                break;
            }
            case 'addTableCell':
            {
                # value, colspan
                $type = 'html';

                if (isset($arguments[1]))
                {
                    $this->defineCell($arguments[1]);
                }

                if (isset($attributes['id']))
                {
                    unset($attributes['id']);
                }

                if (isset($arguments[0]))
                {
                    $value = $arguments[0];
                }

                break;
            }
            case 'addFormHeading':
            {
                $type = 'heading';

                unset($attributes['id']);
                $value = $arguments[0];
                break;
            }
            case 'addTableHeading':
            {
                $type = 'th';

                unset($attributes['id']);
                $value = $arguments[0];
                break;
            }
            case 'addSubmitButton':
            {
                # id, value, (optional) colspan
                $type = 'submit';
                $value = $arguments[1];

                if (isset($arguments[2]))
                {
                    $this->defineCell = array_merge(
                        array(
                            'colspan' => $arguments[2]
                        ),
                        $this->defineCell
                    );
                }

                break;
            }
            # id, value, (optional) colspan
            case 'addButton':
            {
                $type = 'button';
                $value = $arguments[1];

                if (isset($arguments[2]))
                {
                    $this->defineCell = array_merge(
                        array(
                            'colspan' => $arguments[2]
                        ),
                        $this->defineCell
                    );
                }

                break;
            }
            case 'addImageButton':
            {
                # id, src, alt, value
                $type = 'image';

                $attributes['src'] = $arguments[1];
                $attributes['alt'] = $arguments[2];

                if (isset($arguments[3]))
                {
                    $value = $arguments[3];
                }

                break;
            }
            default:
            {
                if (in_array($method, $this->variables))
                {
                    # no other method is defined, it's a variable.
                    if (isset($this->hForm[$method]))
                    {
                        return $this->hForm[$method];
                    }
                    else if (isset($arguments[0]))
                    {
                        return $arguments[0];
                    }
                    else
                    {
                        return '';
                    }
                }
                else
                {
                    $GLOBALS['hFramework']->warning("Method '{$method}' is not defined.", __FILE__, __LINE__);
                }
            }
        }

        if (isset($type))
        {
            if (empty($attributes['id']))
            {
                unset($attributes['id']);
            }

            if (!isset($attributes['name']) && isset($attributes['id']))
            {
                $attributes['name'] = $attributes['id'];
            }

            if (!isset($value))
            {
                $value = $this->setValue;
            }

            if ($isDisabled)
            {
                $attributes['disabled'] = 'disabled';
            }

            if ($isReadOnly)
            {
                $attributes['readonly'] = 'readonly';
            }

            $this->field(
                array(
                    'type'       => $type,
                    'value'      => $value,
                    'attributes' => array_merge($attributes, $this->defineAttributes),
                    'cell'       => $this->defineCell,
                    'options'    => array_merge($options, $this->setVariable),
                    'validation' => $this->validation
                )
            );

            $this->setValue         = null;
            $this->defineAttributes = array();
            $this->defineCell       = array();
            $this->setVariable      = array();
            $this->validation       = array();
        }
        else
        {
            $GLOBALS['hFramework']->warning('Required attribute, type, is not set.', __FILE__, __LINE__);
        }

        return $this;
    }

    public function &addInputLabel($id, $label)
    {
        $ndCharacter = substr($label, 1, 1);

        if ($ndCharacter == ':')
        {
            $attributes['accesskey'] = substr($label, 0, 1);
            $value = substr($label, 2);
        }
        else
        {
            $value = $label;
        }

        $type = 'label'.(stristr($value, ' -L')? ' -L' : '');

        $value = str_replace(' -L', '', $value);

        $attributes['for'] = $id;

        unset($attributes['id']);
        unset($label);

        $this->field(
            array(
                'type'       => $type,
                'value'      => $value,
                'attributes' => $attributes,
                'cell'       => $this->defineLabelCell
            )
        );

        $this->defineLabelCell = array();

        return $this;
    }

    public function &setAttribute($attribute, $value)
    {
        $this->defineAttributes[$attribute] = $value;

        return $this;
    }

    public function &setAttributes($attributes)
    {
        if (empty($this->defineAttributes) || !count($this->defineAttributes))
        {
            $this->defineAttributes = $attributes;
        }
        else
        {
            $this->defineAttributes = array_merge($this->defineAttributes, $attributes);
        }

        return $this;
    }

    private function &getSize($size, &$x, &$y)
    {
        if (!empty($size))
        {
            if (strstr($size, ','))
            {
                $sizes = explode(',', $size);

                if (!empty($sizes[0]))
                {
                    $x = $sizes[0];
                }

                $y = $sizes[1];
            }
            else
            {
                $x = $size;
                unset($y);
            }
        }

        return $this;
    }

    public function __get($key)
    {
        if (isset($this->hForm[$key]))
        {
            return $this->hForm[$key];
        }
        else
        {
            return '';
        }
    }

    public function __set($key, $value)
    {
        $this->hForm[$key] = $value;
    }

    private function &setDivisionCounter($d)
    {
        $this->divisionCounter = $d;
        return $this;
    }

    private function &setFieldsetCounter($f)
    {
        $this->fieldsetCounter = $f;
        return $this;
    }

    private function &getArgumentByValue(&$rtn, $value)
    {
        if (in_array($value, $this->callArguments))
        {
            unset($this->callArguments[array_search($value, $this->callArguments)]);
            $rtn = $value;
        }

        return $this;
    }

    private function &getArgumentByType(&$rtn, $function, $offset = 1)
    {
        $i = 1;

        foreach ($this->callArguments as $value)
        {
            if (in_array($function, $this->typeFunctions) && function_exists('is_'.$function))
            {
                if (call_user_func('is_'.$function, $value))
                {
                    if ($i === $offset)
                    {
                        $rtn = $value;
                        break;
                    }

                    $i++;
                }
            }
            else if ($function == '*')
            {
                if ($i == $offset)
                {
                    $rtn = $value;
                    break;
                }

                $i++;
            }
        }

        return $this;
    }

    public function &setValue($value)
    {
        $this->setValue = $value;

        return $this;
    }

    # For most use cases, a table definition consists of little more than the table
    # size and the column sizes.

    public function &defineTable($table, $columns)
    {
        $attributes = array();

        if (!is_array($table))
        {
            if (!empty($table) && $this->isMeasurement($table))
            {
                $attributes = array_merge($attributes, array('style' => 'width: '.$table.';'));
            }
        }
        else
        {
            $attributes = $table;
        }

        $columnAttributes = array();

        if (!empty($columns) && !is_array($columns))
        {
            if (strstr($columns, ','))
            {
                $columns = explode(',', $columns);

                $this->defineCols = count($columns);

                foreach ($columns as $column)
                {
                    if ($this->isMeasurement($column))
                    {
                        array_push($columnAttributes, array('style' => 'width: '.$column.';'));
                    }
                    else
                    {
                        array_push($columnAttributes, array());
                    }
                }
            }
            else
            {
                $this->defineCols = 1;
                array_push($columnAttributes, array('style' => 'width: '.$columns.';'));
            }
        }
        else
        {
            $columnAttributes = $columns;
        }

        $this->defineTable = array(
            'table' => array(
                'columns'    => $columnAttributes,
                'attributes' => $attributes
            ),
        );

        return $this;
    }

    public function defineValidation()
    {

    }

    public function &addDiv($id, $legend = null)
    {
        $this->pushDiv($this->getAttributesFromId($id), $legend);

        return $this;
    }

    private function getAttributesFromId($attrs)
    {
        $attributes = array();

        if (is_array($attrs))
        {
            switch (true)
            {
                case isset($attrs[0]) && isset($attrs[1]) && isset($attrs[2]) && isset($attrs[3]);
                {
                    $attributes['id'] = $attrs[0];
                    $attributes['class'] = $attrs[1];
                    $attributes['name'] = $attrs[2];
                    $attributes['list'] = $attrs[3];
                    break;
                }
                case isset($attrs[0]) && isset($attrs[1]) && isset($attrs[2]):
                {
                    $attributes['id'] = $attrs[0];
                    $attributes['class'] = $attrs[1];
                    $attributes['name'] = $attrs[2];
                    break;
                }
                case isset($attrs[0]) && isset($attrs[1]):
                {
                    $attributes['id'] = $attrs[0];
                    $attributes['name'] = $attrs[0];
                    $attributes['class'] = $ids[1];
                    break;
                }
                case isset($attrs[0]):
                {
                    $attributes['id'] = $attrs[0];
                    $attributes['name'] = $attrs[0];
                    break;
                }
                default:
                {
                    foreach ($attrs as $attribute => $value)
                    {
                        $attributes[$attribute] = $value;
                    }

                    if (isset($attributes['id']) && !isset($attributes['name']))
                    {
                        $attributes['name'] = $attributes['id'];
                    }
                }
            }
        }
        else
        {
            switch (true)
            {
                case strstr($attrs, ',') && strstr($attrs, ':'):
                {
                    # "name:aname,id:anid,class:aclass,list:alist"
                    $attrs = hString::trimEach(str_replace('=', ':', $attrs), ',');

                    foreach ($attrs as $attr)
                    {
                        list($attr, $value) = explode(':', $attr);
                        $attributes[trim(strtolower($attr))] = $value;
                    }

                    break;
                }
                case strstr($attrs, ',') || strstr($attrs, ';'):
                {
                    $attrs = hString::trimEach(str_replace(';', ',', $attrs), ',');

                    switch (true)
                    {
                        case isset($attrs[0]) && isset($attrs[1]) && isset($attrs[2]) && isset($attrs[3]):
                        {
                            # id,name,class,list
                            $attributes['id'] = $attrs[0];
                            $attributes['name'] = $attrs[1];
                            $attributes['class'] = $attrs[2];
                            $attributes['list'] = $attrs[3];
                            break;
                        }
                        case isset($attrs[0]) && isset($attrs[1]) && isset($attrs[2]):
                        {
                            # id,name,class
                            $attributes['id'] = $attrs[0];
                            $attributes['name'] = $attrs[1];
                            $attributes['class'] = $attrs[2];
                            break;
                        }
                        case isset($attrs[0]) && isset($attrs[1]):
                        {
                            # id,class (and name == id)
                            $attributes['id'] = $attrs[0];
                            $attributes['name'] = $attrs[0];
                            $attributes['class'] = $attrs[1];
                            break;
                        }
                    }
                }
                case strstr($attrs, ':'):
                {
                    # Support for the legacy method of defining these.
                    # class:id
                    # class:id:name
                    # :id:name
                    # class::name

                    if (substr_count($attrs, ':') == 1)
                    {
                        $attrs .= ':';
                    }

                    list($class, $id, $name) = hString::trimEach($attrs, ':');

                    if (!empty($class))
                    {
                        $attributes['class'] = $class;
                    }

                    $attributes['name'] = !empty($name)? $name : $id;
                    $attributes['id'] = $id;
                    break;
                }
                default:
                {
                    $attributes['id'] = $attrs;
                    $attributes['name'] = $attrs;
                }
            }
        }

        return $attributes;
    }

    public function &addFieldset($legend, $table = null, $columns = null, $id = null)
    {
        $this->defineTable($table, $columns);
        $this->pushFieldset($legend, $this->getAttributesFromId($id));

        return $this;
    }

    public function &addFieldsetDivision($id, $legend, $table = null, $columns = null)
    {
        $this->pushDiv(array('id' => $id));
        $this->defineTable($table, $columns);
        $this->pushFieldset($legend);

        return $this;
    }

    public function &setFormAttribute($attribute, $value)
    {
        $this->form['attributes'][$attribute] = $value;

        return $this;
    }

    public function &setUploadAttributes($action = null, $target = null)
    {
        $this->form['attributes']['action'] = empty($action)? $GLOBALS['hFramework']->hFilePath : $action;

        if (!empty($target))
        {
            $this->form['attributes']['target'] = $target;
        }

        $this->form['attributes']['enctype'] = 'multipart/form-data';

        return $this;
    }

    public function &defineCell()
    {
        # @description
        # <p>
        # This method defines cell properties for the next method which creates an object
        # contained within a table cell, for example, $this->getTextInput() creates a
        # text input that's contained in a table cell, this method will define that
        # cell's properties.
        # </p>
        # @end

        $arguments = func_get_args();
        $this->defineCell = $this->getCellAttributes($arguments);
        return $this;
    }

    public function &defineLabelCell()
    {
        $arguments = func_get_args();
        $this->defineLabelCell = $this->getCellAttributes($arguments);
        return $this;
    }

    public function &setLabelCellAttributes($attributes)
    {
        $this->defineLabelCell = array_merge($this->defineLabelCell, $attributes);
        return $this;
    }

    public function &setLabelCellAttribute($attribute, $value)
    {
        $this->defineLabelCell[$attribute] = $value;
        return $this;
    }

    private function getCellAttributes(Array $arguments)
    {
        if (count($arguments) == 1 && is_array($arguments[0]))
        {
            # @description
            # <p>
            # Define a cell by passing an array of options, if desired.
            # The array will be a key => value association of attributes
            # and values, where "key" is the HTML attribute name and
            # "value" is the attribute's value.
            # </p>

            return $arguments[0];
        }
        else
        {
            # <p>
            #   Otherwise it'll try and pick out common properties you'd want to define
            #   for a cell by intelligently switching the types of arguments
            #   this method can accept.
            # </p>
            # <p>
            #   If an array is present, it's always the cell attributes.
            # </p>
            # <p>
            #   If two arrays are present, one is attributes, the second is options.
            # </p>
            # <p>
            #   If a string is present, it's always the value of the "style" attribute.
            # </p>
            # </p>
            # <p>
            #   If arguments are numeric, they'll be treated as follows:
            # </p>
            # <p>
            #   1 argument   == colspan<br />
            #   2 arguments  == colspan, rowspan
            # </p>
            # <p>
            #   If either value == 1, no colspan or rowspan is applied.
            # </p>
            # @end

            $attributes = array();
            $otherAttributes = array();

            foreach ($arguments as $argument)
            {
                switch (true)
                {
                    case is_array($argument):
                    {
                        $otherAttributes = $argument;
                        break;
                    }
                    case is_numeric($argument):
                    {
                        if (!isset($attributes['colspan']))
                        {
                            $attributes['colspan'] = (int) $argument;
                        }
                        else
                        {
                            $attributes['rowspan'] = (int) $argument;
                        }
                        break;
                    }
                    case is_string($argument):
                    default:
                    {
                        if (strstr($argument, ':'))
                        {
                            $attributes['style'] = $argument;
                        }
                        else if (strstr($argument, '#'))
                        {
                            $attributes['id'] = substr($argument, 1);
                        }
                        else if (strstr($arguments, '.'))
                        {
                            $attributes['class'] = substr($argument, 1);
                        }
                    }
                }
            }

            return array_merge($otherAttributes, $attributes);
        }
    }

    public function &field($field)
    {
        if (!is_array($field))
        {
            $GLOBALS['hFramework']->warning('Argument $field must be an array.', __FILE__, __LINE__);
        }

        if (isset($this->fields[($this->div-1)][($this->fieldset-1)]) && is_array($this->fields[($this->div-1)][($this->fieldset-1)]))
        {
            array_push($this->fields[($this->div-1)][($this->fieldset-1)], $field);
        }

        return $this;
    }

    public function getCountries()
    {
        $this->hLocation = $this->library('hLocation');
        return $this->hLocation->getCountries(true, 'Please select a country');
    }

    public function getStates($hLocationCountryId, $default = true)
    {
        $this->hLocation = $this->library('hLocation');
        return $this->hLocation->getStates($hLocationCountryId, $default);
    }

    public function getLegends()
    {
        return $this->formLegends;
    }

    public function &setFormHeaders()
    {
        if ($this->hWYSIWYG)
        {
            $this->javascript .= $this->hWYSIWYG->getJavaScript();
        }

        $GLOBALS['hFramework']->hFileCSS .= $this->css;
        $GLOBALS['hFramework']->hFileJavaScript .= $this->javascript;

        $this->css        = '';
        $this->javascript = '';

        return $this;
    }

    public function getForm($identifier = null)
    {
        if (!empty($identifier))
        {
            $this->hFormIdentifier = $identifier;
        }

        if (!$this->hFormRender)
        {
            $this->hFormRender = 'form';
        }

        $form = (string) '';

        $form .=
          "\n".
          "<!-- Begin Hot Toddy Form Framework Output -->\n";

        $this->setElement('form');

        # Generate form tag.
        if ($this->hFormElement(true) && $this->hFormOpeningTag(true))
        {
            #$this->setAttributeDefault('action', $GLOBALS['hFramework']->hFilePath);
            $this->setAttributeDefault('method', 'post');
            $this->setAttributeDefault('accept-charset', 'UTF-8');

            $form .= '<form'.$this->getAttributes($this->form['attributes']).">\n";
        }

        if ($this->hFormPrependCallback)
        {
            $form .= call_user_func_array($this->hFormPrependCallback, $this->hFormPrependCallbackArguments(array()));
        }

        # Counter Variable Reference
        #
        # $d = div
        # $f = fieldset
        # $n = input
        # $r = row
        # $c = column
        # $i = generic counter
        $this->validate();

        $this->setVariableSnapShot();

        for ($d = 0, $dc = count($this->fields); $d < $dc; $d++)
        {
            $this->setDivisionCounter($d);

            if (empty($this->fields[$d]))
            {
                continue;
            }

            $this->setElement('div');
            $this->setAttributeDefault('class', 'hFormDivision'.($this->hFormHasErrors? ' hFormDivisionErrors' : ''));

            $form .= "    <div".$this->getAttributes().">\n";

            if (isset($this->fields[$d]['divLegend']))
            {
                $this->setElement('divLegend');
                $this->setAttributeDefault('class', 'hFormDivisionLegend');

                array_push(
                    $this->formLegends,
                    array(
                        'value' => $this->fields[$d]['divLegend']['value'],
                        'id' => $this->fields[$d]['attributes']['id']
                    )
                );

                $form .= "        <div".$this->getAttributes()."><span>".$GLOBALS['hFramework']->translate($this->fields[$d]['divLegend']['value'])."</span></div>\n";

                unset($this->fields[$d]['divLegend']);
            }

            unset($this->fields[$d]['attributes']);

            for ($f = 0, $fc = count($this->fields[$d]); $f < $fc; $f++)
            {
                $this->setFieldsetCounter($f);

                if (empty($this->fields[$d][$f]))
                {
                    continue;
                }

                $this->setElement('fieldset');

                $form .=
                    "        <div class='hFormFieldsetWrapper'>\n".
                    "            <fieldset".$this->getAttributes().">\n";

                $this->setElement('legend');

                $legend = "                <legend".$this->getAttributes().">".$GLOBALS['hFramework']->translate($this->getElementValue())."</legend>\n";

                # Keep the attributes applied to the <legend> element
                $form .= $legend.str_replace(
                    array(
                        '<legend>',
                        '</legend>'
                    ),
                    array(
                        "<div class='hFormLegend'><span>",
                        "</span></div>"
                    ),
                    $legend
                );

                $this->setElement('table');
                $this->setAttributeDefault('class', 'hFormTable');

                # Begin template
                $form .=
                    "                <table".$this->getAttributes().">\n".
                    "                    <colgroup>\n";

                $cols = isset($this->fields[$d][$f]['cols'])? $this->fields[$d][$f]['cols'] : 2;

                $this->setElement('col');

                for ($i = 0; $i < $cols; $i++)
                {
                    $this->tableColumn = $i;

                    $form .= "                        <col".$this->getAttributes()." />\n";
                }

                unset($this->fields[$d][$f]['table']);

                $form .=
                    "                    </colgroup>\n".
                    "                    <tbody>\n";

                # Get row count and put each field in
                # a corresponding field numbered offset by the column and row position of each field.
                $this->compileTable($cols, $d, $f);

                # Row counter
                foreach ($this->table as $r => $column)
                {
                    $form .= "                        <tr>\n";

                    # Column counter
                    foreach ($column as $c => $cell)
                    {
                        $this->restoreVariableSnapShot();

                        $this->field = &$cell;

                        if (!isset($this->field['skip']))
                        {
                            if (isset($this->field['type']))
                            {
                                $error = '';

                                if ($this->hFormIdentifier && isset($_POST[$this->hFormIdentifier]) && isset($this->field['validation']))
                                {
                                    if (false !== ($error = $this->validateField($this->field['validation'], $this->field['value'])))
                                    {
                                        $this->field['options']['hFormFieldHasErrors'] = true;
                                    }
                                }

                                # An existing per type options will overwrite any global option of the same name.
                                $this->arrayMustExist($this->types[$this->field['type']]['options']);

                                # An existing per field options will overwrite any global or per type options of the same name
                                $this->arrayMustExist($this->table[$r][$c]['options']);

                                # Merge options
                                # Per type options
                                # Per field options

                                $this->setVariables($this->types[$this->field['type']]['options']);
                                $this->setVariables($this->field['options']);

                                $this->setElement('td');

                                # Set the default classname
                                if (isset($this->types[$this->field['type']]['class']))
                                {
                                    $this->setAttributeDefault('class', $this->types[$this->field['type']]['class']);
                                }

                                if (!$this->hFormJoinInputToLast(false))
                                {
                                    $form .= "<".($this->field['type'] == 'th'? 'th' : 'td').$this->getAttributes().">";
                                }

                                if ($this->hFormAutoValues(true))
                                {
                                    $this->setElementValue();
                                }

                                $this->applyOptionsToValue();

                                $this->setElement('input');

                                if (isset($this->inputTypes[$this->field['type']]['class']))
                                {
                                    $this->setAttributeDefault('class', $this->inputTypes[$this->field['type']]['class']);
                                }

                                if ($this->renderMode('form') && $this->hFormDisplayOnForm(true))
                                {
                                    $form .= $this->getInputElement();
                                }
                                else if ($this->renderMode('verify') && $this->hFormDisplayOnVerify(true))
                                {
                                    $form .= $this->getVerifyElement();
                                }

                                if (isset($this->field['attributes']['name']))
                                {
                                    array_push($this->fieldNames, $this->field['attributes']['name']);
                                }

                                if (!empty($error))
                                {
                                    $form .= "<p class='hFormError'>".$error."</p>";
                                }

                                if (!$this->hFormJoinInputToNext(false))
                                {
                                    $form .= "</".($this->field['type'] == 'th'? 'th' : 'td').">\n";
                                }

                                # Adjust column counter
                                if (isset($this->field['cell']['colspan']))
                                {
                                    $c += $this->field['cell']['colspan'] - 1;
                                }
                            }
                            else
                            {
                                $GLOBALS['hFramework']->warning("Required field 'type' not specified.", __FILE__, __LINE__);
                            }
                        }

                        $this->options = array();

                        if (isset($this->table[$r]) && isset($this->table[$r][$c]))
                        {
                            unset($this->table[$r][$c]);
                        }
                    }

                    $form .= "                        </tr>\n";
                }

                $form .=
                    "                    </tbody>\n".
                    "                </table>\n".
                    "            </fieldset>\n".
                    "        </div>\n";
            }

            $form .= "    </div>\n";
        }

        $form .= $this->hiddenFields;

        if ($this->hFormIdentifier)
        {
            $form .= "    <input type='hidden' name='{$this->hFormIdentifier}' value='1' />\n";
        }

        if ($this->hFormSessionId(false))
        {
            $form .= "    <input type='hidden' name='".session_name()."' value='".session_id()."' />\n";
        }

        if (!empty($this->hFormAppendCallback))
        {
            $form .= call_user_func($this->hFormAppendCallback, $this->hFormAppendCallbackArguments);
        }

        if ($this->hFormElement(true) && $this->hFormClosingTag(true))
        {
            $form .= "</form>\n";
        }

        if ($this->hFormHasErrors)
        {
            $form = $this->hFormErrorText(
                "<div class='hFormHasErrors'>\n".
                "   <p>\n".
                "       Some errors were detected in the information you submitted.\n".
                "       Please review and correct the errors, then try submitting again.\n".
                "   </p>\n".
                "</div>\n"
            ).
            $form;
        }

        $form .= "<!-- End Hot Toddy Form Framework Output -->\n\n";

        if ($this->hFormAutoLoadHeaders(true))
        {
            $this->setFormHeaders();
        }

        return $form;
    }

    private function getVerifyElement()
    {
        $value = $this->getElementValue();
        $form  = '';

        switch ($this->field['type'])
        {
            case 'plugin':
            {
                $this->plugins[$this->hPlugin] = $GLOBALS['hFramework']->plugin($this->hPlugin);
                $form .= $this->plugins[$this->hPlugin]->hForm($this->field);
                break;
            }
            case 'h6':
            case 'h5':
            case 'h4':
            case 'h3':
            case 'h2':
            case 'h1':
            {
                $form .= "<".$this->field['type'].$this->getAttributes()."><span>{$value}</span></".$this->field['type'].">";
                break;
            }
            case 'label':
            case 'label -L':
            case 'help':
            case 'html':
            case 'th':
            {
                $form .= $this->translate($value)."\n";
                break;
            }
            case 'wysiwyg':
            case 'textarea':
            case 'text':
            case 'email':
            case 'url':
            case 'number':
            case 'range':
            case 'date':
            case 'month':
            case 'week':
            case 'time':
            case 'datetime':
            case 'datetime-local':
            case 'search':
            case 'color':
            case 'tel':
            {
                $form .= $this->formatText();
                break;
            }
            case 'password':
            {
                $form .= ' '.$this->hFormVerifyPasswordMessage('[password not revealed for security purposes]');
                break;
            }
            case 'file':
            case 'image':
            case 'hidden':
            case 'submit':
            case 'reset':
            case 'button':
            {
                break;
            }
            case 'select':
            {
                $form .= $this->getVerifyOptions();
                break;
            }
            case 'checkbox':
            {
                break;
            }
            case 'radio':
            {
                if (isset($this->field['attributes']['options']) && is_array($this->field['attributes']['options']))
                {
                    $i = 0;

                    foreach ($this->field['attributes']['options'] as $optionValue => $optionLabel)
                    {
                        if ($this->hFormUseLabelAsValue(false))
                        {
                            $optionValue = $optionLabel;
                        }

                        if ($this->hFormBlockWrapper(true))
                        {
                            $form .= "<div>";
                        }

                        if ($value == $optionValue || is_array($this->field['value']) && in_array($optionValue, $this->field['value']))
                        {
                            $form .= $optionLabel;
                        }

                        if ($this->hFormBlockWrapper(true))
                        {
                            $form .= "</div>";
                        }

                        $i++;
                    }
                }
                break;
            }
            default:
            {
                $this->warning("Invalid field type specified '{$this->field['type']}'.", __FILE__, __LINE__);
            }
        }

        return $form;
    }

    private function translate($value)
    {
        if ($GLOBALS['hFramework']->hLanguageId > 1)
        {
            $colon = false;

            $value = trim($value);

            if (substr($value, -1) == ':')
            {
                $value = substr($value, 0, -1);

                $colon = true;
            }

            $value = $GLOBALS['hFramework']->translate($value);

            if ($colon)
            {
                $value .= ':';
            }
        }

        return $value;
    }

    private function getInputElement()
    {
        $form = '';
        $value = $this->getElementValue();

        switch ($this->field['type'])
        {
            case 'plugin':
            {
                $form .= "<div class='hFormPluginWrapper'>";
                break;
            }
            case 'h6':
            case 'h5':
            case 'h4':
            case 'h3':
            case 'h2':
            case 'h1':
            {
                $form .= "<div class='hFormHeadingWrapper'>";
            }
            case 'label':
            case 'label -L':
            {
                $form .= "<div class='hFormLabelWrapper'>";
                break;
            }
            case 'help':
            case 'html':
            case 'custom':
            case 'th':
            {
                $form .= "<div class='hFormCustomWrapper'>";
                break;
            }
            case 'wysiwyg':
            {
                $form .= "<div class='hFormInputWrapper hFormWYSIWYGWrapperOuter'>";
                break;
            }
            case 'textarea':
            {
                $form .= "<div class='hFormInputWrapper hFormTextareaWrapperOuter'>";
                break;
            }
            case 'image':
            case 'text':
            case 'file':
            case 'password':
            case 'hidden':
            case 'submit':
            case 'reset':
            case 'email':
            case 'url':
            case 'number':
            case 'range':
            case 'date':
            case 'month':
            case 'week':
            case 'time':
            case 'datetime':
            case 'datetime-local':
            case 'search':
            case 'color':
            case 'tel':
            {
                $form .= "<div class='hFormInputWrapper hFormGenericInputWrapper'>";
                break;
            }
            case 'select':
            {
                $form .= "<div class='hFormInputWrapper hFormSelectWrapper'>";
                break;
            }
            case 'checkbox':
            {
                $form .= "<div class='hFormInputWrapper hFormCheckboxWrapper'>";
                break;
            }
            case 'radio':
            {
                $form .= "<div class='hFormInputWrapper hFormRadioWrapperOuter'>";
                break;
            }
            case 'button':
            {
                $form .= "<div class='hFormInputWrapper hFormButtonWrapper'>";
                break;
            }
        }

        switch ($this->field['type'])
        {
            case 'wysiwyg':
            {
                $form .= "<div class='hFormWYSIWYGWrapper'>";
                break;
            }
            case 'textarea':
            {
                $form .= "<div class='hFormTextareaWrapper'>";
                break;
            }
        }

        if ($this->hFormPrependInput(''))
        {
            $form .= $this->hFormPrependInput;
        }

        switch ($this->field['type'])
        {
            case 'plugin':
            {
                $this->plugins[$this->hPlugin] = $GLOBALS['hFramework']->plugin($this->hPlugin);
                $form .= $this->plugins[$this->hPlugin]->hForm($this->field);
                break;
            }
            case 'h6':
            case 'h5':
            case 'h4':
            case 'h3':
            case 'h2':
            case 'h1':
            case 'label':
            case 'label -L':
            {
                $type = str_replace(' -L', '', $this->field['type']);
                $form .= '<'.$type.$this->getAttributes()."><span>".$this->translate($this->getElementValue()).'</span></'.$type.'>';
                break;
            }
            case 'help':
            case 'html':
            case 'custom':
            case 'th':
            {
                $form .= $this->translate($this->getElementValue());
                break;
            }
            case 'wysiwyg':
            {
                $form .= "<textarea".$this->getAttributes().">".$value."</textarea>";
                break;
            }
            case 'textarea':
            {
                $form .= "<textarea".$this->getAttributes().">".$value."</textarea>";
                break;
            }
            case 'image':
            case 'text':
            case 'file':
            case 'password':
            case 'hidden':
            case 'submit':
            case 'reset':
            case 'email':
            case 'url':
            case 'number':
            case 'range':
            case 'date':
            case 'month':
            case 'week':
            case 'time':
            case 'datetime':
            case 'datetime-local':
            case 'search':
            case 'color':
            case 'tel':
            {
                $form .= "<input type='{$this->field['type']}'".$this->getAttributes()." value='".$this->translate($this->getAttribute('value', $value))."' />";
                break;
            }
            case 'select':
            {
                $form .= "<select".$this->getAttributes().">".$this->getInputOptions()."</select>";
                break;
            }
            case 'checkbox':
            {
                $form .= "<input type='checkbox'".$this->getAttributes()." value='1'".(!empty($value)? " checked='checked'" : '')." />";

                if ($this->hFormLabel)
                {
                    $form .= " <label for='{$this->field['attributes']['id']}'>{$this->hFormLabel}</label>\n";
                }

                break;
            }
            case 'radio':
            {
                 $form .= "<div class='hFormRadioWrapper'>";

                if (isset($this->field['attributes']['options']) && is_array($this->field['attributes']['options']))
                {
                    $i  = 0;
                    $id = $this->field['attributes']['id'];

                    $form .= "\n";

                    foreach ($this->field['attributes']['options'] as $value => $label)
                    {
                        if ($this->hFormUseLabelAsValue(false) || $this->hFormOptionLabelIsValue(false))
                        {
                            $value = $label;
                        }

                        if ($this->hFormBlockWrapper(true))
                        {
                            $form .= "<div class='hFormRadioInput'>";
                        }

                        $this->field['attributes']['id'] = $id.'-'.$value;

                        $form .= "<input type='{$this->field['type']}' value='".$this->getAttribute('value', $value)."'".$this->getAttributes();

                        if ($this->field['value'] == $value || is_array($this->field['value']) && in_array($value, $this->field['value']))
                        {
                            $form .= " checked='".$this->getAttribute('checked', 'checked')."'";
                        }

                        $form .= " />";

                        if (!empty($label))
                        {
                            $form .= "<label for='".$this->getAttribute('for', $this->field['attributes']['id'])."'>".$GLOBALS['hFramework']->translate($label)."</label>";
                        }

                        if ($this->hFormBlockWrapper(true))
                        {
                            $form .= "</div>\n";
                        }

                        $i++;
                    }
                }

                $form .= "</div>";

                break;
            }
            case 'button':
            {
                $form .= "<button".$this->getAttributes().">".$GLOBALS['hFramework']->translate($this->getElementValue())."</button>\n";
                break;
            }
            default:
            {
                $GLOBALS['hFramework']->warning("Invalid field type specified '{$this->field['type']}'.", __FILE__, __LINE__);
            }
        }

        if ($this->field['type'] == 'textarea')
        {
            $form .= "</div>";
        }

        if ($this->field['type'] == 'wysiwyg')
        {
            $form .= "<div class='hFormWYSIWYGAdjacent'></div></div>";
        }

        if ($this->hFormRequiredIndicator(true) && $this->renderMode('form') && isset($this->field['validation']) && is_array($this->field['validation']))
        {
            foreach ($this->field['validation'] as $x => $validation)
            {
                if (isset($validation['type']) && $validation['type'] == 'required')
                {
                    $form .= "<span class='hFormRequiredIndicator'>*</span>";
                    break;
                }
            }
        }

        if ($this->hFormAppendInput(''))
        {
            $form .= $this->hFormAppendInput;
        }

        $form .= "</div>";

        return $form;
    }

    public function getFieldNames()
    {
        return $this->fieldNames;
    }

    private function getVerifyOptions()
    {
        $options = $this->field['attributes']['options'];
        $value   = $this->getElementValue();

        $form = '';

        if (isset($options) && is_array($options))
        {
            foreach ($options as $optionValue => $optionLabel)
            {
                if ($value == $optionValue || is_array($value) && in_array($optionValue, $value))
                {
                    $form .= "<div>".((is_array($optionLabel) && isset($optionLabel['text']))? $optionLabel['text'] : $optionLabel)."</div>\n";
                }
            }
        }

        return $form;
    }

    private function getInputOptions()
    {
        $options = $this->field['attributes']['options'];
        $value   = $this->getElementValue();

        $form = '';

        if (isset($options) && is_array($options))
        {
            foreach ($options as $optionValue => $optionLabel)
            {
                $setValue = ($this->getAttribute('value', $this->hFormUseLabelAsValue(false) || $this->hFormOptionLabelIsValue(false)? $optionLabel : $optionValue));

                $form .= "<option value='{$setValue}'";

                if (is_array($optionLabel) && isset($optionLabel['attributes']))
                {
                    $form .= $this->getAttributes($optionLabel['attributes']);
                }

                if ($value == $setValue || is_array($value) && in_array($setValue, $value))
                {
                    $form .= " selected='".$this->getAttribute('selected', 'selected')."'";
                }

                if ($this->hFormStates(false))
                {
                    $hLocationStateCode = $this->hDatabase->getResult(
                        "SELECT `hLocationStateCode`
                           FROM `hLocationStates`
                          WHERE `hLocationStateId` = ". (int) $setValue
                    );

                    $form .= " title='{$hLocationStateCode}'";
                }

                $form .= ">".((is_array($optionLabel) && isset($optionLabel['text']))? $optionLabel['text'] : $optionLabel)."</option>";
            }
        }

        return $form;
    }

    public function &resetForm()
    {
        $this->reset();

        return $this;
    }

    public function &reset()
    {
        if ($this->hWYSIWYG)
        {
            $hWYSIWYG = &$this->hWYSIWYG;
        }

        $this->index = 0;
        $this->field = array();
        $this->fields = array();
        $this->compiled = array();
        $this->table = array();
        $this->hForm = null;
        $this->hFormSnapShot = null;
        $this->tableColumn = 0;
        $this->divisionCounter = 0;
        $this->fieldsetCounter = 0;
        $this->div = null;
        $this->fieldset = null;
        $this->render = 'form';
        $this->fieldsTemp = array();
        $this->defineCell = array();
        $this->defineTable = array();
        $this->defineCols = 2;
        $this->defineAttributes = array();
        $this->setVariable = array();
        $this->setValue = null;
        $this->callArguments = array();
        $this->setFormOptions = true;
        $this->hiddenFields = '';
        $this->field = null;
        $this->options = null;
        $this->css = null;
        $this->javascript = null;
        $this->plugins = null;
        $this->element = null;
        $this->validation = array();
        $this->fieldNames = array();
        $this->formLegends = array();

        $this->hFormRender = 'form';

        if ($this->hWYSIWYG)
        {
            $this->hWYSIWYG = &$hWYSIWYG;
        }

        return $this;
    }

    private function &setElementValue()
    {
        if (!empty($this->field['attributes']['name']) && empty($this->field['value']) && !empty($_POST))
        {
            $this->field['value'] = $this->getHTTPVariableFromName($this->field['attributes']['name']);
        }

        return $this;
    }

    private function &setId($r, $c)
    {
        if (!isset($this->table[$r][$c]['attributes']['id']) && !empty($this->table[$r][$c]['attributes']['name']))
        {
            $this->table[$r][$c]['attributes']['id'] = str_replace(array('[', ']'), '', $this->table[$r][$c]['attributes']['name']);
        }

        return $this;
    }

    protected function strToWord($word, $sep = '_')
    {
        return(trim(str_replace(' ', $sep, strToLower(strip_tags(str_replace(array('[', ']', '/', '\\', ','), '', $word))))));
    }

    private function &applyOptionsToValue()
    {
        !is_array($this->field['value'])? $this->applyOptions($this->field['value']) : array_walk($this->field['value'], array($this, 'applyOptions'));

        return $this;
    }

    private function &applyOptions(&$value)
    {
        if ($this->hFormPrepend)
        {
            $value = $this->hFormPrepend.$value;
        }

        if ($this->hFormAppend)
        {
            $value .= $this->hFormAppend;
        }

        if ($this->hFormNL2BR)
        {
            $value = nl2br($value);
        }

        return $this;
    }

    protected function attributeExists(&$attribute, $default = null)
    {
        if (!isset($attribute) && !empty($default))
        {
            $attribute = $default;
            return true;
        }
        else if (!isset($attribute))
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    protected function compileTable(&$cols, $d, $f)
    {
        $this->table = array();

        unset($this->fields[$d][$f]['attributes'], $this->fields[$d][$f]['legend'], $this->fields[$d][$f]['cols'], $this->fields[$d][$f]['table']);

        $i = 0; # Cell counter
        $r = 1; # Row counter
        $c = 1; # Column counter

        # Get the number of cells
        foreach($this->fields[$d][$f] as $n => $value)
        {
            if (isset($this->fields[$d][$f][$n]['type']) && $this->fields[$d][$f][$n]['type'] == 'hidden')
            {
                # All hidden fields are gathered and placed at the end of the form.
                $hiddenInputValue = $this->getAttribute('value', $this->fields[$d][$f][$n]['value']);
                $this->hiddenFields .= "<input type='hidden' value='{$hiddenInputValue}'".$this->getAttributes($this->fields[$d][$f][$n]['attributes'])." />\n";

                array_push($this->fieldNames, $this->fields[$d][$f][$n]['attributes']['name']);
            }
            else
            {
                switch (true)
                {
                    case (isset($this->fields[$d][$f][$n]['cell']['rowspan']) && isset($this->fields[$d][$f][$n]['cell']['colspan'])):
                    {
                        $i += ($this->fields[$d][$f][$n]['cell']['rowspan'] * $this->fields[$d][$f][$n]['cell']['colspan']);
                        break;
                    }
                    case (!isset($this->fields[$d][$f][$n]['cell']['colspan']) && isset($this->fields[$d][$f][$n]['cell']['rowspan'])):
                    {
                        $i += $this->fields[$d][$f][$n]['cell']['rowspan'];
                        break;
                    }
                    case (isset($this->fields[$d][$f][$n]['cell']['colspan']) && !isset($this->fields[$d][$f][$n]['cell']['rowspan'])):
                    {
                        $i += $this->fields[$d][$f][$n]['cell']['colspan'];
                        break;
                    }
                    default:
                    {
                        $i++;
                    }
                }

                $this->table[$r][$c] = isset($this->table[$r][$c])? array_merge($this->table[$r][$c], $this->fields[$d][$f][$n]) : $this->fields[$d][$f][$n];

                switch (true)
                {
                    case (isset($this->fields[$d][$f][$n]['cell']['rowspan']) && isset($this->fields[$d][$f][$n]['cell']['colspan'])):
                    {
                        $this->examineRowspan($r, $c, $this->fields[$d][$f][$n]['cell']['colspan']);
                        $c += $this->fields[$n]['cell']['colspan'];
                        break;
                    }
                    case (isset($this->fields[$d][$f][$n]['cell']['rowspan']) && !isset($this->fields[$d][$f][$n]['cell']['colspan'])):
                    {
                        $this->examineRowspan($r, $c);
                        $c++;
                        break;
                    }
                    case (isset($this->fields[$d][$f][$n]['cell']['colspan']) && !isset($this->fields[$d][$f][$n]['cell']['rowspan'])):
                    {
                        $c += $this->fields[$d][$f][$n]['cell']['colspan'];
                        break;
                    }
                    case (!isset($this->fields[$d][$f][$n]['cell']['colspan']) && !isset($this->fields[$d][$f][$n]['cell']['rowspan'])):
                    {
                        $c++;
                        break;
                    }
                }

                # Probe for skipped columns that appear directly after this column on this row
                $this->probeForSkippedColumns($r, $c, $cols, $c);

                if ($c > $cols)
                {
                    $c = 1;
                    $r++;

                    # Probe for skipped columns at the start of the next row.
                    $this->probeForSkippedColumns($r, $c, $cols);
                }
            }
        }

        # Divide number of cells by number of columns
        return $i / $cols;
    }

    private function &probeForSkippedColumns($r, &$c, $cols, $start = 1)
    {
        # Increment the column counter for every skipped column
        for ($i = $start; $i <= $cols; $i++)
        {
            if (isset($this->table[$r][$i]['skip']))
            {
                $c++;
            }
            else
            {
                break;
            }
        }

        return $this;
    }

    private function &examineRowspan($r, $c, $colspan = 1)
    {
        # Copy the current row count to $i
        # Increment $i for each row that is spanned.
        # Start on the next row, since the current row's information is already compiled.

        for ($i = ($r + 1); $i <= ($r + ($this->table[$r][$c]['cell']['rowspan'] - 1)); $i++)
        {
            for ($w = $c; $w <= (($colspan - 1) + $c); $w++)
            {
                $this->table[$i][$w]['skip'] = true;
            }
        }

        return $this;
    }

    private function getAttributes($attributes = array())
    {
        if (!count($attributes))
        {
            $attributes = $this->getElementVariable();
        }

        if (isset($attributes) && is_array($attributes))
        {
            $attrs = '';

            foreach ($attributes as $attribute => $value)
            {
                if (empty($value) && isset($this->attributes['validation'][$attribute]['default']))
                {
                    $value = $this->attributes['validation'][$attribute]['default'];
                }

                if ($attribute == 'class' && $this->hFormFieldHasErrors(false))
                {
                    $value .= ' hFormError';
                }

                if (!is_array($value))
                {
                    $attrs .= " {$attribute}='".$this->getAttribute($attribute, $value)."'";
                }
            }

            return $attrs;
        }
        else
        {
            return '';
        }
    }

    private function getAttribute($attribute, $value)
    {
        if (isset($this->attributes['validation'][$attribute]['validation']) && !empty($this->attributes['validation'][$attribute]['validation']))
        {
            # Explicitly suppress any errors in syntax
            if (substr($value, 0, 1) == '@')
            {
                return substr($value, 1);
            }
            else
            {
                foreach ($this->attributes['validation'][$attribute]['validation'] as $pattern => $error)
                {
                    if (!preg_match($pattern, $value))
                    {
                        if (!empty($value))
                        {
                        }
                    }
                }
            }
        }

        return $value;
    }

    private function &setElement($element)
    {
        $this->element = $element;

        return $this;
    }

    private function &getElementVariable($key = null)
    {
         switch ($this->element)
         {
             case 'form':      return $this->form[$key? $key : 'attributes'];
             case 'div':       return $this->fields[$this->divisionCounter][$key? $key : 'attributes'];
             case 'divLegend': return $this->fields[$this->divisionCounter]['divLegend'][$key ? $key : 'attributes'];
             case 'fieldset':  return $this->fields[$this->divisionCounter][$this->fieldsetCounter][$key? $key : 'attributes'];
             case 'legend':    return $this->fields[$this->divisionCounter][$this->fieldsetCounter]['legend'][$key? $key : 'attributes'];
             case 'table':     return $this->fields[$this->divisionCounter][$this->fieldsetCounter]['table'][$key? $key : 'attributes'];
             case 'col':       return $this->fields[$this->divisionCounter][$this->fieldsetCounter]['table']['columns'][$this->tableColumn];
             case 'td':        return $this->field[$key? $key : 'cell'];
             case 'input':     return $this->field[$key? $key : 'attributes'];
             default:          return $key;
         }
    }

    private function getElementValue()
    {
        return $this->getElementVariable('value');
    }

    private function &setAttributeDefault($attribute, $default)
    {
        $element = &$this->getElementVariable();

        if (empty($element[$attribute]) || !isset($element[$attribute]))
        {
            $element[$attribute] = $default;
        }

        return $this;
    }

    private function underlineAccessKey($label, $access_key)
    {
        # Reformat the string so that the access key is underlined.
        if (false !== ($pos = stripos($label, $access_key)))
        {
            return (substr($label, 0, $pos).'<u>'.(substr($label, $pos, 1) === strtoupper($access_key)? strtoupper($access_key) : strtolower($access_key)).'</u>'.substr($label, $pos + 1));
        }
        else
        {
            $this->warning("The access key '{$access_key}' does not exist in the label text '{$label}'.", __FILE__, __LINE__);
        }

        return '';
    }

    private function formatText()
    {
        return nl2br($this->getElementValue());
    }

    private function &arrayMustExist(&$array)
    {
        if (!isset($array) || !is_array($array))
        {
            $array = array();
        }

        return $this;
    }

    private function &getHTTPVariableFromName($field)
    {
        $input = '';

        if (!strstr($field, '[]') && !empty($field))
        {
            # name[key1][key2]
            # $_POST['name']['key1']['key2']
            # $_POST['name']

            # step 1. replace ][ with '||'
            # step 2. replace ]  with ']
            # step 3. replace [  with ']['
            # step 4. append  ['
            # step 5. replace '||' with ']['

            $field = str_replace('][', "'||'", $field);

            if (strstr($field, ']'))
            {
                $field = str_replace(']', "']", $field);
            }
            else
            {
                $field .= "']";
            }

            $field = str_replace('[', "']['", $field);

            $field = "['".$field;

            $field = str_replace("'||'", "']['", $field);

            # Technically I could parse the number indices too
            # It would just require a reg-expression
            #
            # It ought to work like this.
            eval(
               "if (isset(\$_POST{$field}))
                {
                    \$input = &\$_POST{$field};
                }
                else if (isset(\$_GET{$field}))
                {
                    \$input = &\$_GET{$field};
                }"
            );

            return $input;
        }
        else
        {
            $GLOBALS['hFramework']->notice('Failed to get field value from HTTP variables, field is empty or auto-incrementing array key.', __FILE__, __LINE__);
        }

        return $input;
    }

    private function isMeasurement($string)
    {
        $units = array('px', '%', 'em', 'pt', 'in', 'cm', 'mm', 'pc', 'ex');

        foreach ($units as $unit)
        {
            if (substr($string, -strlen($unit)) == $unit)
            {
                return true;
            }
        }

        return false;
    }

    private function &pushDiv($attributes = array(), $legend = null)
    {
        if (!is_array($this->fields))
        {
            $this->fields = array();
        }

        $division = array(
            'attributes' => $attributes
        );

        if (!empty($legend))
        {
            $division['divLegend']['value'] = $legend;
        }

        array_push($this->fields, $division);

        $this->div++;
        $this->fieldset = 0;

        return $this;
    }

    private function &pushFieldset($legend, $attributes = array())
    {
        if (!isset($this->fields[($this->div-1)]))
        {
            $GLOBALS['hFramework']->warning('No <div> exists to place a <fieldset> in.', __FILE__, __LINE__);
        }
        else
        {
            array_push(
                $this->fields[($this->div-1)],
                array_merge(
                    array(
                        'legend' => array(
                            'value' => $legend
                        ),
                        'cols' => $this->defineCols,
                        'attributes' => $attributes
                    ),
                    $this->defineTable
                )
            );

            $this->defineTable = array();
            $this->fieldset++;
        }

        $this->defineCols = 2;

        return $this;
    }

    private function validation($condition = true, Array $validation = array())
    {
        if ($condition)
        {
            if (!is_array($validation))
            {
                $GLOBALS['hFramework']->warning('Argument $validation must be an array.', __FILE__, __LINE__);
            }

            return array('validation' => $validation);
        }
        else
        {
            return array();
        }
    }

    private function validate()
    {
        $this->fieldsTemp = $this->fields;

        if ($this->hFormIdentifier)
        {
            $field = $this->getHTTPVariableFromName($this->hFormIdentifier);
        }

        if (!empty($field))
        {
            if ($this->hFormValidate(true))
            {
                for ($d = 0, $dc = count($this->fields); $d < $dc; $d++)
                {
                    if (empty($this->fields[$d]))
                    {
                        continue;
                    }

                    for ($f = 0, $fc = count($this->fields[$d]); $f < $fc; $f++)
                    {
                        if (empty($this->fields[$d][$f]))
                        {
                            continue;
                        }

                        $cols = isset($this->fields[$d][$f]['cols'])? $this->fields[$d][$f]['cols'] : 2;

                        $this->compileTable($cols, $d, $f);

                        # Row counter
                        foreach ($this->table as $column)
                        {
                            # Column counter
                            foreach ($column as $cell)
                            {
                                $this->field = &$cell;

                                if (!isset($this->field['type']))
                                {
                                    continue;
                                }

                                switch ($this->field['type'])
                                {
                                    case 'label': $cont = true; break;
                                    case 'html' : $cont = true; break;
                                }

                                if (isset($cont))
                                {
                                    unset($cont);
                                    continue;
                                }

                                if (isset($this->field['validation']) && $this->validateField())
                                {

                                    $this->hFormRender    = 'form';
                                    $this->hFormHasErrors = true;
                                    $this->validationCleanup();

                                    return false;
                                }
                            }
                        }

                        $this->table = array();
                    }
                }

                # If no errors have been encountered, switch the rendering mode to verify.
                if ($this->hFormRenderOverride)
                {
                    $this->hFormRender = 'form';
                }
                else
                {
                    $this->hFormRender = 'verify';
                }
            }
            else
            {
                $this->hFormRender = 'form';
            }
        }

        $this->validationCleanup();

        return true;
    }

    public function renderMode($type, $set = false)
    {
        return ($this->hFormRender == $type);
    }

    public function passesValidation()
    {
        return $this->renderMode('verify');
    }

    public function passedValidation()
    {
        return $this->renderMode('verify');
    }

    private function &validationCleanup()
    {
         $this->fields = $this->fieldsTemp;
         $this->fieldsTemp = array();
         $this->hidden = '';

         return $this;
    }

    private function validateField()
    {
        if ($this->hFormAutoValues(true))
        {
            $this->setElementValue();
        }

        $value = $this->field['value'];

        if (is_array($this->field['validation']))
        {
            foreach ($this->field['validation'] as $array)
            {
                switch ($array['type'])
                {
                    case 'required':
                    {
                       if (empty($value))
                       {
                           return $array['error'];
                       }
                       break;
                    }
                    case 'compare':
                    {
                        switch ($array['operator'])
                        {
                            case '>':
                            case '<':
                            case '<=':
                            case '>=':
                            case '==':
                            case '===':
                            case '!=':
                            case '!==':
                            case '&':
                            case '|':
                            case '||':
                            case '&&':
                            case '%':
                            case '+':
                            case '*':
                            case '/':
                            case '-':
                            {
                                if (!is_numeric($value))
                                {
                                    $value = strlen($value);
                                }

                                # Careful, don't allow arbitrary code execution.
                                if (eval("return(!((int) {$value} {$array['operator']} (int) {$array['compare']}));"))
                                {
                                    return $array['error'];
                                }

                                break;
                            }
                            default:
                            {
                                $GLOBALS['hFramework']->warning("Invalid operator '{$array['operator']}' specified for comparison.", __FILE__, __LINE__);
                            }
                        }
                        break;
                    }
                    case 'callback':
                    {
                        if (method_exists($array['object'], $array['method']))
                        {
                            if (!$array['object']->$array['method']($value))
                            {
                                return $array['error'];
                            }
                        }
                        else
                        {
                            $GLOBALS['hFramework']->warning("Object method '{$array['method']}' does not exist.", __FILE__, __LINE__);
                        }

                        break;
                    }
                }
            }
        }

        return false;
    }

    private function &pushValidation(&$array)
    {
        $this->validation[] = &$array;

        return $this;
    }

    public function &addRequiredField($error)
    {
        $a = array(
            'type' => 'required',
            'error' => $error
        );

        $this->pushValidation($a);

        return $this;
    }

    public function &addValidationByComparison($error, $operator, $value)
    {
        $a = array(
            'type'     => 'compare',
            'error'    => $error,
            'operator' => $operator,
            'compare'  => $value
        );

        $this->pushValidation($a);

        return $this;
    }

    public function &addValidationByCallback($error, &$object, $method)
    {
        $a = array(
            'type'   => 'callback',
            'error'  => $error,
            'object' => &$object,
            'method' => $method,
        );

        $this->pushValidation($a);

        return $this;
    }
}

?>