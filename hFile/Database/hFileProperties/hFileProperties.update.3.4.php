<?php

class hFileProperties_3to4 extends hPlugin {

    public function hConstructor()
    {
        $this->hFileProperties
            ->addColumn('hFileMD5Checksum', hDatabase::charTemplate(32), 'hFileSystemPath')
            ->addIndex('hFileMD5Checksum');

        // Add MD5 Checksums to every file in the file system
        $query = $this->hDatabase->select(
            array(
                'hFilePath',
                'hDirectories' => array(
                    'hDirectoryId',
                    'hDirectoryPath'
                ),
                'hFiles' => array(
                    'hFileId',
                    'hFileName'
                )
            ),
            array(
                'hFiles',
                'hDirectories'
            ),
            array(
                'hFiles.hDirectoryId' => 'hDirectories.hDirectoryId'
            ),
            'AND',
            'hDirectories.hDirectoryPath'
        );

        foreach ($query as $data)
        {
            $path = $this->hFileSystemPath.$data['hFilePath'];

            if (file_exists($path))
            {
                $checksum = md5_file($path);

                $exists = $this->hFileProperties->selectExists(
                    'hFileId',
                    array(
                        'hFileId' => (int) $data['hFileId']
                    )
                );

                if ($exists)
                {
                    $this->hFileProperties->update(
                        array(
                            'hFileMD5Checksum' => $checksum
                        ),
                        (int) $data['hFileId']
                    );
                }
                else
                {
                    $this->hFileProperties->insert(
                        array(
                            'hFileId' => (int) $data['hFileId'],
                            'hFileMD5Checksum' => $checksum
                        )
                    );
                }
            }     
        }
    }
    
    public function undo()
    {
        $this->hFileProperties
            ->dropColumn('hFileMD5Checksum')
            ->dropIndex('hFileMD5Checksum');
    }
}

?>