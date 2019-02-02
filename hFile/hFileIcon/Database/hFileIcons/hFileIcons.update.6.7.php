<?php

class hFileIcons_6to7 extends hPlugin {

    private $hFileIconDatabase;

    public function hConstructor()
    {
        $this->hFileIconDatabase = $this->database('hFile/hFileIcon');

        $this->hFileIconDatabase
            ->save(
                'directory/sidebar',
                'sidebar_folder.png',
                'SidebarGenericFolder.icns'
            )
            ->save(
                'directory/sidebar-recents',
                'sidebar_recents.png',
                'SidebarRecents.icns'
            )
            ->save(
                'directory/sidebar-network',
                'sidebar_network.png',
                'SidebarNetwork.icns'
            )
            ->save(
                'directory/sidebar-applications',
                'sidebar_applications.png',
                'SidebarApplicationsFolder.icns'
            )
            ->save(
                'directory/sidebar-file',
                'sidebar_file.png',
                'SidebarGenericFile.icns'
            )
            ->save(
                'directory/sidebar-smart-folder',
                'sidebar_smart_folder.png',
                'SidebarSmartFolder.icns'
            );

        $this->hFileIcons->update(
            array(
                'hFileICNS' => 'Contacts.icns'
            ),
            array(
                'hFileName' => 'address_book.png'
            )
        );
    }
}

?>