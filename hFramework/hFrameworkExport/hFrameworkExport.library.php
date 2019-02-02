<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework
#//\\ @@@@  @@@@\\\\\|
#//\\\@@@@| @@@@\\\\\|
#//\\\ @@ |\\@@\\\\\\| https://github.com/rickyork/Hot-Toddy
#//\\\\  ||   \\\\\\\| © Copyright 2019 Richard York, All rights Reserved
#//\\\\  \\_   \\\\\\|
#//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#//\\\\\  ----  \@@@@| https://github.com/rickyork/Hot-Toddy/blob/master/License
#//@@@@@\       \@@@@|
#//@@@@@@\     \@@@@@|
#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#
# Makes it possible to export data from one framework installation and install that
# data into another framework installation.
#
# MAKE SURE BOTH EXPORTING AND IMPORTING INSTALLATIONS ARE FULLY UP-TO-DATE!
#

class hFrameworkExportLibrary extends hPlugin {

    public function hCosntructor()
    {

    }

    /*
    * Return framework data as a JSON object.
    *
    */
    public function getJSON(array $export = array())
    {
        $exportData = array(
            'hUsers'                   => $this->hUsers->select(),                  // Sync users first to get updated hUserIds in place
            'hDirectories'             => $this->hDirectories->select(),            // Insert directories that do not exist on the server.
            'hFiles'                   => $this->hFiles->select(),                  // Sync files to get updated hFileIds and hFilePaths.
            'hCalendars'               => $this->hCalendars->select(),
            'hCalendarCategories'      => $this->hCalendarCategories->select(),
            'hCategories'              => $this->hCategories->select(),
            'hContactAddressBooks'     => $this->hContactAddressBooks->select(),
            'hContacts'                => $this->hContacts->select(),
            'hContactAddresses'        => $this->hContactAddresses->select(),
            'hContactPhoneNumbers'     => $this->hContactPhoneNumbers->select(),
            'hContactEmailAddresses'   => $this->hContactEmailAddresses->select(),
            'hContactInternetAccounts' => $this->hContactInternetAccounts->select(),
            'hContactVariables'        => $this->hContactVariables->select(),
            'hContactUsers'            => $this->hContactUsers->select(),
            'hUserLog'                 => $this->hUserLog->select(),
            'hUserGroupProperties'     => $this->hUserGroupProperties->select(),
            'hUserGroups'              => $this->hUserGroups->select(),
            'hUserPermissions'         => $this->hUserPermissions->select(),
            'hFileDocuments'           => $this->hFileDocuments->select(),
            'hFileHeaders'             => $this->hFileHeaders->select(),
            'hFileLog'                 => $this->hFileLog->select(),
            'hFilePasswords'           => $this->hFilePasswords->select(),
            'hFilePathWildcards'       => $this->hFilePathWildcards->select(),
            'hFileProperties'          => $this->hFileProperties->select(),
            'hFileStatistics'          => $this->hFileStatistics->select(),
            'hFileUserStatistics'      => $this->hFileUserStatistics->select(),
            'hFileVariables'           => $this->hFileVariables->select(),
            'hCategoryFiles'           => $this->hCategoryFiles->select(),
            'hUserPermissionsGroups'   => $this->hUserPermissionsGroups->select(),
            'hUserVariables'           => $this->hUserVariables->select()
        );

        if (count($export))
        {
            foreach ($export as $table)
            {
                $exportData[$table] = $this->$table->select();
            }
        }

        if (!function_exists('json_encode'))
        {

        }

        return json_encode($exportData);
    }
}

?>