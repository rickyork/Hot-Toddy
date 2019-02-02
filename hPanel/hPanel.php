<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Panel Plugin
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
# <h1>Panel UI</h1>
# <p>
#    This plugin provides UI for creating panels.  Panels consist of one or more icons,
#    which can be clicked to launch an application inside of the panel UI.  Panel UI
#    provides UI elements for toggling between a running application and the main panel.
#    This looks and behaves similarly to Apple Mac OS X system preferences.
# </p>
# @end

class hPanel extends hPlugin {

    private $hFileIcon;

    public function hConstructor()
    {
        $this->hFileTitleAppend = '';
        $this->hFileTitlePrepend = '';

        $this->plugin('hApplication/hApplicationForm');

        $this->getPluginFiles();

        $panels = array();

        $this->hFileIcon = $this->library('hFile/hFileIcon');

        if ($this->hPanels && is_array($this->hPanels))
        {
            foreach ($this->hPanels as $panel)
            {
                if (isset($panel->hPanelRequireGroup))
                {
                    if (!$this->inGroup($panel->hPanelRequireGroup))
                    {
                        continue;
                    }
                }

                if (isset($panel->hPanelName))
                {
                    $panels['hPanelName'][] = $panel->hPanelName;

                    if (isset($panel->hPanelApplications) && is_array($panel->hPanelApplications))
                    {
                        $panelApplications = array();

                        foreach ($panel->hPanelApplications as $panelApplication)
                        {
                            $fileId = $this->getFileIdByFilePath($panelApplication);

                            if (empty($fileId))
                            {
                                $fileId = $this->getFileIdByFilePath($panelApplication.'/index.html');

                                if (empty($fileId))
                                {
                                    $this->warning(
                                        "Unable to get an hFileId from application path, '{$panelApplication}'",
                                        __FILE__,
                                        __LINE__
                                    );
                                }
                                else
                                {
                                    $directoryId = $this->getDirectoryId($panelApplication);
                                }
                            }
                            else
                            {
                                $directoryId = $this->getDirectoryId(dirname($panelApplication));
                            }

                            $fileIconId = $this->hDirectoryProperties->selectColumn(
                                'hFileIconId',
                                $directoryId
                            );

                            $panelApplications['hFileTitle'][] = $this->getFileTitle($fileId);

                            $panelApplications['hFileIconPath'][] = $this->hFileIcon->getIconPathById(
                                $fileIconId,
                                '32x32'
                            );

                            $panelApplications['hFilePath'][] = $panelApplication;
                        }

                        $panels['hPanelApplications'][] = $panelApplications;
                    }
                    else
                    {
                        $this->warning(
                            "No applications defined for panel, ".$panel->hPanelName.'.',
                            __FILE__,
                            __LINE__
                        );
                    }
                }
                else
                {
                    $this->warning(
                        'Panel definition found, but it is not named.',
                        __FILE__,
                        __LINE__
                    );
                }
            }
        }

        $this->hFileDocument = $this->getTemplate(
            'Panel',
            array(
                'hPanels' => $panels
            )
        );
    }
}

?>