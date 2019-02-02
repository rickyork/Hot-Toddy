<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Test Mailer
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