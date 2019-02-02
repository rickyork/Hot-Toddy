<?php

class hContactAddresses_4to5 extends hPlugin {

    public function hConstructor()
    {
        $this->hContactAddresses
            ->addColumn('hFileId', hDatabase::id, 'hContactAddressLongitude')
            ->addColumn('hContactAddressOperatingHours', hDatabase::text, 'hFileId');
    }

    public function undo()
    {
        $this->hContactAddresses->dropColumn('hFileId');
    }
}

?>