<?php

class hContactEmailAddresses_1to2 extends hPlugin {

    public function hConstructor()
    {
        $this->hContactEmailAddresses
            ->appendColumn('hContactEmailAddressCreated', hDatabase::time)
            ->appendColumn('hContactEmailAddressLastModified', hDatabase::time)
            ->appendColumn('hContactEmailAddressLastModifiedBy', hDatabase::id);
    }

    public function undo()
    {
        $this->hContactEmailAddresses->dropColumns(
            'hContactEmailAddressCreated',
            'hContactEmailAddressLastModified',
            'hContactEmailAddressLastModifiedBy'
        );
    }
}

?>