<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Contact Directory Library
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
# <h1>Contact Open Directory/Active Directory Integration</h1>
# <p>
#
# </p>
# @end

class hContactDirectoryLibrary extends hPlugin {

    private $hLocation;

    private $keyMap = array(
        'hContactFirstName'    => 'FirstName',
        'hContactLastName'     => 'LastName',
        'hContactDisplayName'  => 'RealName',
        'hContactCompany'      => 'Company',
        'hContactTitle'        => 'JobTitle',
        'hContactDepartment'   => 'Department'
    );

    private $addressMap = array(
        'hContactAddressStreet'     => 'Street',
        'hContactAddressCity'       => 'City',
        'hLocationStateId'          => 'State',
        'hContactAddressPostalCode' => 'PostalCode'
    );

    private $emailMap = array(
        20 => 'EMailAddress'
    );

    private $phoneMap = array(
        6  => 'PhoneNumber',
        5  => 'MobileNumber',
        9  => 'FAXNumber',
        10 => 'PagerNumber'
    );

    private $enabledFields = array();
    private $userName = nil;

    public function hConstructor()
    {
        # @return void

        if (!$this->hContactDirectoryAdministratorUser(nil))
        {
            $this->warning(
                "No administrator username is set.",
                __FILE__,
                __LINE__
            );
        }

        if (!$this->hContactDirectoryAdministratorPassword(nil))
        {
            $this->warning(
                "No administrator password is set.",
                __FILE__,
                __LINE__
            );
        }

        if (is_array($this->hContactDirectoryEnabledFields))
        {
            $this->enabledFields = $this->hContactDirectoryEnabledFields;
        }

        $this->hLocation = $this->library('hLocation');
    }

    public function setUser($user, $overrideCheck = false)
    {
        # @return void

        # @description
        # <h2>Setting the User</h2>
        # <p>
        #   Defines the user Open Directory / Active Directory operations should be
        #   carried out on. The <var>$user</var> should be passed as the username or
        #   a username alias.
        # </p>
        # <p>
        #   <var>$overrideCheck</var> is a boolean that when true disables a check
        #   to see that the user exists and is a valid Open Directory / Active Directory
        #   user.
        # </p>
        # @end

        if (!$overrideCheck)
        {
            $userId = $this->user->getUserId($user);

            if (!$this->user->isDirectoryUser($userId))
            {
                $this->userName = '';
                return;
            }
        }

        $this->userName = hString::entitiesToUTF8($user, false);
    }

    public function userSet()
    {
        # @return boolean

        # @description
        # <h2>Determining if a User is Set</h2>
        # <p>
        #   Checks to see if a user has been set.
        # </p>
        # @end

        return !empty($this->userName);
    }

    public function isEnabled($key)
    {
        # @return boolean

        # @description
        # <h2>Checking Whether or Not a Key is Enabled</h2>
        # <p>
        #   Checks the provided <var>$key</var> to see whether or not it is enabled.
        #   The <var>$key</var> corresponds to a column name used by Hot Toddy. For example,
        #   hContactFirstName, hContactLastName indicate that the first and last name records
        #   are enabled for editting and when that information is updated within Hot Toddy,
        #   it will be written back to Open Directory / Active Directory. The various internal
        #   properties: <var>$keyMap</var>, <var>$addressMap</var>, <var>emailMap</var>, and
        #   <var>$phoneMap</var> map Hot Toddy's field names to Open Directory / Active Directory
        #   field names.
        # </p>
        # <p>
        #   The internal <var>$enabledFields</var> property is an array that contains the
        #   contents of the <var>hContactDirectoryEnabledFields</var> configuration variable,
        #   which defines all of the fields that can be updated within Active Directory /
        #   Open Directory by Hot Toddy.
        # </p>
        # @end

        if (is_array($this->enabledFields) && count($this->enabledFields))
        {
            return in_array($key, $this->enabledFields);
        }

        return true;
    }

    public function &save(array $contact)
    {
        # @return hContactDirectoryLibrary

        # @description
        # <h2>Saving Contact Data</h2>
        # <p>
        #   Takes the array of data provided in the <var>$contact</var> argument and
        #   saves it to Open Directory / Active Directory.
        # </p>
        # @end

        if (empty($this->userName))
        {
            return;
        }

        foreach ($contact as $key => $value)
        {
            if (isset($this->keyMap[$key]) && $this->isEnabled($key))
            {
                $this->saveKey($this->keyMap[$key], $value);
            }
        }

        return $this;
    }

    public function &saveAddress(array $contactAddress, $contactFieldId)
    {
        # @return hContactDirectoryLibrary

        # @description
        # <h2>Saving a Contact's Address</h2>
        # <p>
        #   Takes the array of data provided in the <var>$contactAddress</var> argument
        #   and saves it to Open Directory / Active Directory.
        # </p>
        # @end

        if ($contactFieldId == 2)
        {
            foreach ($contactAddress as $key => $value)
            {
                if (isset($this->addressMap[$key]) && $this->isEnabled($key))
                {
                    if ($key == 'hLocationStateId')
                    {
                        $value = $this->hLocation->getStateName($value);
                    }

                    $this->saveKey($this->addressMap[$key], $value);
                }
            }
        }

        return $this;
    }

