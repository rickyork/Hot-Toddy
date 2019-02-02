<?php

class hContactEmailAddresses_2to3 extends hPlugin {

    public function hConstructor()
    {
        $this->hContactEmailAddresses
            ->addColumn('hContactAddressId', hDatabase::id, 'hContactEmailAddressId')
            ->addKey('hContactAddressId')
            ->addKey(array('hContactId', 'hContactAddressId'));
            
        $contactIds = $this->hContacts->select('hContactId');

        foreach ($contactIds as $contactId)
        {
            $addressIds = $this->hContactAddresses->selectColumn(
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
                $this->hContactEmailAddresses->update(
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
        $this->hContactEmailAddresses
            ->dropKey('hContactAddressId')
            ->dropKey(array('hContactId', 'hContactAddressId'))
            ->dropColumn('hContactAddressId');
    }
}

?>