<?php

class hContacts_2to3 extends hPlugin {

    public function hConstructor()
    {
        $this->hContacts->addFullTextIndex(
            array(
                'hContactFirstName',
                'hContactLastName',
                'hContactCompany',
                'hContactTitle',
                'hContactDepartment'
            )
        );
    }
    
    public function undo()
    {
        $this->hContacts->dropFullTextIndex(
            array(
                'hContactFirstName',
                'hContactLastName',
                'hContactCompany',
                'hContactTitle',
                'hContactDepartment'
            )
        );
    }
}

?>