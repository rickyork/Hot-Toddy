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