    public function &saveEmailAddress($contactEmailAddress, $contactFieldId)
    {
        # @return hContactDirectoryLibrary

        # @description
        # <h2>Saving an Email Address</h2>
        # <p>
        #   Saves the provided email address to Open Directory / Active Directory.
        # </p>
        # @end

        foreach ($this->emailMap as $key => $value)
        {
            if ($contactFieldId == $key && $this->isEnabled('hContactEmailAddress'))
            {
                $this->saveKey($value, $contactEmailAddress);
            }
        }

        return $this;
    }

    public function &savePhoneNumber($contactPhoneNumber, $contactFieldId)
    {
        # @return hContactDirectoryLibrary

        # @description
        # <h2>Saving a Phone Number</h2>
        # <p>
        #   Saves the provided phone number to Open Directory / Active Directory.
        # </p>
        # @end

        foreach ($this->phoneMap as $key => $value)
        {
            $fieldName = '';

            switch ($value)
            {
                case 'PhoneNumber':
                {
                    $fieldName = 'hContactPhoneNumber';
                    break;
                }
                case 'MobileNumber':
                {
                    $fieldName = 'hContactPhoneNumberMobile';
                    break;
                }
                case 'FAXNumber':
                {
                    $fieldName = 'hContactPhoneNumberFax';
                    break;
                }
                case 'PagerNumber':
                {
                    $fieldName = 'hContactPhoneNumberPager';
                    break;
                }
            }

            if ($contactFieldId == $key && $this->isEnabled($fieldName))
            {
                $this->saveKey($value, $contactPhoneNumber);
            }
        }

        return $this;
    }

    public function &saveKey($key, $value)
    {
        # @return hContactDirectoryLibrary

        # @description
        # <h2>Saving a Directory Key</h2>
        # <p>
        #   Saves the specified directory key to Open Directory / Active Directory.
        # </p>
        # @end

        $value = hString::entitiesToUTF8($value, false);
        $existingValue = $this->getKey($key, false);

        if (empty($value))
        {
            $result = $this->pipeCommand(
                '/usr/bin/dscl',
                '-u '.escapeshellarg($this->hContactDirectoryAdministratorUser(nil)).' '.
                '-P '.escapeshellarg($this->hContactDirectoryAdministratorPassword(nil)).' '.
                escapeshellarg($this->hContactDirectoryPath('.')).' '.
                '-delete '.escapeshellarg('/Users/'.$this->userName).' '.$key,
                1, false
            );
        }
        else
        {
            if (!$existingValue)
            {
                $result = $this->pipeCommand(
                    '/usr/bin/dscl',
                    '-u '.escapeshellarg($this->hContactDirectoryAdministratorUser(nil)).' '.
                    '-P '.escapeshellarg($this->hContactDirectoryAdministratorPassword(nil)).' '.
                    escapeshellarg($this->hContactDirectoryPath('.')).' '.
                    '-merge '.escapeshellarg('/Users/'.$this->userName).' '.$key.' '.escapeshellarg($value),
                    1, false
                );
            }
            else
            {
                $result = $this->pipeCommand(
                    '/usr/bin/dscl',
                    '-u '.escapeshellarg($this->hContactDirectoryAdministratorUser(nil)).' '.
                    '-P '.escapeshellarg($this->hContactDirectoryAdministratorPassword(nil)).' '.
                    escapeshellarg($this->hContactDirectoryPath('.')).' '.
                    '-change '.escapeshellarg('/Users/'.$this->userName).' '.$key.' '.escapeshellarg($existingValue).' '.escapeshellarg($value),
                    1, false
                );
            }
        }

        return $this;
    }

    public function getName()
    {
        # @return string

        # @description
        # <h2>Getting the RealName Key</h2>
        # <p>
        #   Returns the value of the RealName key from Open Directory / Active Directory.
        # </p>
        # @end

        return $this->getKey('RealName');
    }

    public function getPhoto()
    {
        //dscl . -read /Users/richard JPEGPhoto | tail -1 | xxd -r -p
    }

    public function getFirstName()
    {
        # @return string

        # @description
        # <h2>Getting the FirstName Key</h2>
        # <p>
        #   Returns the value of the FirstName key from Open Directory / Active Directory.
        # </p>
        # @end

        return $this->getKey('FirstName');
    }

    public function getLastName()
    {
        # @return string

        # @description
        # <h2>Getting the LastName Key</h2>
        # <p>
        #   Returns the value of the LastName key from Open Directory / Active Directory.
        # </p>
        # @end

        return $this->getKey('LastName');
    }

    public function getEmailAddress()
    {
        # @return string

        # @description
        # <h2>Getting the EMailAddress Key</h2>
        # <p>
        #   Returns the value of the EMailAddress key from Open Directory / Active Directory.
        # </p>
        # @end

        return $this->getKey('EMailAddress');
    }

