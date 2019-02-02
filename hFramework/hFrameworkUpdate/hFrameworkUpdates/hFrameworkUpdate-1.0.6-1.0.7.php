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

class hFrameworkUpdate_106To107 extends hPlugin {

    public function hConstructor($arguments)
    {
        if ($this->hDatabase->tableExists('hPluginApplication'))
        {
            $this->hPluginApplication->drop();
        }

        if ($this->hDatabase->tableExists('hPluginListenerResponseCodes'))
        {
            $this->hPluginListenerResponseCodes->drop();
        }

        if ($this->hDatabase->tableExists('hPluginListeners'))
        {
            $this->hPluginListeners->drop();
        }

        if ($this->hDatabase->tableExists('hPluginMethodArguments'))
        {
            $this->hPluginMethodArguments->drop();
        }

        if ($this->hDatabase->tableExists('hPluginMethods'))
        {
            $this->hPluginMethods->drop();
        }

        if ($this->hDatabase->tableExists('hPluginPrivate'))
        {
            $this->hPluginPrivate->drop();
        }

        if ($this->hDatabase->tableExists('hPluginsPrivate'))
        {
            $this->hPluginsPrivate->drop();
        }

        if ($this->hDatabase->tableExists('hPluginPrivateListeners'))
        {
            $this->hPluginPrivateListeners->drop();
        }

        if ($this->hDatabase->tableExists('hPlugins'))
        {
            $this->hPlugins->drop();
        }

        if ($this->hDatabase->tableExists('hPluginServicesApplications'))
        {
            $this->hPluginServicesApplications->drop();
        }

        if ($this->hDatabase->tableExists('hPluginServicesPrivate'))
        {
            $this->hPluginServicesPrivate->drop();
        }

        if ($this->hDatabase->tableExists('hTerritories'))
        {
            $this->hTerritories->drop();
        }

        if ($this->hDatabase->tableExists('hTerritoryLocations'))
        {
            $this->hTerritoryLocations->drop();
        }

        if ($this->hDatabase->tableExists('hTerritoryResources'))
        {
            $this->hTerritoryResources->drop();
        }
    }
}

?>