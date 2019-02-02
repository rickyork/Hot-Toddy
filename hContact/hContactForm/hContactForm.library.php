<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Contact Form
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
# <h1>Contact Form API</h1>
# <p>
#   <var>hContactForm</var> provides a reusable, extensible, highly configurable
#   interface for providing common contact form inputs to users.  It provides
#   HTML form inputs, optional validation, as well as methods to easily save
#   contact information in the contact database.
# </p>
# <h2>hContactFormTitle Interface</h2>
# <p>
#   The <var>hContactFormTitle</var> interface provides a way to easily replace
#   the <var>hContactTitle</var> field.
# </p>
# <h2>Contact Form Fields</h2>
# <p>
#   Contact form fields are defined and laid out using the <var>hContactForm::$methods</var>
#   member property.
# </p>
# <table>
#   <thead>
#   </thead>
#   <tbody>
#       <tr class='hDocumentationTableHeading'>
#           <td><b>Field:</b></td>
#           <td class='code' colspan='2'>hContactFirstName</td>
#       </tr>
#       <tr>
#           <td><b>Method:</b></td>
#           <td colspan='2'><a href='#getFirstName' class='code'>getFirstName()</a></td>
#       </tr>
#       <tr>
#           <td rowspan='4'><b>Options:</b></td>
#           <td>label</td>
#           <td>First Name:</td>
#       </tr>
#       <tr>
#           <td>required</td>
#           <td class='code'>true</td>
#       </tr>
#       <tr>
#           <td>enabled</td>
#           <td class='code'>true</td>
#       </tr>
#       <tr>
#           <td>requiredError</td>
#           <td>You did not specify your first name.</td>
#       </tr>
#       <tr class='hDocumentationTableHeading'>
#           <td><b>Field:</b></td>
#           <td class='code' colspan='2'>hContactLastName</td>
#       </tr>
#       <tr>
#           <td><b>Method:</b></td>
#           <td colspan='2'><a href='#getLastName' class='code'>getLastName()</a></td>
#       </tr>
#       <tr>
#           <td rowspan='4'><b>Options:</b></td>
#           <td>label</td>
#           <td>Last Name:</td>
#       </tr>
#       <tr>
#           <td>required</td>
#           <td class='code'>true</td>
#       </tr>
#       <tr>
#           <td>enabled</td>
#           <td class='code'>true</td>
#       </tr>
#       <tr>
#           <td>requiredError</td>
#           <td>You did not specify your last name.</td>
#       </tr>
#       <tr class='hDocumentationTableHeading'>
#           <td><b>Field:</b></td>
#           <td class='code' colspan='2'>hContactCompany</td>
#       </tr>
#       <tr>
#           <td><b>Method:</b></td>
#           <td colspan='2'><a href='#getCompany' class='code'>getCompany()</a></td>
#       </tr>
#       <tr>
#           <td rowspan='4'><b>Options:</b></td>
#           <td>label</td>
#           <td>Company:</td>
#       </tr>
#       <tr>
#           <td>required</td>
#           <td class='code'>true</td>
#       </tr>
#       <tr>
#           <td>enabled</td>
#           <td class='code'>true</td>
#       </tr>
#       <tr>
#           <td>requiredError</td>
#           <td>You did not specify your company.</td>
#       </tr>
#       <tr class='hDocumentationTableHeading'>
#           <td><b>Field:</b></td>
#           <td class='code' colspan='2'>hContactDepartment</td>
#       </tr>
#       <tr>
#           <td><b>Method:</b></td>
#           <td colspan='2'><a href='#getDepartment' class='code'>getDepartment()</a></td>
#       </tr>
#       <tr>
#           <td rowspan='4'><b>Options:</b></td>
#           <td>label</td>
#           <td>Department:</td>
#       </tr>
#       <tr>
#           <td>required</td>
#           <td class='code'>false</td>
#       </tr>
#       <tr>
#           <td>enabled</td>
#           <td class='code'>false</td>
#       </tr>
#       <tr>
#           <td>requiredError</td>
#           <td>You did not specify a department.</td>
#       </tr>
#       <tr class='hDocumentationTableHeading'>
#           <td><b>Field:</b></td>
#           <td class='code' colspan='2'>hContactWebsite</td>
#       </tr>
#       <tr>
#           <td><b>Method:</b></td>
#           <td colspan='2'><a href='#getWebsite' class='code'>getWebsite()</a></td>
#       </tr>
#       <tr>
#           <td rowspan='4'><b>Options:</b></td>
#           <td>label</td>
#           <td>Website:</td>
#       </tr>
#       <tr>
#           <td>required</td>
#           <td class='code'>false</td>
#       </tr>
#       <tr>
#           <td>enabled</td>
#           <td class='code'>false</td>
#       </tr>
#       <tr>
#           <td>requiredError</td>
#           <td>You did not specify a website.</td>
#       </tr>
#       <tr class='hDocumentationTableHeading'>
#           <td><b>Field:</b></td>
#           <td class='code' colspan='2'>hContactTitle</td>
#       </tr>
#       <tr>
#           <td><b>Method:</b></td>
#           <td colspan='2'><a href='#getTitle' class='code'>getTitle()</a></td>
#       </tr>
#       <tr>
#           <td rowspan='4'><b>Options:</b></td>
#           <td>label</td>
#           <td>Title:</td>
#       </tr>
#       <tr>
#           <td>required</td>
#           <td class='code'>true</td>
#       </tr>
#       <tr>
#           <td>enabled</td>
#           <td class='code'>true</td>
#       </tr>
#       <tr>
#           <td>requiredError</td>
#           <td>You did not specify your title.</td>
#       </tr>
#       <tr class='hDocumentationTableHeading'>
#           <td><b>Field:</b></td>
#           <td class='code' colspan='2'>hContactEmailAddress</td>
#       </tr>
#       <tr>
#           <td><b>Method:</b></td>
#           <td colspan='2'><a href='#getEmailAddress' class='code'>getEmailAddress()</a></td>
#       </tr>
#       <tr>
#           <td rowspan='4'><b>Options:</b></td>
#           <td>label</td>
#           <td>Email Address:</td>
#       </tr>
#       <tr>
#           <td>required</td>
#           <td class='code'>true</td>
#       </tr>
#       <tr>
#           <td>enabled</td>
#           <td class='code'>false</td>
#       </tr>
#       <tr>
#           <td>requiredError</td>
#           <td>You did not specify your email address.</td>
#       </tr>
#       <tr class='hDocumentationTableHeading'>
#           <td><b>Field:</b></td>
#           <td class='code' colspan='2'>hLocationCountryId</td>
#       </tr>
#       <tr>
#           <td><b>Method:</b></td>
#           <td colspan='2'><a href='#getEmailAddress' class='code'>getEmailAddress()</a></td>
#       </tr>
#       <tr>
#           <td rowspan='4'><b>Options:</b></td>
#           <td>label</td>
#           <td>
#               <i>An image of the selected country's flag (U.S. is the default flag)</i>
#               <p><img src='/images/icons/32x32/flags/us.png' alt='U.S. Flag' style='width: 32px;' /></p>
#           </td>
#       </tr>
#       <tr>
#           <td>required</td>
#           <td class='code'>true</td>
#       </tr>
#       <tr>
#           <td>enabled</td>
#           <td class='code'>true</td>
#       </tr>
#       <tr>
#           <td>requiredError</td>
#           <td>You did not specify your country.</td>
#       </tr>
#       <tr class='hDocumentationTableHeading'>
#           <td><b>Field:</b></td>
#           <td class='code' colspan='2'>hContactAddressStreet</td>
#       </tr>
#       <tr>
#           <td><b>Method:</b></td>
#           <td colspan='2'><a href='#getStreetAddress' class='code'>getStreetAddress()</a></td>
#       </tr>
#       <tr>
#           <td rowspan='4'><b>Options:</b></td>
#           <td>label</td>
#           <td>Street:</td>
#       </tr>
#       <tr>
#           <td>required</td>
#           <td class='code'>true</td>
#       </tr>
#       <tr>
#           <td>enabled</td>
#           <td class='code'>true</td>
#       </tr>
#       <tr>
#           <td>requiredError</td>
#           <td>You did not specify your street address.</td>
#       </tr>
#       <tr class='hDocumentationTableHeading'>
#           <td><b>Field:</b></td>
#           <td class='code' colspan='2'>hContactAddressCity</td>
#       </tr>
#       <tr>
#           <td><b>Method:</b></td>
#           <td colspan='2'><a href='#getCity' class='code'>getCity()</a></td>
#       </tr>
#       <tr>
#           <td rowspan='4'><b>Options:</b></td>
#           <td>label</td>
#           <td>City:</td>
#       </tr>
#       <tr>
#           <td>required</td>
#           <td class='code'>true</td>
#       </tr>
#       <tr>
#           <td>enabled</td>
#           <td class='code'>true</td>
#       </tr>
#       <tr>
#           <td>requiredError</td>
#           <td>You did not specify a city.</td>
#       </tr>
#       <tr class='hDocumentationTableHeading'>
#           <td><b>Field:</b></td>
#           <td class='code' colspan='2'>hLocationStateId</td>
#       </tr>
#       <tr>
#           <td><b>Method:</b></td>
#           <td colspan='2'><a href='#getState' class='code'>getState()</a></td>
#       </tr>
#       <tr>
#           <td rowspan='4'><b>Options:</b></td>
#           <td>label</td>
#           <td>
#               State:
#               <p>
#                   The label used is changed dynamically with the country selection,
#                   or if no country selection is used, then the label will change with
#                   the country specified for the selection of "states".  The label can
#                   change to "Province:", for example, when the country is set to Canada.
#                   It is "County:" if the country is United Kingdom.  "Emirate:", if the
#                   country is United Arab Emirates, and so on.  There are many, many of these
#                   defined for many countries.
#               </p>
#           </td>
#       </tr>
#       <tr>
#           <td>required</td>
#           <td>
#               <span class='code'>true</span>
#               <p>
#                   Whether or not this field is required depends on whether or not the
#                   selected country has sub-regions defined for selection.
#               </p>
#           </td>
#       </tr>
#       <tr>
#           <td>enabled</td>
#           <td class='code'>true</td>
#       </tr>
#       <tr>
#           <td>requiredError</td>
#           <td>
#               You did not specify a {/$label}.
#               <p>
#                   What is used in place of <var>{/$label}</var> depends on the country selection.
#               </p>
#           </td>
#       </tr>
#       <tr class='hDocumentationTableHeading'>
#           <td><b>Field:</b></td>
#           <td class='code' colspan='2'>hContactAddressPostalCode</td>
#       </tr>
#       <tr>
#           <td><b>Method:</b></td>
#           <td colspan='2'><a href='#getPostalCode' class='code'>getPostalCode()</a></td>
#       </tr>
#       <tr>
#           <td rowspan='4'><b>Options:</b></td>
#           <td>label</td>
#           <td>Postal Code:</td>
#       </tr>
#       <tr>
#           <td>required</td>
#           <td class='code'>true</td>
#       </tr>
#       <tr>
#           <td>enabled</td>
#           <td class='code'>true</td>
#       </tr>
#       <tr>
#           <td>requiredError</td>
#           <td>You did not specify your postal code.</td>
#       </tr>
#       <tr class='hDocumentationTableHeading'>
#           <td><b>Field:</b></td>
#           <td class='code' colspan='2'>hLocationCountyId</td>
#       </tr>
#       <tr>
#           <td><b>Method:</b></td>
#           <td colspan='2'><a href='#getCounty' class='code'>getCounty()</a></td>
#       </tr>
#       <tr>
#           <td rowspan='4'><b>Options:</b></td>
#           <td>label</td>
#           <td>Postal Code:</td>
#       </tr>
#       <tr>
#           <td>required</td>
#           <td class='code'>false</td>
#       </tr>
#       <tr>
#           <td>enabled</td>
#           <td class='code'>false</td>
#       </tr>
#       <tr>
#           <td>requiredError</td>
#           <td>You did not specify your county.</td>
#       </tr>
#       <tr class='hDocumentationTableHeading'>
#           <td><b>Field:</b></td>
#           <td class='code' colspan='2'>hContactPhoneNumber</td>
#       </tr>
#       <tr>
#           <td><b>Method:</b></td>
#           <td colspan='2'><a href='#getPhoneNumber' class='code'>getPhoneNumber()</a></td>
#       </tr>
#       <tr>
#           <td rowspan='4'><b>Options:</b></td>
#           <td>label</td>
#           <td>Phone Number:</td>
#       </tr>
#       <tr>
#           <td>required</td>
#           <td class='code'>true</td>
#       </tr>
#       <tr>
#           <td>enabled</td>
#           <td class='code'>true</td>
#       </tr>
#       <tr>
#           <td>requiredError</td>
#           <td>You did not specify a phone number.</td>
#       </tr>
#       <tr class='hDocumentationTableHeading'>
#           <td><b>Field:</b></td>
#           <td class='code' colspan='2'>hContactFaxNumber</td>
#       </tr>
#       <tr>
#           <td><b>Method:</b></td>
#           <td colspan='2'><a href='#getFaxNumber' class='code'>getFaxNumber()</a></td>
#       </tr>
#       <tr>
#           <td rowspan='4'><b>Options:</b></td>
#           <td>label</td>
#           <td>Fax:</td>
#       </tr>
#       <tr>
#           <td>required</td>
#           <td class='code'>false</td>
#       </tr>
#       <tr>
#           <td>enabled</td>
#           <td class='code'>true</td>
#       </tr>
#       <tr>
#           <td>requiredError</td>
#           <td>You did not specify a fax number.</td>
#       </tr>
#       <tr class='hDocumentationTableHeading'>
#           <td><b>Field:</b></td>
#           <td class='code' colspan='2'>hContactGender</td>
#       </tr>
#       <tr>
#           <td><b>Method:</b></td>
#           <td colspan='2'><a href='#getGender' class='code'>getGender()</a></td>
#       </tr>
#       <tr>
#           <td rowspan='4'><b>Options:</b></td>
#           <td>label</td>
#           <td>Gender:</td>
#       </tr>
#       <tr>
#           <td>required</td>
#           <td class='code'>false</td>
#       </tr>
#       <tr>
#           <td>enabled</td>
#           <td class='code'>false</td>
#       </tr>
#       <tr>
#           <td>requiredError</td>
#           <td>You did not specify your gender.</td>
#       </tr>
#       <tr class='hDocumentationTableHeading'>
#           <td><b>Field:</b></td>
#           <td class='code' colspan='2'>hContactDateOfBirth</td>
#       </tr>
#       <tr>
#           <td><b>Method:</b></td>
#           <td colspan='2'><a href='#getDateOfBirth' class='code'>getDateOfBirth()</a></td>
#       </tr>
#       <tr>
#           <td rowspan='4'><b>Options:</b></td>
#           <td>label</td>
#           <td>Date of Birth:</td>
#       </tr>
#       <tr>
#           <td>required</td>
#           <td class='code'>false</td>
#       </tr>
#       <tr>
#           <td>enabled</td>
#           <td class='code'>false</td>
#       </tr>
#       <tr>
#           <td>requiredError</td>
#           <td>You did not specify your date of birth.</td>
#       </tr>
#   </tbody>
# </table>
# <h3>Additional Options</h3>
# <p>
#   Beyond the options specified above, for any contact field, you may also specify any
#   of two additional options: <var>prependInput</var> and <var>appendInput</var>, which
#   can be used to add custom HTML content before or after an input field, respectively.
#   Since form inputs are laid out using an HTML table, that means that custom HTML using
#   either of these options will appear directly before or directly after the input itself,
#   occupying the same table cell as the input.
# </p>
# <h3>Further Customizing Contact Fields</h3>
# <p>
#   If you wish to customize the layout of contact fields beyond the options provided
#   here, you can also generate these inputs manually, either using HTML directly, or
#   via the <a href='/Hot Toddy/Documentation?hForm/hForm.library.php'>hForm</a> library.
#   Simply give the fields the same names used above and this object can still be used
#   to save and retrieve contact information-related fields using Hot Toddy's
#   <var>hContact</var> API.
# </p>
# @end

