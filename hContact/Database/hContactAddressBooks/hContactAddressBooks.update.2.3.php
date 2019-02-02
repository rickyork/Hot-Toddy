<?php

class hContactAddressBooks_2to3 extends hPlugin {

    public function hConstructor()
    {        
        $this->hContactAddressBooks->addColumn('hContactAddressBookLastModifiedBy', hDatabase::id, 'hContactAddressBookLastModified');
    }
    
    public function undo()
    {
        $this->hContactAddressBooks->dropColumn('hContactAddressBookLastModifiedBy');
    }
}

?>