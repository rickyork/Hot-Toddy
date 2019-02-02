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

class hTidyLibrary extends hPlugin {

    private $tidyPath;
    private $tidyExists = true;

    public function hConstructor()
    {
        $os = '';

        switch ($this->hOS)
        {
            case 'Darwin':
            {
                $os = 'Mac OS X';
                break;
            }
            case 'Linux':
            {
                $os = 'Linux';
                break;
            }
        }

        $this->tidyPath = $this->hFrameworkLibraryPath.'/Tidy/'.$os.'/tidy';

        if (!file_exists($this->tidyPath))
        {
            $this->warning(
                'HTML Tidy does not exist at the path: '.$this->tidyPath.'.',
                __FILE__,
                __LINE__
            );

            $this->tidyExists = false;
        }

        if (!is_executable($this->tidyPath))
        {
            $this->warning(
                'HTML Tidy is not executable. Path: '.$this->tidyPath.'.',
                __FILE__,
                __LINE__
            );

            $this->tidyExists = false;
        }
    }

    public function getHTML($html)
    {
        if (!$this->tidyExists)
        {
            return $html;
        }

        $path = escapeshellarg($this->tidyPath).
          ' --output-xhtml 1'.
          ' --indent-spaces 2'.
          ' --show-body-only 1'.
          ' --show-errors 0'.
          ' --quiet 1'.
          ' --clean 1'.
          ' --indent auto'.
          ' --vertical-space 0'.
          ' --uppercase-tags 0'.
          ' --drop-empty-paras 1'.
          ' --drop-proprietary-attributes 1'.
          ' --drop-font-tags 1'.
          ' --uppercase-attributes 0'.
          ' --word-2000 1';

        $process = proc_open(
            $path,
            array(
                array('pipe', 'r'),                         // stdin is a pipe that the child will read from
                array('pipe', 'w'),                         // stdout is a pipe that the child will write to
                array('file', '/tmp/error-output.txt', 'a') // stderr is a file to write to
            ),
            $pipes
        );

        if (is_resource($process))
        {
            // $pipes now looks like this:
            // 0 => writeable handle connected to child stdin
            // 1 => readable handle connected to child stdout
            // Any error output will be appended to /tmp/error-output.txt
            fwrite($pipes[0], $html);
            fclose($pipes[0]);

            $html = stream_get_contents($pipes[1]);

            fclose($pipes[1]);

            // It is important that you close any pipes before calling
            // proc_close in order to avoid a deadlock
            $return_value = proc_close($process);
            //echo "Return Value: ".$return_value."\n";
        }

        return $html;
    }
}

?>