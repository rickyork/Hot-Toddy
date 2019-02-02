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

class hFrameworkUpdate_104To105 extends hPlugin {

    private $hFileUtilities;
    private $hPluginDatabase;

    public function hConstructor($arguments)
    {
        $this->hFileUtilities = $this->library(
            'hFile/hFileUtilities',
            array(
                'autoScanEnabled' => false,
                'includeFileTypes' => array(
                    'php'
                ),
                'excludeFolders' => array()
            )
        );

        $find = array(
            '$this->hLibrary('          => '$this->library(',
            '$this->hPlugin('           => '$this->plugin(',
            '$this->hUser->'            => '$this->user->',
            '$this->hContact->'         => '$this->contact->',
            'getFilePathById('          => 'getFilePathByFileId(',
            'getIdByFilePath('          => 'getFileIdByFilePath(',
            '$this->hFileCSS('          => '$this->getPluginCSS(',
            '$this->hFileJavaScript('   => '$this->getPluginJavaScript(',
            '$this->hFileHeaders('      => '$this->getPluginFiles(',
            'hUserCMS'                  => "('Website Administrators')"
        );

        $plugins = array(
            'hCalendar/hCalendarDatabase' => 'hCalendar',
            'hCategory/hCategoryDatabase' => 'hCategory',
            'hConsole/hConsoleDatabase' => 'hConsole',
            'hContact/hContactDatabase' => 'hContact',
            'hDocumentation/hDocumentationDatabase' => 'hDocumentation',
            'hFile/hFileDatabase' => 'hFile',
            'hFile/hFileComments/hFileCommentsDatabase' => 'hFile/hFileComments',
            'hFile/hFileIcon/hFileIconDatabase' => 'hFile/hFileIcon',
            'hFile/hFilePassword/hFilePasswordDatabase' => 'hFile/hFilePassword',
            'hForum/hForumDatabase' => 'hForum',
            'hList/hListDatabase' => 'hList',
            'hMail/hMailDatabase' => 'hMail',
            'hMovie/hMovieDatabase' => 'hMovie',
            'hPhoto/hPhotoDatabase' => 'hPhoto',
            'hPlugin/hPluginDatabase' => 'hPlugin',
            'hSearch/hSearchDatabase' => 'hSearch',
            'hTemplate/hTemplateDatabase' => 'hTemplate',
            'hUser/hUserDatabase' => 'hUser'
        );  

        foreach ($plugins as $old => $new)
        {
            $find['$'."this->library('".$old."')"] = '$'."this->database('".$new."')";
            $find['$'.'this->library("'.$old.'")'] = '$'."this->database('".$new."')";
        }                                                                            

        $this->hFileUtilities->addExcludeFile('hFrameworkUpdate-1.0.4-1.0.5.php');
        $this->hFileUtilities->addExcludeFile('.hFrameworkUpdate-1.0.4-1.0.5.php');

        $this->hFileUtilities->scanFiles($this->hServerDocumentRoot);
        $this->hFileUtilities->scanFiles($this->hFrameworkPath.'/Plugins');

        $found = $this->hFileUtilities->findAndReplace(
            $find, null,
            array(
                'dryRun' => true
            )
        );

        var_dump($found);

        $this->hDatabase->query("DELETE FROM `hPlugins` WHERE `hPluginPath` LIKE '%.listener.php%'");

        $this->hPluginInstallFiles = true;

        $this->hPluginDatabase = $this->database('hPlugin');
        $this->hPluginDatabase->register('hFramework/hFrameworkService', null, null);

        $this->hErrorLog->drop();
    }
}

?>