interface hContactFormTitle {

    public function setForm(hFormLibrary &$form);

    public function setTitle();
}

class hContactFormLibrary extends hPlugin {

    private $hForm;
    private $hContactId;

    private $hContactValidation;
    private $hLocationValidation;
    private $hContactDatabase;
    private $hUserValidation;

    private $hContactFormTitle;
    private $hContactFormCompany;

    private $addressFieldId = 2;
    private $phoneFieldId = 6;
    private $companyFieldId = 8;
    private $appointmentFieldId = 24;
    private $tollFreeFieldId = 23;
    private $mainFieldId = 22;
    private $faxFieldId = 9;
    private $mobileFieldId = 5;
    private $pagerFieldId = 10;
    private $emailFieldId = 19;
    private $schedulingFieldId = 47;
    private $duplicateFields = false;
    private $contactFormExists = false;

    private $defaultEmail = false;
    private $callLog = array();

    private $methods = array(
        'hContactFirstName' => array(
            'method'  => 'getFirstName',
            'options' => array(
                'label' => 'First Name:',
                'required' => true,
                'enabled' => true,
                'requiredError' => 'You did not specify your first name.'
            )
        ),
        'hContactMiddleName' => array(
            'method' => 'getMiddleName',
            'options' => array(
                'label' => 'Middle Name:',
                'required' => false,
                'enabled' => false,
                'requiredError' => 'You did not specify your middle name.'
            )
        ),
        'hContactLastName' => array(
            'method' => 'getLastName',
            'options' => array(
                'label' => 'Last Name:',
                'required' => true,
                'enabled' => true,
                'requiredError' => 'You did not specify your last name.'
            )
        ),
        'hContactCompany' => array(
            'method' => 'getCompany',
            'options' => array(
                'label' => 'Company:',
                'required' => true,
                'enabled' => true,
                'requiredError' => 'You did not specify your company.'
            )
        ),
        'hContactDepartment' => array(
            'method' => 'getDepartment',
            'options' => array(
                'label'  => 'Department:',
                'required' => false,
                'enabled' => false,
                'requiredError' => 'You did not specify a department.'
            )
        ),
        'hContactWebsite' => array(
            'method' => 'getWebsite',
            'options' => array(
                'label' => 'Website:',
                'required' => false,
                'enabled' => false,
                'requiredError' => 'You did not specify a website.'
            )
        ),
        'hContactTitle' => array(
            'method' => 'getTitle',
            'options' => array(
                'label' => 'Title:',
                'required' => true,
                'enabled' => true,
                'requiredError' => 'You did not specify your title.'
            )
        ),
        'hContactEmailAddress' => array(
            'method' => 'getEmailAddress',
            'options' => array(
                'label' => 'Email Address:',
                'required' => true,
                'enabled' => false,
                'requiredError' => 'You did not specify your email address.'
            )
        ),
        'hLocationCountryId' => array(
            'method' => 'getCountry',
            'options' => array(
                'label' => '',
                'required' => true,
                'enabled' => true,
                'requiredError' => 'You did not specify your country.'
            )
        ),
        'hContactAddressStreet' => array(
            'method' => 'getStreetAddress',
            'options' => array(
                'label' => 'Street:',
                'required' => true,
                'enabled' => true,
                'requiredError' => 'You did not specify your street address.'
            )
        ),
        'hContactAddressCity' => array(
            'method' => 'getCity',
            'options' => array(
                'label' => 'City:',
                'required' => true,
                'enabled' => true,
                'requiredError' => 'You did not specify a city.'
            )
        ),
        'hLocationStateId' => array(
            'method' => 'getState',
            'options' => array(
                'label' => '',
                'required' => true,
                'enabled' => true,
                'requiredError' => 'You did not specify a {$label}.'
            )
        ),
        'hContactAddressPostalCode' => array(
            'method' => 'getPostalCode',
            'options' => array(
                'label' => 'Postal Code:',
                'required' => true,
                'enabled' => true,
                'requiredError' => 'You did not specify your postal code.'
            )
        ),
        'hLocationCountyId' => array(
            'method' => 'getCounty',
            'options' => array(
                'label' => 'County:',
                'required' => false,
                'enabled' => false,
                'requiredError' => 'You did not specify your county.'
            )
        ),
        'hContactPhoneNumber' => array(
            'method' => 'getPhoneNumber',
            'options' => array(
                'label' => 'Phone Number:',
                'required' => true,
                'enabled' => true,
                'requiredError' => 'You did not specify a phone number.'
            )
        ),
        'hContactPhoneNumberCompany' => array(
            'method' => 'getCompanyNumber',
            'options' => array(
                'label' => 'Company Phone Number:',
                'required' => false,
                'enabled' => false,
                'requiredError' => 'You did not specify a company phone number.'
            )
        ),
        'hContactPhoneNumberFax' => array(
            'method' => 'getFaxNumber',
            'options' => array(
                'label' => 'Fax:',
                'required' => false,
                'enabled' => true,
                'requiredError' => 'You did not specify a fax number.'
            )
        ),
        'hContactPhoneNumberAppointment' => array(
            'method' => 'getAppointmentNumber',
            'options' => array(
                'label' => 'Appointment:',
                'required' => false,
                'enabled' => false,
                'requiredError' => 'You did not specify an appointment number.'
            )
        ),
        'hContactPhoneNumberTollFree' => array(
            'method' => 'getTollFreeNumber',
            'options' => array(
                'label' => 'Toll Free:',
                'required' => false,
                'enabled' => false,
                'requiredError' => 'You did not specify a toll free number.'
            )
        ),
        'hContactPhoneNumberMain' => array(
            'method' => 'getMainNumber',
            'options' => array(
                'label' => 'Main Number:',
                'required' => false,
                'enabled' => false,
                'requiredError' => 'You did not specify a main number.'
            )
        ),
        'hContactPhoneNumberScheduling' => array(
            'method' => 'getSchedulingNumber',
            'options' => array(
                'label' => 'Scheduling Number:',
                'required' => false,
                'enabled' => false,
                'requiredError' => 'You did not specify a scheduling number.'
            )
        ),
        'hContactGender' => array(
            'method' => 'getGender',
            'options' => array(
                'label' => 'Gender:',
                'required' => false,
                'enabled' => false,
                'requiredError' => 'You did not specify your gender.'
            )
        ),
        'hContactDateOfBirth' => array(
            'method' => 'getDateOfBirth',
            'options' => array(
                'label' => 'Date of Birth:',
                'required' => false,
                'enabled' => false,
                'requiredError' => 'You did not specify your date of birth.'
            )
        )
    );

