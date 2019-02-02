<?php

class hFiles_3to4 extends hPlugin {

    public function hConstructor()
    {        
        $this->hFiles
            ->modifyColumn('hLanguageId', "int(3) NOT NULL DEFAULT '1'")
            ->modifyColumn('hDirectoryId', "int(11) NOT NULL DEFAULT '0'")
            ->modifyColumn('hUserId', "int(11) NOT NULL DEFAULT '0'")
            ->modifyColumn('hFileParentId', "int(11) NOT NULL DEFAULT '0'")
            ->modifyColumn('hPluginId', "int(11) NOT NULL DEFAULT '0'")
            ->modifyColumn('hPluginIdIsPrivate', "tinyint(1) NOT NULL DEFAULT '0'")
            ->update(
                array(
                    'hLanguageId' => 1
                ),
                array(
                    'hLanguageId' => 0
                )
            );
    }
}

?>