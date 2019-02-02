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

class hFileIndexVolumeShell extends hShell {

    private $hFileIndexVolume;

    public function hConstructor()
    {
        if ($this->shellArgumentsExists('index', '--index') && $this->shellArgumentExists('to', '--to'))
        {
            $index = $this->getShellArgumentValue('index', '--index');

            if (file_exists($index) && is_dir($index))
            {
                $to = $this->getShellArgumentValue('to', '--to');

                $this->hFileIndexVolume = $this->library('hFile/hFileIndexVolume');

                $this->hFileIndexVolume->setVolumeName($to);

                $this->hFileIndexVolume->index($index);
            }
            else
            {
                $this->fatal('Volume to be indexed, '.$index.', either does not exist or is not a directory.', __FILE__, __LINE__);
            }
        }
    }
}

?>