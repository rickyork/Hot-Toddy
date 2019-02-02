<?php

class hUsers_2to3 extends hPlugin {

    public function hConstructor()
    {
    
        // No structural changes, just need to rename groups...
        /*
        $groups = array(
            'Website Administrators' => 1,
            'Finder Administrators'  => 1,
            'Finder Folders'         => 0,
            'User Administators'     => 1,
            'Employees'              => 0,
            'Disabled Users'         => 0,
            'Contact Administrators' => 1,
            'Contact Address Book'   => 0
        );

        $groups = array(
            'hFinderDocument'       => 1,
            //'file-admin'            => 1,
            //'Finder Administrators' => 1,
            'Users'                 => 1,
            'Employees'             => 0,
            'User Folders'          => 1,
            'Inactive'              => 0,
            'FTP Admin'             => 1,
            'hContactAll'           => 0,
            'hContactMine'          => 0,
            'hSites'                => 0
        );

        $groups = array(
            'hFinderDocument' => 'Website Administrators',
            'file-admin'      => 'Finder Administrators',
            'Users'           => 'User Administrators',
            'Inactive'        => 'Disabled User Accounts',
            'hContactAll'     => 'Contact Administrators',
            'hContactMine'    => 'Contact Address Book',
            'hSites'          => 'Finder Home Folder'
        );

        foreach ($groups as $old => $new)
        {
            $hUserId = $this->hUsers->selectColumn(
                'hUserId',
                array(
                    'hUserName' => $new
                )
            );

            if (empty($hUserId))
            {
                $this->hUsers->update(
                    array(
                        'hUserName' => $new
                    ),
                    array(
                        'hUserName' => $old
                    )
                );
            }
        }
      */
    }
}

?>