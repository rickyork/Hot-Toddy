<?php

class hFileCache_2to3 extends hPlugin {

    public function hConstructor()
    {
        $this->hFileCache->addColumn(
            'hFileCacheExpires',
            hDatabase::time,
            'hFileCacheLastModified'
        );
    }

    public function undo()
    {
        $this->hFileCache->dropColumn(
            'hFileCacheExpires'
        );
    }
}