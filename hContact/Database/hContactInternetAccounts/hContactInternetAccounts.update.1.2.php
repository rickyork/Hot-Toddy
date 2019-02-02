<?php

class hContactInternetAccounts_1to2 extends hPlugin {

    public function hConstructor()
    {
        $this->hContactInternetAccounts
            ->appendColumn('hContactInternetAccountCreated', hDatabase::time)
            ->appendColumn('hContactInternetAccountLastModified', hDatabase::time)
            ->appendColumn('hContactInternetAccountLastModifiedBy', hDatabase::id);
    }

    public function undo()
    {
        $this->hContactInternetAccounts->dropColumns(
            'hContactInternetAccountCreated',
            'hContactInternetAccountLastModified',
            'hContactInternetAccountLastModifiedBy'
        );
    }
}

?>