<?php

class hContactAddresses_2to3 extends hPlugin {

    public function hConstructor()
    {        
        $this->hContactAddresses->addColumn('hLocationCountyId', hDatabase::id, 'hContactAddressPostalCode');   
    }
    
    public function undo()
    {
        $this->hContactAddresses->dropColumn('hLocationCountyId');
    }
}

?>