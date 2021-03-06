<?php

class hContactInternetAccounts_2to3 extends hPlugin {

    public function hConstructor()
    {
        $this->hContactInternetAccounts
            ->addColumn('hContactAddressId', hDatabase::id, 'hContactInternetAccountId')
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
                $this->hContactInternetAccounts->update(
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
        $this->hContactInternetAccounts
            ->dropKey('hContactAddressId')
            ->dropKey(array('hContactId', 'hContactAddressId'))
            ->dropColumn('hContactAddressId');
    }
}

?>