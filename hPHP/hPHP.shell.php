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