    private $countryFieldExecuted = false;

    private $addressFields = array(
        'hLocationCountryId',
        'hContactAddressStreet',
        'hContactAddressCity',
        'hLocationStateId',
        'hContactAddressPostalCode'
    );

    public function hConstructor()
    {
        # @return void

        # @description
        # <h2>Constructor</h2>
        #
        #
        # @end

        $this->hContactValidation  = $this->library('hContact/hContactValidation');
        $this->hLocationValidation = $this->library('hLocation/hLocationValidation');
        $this->hUserValidation = $this->library('hUser/hUserValidation');
        $this->hForm = $this->library('hForm');

        $this->getPluginFiles();
    }

    public function setDefaultEmail($defaultEmail)
    {
        # @return void

        # @description
        # <h2>Setting Default Email Behavior</h2>
        # <p>
        #   When retrieving contact information, by default, it's possible that no email
        #   address is returned.  Setting <var>$defaultEmail</var> to <var>true</var> will
        #   trigger a default email fallback, if no email address is found in the contact
        #   record, the <var>hUserId</var> that owns the contact record will be used to
        #   supply a default email address from the <var>hUsers</var> table, making the
        #   <var>hUserEmail</var> associated with the <var>hUserId</var> the default
        #   email address.
        # </p>
        # <p>
        #   By default, a default email address <i>is</i> retrieved automatically if the
        #   address book in question is <var>contactAddressBookId</var> <var>1</var>, or
        #   <i>Website Registrations</i>.  For any other address book, to enable setting a
        #   default email address, this flag, <var>$defaultEmail</var>, must be explicitly
        #   set to a <var>true</var> value.
        # </p>
        # @end

        $this->defaultEmail = $defaultEmail;
    }

    public function &setForm(hFormLibrary &$form)
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Providing a Form Object</h2>
        #
        #
        # @end

        $this->hForm = &$form;

