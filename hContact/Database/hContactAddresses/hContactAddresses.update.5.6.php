<?php

class hContactAddresses_5to6 extends hPlugin {

    public function hConstructor()
    {
        $this->hContactAddresses
            ->modifyColumn('hContactAddressLatitude', hDatabase::latitudeLongitude)
            ->modifyColumn('hContactAddressLongitude', hDatabase::latitudeLongitude);
    }

    public function undo()
    {
        $this->hContactAddresses
            ->modifyColumn('hContactAddressLatitude', hDatabase::varCharTemplate(20))
            ->modifyColumn('hContactAddressLongitude', hDatabase::varCharTemplate(20));
    }
}

?>