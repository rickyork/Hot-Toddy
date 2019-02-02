<?php

class hFileServer_1to2 extends hPlugin {
    
    public function hConstructor()
    {
        $this->hFileServer
            ->addColumn('hUserId', hDatabase::id, 'hFileServerId')
            ->appendColumn('hFileServerCreated', hDatabase::time)
            ->appendColumn('hFileServerLastModified', hDatabase::time)
            ->appendColumn('hFileServerLastModifiedBy', hDatabase::id);
    }

    public function undo()
    {
        $this->hFileServer->dropColumns(
            'hUserId',
            'hFileServerCreated',
            'hFileServerLastModified',
            'hFileServerLastModifiedBy'
        );
    }
}


?>