<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework to Ruby Shell
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

class hFrameworkToRubyShell extends hShell {

    private $hFrameworkToRuby;

    public function hConstructor()
    {
        $this->hFrameworkToRuby = $this->library('hFramework/hFrameworkToRuby');
        
        if ($this->shellArgumentExists('all', '--all'))
        {
            $this->hFrameworkToRuby->rubifyAll();
        }
        else
        {
            $path = $this->getShellArgumentValue('toRuby', '-tr');
            
            if (!empty($path))
            {
                if (file_exists($path))
                {
                    if (is_dir($path))
                    {
                        $this->hFrameworkToRuby->rubifyFolder($path);
                    }
                    else
                    {
                        $this->hFrameworkToRuby->rubify($path);
                    }
                }
                else
                {
                    $this->console("Error: Unable to rubify anything the path '{$path}' does not exist.");
                }
            }
            else
            {
                $this->console("Error: Unable to rubify anything, no path provided."); 
            }
        }
    }
}

?>