        return $this;
    }

    public function &setAddressFieldId($addressFieldId)
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Set the Address Field Id</h2>
        #
        #
        # @end

        $this->addressFieldId = $addressFieldId;

        return $this;
    }

    public function &setPhoneFieldId($phoneFieldId)
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Set the Phone Field Id</h2>
        #
        #
        # @end

        $this->phoneFieldId = $phoneFieldId;

        return $this;
    }

    public function &setFaxFieldId($faxFieldId)
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Set the Fax Field Id</h2>
        #
        #
        # @end

        $this->faxFieldId = $faxFieldId;

        return $this;
    }

    public function &setEmailFieldId($emailFieldId)
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Set the Email Address Field Id</h2>
        #
        #
        # @end

        $this->emailFieldId = $emailFieldId;

        return $this;
    }

    public function &setDuplicateFields($duplicateFields)
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Controlling Duplicate Data Entry</h2>
        #
        #
        # @end

        $this->duplicateFields = $duplicateFields;

        return $this;
    }

    public function &setContactId($contactId = 0)
    {
        if (empty($contactId))
        {
            $contactId = $this->user->getContactId();
        }

        $this->hContactId = (int) $contactId;

        return $this;
    }

    public function saveContactForm($userId = 0, $contactAddressBookId = 1, $uniqueByEmailAddress = false, array $fieldNames = array())
    {
        # @return integer

        # @description
        # <h2>Saving the Contact Form</h2>
        # <p>
        #   Alias of <a href='#save' class='code'>save()</a>.
        # </p>
        # @end

        return $this->save($userId, $contactAddressBookId, $uniqueByEmailAddress, $fieldNames);
    }

    public function save($userId = 0, $contactAddressBookId = 1, $uniqueByEmailAddress = false, array $fieldNames = array())
    {
        # @return integer
        # <p>
        #   The inserted or updated <var>contactId</var>.
        # </p>
        # @end

        # @description
        # <h2>Saving the Contact Form</h2>
        # <p>
        #
        # </p>
        # @end

        if (!count($fieldNames))
        {
            if ($this->contactFormExists)
            {
                $fieldNames = $this->hForm->getFieldNames();
            }
            else
            {
                // No form exists..
                // Create a form on the fly to get the acceptable fields names.
                $this->hForm = $this->library('hForm');
                $this->hForm->addDiv('hContactForm');
                $this->addContactForm($uniqueByEmailAddress);

                $form = $this->hForm->getForm();

                $fieldNames = $this->hForm->getFieldNames();
            }
        }

        $this->user->whichUserId($userId);

        $this->hContactDatabase = $this->database('hContact');
        $this->hContactDatabase->setDuplicateFields($this->duplicateFields);

        if (!isset($_POST['hContactDisplayName']) && isset($_POST['hContactFirstName']) && isset($_POST['hContactLastName']))
        {
            $_POST['hContactDisplayName'] = $_POST['hContactFirstName'].' '.$_POST['hContactLastName'];
        }

        if (isset($_POST['hContactDateOfBirth']))
        {
            $_POST['hContactDateOfBirth'] = strtotime($_POST['hContactDateOfBirth']);
        }

        if (isset($_POST['hContactGender']))
        {
            if ($_POST['hContactGender'] == 1) // Value must be non-empty!
            {
                $_POST['hContactGender'] = 0; // Female == false
            }
            else if ($_POST['hContactGender'] == 2)
            {
                $_POST['hContactGender'] = 1; // Male == true
            }
        }

        // This will cause a record existing with the same email address to be
        // updated, rather than inserted as new.
        if ($uniqueByEmailAddress)
        {
            $contactId = $this->hContactDatabase->getContactIdByEmailAddress(
                $_POST['hContactEmailAddress'],
                $contactAddressBookId
            );
        }
        else if (!empty($userId) && $contactAddressBookId == 1)
        {
            $contactId = $this->hContactDatabase->getContactIdByUserId(
                $userId,
                $contactAddressBookId
            );
        }
        else if ($contactAddressBookId > 1 && !empty($this->hContactId))
        {
            $contactId = $this->hContactId;
        }
        else
        {
            $contactId = 0;
        }

        array_push($fieldNames, 'hContactDisplayName');

        $contactId = $this->hContactDatabase->saveContact(
            $this->hDatabase->getPostDataByColumnName('hContacts', $fieldNames),
            $contactAddressBookId,
            $userId,
            $contactId
        );

        $addressFieldsAreSet = (
            isset($_POST['hContactAddressStreet']) ||
            isset($_POST['hContactAddressCity']) ||
            isset($_POST['hLocationStateId']) ||
            isset($_POST['hContactAddressPostalCode']) ||
            isset($_POST['hLocationCountryId'])
        );

        if ($addressFieldsAreSet)
        {
            $this->hContactDatabase->saveAddress(
                $this->hDatabase->getPostDataByColumnName(
                    'hContactAddresses',
                    $fieldNames
                ),
                $this->addressFieldId
            );
        }

        if (isset($_POST['hContactPhoneNumber']))
        {
            $this->hContactDatabase->savePhoneNumber(
                $_POST['hContactPhoneNumber'],
                $this->phoneFieldId
            );
        }

        if (isset($_POST['hContactFaxNumber']))
        {
            $this->hContactDatabase->savePhoneNumber(
                $_POST['hContactFaxNumber'],
                $this->faxFieldId
            );
        }

        if (isset($_POST['hContactPhoneNumberFax']))
        {
            $this->hContactDatabase->savePhoneNumber(
                $_POST['hContactPhoneNumberFax'],
                $this->faxFieldId
            );
        }

        if (isset($_POST['hContactPhoneNumberCompany']))
        {
            $this->hContactDatabase->savePhoneNumber(
                $_POST['hContactPhoneNumberCompany'],
                $this->companyFieldId
            );
        }

        if (isset($_POST['hContactPhoneNumberMobile']))
        {
            $this->hContactDatabase->savePhoneNumber(
                $_POST['hContactPhoneNumberMobile'],
                $this->mobileFieldId
            );
        }

        if (isset($_POST['hContactPhoneNumberPager']))
        {
            $this->hContactDatabase->savePhoneNumber(
                $_POST['hContactPhoneNumberPager'],
                $this->pagerFieldId
            );
        }

        if (isset($_POST['hContactPhoneNumberAppointment']))
        {
            $this->hContactDatabase->savePhoneNumber(
                $_POST['hContactPhoneNumberAppointment'],
                $this->appointmentFieldId
            );
        }

        if (isset($_POST['hContactPhoneNumberMain']))
        {
            $this->hContactDatabase->savePhoneNumber(
                $_POST['hContactPhoneNumberMain'],
                $this->mainFieldId
            );
        }

        if (isset($_POST['hContactPhoneNumberTollFree']))
        {
            $this->hContactDatabase->savePhoneNumber(
                $_POST['hContactPhoneNumberTollFree'],
                $this->tollFreeFieldId
            );
        }

        if (isset($_POST['hContactPhoneNumberScheduling']))
        {
            $this->hContactDatabase->savePhoneNumber(
                $_POST['hContactPhoneNumberScheduling'],
                $this->schedulingFieldId
            );
        }

        if (isset($_POST['hContactEmailAddress']))
        {
            $this->hContactDatabase->saveEmailAddress(
                $_POST['hContactEmailAddress'],
                $this->emailFieldId
            );
        }

        $this->setContactId($contactId);

        return $contactId;
    }

    /**
    * Return an array that has all of the fields of the contact form set,
    * in the same format as the $_POST array.
    */
    public function getContactData($setPost = false)
    {
        # @return array

        # @description
        # <h2>Retrieving Contact Data</h2>
        # <p>
        #   Gets the contact data either after saving it using the
        #   <a href='#save' class='code'>save()</a> method, or it can be used to
        #   retrieve any contact information when you first pass a <var>contactId</var>
        #   to <a href='#setContactId' class='code'>setContactId()</a>.
        # </p>
        # <p>
        #   If the optional <var>$setPost</var> argument is <var>true</var> (it is <var>false</var>,
        #   by default), then contact information is set in the <var>$_POST</var> superglobal, in
        #   addition to being returned.  <var>$setPost</var> can be used to populate the
        #   <var>$_POST</var> superglobal prior to generating a contact form, which will then, in turn,
        #   populate all form fields with contact data.
        # </p>
        # <p>
        #   This method retrieves contact information in an associative array with field names
        #   identical to the field names used in the form itself.  This can conveniently be
        #   then passed on to a mailer template.  Fields for address, phone number, fax number,
        #   and email address are also made available identically to the field names used in
        #   the form, whereas, if you were to call
        #   <a href='/Hot Toddy/Documentation?hContact/hContact.library.php#getRecord' class='code'>$this-&gt;hContact-&gt;getRecord()</a>
        #   you'd first have to process the returned information to flatten the arrays used for
        #   address, phone numbers, and email addresses, since an account can have multiple
        #   addresses, phone numbers, and email addresses associated with it.  And also, using
        #   this method, you are guaranteed to select the same address, phone numbers, and
        #   email addresses used to submit the form, rather than having to process the arrays
        #   to select the correct address, phone numbers, and email address.
        # </p>
        # <p>
        #   In addition to all of the contact information returned by
        #   <a href='/Hot Toddy/Documentation?hContact/hContact.library.php#getRecord' class='code'>$this-&gt;hContact-&gt;getRecord()</a>,
        #   (see that documentation for a comprehensive listing of data returned by that method)
        #   the following indices are added to the top level of the contact information array
        #   as associative indices:
        # </p>
        # <table>
        #   <tbody>
        #     <tr>
        #       <td class='code'>hContactAddressId</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactFieldId</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactAddressStreet</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactAddressCity</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationStateId</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactAddressPostalCode</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationCountyId</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationCountryId</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactAddressLatitude</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactAddressLongitude</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationCountryName</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationCountryISO2</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationCountryISO3</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactAddressTemplateId</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationStateLabel</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationUseStateCode</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationCountyName</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactAddressTemplate</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationStateCode</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationStateName</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactFieldName</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationCity</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationCounty</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationSequenceNumber</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationAcceptable</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactPhoneNumber</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactPhoneNumberFax</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactPhoneNumberMobile</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactPhoneNumberPager</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactEmailAddress</td>
        #     </tr>
        #   </tbody>
        # </table>
        # <h3>Default Email Address</h3>
        # <p>
        #   If there are no email addresses in the user's contact rolodex,
        #   then the email address for the <var>userId</var> that owns the contact record is
        #   added as <var>hContactEmailAddrses</var> (<var>hUserEmailAddress</var>).  If
        #   the address book the <var>contactId</var> resides in is <i>Website Registrations</i>,
        #   that means you get the email address on the user's account, that can optionally be
        #   used to login.  So, it takes the value of <var>hUserId</var> from the <var>hContacts</var>
        #   database table for the specified <var>hContactId</var> and returns the
        #   <var>hUserEmailAddress</var> from the <var>hUsers</var> table for that same
        #   <var>hUserId</var>.  This ensures that an email address is always specified,
        #   and usually it also means the email address still corresponds to the right
        #   person.  Where it might not be the email address you want is in situations where
        #   you are working with a different address book, for example any <var>contactAddressBookId</var>
        #   greater than <var>1</var>, if that situation applies, then you want to be sure that
        #   either there is an email address in the contact record's rolodex (by providing
        #   the field on the form and requiring it to be entered), or by ensuring that the
        #   <var>hUserId</var> (the owner of the contact record) on record in the
        #   <var>hContacts</var> (the owner of the contact record) table maps to the
        #   appropriate user.
        # </p>
        # @end

        $contact = $this->contact->getRecord($this->hContactId? $this->hContactId : 0);

        foreach ($contact['hContactAddresses'] as $contactAddressId => $address)
        {
            if ($address['hContactFieldId'] == $this->addressFieldId)
            {
                $contact = array_merge($address, $contact);
            }
        }

        if (count($contact['hContactPhoneNumbers']) == 1)
        {
            foreach ($contact['hContactPhoneNumbers'] as $contactPhoneNumberId => $number)
            {
                $contact['hContactPhoneNumber'] = $number['hContactPhoneNumber'];
                break;
            }
        }
        else
        {
            foreach ($contact['hContactPhoneNumbers'] as $contactPhoneNumberId => $number)
            {
                switch ($number['hContactFieldId'])
                {
                    case $this->phoneFieldId:
                    {
                        $contact['hContactPhoneNumber'] = $number['hContactPhoneNumber'];
                        break;
                    }
                    case $this->faxFieldId:
                    {
                        $contact['hContactFaxNumber'] = $number['hContactPhoneNumber'];
                        $contact['hContactPhoneNumberFax'] = $number['hContactPhoneNumber'];
                        break;
                    }
                    case $this->mobileFieldId:
                    {
                        $contact['hContactPhoneNumberMobile'] = $number['hContactPhoneNumber'];
                        break;
                    }
                    case $this->pagerFieldId:
                    {
                        $contact['hContactPhoneNumberPager'] = $number['hContactPhoneNumber'];
                        break;
                    }
                    case $this->schedulingFieldId:
                    {
                        $contact['hContactPhoneNumberScheduling'] = $number['hContactPhoneNumber'];
                        break;
                    }
                }
            }
        }

        if (count($contact['hContactEmailAddresses']))
        {
            foreach ($contact['hContactEmailAddresses'] as $contactEmailAddressId => $address)
            {
                if (!empty($address))
                {
                    $contact['hContactEmailAddress'] = $address['hContactEmailAddress'];
                    break;
                }
            }
        }
        else if ($contact['hContactAddressBookId'] == 1 || $this->defaultEmail)
        {
            $contact['hContactEmailAddress'] = $this->user->getUserEmail(
                $this->contact->getUserId($this->hContactId)
            );
        }

        if ($setPost)
        {
            $_POST = $contact;
        }

        return $contact;
    }

    public function &setLayout()
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Customizing the Contact Form's Layout</h2>
        # <p>
        #   This method is used to redefine the contact form's layout, and allows
        #   you to add custom fields (by specifying one or more callback functions),
        #   in addition to rearranging the layout of the contact form however you like.
        # </p>
        # @end

        $arguments = func_get_args();

        $this->hContactFormLayout = (isset($arguments[0]) && is_array($arguments[0])) ?
            $arguments[0] : $arguments;

        return $this;
    }

    public function &createForm($fieldset = 'Contact Information', $id = 'hContactForm')
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Generating the Contact Form</h2>
        # <p>
        #   Alias of <a href='#getForm'>getForm()</a>
        # </p>
        # @end

        return $this->getForm($fieldset, $id);
    }

    public function &getForm($fieldset = 'Contact Information', $id = 'hContactForm')
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Generating the Contact Form</h2>
        # <p>
        #   This method takes either the default layout or a custom layout and
        #   generates a contact form.  To generate a contact form, you must first
        #   initialize an
        #   <a href='/Hot Toddy/Documenation?hForm/hForm.library.php'>hForm</a>
        #   object and pass that object to <a href='#setForm' class='code'>setForm()</a>,
        #   so that fields associated with contact information can be automatically
        #   added to the form.  After you have called this method, you will then need to
        #   call <a href='/Hot Toddy/Documenation?hForm/hForm.library.php'>hFormLibrary::getForm()</a>,
        #   which in turn creates the form's HTML source, and then assign that output to a variable.
        # </p>
        # @end

        $this->contactFormExists = true;

        if (!$this->hContactFormLayout)
        {
            $this->hContactFormLayout = array(
                'hContactFirstName',
                'hContactMiddleName',
                'hContactLastName',
                'hContactCompany',
                'hContactWebsite',
                'hContactTitle',
                'hContactEmailAddress',
                'hLocationCountryId',
                'hContactAddressStreet',
                'hContactAddressCity',
                'hLocationStateId',
                'hContactAddressPostalCode',
                'hContactPhoneNumber',
                'hContactPhoneNumberFax',
                'hContactGender',
                'hContactDateOfBirth'
            );
        }

        $form = &$this->hForm;

        $this->hContactValidation->setCountryId($_POST['hLocationCountryId']);

        $this->hLocationValidation
            ->setCountryId($_POST['hLocationCountryId'])
            ->setStateId($_POST['hLocationStateId'])
            ->setPostalCode($_POST['hContactAddressPostalCode']);

        $form->addFieldset(
            $fieldset,
            '100%',
            $this->hContactFormLabelColumnWidth('175px').',auto',
            $id
        );

        if ($this->hContactFormPrependData(nil))
        {
            $form->addData('', $this->hContactFormPrependLabel, $this->hContactFormPrependData);
        }

        foreach ($this->hContactFormLayout as $field => $data)
        {
            if (!is_numeric($field) && $field == 'hContactFaxNumber')
            {
                $field = 'hContactPhoneNumberFax';
            }
            else if (is_numeric($field) && $data == 'hContactFaxNumber')
            {
                $data = 'hContactPhoneNumberFax';
            }

            if (is_numeric($field))
            {
                if (in_array($data, $this->addressFields) && !$this->hContactFormEnableAddress(true))
                {
                    continue;
                }

                $method = $this->methods[$data]['method'];

                if (method_exists($this, $method))
                {
                    $label = $this->getLabel(
                        $method,
                        $this->methods[$data]['options']['label'],
                        $bullocks
                    );

                    // Is enabled by virtue of being in the array!
                    $isEnabled = $this->isEnabled(
                        $method,
                        $this->methods[$data]['options']['enabled'],
                        $asdf
                    );

                    $isRequired = $this->isRequired(
                        $method,
                        $this->methods[$data]['options']['required'],
                        $bullocks
                    );

                    $requiredError = $this->getRequiredError(
                        $method,
                        $this->methods[$data]['options']['requiredError'],
                        $bullocks
                    );

                    if (!$isEnabled)
                    {
                        continue;
                    }

                    if ($isRequired)
                    {
                        $this->hForm->addRequiredField($requiredError);
                    }

                    $this->$method($label);
                }
                else
                {
                    $this->warning(
                        'Contact form method, '.$method.', does not exist.',
                        __FILE__, __LINE__
                    );
                }
            }
            else
            {
                if (is_array($data))
                {
                    $callback = nil;
                    $method = nil;
                    $options = array();

                    foreach ($data as $key => $value)
                    {
                        switch (true)
                        {
                            case is_object($value):
                            {
                                $callback = $value;
                                break;
                            }
                            case !is_numeric($key):
                            {
                                $options[$key] = $value;
                                break;
                            }
                            default:
                            {
                                $method = $value;
                            }
                        }
                    }

                    // See if there is a default method being replaced...
                    $arguments = array();
                    $isEnabled = true;
                    $isRequired = false;
                    $requiredError = '';

                    if (isset($this->methods[$field]['method']))
                    {
                        $method = $this->methods[$field]['method'];

                        $arguments[0] = $this->getLabel(
                            $method,
                            $this->methods[$field]['options']['label'],
                            $options['label']
                        );

                        $isEnabled = $this->isEnabled(
                            $method,
                            $this->methods[$field]['options']['enabled'],
                            $options['enabled']
                        );

                        $isRequired = $this->isRequired(
                            $method,
                            $this->methods[$field]['options']['required'],
                            $options['required']
                        );

                        $requiredError = $this->getRequiredError(
                            $method,
                            $this->methods[$field]['options']['requiredError'],
                            $options['requiredError']
                        );

                        if ($method == 'getCountry')
                        {
                            $arguments[1] = $isEnabled;
                        }
                        else
                        {
                            if (!$isEnabled)
                            {
                                continue;
                            }
                        }
                    }
                    else
                    {
                        if (!empty($options['enabled']))
                        {
                            continue;
                        }

                        $isRequired = isset($options['required']) ?
                            $options['required'] : false;

                        $requiredError = isset($options['requiredError']) ?
                            $options['requiredError'] : '';
                    }

                    if (!empty($isRequired) && !empty($requiredError))
                    {
                        $this->hForm->addRequiredField($requiredError);
                    }

                    if (!empty($options['appendInput']))
                    {
                        $this->hForm->setVariable(
                            'hFormAppendInput',
                            $options['appendInput']
                        );
                    }

                    if (!empty($options['prependInput']))
                    {
                        $this->hForm->setVariable(
                            'hFormPrependInput',
                            $options['prependInput']
                        );
                    }

                    if (is_object($callback) && !empty($method))
                    {
                        $className = get_class($callback);

                        if (!isset($this->callLog[$className]))
                        {
                            $this->callLog[$className] = array();
                        }

                        if (!isset($this->callLog[$className][$method]))
                        {
                            $this->callLog[$className][$method] = true;

                            call_user_func_array(
                                array(
                                    $callback,
                                    $method
                                ),
                                $arguments
                            );
                        }
                    }
                    else
                    {
                        call_user_func_array(
                            array($this, $method),
                            $arguments
                        );
                    }

                    unset($options);
                }
            }
        }

        if (!$this->countryFieldExecuted)
        {
            $this->getCountry('', false);
        }

        return $this;
    }

    private function getOption($variable, $method, $default, &$option)
    {
        # @return mixed

        # @description
        # <h2>Getting an Option</h2>
        #
        # @end

        if (isset($option))
        {
            return $option;
        }

        $variable = str_replace(
            '{$name}',
            str_replace('get', '', $method),
            $variable
        );

        return $this->$variable($default);
    }

    private function getLabel($method, $default, &$option)
    {
        # @return string

        # @description
        # <h2>Getting a Field's Label</h2>
        #
        #
        # @end

        return $this->getOption(
            'hContactForm{$name}Label',
            $method,
            $default,
            $option
        );
    }

    private function isRequired($method, $default, &$option)
    {
        # @return boolean

        # @description
        # <h2>Getting a Field's Required Status</h2>
        #
        #
        # @end

        return $this->getOption(
            'hContactFormRequire{$name}',
            $method,
            $default,
            $option
        );
    }

    private function getRequiredError($method, $default, &$option)
    {
        # @return string

        # @description
        # <h2>Getting a Field's 'Required' Error</h2>
        #
        # @end

        return $this->getOption(
            'hContactFormRequire{$name}Error',
            $method,
            $default,
            $option
        );
    }

    private function isEnabled($method, $default, &$option)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Field is Enabled</h2>
        #
        # @end

        return $this->getOption(
            'hContactFormEnable{$name}',
            $method,
            $default,
            $option
        );
    }

    public function &addContactForm($email = true, $fieldset = 'Contact Information', $id = 'hContactForm')
    {
        $this->hContactFormEnableEmailAddress = $email;
        $this->getForm($fieldset, $id);

        return $this;
    }

    public function &getFirstName($label = 'F:First Name:')
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Adding a First Name Field</h2>
        #
        # @end

        $this->hForm
            ->addValidationByComparison(
                'Your first name must be entered in 100 characters or less.',
                '<=', 100
            )
            ->addTextInput(
                'hContactFirstName',
                $label,
                '25,100'
            );

        return $this;
    }

    public function &getMiddleName($label = 'M:Middle Name:')
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Adding a Middle Name Field</h2>
        #
        # @end

        $this->hForm
            ->addValidationByComparison(
                'Your middle name must be entered in 100 characters or less.',
                '<=', 100
            )
            ->addTextInput(
                'hContactMiddleName',
                $label,
                '25,100'
            );

        return $this;
    }

    public function &getLastName($label = 'L:Last Name:')
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Adding a Last Name Field</h2>
        #
        # @end

        $this->hForm
            ->addValidationByComparison(
                'Your last name must be entered in 100 characters or less.',
                '<=', 100
            )
            ->addTextInput(
                'hContactLastName',
                $label,
                '25,100'
            );

        return $this;
    }

    public function &getCompany($label = 'o:Company:')
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Adding a Company Field</h2>
        #
        # @end

        if ($this->hContactFormCompanyPlugin(nil))
        {
            # This is legacy field customization support.  The setLayout() method
            # supercedes and obsoletes the need for this.
            $this->hContactFormCompany = $this->plugin($this->hContactFormCompanyPlugin(nil));
            $this->hContactFormCompany->setForm($this->hForm);
            $this->hContactFormCompany->setCompany();
        }
        else
        {
            $this->hForm
                ->addValidationByComparison(
                    'Your company must be entered in 200 characters or less.',
                    '<=', 200
                )
                ->addTextInput(
                    'hContactCompany',
                    $label,
                    '25,200'
                );
        }

        return $this;
    }

    public function &getWebsite($label = 'W:Website:')
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Adding a Website Field</h2>
        #
        # @end

        $this->hForm
            ->addValidationByComparison(
                'Your website must be entered in 255 characters or less.',
                '<=', 255
            )
            ->addURLInput(
                'hContactWebsite',
                $label,
                '25,255',
                !isset($_POST['hContactWebsite']) ? 'http://' : ''
            );

        return $this;
    }

    public function &getDepartment($label = 'D:Department:')
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Adding a Department Field</h2>
        #
        # @end

        $this->hForm
            ->addValidationByComparison(
                'Your department must be entered in 100 characters or less.',
                '<=', 100
            )
            ->addTextInput(
                'hContactDepartment',
                $label,
                '25,100'
            );

        return $this;
    }

    public function &getTitle($label = 'T:Title:')
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Adding a Title Field</h2>
        #
        #
        # @end

        // You may not want a text field for the title, you may want a drop down selection instead...
        // this lets you do as you please.
        if ($this->hContactFormTitlePlugin(nil))
        {
            # This is legacy field customization support.  The setLayout() method
            # supercedes and obsoletes the need for this.
            $this->hContactFormTitle = $this->plugin($this->hContactFormTitlePlugin(nil));
            $this->hContactFormTitle->setForm($this->hForm);
            $this->hContactFormTitle->setTitle();
        }
        else
        {
            $this->hForm
                ->addValidationByComparison(
                    'Your job title must be entered in 100 characters or less.',
                    '<=', 100
                )
                ->addTextInput(
                    'hContactTitle',
                    $label,
                    '50,100'
                );
        }

        return $this;
    }

    public function &getEmailAddress($label = 'l:Email Address:')
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Adding an Email Address</h2>
        #
        # @end

        $this->hForm
            ->addValidationByComparison(
                'Your email address must be entered in 255 characters or less.',
                '<=', 255
            )
            ->addValidationByCallback(
                'The email address you entered is not valid.',
                $this->hUserValidation,
                'isValidEmailAddress'
            )
            ->addEmailInput(
                'hContactEmailAddress',
                $label,
                '25,255'
            );

        return $this;
    }

    public function &getCounty($label = '', $enabled = false)
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Adding a County Selection</h2>
        # @end

        $this->hForm->addSelectInput(
            'hLocationCountyId',
            $label,
            array()
        );

        return $this;
    }

    public function &getCountry($label = '', $enabled = true)
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Adding a Country Selection</h2>
        # @end

        $this->countryFieldExecuted = true;

        $this->hForm
            ->addValidationByCallback(
                'You did not specify a valid country.',
                $this->hLocationValidation,
                'isCountryId'
            )
            ->defineCell(
                array(
                    'id' => 'hContactFormCountryInput'
                )
            );

        if ($enabled)
        {
            if (!empty($_POST['hLocationCountryId']))
            {
                $iso2 = $this->hDatabase->selectColumn(
                    'hLocationCountryISO2',
                    'hLocationCountries',
                    (int) $_POST['hLocationCountryId']
                );
            }
            else
            {
                $iso2 = $this->hContactFormDefaultCountryISO2('us');
            }

            $this->hForm
                ->setCellAttributes(
                    array(
                        'id' => 'hLocationCountryIdCell'
                    )
                )
                ->addSelectCountry(
                    'hLocationCountryId',
                    !empty($label) ?
                        $label : $this->getTemplate(
                            'Country Label',
                            array(
                                'iso2' => strtolower($iso2)
                            )
                        ),
                    1,
                    isset($_POST['hLocationCountryId']) ?
                        $_POST['hLocationCountryId'] : $this->hContactFormDefaultCountryId(223)
                );
        }
        else
        {
            $this->hForm->addHiddenInput(
                'hLocationCountryId',
                $this->hContactFormDefaultCountryId(223)
            );
        }

        return $this;
    }

    public function &getStreetAddress($label = 'S:Street Address: -L')
    {
        # @return void

        # @description
        # <h2>Adding a Street Address</h2>
        # @end

        if ($this->hContactFormAddressStreetInputType == 'text')
        {
            $this->hForm->addTextInput(
                'hContactAddressStreet',
                $label,
                '35,250'
            );
        }
        else
        {
            $this->hForm->addTextareaInput(
                'hContactAddressStreet',
                $label,
                '35,2'
            );
        }

        return $this;
    }

    public function &getCity($label = 'y:City:')
    {
        # @return void

        # @description
        # <h2>Adding a City</h2>
        #
        # @end

        $this->hForm
            ->addValidationByComparison(
                'Your city must be entered in 100 characters or less.',
                '<=', 100
            )
            ->addTextInput(
                'hContactAddressCity',
                $label,
                '25,100'
            );

        return $this;
    }

    public function &getState($label = '')
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Adding a State Selection</h2>
        #
        # @end

        if (empty($label))
        {
            if (!empty($_POST['hLocationCountryId']))
            {
                $label = $this->hLocationCountries->selectColumn(
                    'hLocationStateLabel',
                    (int) $_POST['hLocationCountryId']
                );
            }
            else
            {
                $label = 'State';
            }

            $label .= ':';
        }

        //$form->addRequiredField('You did not specify your '.strtolower($label).'.');
        $this->hForm
            ->addValidationByCallback(
                'The '.strtolower($label).' you specified is not valid for the country you specified.',
                $this->hLocationValidation,
                'isStateId'
            )
            ->defineLabelCell(
                array(
                    'id' => 'hContactFormStateLabel'
                )
            )
            ->addSelectState(
                'hLocationStateId',
                $label,
                !empty($_POST['hLocationCountryId']) ?
                    $_POST['hLocationCountryId'] : $this->hContactFormDefaultCountryId(223),
                1
            );

        return $this;
    }

    public function &getPostalCode($label = 'P:Postal Code:')
    {
        # @return void

        # @description
        # <h2>Adding a Postal Code</h2>
        #
        # @end

        // Postal Code
        $this->hForm
            ->addValidationByComparison(
                'Your postal code must be entered in 15 characters or less.',
                '<=', 15
            )
            ->addValidationByCallback(
                'The postal code you entered is not valid for the country you selected.',
                $this->hLocationValidation,
                'isPostalCode'
            )
            ->addTextInput(
                'hContactAddressPostalCode',
                $label,
                '15,15'
            );

        return $this;
    }

    public function &getPhoneNumber($label = 'p:Telephone:')
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Adding a Phone Number</h2>
        #
        # @end

        if ($this->hContactPhoneNumberStrictFormat(false))
        {
            $this->hForm->addValidationByCallback(
                'Please specify your phone number in the format of '.
                '<b>'.$this->hContactValidation->getPhoneValidationPattern(false).'</b>.',
                $this->hContactValidation,
                'isPhoneNumber'
            );
        }

        $this->hForm
            ->addValidationByComparison(
                'Your phone number must be entered in 255 characters or less.',
                '<=', 255
            )
            ->addPhoneInput(
                'hContactPhoneNumber',
                $label,
                '20,255'
            );

        return $this;
    }

    public function &getCompanyNumber($label = 'x:Company Phone Number:')
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Adding an Company Number</h2>
        #
        # @end

        $this->hForm
            ->addValidationByCallback(
                'Please specify your company number in the format of '.
                '<b>'.$this->hContactValidation->getPhoneValidationPattern(false).'</b>.',
                $this->hContactValidation,
                'isPhoneNumber'
            )
            ->addValidationByComparison(
                'The company phone number must be entered in 255 characters or less.',
                '<=', 255
            )
            ->addTextInput(
                'hContactPhoneNumberCompany',
                $label,
                '20,255'
            );

        return $this;
    }

    public function &getAppointmentNumber($label = 'x:Appointment:')
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Adding an Appointment Number</h2>
        #
        # @end

        $this->hForm
            ->addValidationByCallback(
                'Please specify your appointment number in the format of '.
                '<b>'.$this->hContactValidation->getPhoneValidationPattern(false).'</b>.',
                $this->hContactValidation,
                'isPhoneNumber'
            )
            ->addValidationByComparison(
                'The appointment phone number must be entered in 255 characters or less.',
                '<=', 255
            )
            ->addTextInput(
                'hContactPhoneNumberAppointment',
                $label,
                '20,255'
            );

        return $this;
    }

    public function &getTollFreeNumber($label = 'x:Toll Free:')
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Adding an Toll Free Number</h2>
        #
        # @end

        $this->hForm
            ->addValidationByCallback(
                'Please specify your toll free number in the format of '.
                '<b>'.$this->hContactValidation->getPhoneValidationPattern(false).'</b>.',
                $this->hContactValidation,
                'isPhoneNumber'
            )
            ->addValidationByComparison(
                'The toll-free number must be entered in 255 characters or less.',
                '<=', 255
            )
            ->addTextInput(
                'hContactPhoneNumberTollFree',
                $label,
                '20,255'
            );

        return $this;
    }

    public function &getMainNumber($label = 'n:Main Phone Number:')
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Adding a Main Number</h2>
        #
        # @end

        $this->hForm
            ->addValidationByCallback(
                'Please specify your main number in the format of '.
                '<b>'.$this->hContactValidation->getPhoneValidationPattern(false).'</b>.',
                $this->hContactValidation,
                'isPhoneNumber'
            )
            ->addValidationByComparison(
                'The main phone number must be entered in 255 characters or less.',
                '<=', 255
            )
            ->addTextInput(
                'hContactPhoneNumberMain',
                $label,
                '20,255'
            );

        return $this;
    }

    public function &getSchedulingNumber($label = 'u:Scheduling Phone Number:')
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Adding a Scheduling Number</h2>
        #
        # @end

        $this->hForm
            ->addValidationByCallback(
                'Please specify your scheduling number in the format of '.
                '<b>'.$this->hContactValidation->getPhoneValidationPattern(false).'</b>.',
                $this->hContactValidation,
                'isPhoneNumber'
            )
            ->addValidationByComparison(
                'The scheduling phone number must be entered in 255 characters or less.',
                '<=', 255
            )
            ->addTextInput(
                'hContactPhoneNumberScheduling',
                $label,
                '20,255'
            );

        return $this;
    }

    public function &getFaxNumber($label = 'x:Fax:')
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Adding a Fax Number</h2>
        #
        # @end

        $this->hForm
            ->addValidationByCallback(
                'Please specify your fax number in the format of '.
                '<b>'.$this->hContactValidation->getPhoneValidationPattern(false).'</b>.',
                $this->hContactValidation,
                'isPhoneNumber'
            )
            ->addValidationByComparison(
                'The fax number must be entered in 255 characters or less.',
                '<=', 255
            )
            ->addTextInput(
                'hContactPhoneNumberFax',
                $label,
                '20,255'
            );

        return $this;
    }

    public function &getGender($label = 'Gender:')
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Adding a Gender Toggle</h2>
        #
        # @end

        $this->hForm
            ->setVariable(
                'hFormBlockWrapper',
                false
            )
            ->addRadioInput(
                'hContactGender',
                $label,
                array(
                    1 => 'Female',
                    2 => 'Male'
                )
            );

        return $this;
    }

    public function &getDateOfBirth($label = 'Date of Birth:')
    {
        # @return hContactFormLibrary

        # @description
        # <h2>Adding a Date of Birth Calendar Date Selector</h2>
        #
        # @end

        $this->jQuery('Datepicker');

        $this->hForm->addTextInput(
            'hContactDateOfBirth',
            $label,
            '10,10'
        );

        return $this;
    }

    public function getCoreMetrics($newsletter = nil, $subscribed = nil)
    {
        if ($_POST['hLocationCountryId'] == 223)
        {
            $registration = array(
                'hContactId' => $this->contact->hContactId,
                'hContactEmailAddress' => $_POST['hContactEmailAddress'],
                'hContactAddressCity' => $_POST['hContactAddressCity'],
                'hLocationStateCode' => $this->hDatabase->selectColumn(
                    'hLocationStateCode',
                    'hLocationStates',
                    (int) $_POST['hLocationStateId']
                ),
                'hContactAddressPostalCode' => $_POST['hContactAddressPostalCode'],
                'hLocationCountryName' => $this->hDatabase->selectColumn(
                    'hLocationCountryName',
                    'hLocationCountries',
                    (int) $_POST['hLocationCountryId']
                ),
                'hContactCategory' => $this->getPrivateCategory(),
                'hContactTitle' => $_POST['hContactTitle'],
                'hContactCompany' => $_POST['hContactTitle'],
                'hContactRegistered' => $this->isLoggedIn()
            );

            if ($newsletter != nil)
            {
                 $registration['newsletterName'] = $newsletter;
                 $registration['newsletterSubscribed'] = $subscribed;
            }

            return $this->getRegistrationTag($registration);
        }
        else
        {
            return '';
        }
    }
}


