<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Template Install Shell
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
#
# This plugin installs the default Hot Toddy template.

class hTemplateInstallShell extends hShell {
    
    public function hConstructor()
    {
        echo "Installing the default template...\n";
        
        $this->hDatabase->insert(
            array(
                'hTemplateId'               => 1,
                'hTemplatePath'             => '/HotToddy/HotToddy.template.php',
                'hTemplateName'             => 'Default',
                'hTemplateDescription'      => 'Default site template',
                'hTemplateToggleVariables'  => 0,
                'hTemplateCascadeVariables' => 0,
                'hTemplateMergeVariables'   => 0
            ),
            'hTemplates'
        );
        
        $this->hDatabase->insert(
            array(
                'hTemplateId'  => 1,
                'hDirectoryId' => 1
            ),
            'hTemplateDirectories'
        );

        echo "Installed the default template, /hFramework/hFrameworkDefault/template.php\n";
    }
}

?>