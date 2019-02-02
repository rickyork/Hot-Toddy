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

class hTemplateLanguageShell extends hShell {

    public function hConstructor()
    {
        $_GET['test'] = 'This is a GET variable';
        $_POST['test'] = 'This is a POST variable';
        $_COOKIE['test'] = 'This is a COOKIE variable';
        $_SESSION['test'] = 'This is a SESSION variable';
        $GLOBALS['test'] = 'This is a global variable';
        $_ENV['USER'] = 'richard';
        
        $templateVariables = array(
            'singleVariable' => 'This is a single variable',
            'associative' => array(
                'index' => 'This is an associative array'
            ),
            'numeric' => array(
                'This is offset 0 in a numeric array'
            ),
            'emptyVariable' => '',
            'compare1' => '?',
            'compare2' => '?',
            'files' => array(
                'hFileName' => array(
                    'index.html',
                    'documents.html'
                ),
                'hFileTitle' => array(
                    'Welcome to Hot Toddy',
                    'Documents'
                ),
                'hFilePath' => array(
                    '/index.html',
                    '/path/documents.html'
                ),
                'subfiles' => array(
                    array(
                        'hFileName' => array(
                            'list.html',
                            'fire.html'
                        ),
                        'hFileTitle' => array(
                            'List',
                            'Fire'
                        ),
                        'hFilePath' => array(
                            '/path/light/list.html',
                            '/path/light/fire.html'
                        )
                    ),
                    array(
                        'hFileName' => array(
                            'stuff.html',
                            'water.html'
                        ),
                        'hFileTitle' => array(
                            'Stuff',
                            'Water'
                        ),
                        'hFilePath' => array(
                            '/path/dark/stuff.html',
                            '/path/dark/water.html'
                        )
                    )
                )
            )
        );
        
        $test = $this->getShellArgumentValue('test', '--test');
            
        if ($this->shellArgumentExists('test', '--test'))
        {
            switch ($test)
            {
                case 'legacy':
                {
                    $test = 'Legacy Iteration';
                    break;
                }
                default:
                {
                    $test = ucwords($test);
                }
            }
        }
        else
        {
            $this->fatal("Unable to test template language because no 'test' argument was provided.");
        
        }

        if (file_exists(dirname(__FILE__).'/TXT/'.$test.'.txt'))
        {
            $string = file_get_contents(dirname(__FILE__).'/TXT/'.$test.'.txt');
            
            $_POST['test'] = "Lorem ipsum...";
            
            echo $this->parseTemplate($string, $templateVariables);
        }
        else
        {
            $this->fatal("Unable to test template language because no test by the name '{$test}' exists.");
        }
    }
}

?>