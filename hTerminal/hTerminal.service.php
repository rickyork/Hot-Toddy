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

class hTerminalService extends hService {

    public function hConstructor()
    {
        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }

        if (!$this->inGroup('root'))
        {
            $this->JSON(-1);
            return;
        }
    }

    public function execute()
    {
        if (isset($_GET['command']))
        {
            $command = hString::entitiesToUTF8($_GET['command'], false);
        }
        else if (isset($_POST['command']))
        {
            $command = hString::entitiesToUTF8($_POST['command'], false);
        }

        $descriptor = array(
            0 => array('pipe', 'r'),  # stdin is a pipe that the child will read from
            1 => array('pipe', 'w'),  # stdout is a pipe that the child will write to
            2 => array('pipe', 'w')   # stderr is a pipe that the child will write to
        );

        $pipes = array();

        $process = proc_open(
            $command,
            $descriptor,
            $pipes
        );

        if (is_resource($process))
        {
            # $pipes now looks like this:
            # 0 => writeable handle connected to child stdin
            # 1 => readable handle connected to child stdout
            # 2 => readable handle connected to child stderr
            #
            #fwrite($pipes[0], '');
            fclose($pipes[0]);

            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            # It is important that you close any pipes before calling
            # proc_close in order to avoid a deadlock
            $return = proc_close($process);
        }

        $this->JSON(
            array(
                'output' => $this->getTemplate(
                    'Output',
                    array(
                        'command' => $command,
                        'output'  =>  !empty($output)? $output : $stderr
                    )
                )
            )
        );
    }

    public function saveWindowDimensions()
    {
        if (empty($_GET['width']) || empty($_GET['height']))
        {
            $this->JSON(-5);
            return;
        }

        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }

        $this->user->saveVariable(
            'hTerminalWindowWidth',
            (int) $_GET['width']
        );

        $this->user->saveVariable(
            'hTerminalWindowHeight',
            (int) $_GET['height']
        );

        $this->JSON(1);
    }
}

?>