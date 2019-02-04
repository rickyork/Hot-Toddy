<?php

class hFileIcons_7to8 extends hPlugin {
    
    private $hFileIconDatabase;

    public function hConstructor()
    {
        $this->hFileIconDatabase = $this->database('hFile/hFileIcon');

        $this->hFileIcons->update(
            array(
                'hFileICNS' => 'html.icns'
            ),
            array(
                'hFileName' => 'safari_document.png'
            )
        );

        $this->hFileIconDatabase
            ->save(
                '',
                'group.png',
                'GroupIcon.icns'
            )
            ->save(
                '',
                'everyone.png',
                'Everyone.icns'
            )
            ->save(
                '',
                'user.png',
                'UserIcon.icns'
            );
    }
}

?>