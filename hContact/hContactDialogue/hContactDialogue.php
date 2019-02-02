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
# @description
# <h1>Contact Selection Dialogue</h1>
# <p>
#
# </p>
# @end

class hContactDialogue extends hPlugin {

    private $hContactDatabase;
    private $hContactAddressBook;
    private $hContactApplication;
    private $hSpotlight;
    private $hSpotlightSearch;

    public function hConstructor()
    {
        if ($this->isLoggedIn())
        {
            $this->hFileCSS = '';
            $this->hFileJavaScript = '';
            $this->hFileTitlePrepend = '';
            $this->hFileTitleAppend  = '';

            $this->hFileFavicon = '/hContact/Pictures/Contacts.ico';

            if (!isset($_GET['hContactAddressBookId']))
            {
                $_GET['hContactAddressBookId'] = 1;
            }

            $contactAddressBookId = (int) $_GET['hContactAddressBookId'];

            $this->hContactDatabase = $this->database('hContact');

            $addressBook = $this->hContactDatabase->getAddressBook($contactAddressBookId);

            if ($addressBook !== false)
            {
                $this->plugin('hApplication/hApplicationForm');

                $this->hContactAddressBook = $this->plugin('hContact/hContactAddressBook');
                $this->hContactApplication = $this->hContactAddressBook->getAddressBookPlugin($addressBook['hPlugin']);

                if ($this->hContactAll)
                {
                    if ($this->hContactPageTitle(null))
                    {
                        $this->hFileTitle = 'Select a Person or Group - '.$this->hContactPageTitle;
                    }
                    else
                    {
                        $this->hFileTitle = 'Select a Person or Group - '.$this->hServerHost.' All Address Books';
                    }

                    $this->hContactAddressBookName = 'All Address Books';
                    $this->hContactAddressBook->setAddressBookName('All Address Books');
                }
                else
                {
                    if ($this->hContactPageTitle(null))
                    {
                        $this->hFileTitle = 'Select a Person or Group - '.$this->hContactPageTitle;
                    }
                    else
                    {
                        $this->hFileTitle = 'Select a Person or Group - '.$this->hServerHost.' '.$addressBook['hContactAddressBookName'];
                    }

                    $this->hContactAddressBookName = $addressBook['hContactAddressBookName'];
                    $this->hContactAddressBook->setAddressBookName($addressBook['hContactAddressBookName']);
                }

                $this->hSpotlight       = $this->library('hSpotlight');
                $this->hSpotlightSearch = $this->library('hSpotlight/hSpotlightSearch');

                $sortColumns = $this->hSpotlightSearch->getSortColumns();

                $contactSortColumns = '';

                if (count($sortColumns))
                {
                    $i = 0;
                    $contactSortColumns = array();

                    foreach ($sortColumns as $sortColumn)
                    {
                        $contactSortColumns['hContactSortColumnId'][] = $i;
                        $contactSortColumns['hContactSortColumn'][] = $sortColumn;
                        $contactSortColumns['hContactSortColumnLabel'][] = $this->hSpotlightSearch->getColumnLabel($sortColumn);
                        $i++;
                    }
                }

                $this->getPluginFiles();

                $this->hFileDocument = $this->getTemplate(
                    'Contact Dialogue',
                    array(
                        'groups' => $this->hContactApplication->getGroups(),
                        'spotlightSearch' => $this->hSpotlight->getSearch('Contacts', false)
                    )
                );
            }
        }
        else
        {
            $this->notLoggedIn();
        }
    }
}

?>