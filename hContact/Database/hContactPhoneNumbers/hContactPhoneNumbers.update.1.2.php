<?php

class hContactPhoneNumbers_1to2 extends hPlugin {

    public function hConstructor()
    {
        $this->hContactPhoneNumbers
            ->appendColumn('hContactPhoneNumberCreated', hDatabase::time)
            ->appendColumn('hContactPhoneNumberLastModified', hDatabase::time)
            ->appendColumn('hContactPhoneNumberLastModifiedBy', hDatabase::id);
    }

    public function undo()
    {
        $this->hContactPhoneNumbers->dropColumns(
            'hContactPhoneNumberCreated',
            'hContactPhoneNumberLastModified',
            'hContactPhoneNumberLastModifiedBy'
        );
    }
}

?>