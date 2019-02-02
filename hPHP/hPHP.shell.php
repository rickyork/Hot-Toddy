<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy PHP
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

class hPHPShell extends hShell {

    public function hConstructor()
    {
        $this->console("Requires PHP 5.4 or later.  PHP is version: ".phpversion().".\n");        
    
        $this->plugin('hPHP');

        $item = type::factory([
            "item" => "test",
            "item2" => "test2",
            "item3"
        ]);

        $this->console("Implode: ".$item->implode(','));
        $this->console("Join: ".$item->join(','));
        
        $item->shuffle();
        
        $this->console("Shuffle: ".$item->implode(','));
        
        $item->flip();
        
        $this->console("Flip: ".$item->implode(','));

        $this->console("Length: ".$item->length);
        $this->console("Length (fn): ".$item->length());

        $this->console("\n");
    }
}

?>