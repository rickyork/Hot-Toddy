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


class hMailTestMailer extends hPlugin {

    public function hConstructor()
    {
        $this->sendMail(
            'hMailTestMail',
            array(
                'hContactDisplayName'  => 'John Appleseed',
                'hContactEmailAddress' => 'john@example.com',
                'hContactFirstName'    => 'John',
                'hUserName'            => 'jappleseed',
                'hUserPassword'        => 'SomePassword123',
                'testVariable'         => true
            )
        );

        if (!$this->hFileDocument)
        {
            $this->hFileTitle = '';
            $this->hFileDocument = $this->getTemplate('Test Mail');
        }
    }
}

?>