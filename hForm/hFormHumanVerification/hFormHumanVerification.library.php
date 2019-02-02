<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Form Human Verification
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
#
# @todo add CAPTCHA
#

class hFormHumanVerificationLibrary extends hPlugin {

    private $hForm;

    public function hConstructor()
    {
        $this->hForm = $this->library('hForm');
    }

    public function add()
    {
        $this->hForm
            ->addRequiredField(
                $this->hFormHumanVerificationRequiredErrorText(
                    'You did not answer the question "What year is it?"'
                )
            )
            ->addValidationByCallback(
                $this->hFormHumanVerificationErrorText(
                    'You did not correctly answer the question "What year is it?"'
                ),
                $this, 'checkAnswer'
            )
            ->addTextInput(
                'hFormHumanVerification',
                $this->hFormHumanVerificationQuestion("What year is it?<br /><i>Enter as xxxx</i>"),
                $this->hFormHumanVerificationSize(4)
            );
    }

    public function checkAnswer()
    {
        if (!isset($_POST['hFormHumanVerification']))
        {
            return false;
        }

        switch ($this->hFormHumanVerificationOption('date'))
        {
            case '>':
                return (int) $_POST['hFormHumanVerification'] > (int) $this->hFormHumanVerificationAnswer(null);
            case '<':
                return (int) $_POST['hFormHumanVerification'] < (int) $this->hFormHumanVerificationAnswer(null);
            case '>=':
                return (int) $_POST['hFormHumanVerification'] >= (int) $this->hFormHumanVerificationAnswer(null);
            case '<=':
                return (int) $_POST['hFormHumanVerification'] <= (int) $this->hFormHumanVerificationAnswer(null);
            case 'eval':
                return (int) $_POST['hFormHumanVerification'] == eval($this->hFormHumanVerificationAnswer(null));
            case 'string':
                return (string) $_POST['hFormHumanVerification'] == (string) $this->hFormHumanVerificationAnswer(null);
            case 'int':
                return (int) $_POST['hFormHumanVerification'] == (int) $this->hFormHumanVerificationAnswer(null);
            case 'date':
                return (int) $_POST['hFormHumanVerification'] == (int) date($this->hFormHumanVerificationAnswer('Y'));
            default:
                return trim(strtolower($_POST['hFormHumanVerification'])) == trim(strtolower($this->hFormHumanVerificationAnswer(null)));
        }
    }
}

?>