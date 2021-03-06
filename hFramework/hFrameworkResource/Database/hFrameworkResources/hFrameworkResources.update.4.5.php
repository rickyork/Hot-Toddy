<?php


class hFrameworkResources_4to5 extends hPlugin {
    
    public function hConstructor()
    {
        $this->hFrameworkResources->insert(
            array(
                'hFrameworkResourceId' => 23,
                'hFrameworkResourceTable' => 'hPlugins',
                'hFrameworkResourcePrimaryKey' => 'hPluginId',
                'hFrameworkResourceNameColumn' => 'hPluginName',
                'hFrameworkResourceLastModifiedColumn' => 'hPluginLastModified',
                'hFrameworkResourceLastModifiedByColumn' => 'hPluginLastModifiedBy'
            )
        );
        
        $this->hFrameworkResources->insert(
            array(
                'hFrameworkResourceId' => 24,
                'hFrameworkResourceTable' => 'hPluginsPrivate',
                'hFrameworkResourcePrimaryKey' => 'hPluginId',
                'hFrameworkResourceNameColumn' => 'hPluginName',
                'hFrameworkResourceLastModifiedColumn' => 'hPluginLastModified',
                'hFrameworkResourceLastModifiedByColumn' => 'hPluginLastModifiedBy'
            )
        );
    }
}

?>