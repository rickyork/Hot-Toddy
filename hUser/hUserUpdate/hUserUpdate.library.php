<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy User Update Library
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

class hUserUpdateLibrary extends hPlugin {

    private $hUserDatabase;
    private $hContactDatabase;
    private $hContactValidation;
    private $hLocation;

    public function hConstructor($users)
    {
        if (isset($users->users) && is_object($users) && is_array($users->users))
        {
            $users = $users->users;
        }

        $this->hUserDatabase = $this->database('hUser');
        $this->hContactDatabase = $this->database('hContact');
        $this->hContactValidation = $this->library('hContact/hContactValidation');
        $this->hLocation = $this->library('hLocation');

        if (is_array($users) && count($users))
        {
            foreach ($users as $user)
            {
                $userId = $this->user->getUserId($user->name);

                if (!$userId)
                {
                    $userId = 0;
                }

                $this->console("Existing userId: '{$userId}'");

                $userId = $this->hUserDatabase->save(
                    isset($user->id) ? $user->id : $userId,
                    $user->name,
                    $user->email,
                    isset($user->password) ? $user->password : $this->getRandomString(15, true, true),
                    isset($user->isActivated) ? (int) $user->isActivated : 1
                );

                $this->console("Saved to userId: '{$userId}'");

                if ($user->isGroup)
                {
                    $this->hUserDatabase->deleteGroupMembers($userId);

                    $group = $this->getGroupProperties($user);

                    $this->hUserDatabase->saveGroupProperties(
                        $userId,
                        $group->owner,
                        $group->isElevated,
                        $group->password,
                        $group->loginEnabled
                    );

                    $this->hUserDatabase->deleteGroupMembers($userId);

                    if (isset($user->users) && is_array($user->users))
                    {
                        $this->hUserDatabase->addUsersToGroup(
                            $userId,
                            $user->users
                        );
                    }
                    else
                    {
                        $this->console("No users were specified as group members.");
                    }

                    if (isset($user->groups) && is_array($user->groups))
                    {
                        $this->hUserDatabase->addGroupsToGroup(
                            $userId,
                            $user->groups
                        );
                    }
                    else
                    {
                        $this->console("No groups were specified as group members.");
                    }
                }
                else
                {
                    // Remove group properties, and group members.
                    $this->hUserDatabase->deleteGroupProperties($userId);
                }

                if (isset($user->variables))
                {
                    $this->user->deleteVariables($userId);

                    foreach ($user->variables as $variable => $value)
                    {
                        $this->user->saveVariable(
                            $variable,
                            $value,
                            $userId
                        );
                    }
                }

                if (isset($user->contact))
                {
                    $contact = $user->contact;

                    $firstName = isset($contact->firstName) ? trim($contact->firstName) : '';
                    $lastName = isset($contact->lastName) ? trim($contact->lastName) : '';

                    $displayName = trim($firstName.' '.$lastName);

                    $dateOfBirth = 0;

                    if (isset($contact->dateOfBirth) && !empty($contact->dateOfBirth))
                    {
                        if (is_numeric($contact->dateOfBirth))
                        {
                            $dateOfBirth = $contact->dateOfBirth;
                        }
                        else
                        {
                            $dateOfBirth = strtotime($contact->dateOfBirth);
                        }
                    }

                    $gender = 0;

                    if (isset($contact->gender))
                    {
                        if (is_numeric($contact->gender))
                        {
                            $gender = (int) $contact->gender;
                        }
                        else
                        {
                            switch (strtolower($contact->gender))
                            {
                                case 'female':
                                {
                                    $gender = 0;
                                    break;
                                }
                                case 'male':
                                {
                                    $gender = 1;
                                    break;
                                }
                                default:
                                {
                                    $gender = -1;
                                    break;
                                }
                            }
                        }
                    }

                    $addressBookId = 1;

                    if (isset($contact->addressBook))
                    {
                        if (is_numeric($contact->addressBook))
                        {
                            $addressBookId = $contact->addressBook;
                        }
                        else
                        {
                            if ($this->hContactDatabase->addressBookExists($contact->addressBook))
                            {
                                $addressBookId = $this->hContactDatabase->getAddressBookId($contact->addressBook);
                            }
                            else
                            {
                                $addressBookId = $this->hContactDatabase->saveAddressBook(
                                    array(
                                        'hContactAddressBookId' => 0,
                                        'hUserId' => 1,
                                        'hContactAddressBookName' => $contact->addressBook,
                                        'hPlugin' => '',
                                        'hContactAddressBookIsDefault' => 0
                                    )
                                );
                            }
                        }
                    }
                    else if (isset($contact->addressBookId))
                    {
                        $addressBookId = (int) $contact->addressBookId;
                    }

                    $contactId = $this->user->getContactId($userId);

                    $contactId = $this->hContactDatabase->save(
                        array(
                            'hContactFirstName' => $firstName,
                            'hContactLastName' => $lastName,
                            'hContactDisplayName' => $displayName,
                            'hContactNickName' => isset($contact->nickName) ? $contact->nickName : '',
                            'hContactWebsite' => isset($contact->website) ? $contact->website : '',
                            'hContactCompany' => isset($contact->company) ? $contact->company : '',
                            'hContactTitle' => isset($contact->title) ? $contact->title : '',
                            'hContactDepartment' => isset($contact->department) ? $contact->department : '',
                            'hContactGender' => $gender,
                            'hContactDateOfBirth' => $dateOfBirth
                        ),
                        $addressBookId,
                        $userId,
                        $contactId
                    );

                    if (isset($contact->addresses) && is_array($contact->addresses))
                    {
                        $addresses = $contact->addresses;

                        $this->hContactAddresses->deleteAddresses($contactId);

                        foreach ($addresses as $address)
                        {
                            $street = '';

                            if (isset($address->street))
                            {
                                if (is_array($address->street))
                                {
                                    $street = implode("\n", $address->street);
                                }
                                else
                                {
                                    $street = $address->street;
                                }
                            }

                            $countryId = 223;

                            if (isset($address->country))
                            {
                                $field = 'name';

                                if (strlen($address->country) == 2)
                                {
                                    $field = 'iso2';
                                }
                                else if (strlen($address->country) == 3)
                                {
                                    $field = 'iso3';
                                }

                                $countryId = $this->hLocation->getCountryId(
                                    $address->country,
                                    $field
                                );
                            }

                            $stateId = 0;

                            if (isset($address->state))
                            {
                                if (strlen($address->state) == 2)
                                {
                                    $stateId = $this->hLocation->getStateId(
                                        $countryId,
                                        $address->state
                                    );
                                }
                                else
                                {
                                    $stateId = $this->hLocation->getStateByName(
                                        $countryId,
                                        $address->state
                                    );
                                }
                            }

                            $fileId = 0;

                            if (isset($address->fileId))
                            {
                                $fileId = $address->fileId;
                            }
                            else if (isset($address->filePath))
                            {
                                $fileId = $this->getFileIdByFilePath($address->filePath);
                            }

                            $this->hContactDatabase->saveAddress(
                                array(
                                    'hContactAddressStreet'         => $street,
                                    'hContactAddressCity'           => isset($address->city) ? $address->city : '',
                                    'hLocationStateId'              => $stateId,
                                    'hContactAddressPostalCode'     => isset($address->postalCode) ? $address->postalCode : '',
                                    'hLocationCountyId'             => isset($address->countyId) ? $address->countyId : 0,
                                    'hLocationCountryId'            => $countryId,
                                    'hFileId'                       => $fileId,
                                    'hContactAddressOperatingHours' => isset($address->operatingHours) ? $address->operatingHours : ''
                                ),
                                isset($address->fieldId) ? (int) $address->fieldId : 2
                            );
                        }
                    }

                    if (isset($contact->emailAddresses) && is_array($contact->emailAddresses))
                    {
                        $this->hContactDatabase->deleteEmailAddresses($contactId);

                        foreach ($contact->emailAddresses as $emailAddress)
                        {
                            $this->hContactDatabase->saveEmailAddress(
                                $emailAddress->emailAddress,
                                isset($emailAddress->fieldId) ? (int) $emailAddress->fieldId : 20,
                                0,
                                $contactId
                            );
                        }
                    }

                    if (isset($contact->phoneNumbers) && is_array($contact->phoneNumbers))
                    {
                        $this->hContactDatabase->deletePhoneNumbers($contactId);

                        foreach ($contact->phoneNumbers as $phoneNumber)
                        {
                            $this->hContactDatabase->savePhoneNumber(
                                $phoneNumber->phoneNumber,
                                isset($emailAddress->fieldId) ? (int) $emailAddress->fieldId : 22,
                                0,
                                $contactId
                            );
                        }
                    }

                    if (isset($contact->internetAccounts))
                    {
                        $this->hContactDatabase->deleteInternetAccounts($contactId);

                        foreach ($contact->internetAccounts as $internetAccount)
                        {
                            $this->hContactDatabase->saveInternetAccount(
                                $internetAccount->internetAccount,
                                isset($emailAddress->fieldId) ? (int) $emailAddress->fieldId : 29,
                                0,
                                $contactId
                            );
                        }
                    }
                }
            }
        }
    }

    private function getGroupProperties(&$user)
    {
        if (isset($user->group) && is_object($user->group))
        {
            if (!isset($user->group->owner))
            {
                $user->group->owner = 1;
            }
            else if (!empty($user->group->owner) && is_numeric($user->group->owner))
            {
                $user->group->owner = $this->user->getUserId($uesr->group->owner);
            }

            if (!isset($user->group->isElevated))
            {
                $user->group->isElevated = false;
            }

            if (!isset($user->group->password))
            {
                $user->group->password = $this->getRandomString(15, true, true);
            }

            if (!isset($user->group->loginEnabled))
            {
                $user->group->loginEnabled = false;
            }

            return $user->group;
        }
        else
        {
            $group = array(
                'owner' => 1,
                'isElevated' => false,
                'password' => $this->getRandomString(15, true, true),
                'loginEnabled' => false
            );

            return (object) $group;
        }
    }
}

?>