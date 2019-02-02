<?php

class hContactAddressBooks_1to2 extends hPlugin {

    public function hConstructor()
    {        
        $this->hContactAddressBooks->addColumn('hContactAddressBookCreated', hDatabase::time, 'hContactAddressBookIsDefault');
        $this->hContactAddressBooks->addColumn('hContactAddressBookLastModified', hDatabase::time, 'hContactAddressBookCreated');
    }
    
    public function undo()
    {
        $this->hContactAddressBooks->dropColumns('hContactAddressBookCreated', 'hContactAddressBookLastModified');
    }
}

?>