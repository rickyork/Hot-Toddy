<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework Variables Library
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
# <h1>Framework Variables API</h1>
#
# @end

class hFrameworkVariablesLibrary extends hPlugin {

    public function get($frameworkVariable)
    {
        return $this->hFrameworkVariables->selectColumn(
            'hFrameworkValue',
            array(
                'hFrameworkVariable' => $frameworkVariable
            )
        );
    }

    public function save($frameworkVariable, $frameworkValue)
    {
        $exists = $this->hFrameworkVariables->selectExists(
            'hFrameworkVariable',
            array(
                'hFrameworkVariable' => $frameworkVariable
            )
        );

        if ($exists)
        {
            $this->hFrameworkVariables->update(
                array(
                    'hFrameworkValue' => $frameworkValue
                ),
                array(
                    'hFrameworkVariable' => $frameworkVariable
                )
            );
        }
        else
        {
            $this->hFrameworkVariables->insert(
                array(
                    'hFrameworkVariable' => $frameworkVariable,
                    'hFrameworkValue' => $frameworkValue
                )
            );
        }
    }

    public function delete()
    {
        $variables = func_get_args();

        foreach ($variables as $variable)
        {
            $this->hFrameworkVariables->delete('hFrameworkVariable',  $variable);
        }
    }
}

?>