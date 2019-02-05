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

class hContactShell extends hShell {

    private $hContactDatabase;

    public function hConstructor()
    {
        $this->hContactDatabase = $this->database('hContact');
        
        switch (true)
        {
            case $this->shellArgumentExists('--tidy', 'tidy'):
            {
                $this->console("Tidying addresses\n");

                $addresses = $this->hContactAddresses->select();
                
                foreach ($addresses as $address)
                {
                    $this->console(".");
                                 
                    if (empty($address['hContactAddressStreet']) && empty($address['hContactAddressCity']) && empty($address['hLocationStateId']))
                    {
                        $this->hContactDatabase->deleteAddress($address['hContactAddressId']);
                        $this->console("x");    
                    }
                    else if (empty($address['hLocationCountryId']))
                    {
                        $this->hContactAddresses->update(
                            array(
                                'hLocationCountryId' => 223
                            ),
                            $address['hContactAddressId']
                        );
                        
                        $this->console('Fixed missing countryId.');
                    }
                }
                
                break;
            }
        }
    }   
}   
    
?>