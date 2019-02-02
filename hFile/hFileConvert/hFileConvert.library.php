<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File Converter
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

class hFileConvertLibrary extends hPlugin {

    private $path;

    public function hConstructor()
    {
        $this->path = $this->hFileConvertCommandPath('/usr/local/bin');
    }

    public function executeConvertFileCommand($command, $arguments)
    {
        switch ($command)
        {
            case 'textutil':
            {
                $command = '/usr/bin/textutil';
                break;
            }
            default:
            {
                $command = $this->path.'/'.$command;
            }
        }

        if (is_executable($command))
        {
            $descriptor = array(
                0 => array("pipe", "r"),  # stdin is a pipe that the child will read from
                1 => array("pipe", "w"),  # stdout is a pipe that the child will write to
                2 => array("file", "/tmp/hFramework.txt", "a") # stderr is a file to write to
            );

            $pipes = array();

            $process = proc_open(
                $command.' '.$arguments,
                $descriptor,
                $pipes
            );

            if (is_resource($process))
            {
                # $pipes now looks like this:
                # 0 => writeable handle connected to child stdin
                # 1 => readable handle connected to child stdout
                # Any error output will be appended to /tmp/error-output.txt
                fclose($pipes[0]);

                $output = stream_get_contents($pipes[1]);
                fclose($pipes[1]);

                # It is important that you close any pipes before calling
                # proc_close in order to avoid a deadlock
                $return = proc_close($process);

                $this->verbose(
                    "Command {$command} exited with return value {$return}.",
                    __FILE__,
                    __LINE__
                );
            }

            return isset($output)? $output : nil;
        }
        else
        {
            $this->warning(
                'Command '.$command.' is not executable.',
                __FILE__,
                __LINE__
            );
        }
    }

    #
    # This method is used to extract text from popular file formats.  One use for this
    # is storing the text in the hFileDocument column of the hFileDocuments table, which
    # makes it possible to do fulltext indexing and search on all documents, not just those
    # stored in the framework itself.
    #
    # This requires various third-party Unix shell porgrams, some specific to Mac OS X.
    #
    #   PDF: pdftotext
    #   Excel: xls2csv
    #   Word: catdoc
    #   Word docx, HTML, rtf, rtfd, webarchive: textutil (Mac OS X)
    #   Other text documents: directly extracted as is
    #
    public function getPlainText($file, $mime = nil, $charset = nil)
    {
        if (file_exists($file))
        {
            $extension = $this->getExtension($file);

            switch ($extension)
            {
                case 'pdf':
                {
                    if (file_exists($this->path.'/pdftotext'))
                    {
                        return $this->executeConvertFileCommand(
                            'pdftotext',
                            '"'.$file.'" -'
                        );
                    }
                    else
                    {
                        $this->notice(
                            'Text could not be extracted from the PDF because the pdftotext '.
                            'command is not installed to '.$this->path.'.',
                            __FILE__,
                            __LINE__
                        );
                    }

                    return '';
                }
                case 'xls':
                {
                    if (file_exists($this->path.'/xls2csv'))
                    {
                        return $this->executeConvertFileCommand(
                            'xls2csv',
                            '"'.$file.'"'
                        );
                    }
                    else
                    {
                        $this->notice(
                            'Text could not be extracted from the XLS because the xls2csv '.
                            'command is not installed to '.$this->path.'.',
                            __FILE__,
                            __LINE__
                        );
                    }

                    return '';
                }
                case 'doc':
                {
                    if ($this->hOS != 'Darwin' && file_exists($this->path.'/catdoc'))
                    {
                        return $this->executeConvertFileCommand(
                            'catdoc',
                            '"'.$file.'"'
                        );
                    }
                }
                case 'docx':
                case 'html':
                case 'rtf':
                case 'rtfd':
                case 'webarchive':
                {
                    if ($this->hOS == 'Darwin')
                    {
                        return $this->executeConvertFileCommand(
                            'textutil',
                            '-stdout -convert txt "'.$file.'"'
                        );
                    }
                }
                default:
                {
                    if (!empty($mime))
                    {
                        if (substr($mime, 0, 5) == 'text/' || $charset == 'us-ascii')
                        {
                            return file_get_contents($file);
                        }
                        else
                        {
                            switch ($mime)
                            {
                                case 'application/javascript':
                                case 'application/xml':
                                case 'application/xhtml+xml':
                                {
                                    return file_get_contents($file);
                                }
                            }
                        }
                    }

                    return '';
                }
            }
        }
        else
        {
            $this->warning(
                'File conversion to plain text failed because the file: '.$file.' does not exist.',
                __FILE__,
                __LINE__
            );
        }
    }
}

?>