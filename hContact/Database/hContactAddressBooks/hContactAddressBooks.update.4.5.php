<?php

class hContactAddressBooks_4to5 extends hPlugin {

    public function hConstructor()
    {
        $this->hContactAddressBooks->dropColumns(
            array(
                'hPluginId',
                'hPluginIdIsPrivate'
            )
        );
    }

    public function undo()
    {

    }
}

?>