# @description
# <h1>Contact Form Examples</h1>
# <p>
#   The following example demonstrates how you would customize labels on
#   on a few fields, as well as customize the order of the fields in the
#   resulting form.  Finally, you also see how you'd use a callback function
#   to add custom fields to a contact form.
# </p>
# <code>
#   $this-&gt;hForm        = $this-&gt;hLibrary('hForm');
#   $this-&gt;hContactForm = $this-&gt;hLibrary('hContact/hContactForm');
#
#   $this->hContactForm-&gt;setLayout(
#       array(
#           'hContactFirstName' =&gt; array(
#               'label' =&gt; "Parent's First Name:"
#           ),
#           'hContactLastName' => array(
#               'label' =&gt; "Parent's Last Name:"
#           ),
#           'hContactAddressStreet',
#           'hContactAddressCity',
#           'hLocationStateId',
#           'hContactAddressPostalCode' =&gt; array(
#               'label' =&gt; 'Zip:'
#           ),
#           'hContactEmailAddress' =&gt; array(
#               // The email address field is disabled by default,
#               // so it must be explicitly enabled.
#               'enabled' =&gt; true
#           ),
#           'myCustomForm' =&gt; array(
#               &amp;$this, 'getForm'
#           )
#       )
#   );
#
#   $this-&gt;hForm-&gt;addDiv('MyCustomContactFormDiv');
#
#   $this-&gt;hContactForm-&gt;getForm();
#
#   $this-&gt;hFileDocument = $this-&gt;hForm-&gt;getForm('MyCustomContactForm');
# </code>
# <p>
#   If only the name of a field is provided, as in <var>'hContactAddressStreet'</var>
#   above, the field is included with all the default values for the options as
#   specified at the top of this documentation.  For <var>hContactAddressStreet</var>,
#   that means the field is <var>enabled</var>, <var>required</var> (<var>true</var> is the
#   default value for both of those options, for this particular field), its label is
#   <var>Street:</var>, and if the user does not include a value for the field, they will see
#   the error text: <i>You did not specify your street address</i> when they submit
#   the form, causing the form to fail to be submitted until the user types a
#   value for the field.
# </p>
# <p>
#   In terms of <var>hContactEmailAddress</var>, since its default value for
#   <var>enabled</var> is <var>false</var>, the <var>enabled</var> option must
#   be set to <var>true</var> for the field to appear on the form, for the other
#   options, they remain set to the default values.
# </p>
# <p>
#   The callback function specified above might look something like this:
# </p>
# <code>
#    public function getForm()
#    {
#        $this-&gt;hForm-&gt;addRequiredField('Please enter your child\'s first name.');
#        $this-&gt;hForm-&gt;addValidationByComparison('Your child\'s first name must be entered in 50 characters or less.', '&lt;=', 50);
#        $this-&gt;hForm-&gt;addTextInput('childFirstName', 'Child\'s First Name:', '25,50');
#
#        $this-&gt;hForm-&gt;addRequiredField('Please enter your child\'s last name.');
#        $this-&gt;hForm-&gt;addValidationByComparison('Your child\'s last name must be entered in 50 characters or less.', '&lt;=', 50);
#        $this-&gt;hForm-&gt;addTextInput('childLastName', 'Child\'s Last Name:', '25,50');
#
#        $this-&gt;hForm-&gt;addRequiredField('Please enter your child\'s grade.');
#        $this-&gt;hForm-&gt;addTextInput('childGrade', 'Child\'s Grade:', '2,2');
#
#        $this-&gt;hForm-&gt;addRequiredField('Please enter your child\'s school.');
#        $this-&gt;hForm-&gt;addValidationByComparison('Your child\'s school must be entered in 50 characters or less.', '&lt;=', 50);
#        $this-&gt;hForm-&gt;addTextInput('childSchool', 'Child\'s School:', '25,50');
#
#        $this-&gt;hForm-&gt;addTableCell('');
#        $this-&gt;hForm-&gt;addSubmitButton('MyCustomContactFormSubmit', 'Submit');
#    }
# </code>
# @end

?>