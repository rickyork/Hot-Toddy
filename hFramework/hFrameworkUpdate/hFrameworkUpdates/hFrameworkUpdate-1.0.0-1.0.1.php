<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework Update
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

class hFrameworkUpdate_100To101 extends hPlugin {

    private $hFile;
    private $hUserDatabase;

    public function hConstructor()
    {
        $hServerDocumentRoot = $this->hServerDocumentRoot;
        $hServerDocumentRootBase = dirname($hServerDocumentRoot);
    
        if ($this->hOS == 'Darwin')
        {
            echo `php {$hServerDocumentRootBase}/hShell -p hFile/hFileIcon/hFileIconInstall icns`;
        }

        echo `php {$hServerDocumentRootBase}/hShell -p hPlugin -i hConsole`;

        $this->hFilePathWildcards->insert(
            array(
                'hFilePathWildcard' => '/images/icons',
                'hFileId' => $this->getFileIdByPluginId('hFile/hFileIcon')
            )
        );

        $this->hDatabase->query(
            "DROP TABLE `hGlossary`"
        );

        $this->hDatabase->query(
            "DROP TABLE `hPluginVariables`"
        );

        $hCalendars = $this->hCalendars->select('hCalendarId');

        foreach ($hCalendars as $hCalendarId)
        {
            $hCalendarCategories = $this->hCalendarCategories->select('hCalendarCategoryId');

            foreach ($hCalendarCategories as $hCalendarCategoryId)
            {
                $this->hCalendarResources->insert(
                    array(
                        'hCalendarResourceId' => null,
                        'hCalendarId' => (int) $hCalendarId,
                        'hCalendarCategoryId' => (int) $hCalendarCategoryId,
                        'hUserId' => 1,
                        'hCalendarResourceCreated' => time(),
                        'hCalendarResourceLastModified' => 0
                    )
                );
            }
        }

    }
}

?>