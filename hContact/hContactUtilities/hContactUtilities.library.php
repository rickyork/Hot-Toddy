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

class hContactUtilitiesLibrary extends hPlugin {

    public function cleanUpEmailAddresses()
    {
        $query = $this->hContactEmailAddresses->select();

        $addresses = array();

        foreach ($query as $data)
        {
            if (strstr($data['hContactEmailAddress'], 'LDAPv3') || strstr($data['hContactEmailAddress'], '127.0.0.1'))
            {
                $data['hContactEmailAddress'] = '';
            }

            if (!empty($data['hContactEmailAddress']) && !in_array($data['hContactId'].$data['hContactEmailAddress'].$data['hContactFieldId'], $addresses))
            {
                $addresses[$data['hContactEmailAddressId']] = $data['hContactId'].$data['hContactEmailAddress'].$data['hContactFieldId'];
            }
            else
            {
                $this->hContactEmailAddresses->delete(
                    'hContactEmailAddressId',
                    $data['hContactEmailAddressId']
                );
            }
        }
    }

    public function cleanUpPhoneNumbers()
    {
        $query = $this->hContactPhoneNumbers->select();

        $numbers = array();

        foreach ($query as $data)
        {
            if (strstr($data['hContactPhoneNumber'], 'LDAPv3') || strstr($data['hContactPhoneNumber'], '127.0.0.1'))
            {
                $data['hContactPhoneNumber'] = '';
            }

            preg_match_all('/\d+/', $data['hContactPhoneNumber'], $matches);

            if (!empty($data['hContactPhoneNumber']) && isset($matches[0]) && is_array($matches[0]) && count($matches[0]) && !in_array($data['hContactId'].$data['hContactPhoneNumber'].$data['hContactFieldId'], $numbers))
            {
                $numbers[$data['hContactPhoneNumberId']] = $data['hContactId'].$data['hContactPhoneNumber'].$data['hContactFieldId'];
            }
            else
            {
                $this->hContactPhoneNumbers->delete(
                    'hContactPhoneNumberId',
                    $data['hContactPhoneNumberId']
                );
            }
        }
    }
}

?>