<?php

class hContacts_3to4 extends hPlugin {

    public function hConstructor()
    {
        $this->hContacts->appendColumn('hContactLastModifiedBy', hDatabase::id);
    }

    public function undo()
    {
        $this->hContacts->dropColumn('hContactLastModifiedBy');
    }
}

?>