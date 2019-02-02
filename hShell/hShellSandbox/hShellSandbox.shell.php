<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Shell Sandbox
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

class hShellSandboxShell extends hShell {

    public function hConstructor()
    {
        $this->console("Hot Toddy Interactive Shell\n\n");

        $this->console("Type 'help' for options\n");

        $code = '';

        $exitInterpreter = false;

        while (true)
        {
            $handle = fopen('php://stdin', 'r');

            $line = fgets($handle);

            switch (trim($line))
            {
                case 'exit';
                {
                    $exitInterpreter = true;
                    break;
                }
                case 'c':
                case 'clear':
                {
                    $code = '';
                    break;
                }
                case 'h':
                case 'help':
                {
                    echo $this->getTemplateTXT('Help')."\n";
                    break;
                }
                case 'r':
                case 'e':
                case 'exe':
                case 'exec':
                case 'execute':
                {
                    echo "\n";
                    echo eval($code)."\n\n";
                    break;
                }
                default:
                {
                    $code .= $line;
                }
            }

            fclose($handle);

            if ($exitInterpreter)
            {
                break;
            }
        }
    }
}

?>