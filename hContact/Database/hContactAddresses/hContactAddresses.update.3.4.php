<?php

class hContactAddresses_3to4 extends hPlugin {

    public function hConstructor()
    {
        $this->hContactAddresses
            ->appendColumn('hContactAddressCreated', hDatabase::time)
            ->appendColumn('hContactAddressLastModified', hDatabase::time)
            ->appendColumn('hContactAddressLastModifiedBy', hDatabase::id);
    }

    public function undo()
    {
        $this->hContactAddresses->dropColumns(
            'hContactAddressCreated',
            'hContactAddressLastModified',
            'hContactAddressLastModifiedBy'
        );
    }
}

?>