<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Template Database Library
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

class hTemplateDatabase extends hPlugin {

    public function hConstructor()
    {

    }

    public function templateExists($templateName)
    {
        return $this->hTemplates->selectExists(
            'hTemplateId',
            array(
                'hTemplateName' => $templateName
            )
        );
    }

    public function save($templateId, $templatePath, $templateName = 'Default', $templateDescription = 'Default site template', $templateToggleVariables = false, $templateCascadeVariables = false, $templateMergeVariables = false)
    {
        return $this->hTemplates->save(
            array(
                'hTemplateId'               => (int) $templateId,
                'hTemplatePath'             => $templatePath,
                'hTemplateName'             => $templateName,
                'hTemplateDescription'      => $templateDescription,
                'hTemplateToggleVariables'  => (int) $templateToggleVariables,
                'hTemplateCascadeVariables' => (int) $templateCascadeVariables,
                'hTemplateMergeVariables'   => (int) $templateMergeVariables
            )
        );
    }

    public function templatePluginExists($templateId, $plugin)
    {
        return $this->hTemplatePlugins->selectExists(
            'hTemplateId',
            array(
                'hTemplateId' => (int) $templateId,
                'hPlugin' => $plugin
            )
        );
    }

    public function saveTemplatePlugin($templateId, $plugin)
    {
        if (!$this->templatePluginExists($templateId, $plugin))
        {
            $this->hTemplatePlugins->insert((int) $templateId, $plugin);
        }
    }

    public function templateDirectoryExists($templateId, $directoryId)
    {
        return $this->hTemplateDirectories->selectExists(
            'hTemplateId',
            array(
                'hTemplateId' => (int) $templateId,
                'hDirectoryId' => (int) $directoryId
            )
        );
    }

    public function saveTemplateDirectory($templateId, $directoryId)
    {
        if (!$this->templateDirectoryExists($templateId, $directoryId))
        {
            $this->hTemplateDirectories->insert((int) $templateId, (int) $directoryId);
        }
    }
}

?>