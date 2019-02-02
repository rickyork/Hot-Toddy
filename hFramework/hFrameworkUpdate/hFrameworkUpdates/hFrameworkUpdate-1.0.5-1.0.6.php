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

class hFrameworkUpdate_105To106 extends hPlugin {

    private $hFileUtilities;
    private $hPluginDatabase;

    public function hConstructor($arguments)
    {
        if ($this->hFrameworkPluginSites && is_array($this->hFrameworkPluginSites))
        {
            foreach ($this->hFrameworkPluginRoots as $i => $pluginRoot)
            {
                $siteExists = $this->hFrameworkSites->selectExists(
                    'hFrameworkSiteId',
                    array(
                        'hFrameworkSite' => $this->hFrameworkPluginSites[$i]
                    )
                );

                if (!$siteExists)
                {
                    $this->hFrameworkSites->insert(
                        array(
                            'hFrameworkSiteId' => 0,
                            'hFrameworkSite' => $this->hFrameworkPluginSites[$i],
                            'hFrameworkSitePath' => $pluginRoot,
                            'hFrameworkSiteIsDefault' => $this->hFrameworkPluginDefaults[$i],
                            'hFrameworkSiteCreated' => time(),
                            'hFrameworkSiteLastModified' => 0,
                            'hFrameworkSiteLastModifiedBy' => 1
                        )
                    );
                }
            }
        }

        $plugins = $this->hPluginsPrivate->select();

        $frameworkPath = $this->hFrameworkPath;

        foreach ($plugins as $i => $plugin)
        {
            foreach ($this->hFrameworkPluginRoots as $pluginRoot)
            {
                $pluginPath = $frameworkPath.$pluginRoot.'/'.$plugin['hPluginPath'];

                $this->console("Plugin Path: {$pluginPath}");

                if (file_exists($pluginPath))
                {
                    $this->hPluginsPrivate->update(
                        array(
                            'hUserId' => 1,
                            'hFrameworkSiteId' => $this->hFrameworkSites->selectColumn(
                                'hFrameworkSiteId',
                                array(
                                    'hFrameworkSitePath' => $pluginRoot
                                )
                            )
                        ),
                        array(
                            'hPluginId' => $plugin['hPluginId']
                        )
                    );
                }
            }
        }
    }
}

?>