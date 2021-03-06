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

class hFrameworkUpdate_103To104 extends hPlugin {

    private $hFileUtilities;

    public function hConstructor()
    {
        $this->hFileUtilities = $this->library(
            'hFile/hFileUtilities',
            array(
                'autoScanEnabled' => false,
                'excludeFiles' => array(
                    $this->hFrameworkSite.'.80.conf',
                    $this->hFrameworkSite.'.443.conf'
                ),
                'fileTypes' => array(
                    'json',
                    'conf'
                )
            )
        );

        $this->hFileUtilities->scanFiles($this->hFrameworkPath.'/Configuration');

        $this->hFileUtilities->findAndReplace(
            'Id', 'Id',
            array(
                'dryRun' => false    
            )
        );
    
        $tables = $this->hDatabase->getTables();

        foreach ($tables as $table)
        {
            $columns = $this->hDatabase->getColumns($table);

            if (is_array($columns))
            {
                foreach ($columns as $column => $data)
                {
                    if (strstr($column, 'Id'))
                    {
                        $newName =  str_replace('Id', 'Id', $column);

                        $this->$table->renameColumn($column, $newName);
                        $this->console("Database Table: '{$table}' column '{$column}' renamed '{$newName}'");
                    }
                }
            }
        }

        $this->updateVariables('File');
        $this->updateVariables('Template');
        $this->updateVariables('User');
        $this->updateVariables('Contact');

        $this->updatePlugins('hPluginListeners');
        $this->updatePlugins('hPluginPrivateListeners');
        
        $query = $this->hFrameworkResources->select(
            array(
                'hFrameworkResourceId',
                'hFrameworkResourcePrimaryKey'
            )
        );
        
        foreach ($query as $data)
        {
            $this->hFrameworkResources->update(
                array(
                    'hFrameworkResourcePrimaryKey' => str_replace('Id', 'Id', $data['hFrameworkResourcePrimaryKey'])
                ),
                array(
                    'hFrameworkResourceId' => $data['hFrameworkResourceId']
                )
            );
        }

        $this->hDatabase->truncate(
            array(
                'hFileCache',
                'hUserPermissionsCache',
                'hUserSessions'
            )
        );
    }

    private function updatePlugins($table)
    {
        $query = $this->$table->select(
            array(
                'hPluginListenerId',
                'hPluginListenerMethod'
            )
        );

        foreach ($query as $data)
        {
            if (strstr($data['hPluginListenerMethod'], 'Id'))
            {
                $this->$table->update(
                    array(
                        'hPluginListenerMethod' => str_replace('Id', 'Id', $data['hPluginListenerMethod'])
                    ),
                    array(
                        'hPluginListenerId' => $data['hPluginListenerId']
                    )
                );
            }
        }  
    
    }
    
    private function updateVariables($collection)
    {
        $table = "h{$collection}Variables";
    
        $query = $this->$table->select(
            array(
                'h'.$collection.'Id',
                'h'.$collection.'Variable'
            )
        );
        
        foreach ($query as $data)
        {
            if (strstr($data['h'.$collection.'Variable'], 'Id'))
            {
                $update['h'.$collection.'Variable'] = str_replace('Id', 'Id', $data['h'.$collection.'Variable']);

                $where['h'.$collection.'Id'] = $data['h'.$collection.'Id'];
                $where['h'.$collection.'Variable'] = $data['h'.$collection.'Variable'];

                $this->$table->update($update, $where);
            }
        }
    }
}

?>