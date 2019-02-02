<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Contact Plugin
#//\\ @@@@  @@@@\\\\\|
#//\\\@@@@| @@@@\\\\\|
#//\\\ @@ |\\@@\\\\\\| http://www.hframework.com
#//\\\\  ||   \\\\\\\| © Copyright 2015 Richard York, All rights Reserved
#//\\\\  \\_   \\\\\\|
#//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#//\\\\\  ----  \@@@@| http://www.hframework.com/license
#//@@@@@\       \@@@@|
#//@@@@@@\     \@@@@@|
#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
# @description
# <h1>Contacts Application</h1>
# <p>
#
# </p>
# @end

class hContact extends hPlugin {

    private $hForm;
    private $hContactDatabase;
    private $hContactAddressBook;
    private $hContactApplication;
    private $hContactAddressBookId;
    private $hContactAddressBookName;
    private $hContactSummary;
    private $hApplicationMenu;
    private $hSpotlight;
    private $hSpotlightSearch;
    private $hDialogue;

    private $dialogue;

    private $menus = array();

    public function hConstructor()
    {
        $this->redirectIfSecureIsEnabled();

        if (!isset($_GET['hContactAddressBookId']))
        {
            $_GET['hContactAddressBookId'] = 1;
        }

        if ($this->isLoggedIn())
        {
            $this->hFileCSS = '';
            $this->hFileJavaScript = '';

            $this->contacts();
        }
        else
        {
            $this->notLoggedIn();
        }
    }

    private function contacts()
    {
        if (!empty($_GET['hContactConf']))
        {
            $this->loadConfigurationFile(
                $this->hFrameworkConfigurationPath.'/hContact '.hString::scrubString($_GET['hContactConf'])
            );
        }

        $this->hFileEnableCache = false;
        $this->hFileDisableCache = true;

        $this->hContactDatabase = $this->database('hContact');

        if (isset($_GET['hContactAddressBookId']))
        {
            $this->hContactAddressBookId = (int) $_GET['hContactAddressBookId'];

            $addressBook = $this->hContactDatabase->getAddressBook($this->hContactAddressBookId);

            if ($addressBook !== false)
            {
                $this->hFileTitlePrepend = '';
                $this->hFileTitleAppend  = '';

                if ($this->userAgent->iOS)
                {
                    $this->touchScroll();
                }

                $this->jQuery('Draggable', 'Droppable');

                $this->plugin('hApplication/hApplicationStatus');

                $this->hFileFavicon = '/hContact/Pictures/Contacts.ico';

                $this->getPluginFiles();

                $this->hFileJavaScript .= $this->getTemplate(
                    'Configuration',
                    array(
                        'hContactAddressBookId' => (int) $this->hContactAddressBookId,
                        'hContactSummaryDefaultEmailAddressField' => (int) $this->hContactSummaryDefaultEmailAddressField(20),
                        'hContactSummaryDefaultPhoneNumberField' => (int) $this->hContactSummaryDefaultPhoneNumberField(6),
                        'hContactSummaryDefaultAddressField' => (int) $this->hContactSummaryDefaultAddressField(2),
                        'hContactConf' => isset($_GET['hContactConf']) ? hString::scrubString($_GET['hContactConf']) : ''
                    )
                );

                $this->hContactAddressBook = $this->plugin('hContact/hContactAddressBook');

                $this->hContactApplication = $this->hContactAddressBook->getAddressBookPlugin($addressBook['hPlugin']);

                if ($this->hContactAll)
                {
                    if ($this->hContactPageTitle(nil))
                    {
                        $this->hFileTitle = $this->hContactPageTitle;
                    }
                    else
                    {
                        $this->hFileTitle = $this->hServerHost.' All Address Books';
                    }

                    $this->hContactAddressBookName = 'All Address Books';
                    $this->hContactAddressBook->setAddressBookName('All Address Books');
                }
                else
                {
                    if ($this->hContactPageTitle(nil))
                    {
                        $this->hFileTitle = $this->hContactPageTitle;
                    }
                    else
                    {
                        $this->hFileTitle = $this->hServerHost.' '.$addressBook['hContactAddressBookName'];
                    }

                    $this->hContactAddressBookName = $addressBook['hContactAddressBookName'];
                    $this->hContactAddressBook->setAddressBookName($addressBook['hContactAddressBookName']);
                }

                $this->menus = $this->hContactApplication->getMenus();

                $this->build();
            }
            else
            {
                $this->warning(
                    'Invalid address book specified.',
                    __FILE__,
                    __LINE__
                );
            }
        }
        else
        {
            $this->warning(
                'No address book was specified.',
                __FILE__,
                __LINE__
            );
        }
    }

    private function build()
    {
        $this->hForm = $this->library('hForm');
        $this->hDialogue = $this->library('hDialogue');

        $this->hApplicationMenu = $this->library('hApplication/hApplicationMenu');
        $this->hSpotlight = $this->library('hSpotlight');
        $this->hSpotlightSearch = $this->library('hSpotlight/hSpotlightSearch');

        $hContactGroups = '';

        if (method_exists($this->hContactApplication, 'getGroups') && $this->hContactEnableGroups(true))
        {
            $hContactGroups = $this->hContactApplication->getGroups();
            $hContactEnableGroups = true;
        }
        else
        {
            if ($this->hFileDocumentBodyClassName)
            {
                $this->hFileDocumentBodyClassName .= ' hContactAddressBookNoGroups';
            }
            else
            {
                $this->hFileDocumentBodyClassName = 'hContactAddressBookNoGroups';
            }

            $hContactEnableGroups = false;
        }

        $sortColumns = $this->hSpotlightSearch->getSortColumns();

        $hContactSortColumns = '';

        if (count($sortColumns))
        {
            $i = 0;
            $hContactSortColumns = array();

            foreach ($sortColumns as $sortColumn)
            {
                $hContactSortColumns['hContactSortColumnId'][] = $i;
                $hContactSortColumns['hContactSortColumn'][] = $sortColumn;
                $hContactSortColumns['hContactSortColumnLabel'][] = $this->hSpotlightSearch->getColumnLabel($sortColumn);
                $i++;
            }
        }

        $this->hContactSummary = $this->plugin(
            $this->hContactSummaryPlugin('hContact/hContactSummary')
        );

        $this->hFileDocument =
            $this->getTemplate(
                'Contacts',
                array(
                    'hContactAddressBookName' => $this->hContactAddressBookName,
                    'hSpotlightSearch' => $this->hSpotlight->getSearch('Contacts', false),
                    'hContactGroupLabel' => $this->hContactGroupLabel('Groups'),
                    'hContactGroups' => $hContactGroups,
                    'hContactEnableGroups' => $hContactEnableGroups,
                    'hContactSortColumns' => $hContactSortColumns,
                    'hContactSummary' => $this->hContactSummary->getSummary(
                        $this->hForm,
                        $this->hDialogue,
                        $this->hContactApplication
                    ),
                    'hContactReadOnly' => $this->hContactReadOnly(false)
                )
            );

        if (!$this->user->getVariable('hContactInstructionsDefault', 0))
        {
            $this->hFileDocument .= $this->getTemplate('Instructions');
        }

        if (method_exists($this->hContactApplication, 'getResultTemplates'))
        {
            $this->hFileDocument .= $this->hContactApplication->getResultTemplates();
        }
    }
}

?>