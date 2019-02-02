<?php

class hContacts_1to2 extends hPlugin {

    public function hConstructor()
    {
        $this->hContacts
            ->addColumn('hContactGender', hDatabase::is, 'hContactDepartment')
            ->addColumn('hContactDateOfBirth', hDatabase::time, 'hContactGender');
    }

    public function undo()
    {
        $this->hContacts->dropColumns(
            'hContactGender',
            'hContactDateOfBirth'
        );
    }
}

?>