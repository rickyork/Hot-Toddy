<?php

class hContactPhoneNumbers_2to3 extends hPlugin {

    public function hConstructor()
    {
        $this->hContactPhoneNumbers
            ->addColumn('hContactAddressId', hDatabase::id, 'hContactPhoneNumberId')
            ->addKey('hContactAddressId')
            ->addKey(array('hContactId', 'hContactAddressId'));
            
        $contactIds = $this->hContacts->select('hContactId');

        foreach ($contactIds as $contactId)
        {
            $addressIds = $this->hContactAddresses->select(
                'hContactAddressId', 
                array(
                    'hContactId' => $contactId
                )    
            );

            if (is_array($addressIds))
            {
                $addressId = array_shift($addressIds);
            }
            else
            {
                $addressId = $addressIds;
            }

            if ($addressId > 0)
            {
                $this->hContactPhoneNumbers->update(
                    array(
                        'hContactAddressId' => $addressId
                    ),
                    array(
                        'hContactId' => $contactId,
                        'hContactAddressId' => 0
                    )
                );
            }
        }
    }

    public function undo()
    {
        $this->hContactPhoneNumbers
            ->dropKey('hContactAddressId')
            ->dropKey(array('hContactId', 'hContactAddressId'))
            ->dropColumn('hContactAddressId');
    }
}

?>