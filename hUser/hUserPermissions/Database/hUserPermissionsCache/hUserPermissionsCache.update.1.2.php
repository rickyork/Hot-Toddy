<?php

class hUserPermissionsCache_1to2 extends hPlugin {

    public function hConstructor()
    {
        $this->hUserPermissionsCache
                ->dropKey('hUserID')
                ->addPrimaryKey(
                    array(
                        'hUserId',
                        'hUserPermissionsType',
                        'hUserPermissionsVariable'
                    )
                )
                ->addKey(
                    'hUserId'
                )
                ->addKey(
                    array(
                        'hUserId',
                        'hUserPermissionsType'
                    )
                )
                ->addKey(
                    array(
                        'hUserId',
                        'hUserPermissionsVariable'
                    )
                );
    }
}

?>