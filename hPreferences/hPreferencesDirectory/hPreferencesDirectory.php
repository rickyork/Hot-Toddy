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

class hPreferencesDirectory extends hPlugin {

    private $hForm;

    public function hConstructor()
    {
        $this->plugin('hApplication/hApplicationForm');
        
        $this->getPluginFiles();
        
        $this->hForm = $this->library('hForm');
        
        
        $this->hFileDocument = $this->getForm();
    }   
        
    public function getForm()
    {
        $this->hForm->addDiv('hPreferencesDirectoryDiv');
        $this->hForm->addFieldset('Directory Utility', '100%', '200px,');
        
        $this->hForm->defineCell(2);
        
        $this->hForm->addTableCell($this->getTemplate('Overview'));
        
        $enabled = $this->hContactDirectoryEnabled(false);

        $this->hForm->addSelectInput(
            'hPreferencesDirectoryPath',
            'Directory Location:',
            array(
                '' => '',
                '.' => 'Local Mac OS X Server',
                '/LDAPv3/' => 'Open Directory',
                '/Active Directory/All Domains/' => 'Microsoft Active Directory'
            ),
            1,
            dirname($this->hContactDirectoryPath).'/'
        );

        $this->hForm->addTextInput('hPreferencesDirectoryDomain', 'Directory Domain:', 40, basename($this->hContactDirectoryPath));
        
        if (!$enabled)
        {
            $this->hForm->addTableCell('');
            $this->hForm->addSubmitButton('hPreferencesDirectoryJoin', 'Join');
        }
        else
        {
            $this->hForm->defineCell(2);
            $this->hForm->addTableCell($this->getTemplate('Administrator'));
        
            $this->hForm->addTextInput('hContactDirectoryAdministratorUser', 'Administrator User:', 25, $this->hContactDirectoryAdministratorUser(null));
            $this->hForm->addPasswordInput('hContactDirectoryAdministratorPassword', 'Administrator Password:', 25, $this->hContactDirectoryAdministratorPassword(null));
            
            $this->hForm->defineCell(2);
            $this->hForm->addTableCell($this->getTemplate('Limit Fields'));
            
            $this->hForm->addSelectInput(
                'hContactDirectoryEnabledFields',
                'Enabled Fields: -L',
                array(
                    'hContactFirstName'         => 'First Name',
                    'hContactLastName'          => 'Last Name',
                    'hContactDisplayName'       => 'Display Name',
                    'hContactCompany'           => 'Company',
                    'hContactTitle'             => 'Title',
                    'hContactDepartment'        => 'Department',
                    'hContactAddressStreet'     => 'Street',
                    'hContactAddressCity'       => 'City',
                    'hLocationStateId'          => 'State',
                    'hContactAddressPostalCode' => 'Postal Code',
                    'hContactPhoneNumber'       => 'Phone Number',
                    'hContactPhoneNumberMobile' => 'Mobile Number',
                    'hContactPhoneNumberFax'    => 'Fax Number',
                    'hContactPhoneNumberPager'  => 'Pager Number',
                    'hContactEmailAddress'      => 'Email Address'
                ),
                10,
                $this->hContactDirectoryEnabledFields(array()),
                'multiple'
            );
        }
        
        return $this->hForm->getForm();
    }
}

?>