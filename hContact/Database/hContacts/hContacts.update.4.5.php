<?php

class hContacts_4to5 extends hPlugin {

    public function hConstructor()
    {
        $this->hContacts->addColumn(
            'hContactMiddleName',
            hDatabase::varCharTemplate(100),
            'hContactFirstName'
        );
    }

    public function undo()
    {
        $this->hContacts->dropColumn('hContactMiddleName');
    }
}

?>