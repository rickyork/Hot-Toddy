<?php

class hContactAddresses_1to2 extends hPlugin {

    public function hConstructor()
    {
        $this->hContactAddresses->addIndex(
            array(
                'hContactAddressLatitude',
                'hContactAddressLongitude'
            )
        );
    }
    
    public function undo()
    {
        $this->hContactAddresses->dropIndex(
            array(
                'hContactAddressLatitude',
                'hContactAddressLongitude'
            )
        );
    }
}

?>