    public function getTitle()
    {
        # @return string

        # @description
        # <h2>Getting the JobTitle Key</h2>
        # <p>
        #   Returns the value of the JobTitle key from Open Directory / Active Directory.
        # </p>
        # @end

        return $this->getKey('JobTitle');
    }

    public function getCompany()
    {
        # @return string

        # @description
        # <h2>Getting the Company Key</h2>
        # <p>
        #   Returns the value of the Company key from Open Directory / Active Directory.
        # </p>
        # @end

        return $this->getKey('Company');
    }

    public function getPhoneNumber()
    {
        # @return string

        # @description
        # <h2>Getting the PhoneNumber Key</h2>
        # <p>
        #   Returns the value of the PhoneNumber key from Open Directory / Active Directory.
        # </p>
        # @end

        return $this->getKey('PhoneNumber');
    }

    public function getMobileNumber()
    {
        # @return string

        # @description
        # <h2>Getting the MobileNumber Key</h2>
        # <p>
        #   Returns the value of the MobileNumber key from Open Directory / Active Directory.
        # </p>
        # @end

        return $this->getKey('MobileNumber');
    }

    public function getFaxNumber()
    {
        # @return string

        # @description
        # <h2>Getting the FAXNumber Key</h2>
        # <p>
        #   Returns the value of the FAXNumber key from Open Directory / Active Directory.
        # </p>
        # @end

        return $this->getKey('FAXNumber');
    }

    public function getPagerNumber()
    {
        # @return string

        # @description
        # <h2>Getting the PagerNumber Key</h2>
        # <p>
        #   Returns the value of the PagerNumber key from Open Directory / Active Directory.
        # </p>
        # @end

        return $this->getKey('PagerNumber');
    }

    public function getDepartment()
    {
        # @return string

        # @description
        # <h2>Getting the Department Key</h2>
        # <p>
        #   Returns the value of the Department key from Open Directory / Active Directory.
        # </p>
        # @end
        return $this->getKey('Department');
    }

    public function getStreet()
    {
        # @return string

        # @description
        # <h2>Getting the Street Key</h2>
        # <p>
        #   Returns the value of the Street key from Open Directory / Active Directory.
        # </p>
        # @end

        return $this->getKey('Street');
    }

    public function getCity()
    {
        # @return string

        # @description
        # <h2>Getting the City Key</h2>
        # <p>
        #   Returns the value of the City key from Open Directory / Active Directory.
        # </p>
        # @end

        return $this->getKey('City');
    }

    public function getState()
    {
        # @return string

        # @description
        # <h2>Getting the State Key</h2>
        # <p>
        #   Returns the <var>hLocationStateId</var> of the State key from Open Directory / Active Directory.
        # </p>
        # @end

        return $this->hLocation->getStateId(223, $this->getKey('State'));
    }

    public function getCountry()
    {
        # @return string

        # @description
        # <h2>Getting the RealName Key</h2>
        # <p>
        #   Returns the <var>hLocationCountryId</var> of the Country key from Open Directory / Active Directory.
        #   If no country is specified, then the default country is returned, 223, United States.
        # </p>
        # @end

        $country = $this->getKey('Country');

        if (!$country)
        {
            $country = 'US';
        }

        return $this->hLocation->getCountryId($country);
    }

    public function getPostalCode()
    {
        # @return string

        # @description
        # <h2>Getting the PostalCode Key</h2>
        # <p>
        #   Returns the value of the PostalCode key from Open Directory / Active Directory.
        # </p>
        # @end

        return $this->getKey('PostalCode');
    }

    public function getRecordNames()
    {
        # @return string

        # @description
        # <h2>Getting the RecordName Key</h2>
        # <p>
        #   Returns the value of the RecordName key from Open Directory / Active Directory as
        #   an array.
        # </p>
        # @end

        $recordNames =  $this->getKey('RecordName');

        if (strstr($recordNames, "\n"))
        {
            return explode("\n", $recordNames);
        }
        else
        {
            return explode(" ", $recordNames);
        }
    }

    public function getKey($key, $encodeResult = true)
    {
        # @return string

        # @description
        # <h2>Getting a Directory Key</h2>
        # <p>
        #   Returns the value of the specified <var>$key</var> from Open Directory / Active Directory,
        #   encoded for HTML special characters. If the <var>$encodeResult</var> argument is
        #   false, then the return value is not encoded.
        # </p>
        # @end

        $result = $this->pipeCommand(
            '/usr/bin/dscl',
            ($this->hContactDirectoryAdministratorUser(nil)? '-u '.escapeshellarg($this->hContactDirectoryAdministratorUser(nil)).' ' : '').
            ($this->hContactDirectoryAdministratorPassword(nil)? '-P '.escapeshellarg($this->hContactDirectoryAdministratorPassword(nil)).' ' : '').
            escapeshellarg($this->hContactDirectoryPath('.')).' '.
            '-read '.escapeshellarg('/Users/'.$this->userName).' '.$key,
            1,
            false
        );

        $result = strstr($result, 'No such key:')? '' : trim(substr($result, strlen($key.':')));

        return $encodeResult? hString::encodeHTML($result) : $result;
    }
}

?>