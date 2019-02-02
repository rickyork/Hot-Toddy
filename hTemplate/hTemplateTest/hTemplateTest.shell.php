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

class hTemplateTestShell extends hShell {

    private $templateVariables;
    private $template;

    private $matchCurlyBraces = "/\{([^{}]+|(?R))*\}/"; // Matches properly nested curly brace sets

    public function hConstructor()
    {
        // $this->templateVariables = array(
        //     'singleVariable' => 'Testing a single Variable',
        //     'associative' => array(
        //         'index' => 'Testing an associative array'
        //     ),
        //     'numeric' => array(
        //         0 => 'Testing a numeric array'
        //     ),
        //     'issetVariable' => 'This variable isset',
        //     'notEmptyVariable' => 'This variable is not empty',
        //     'emptyVariable' => '',
        //     'compare1' => 1,
        //     'compare2' => 1,
        //     'compare4' => 1,
        //     'compare5' => 0,
        //     'compare6' => 1,
        //     'iteration' => array(
        //         0 => array(
        //             'arrayTest' => 'Test 1'
        //         ),
        //         1 => array(
        //             'arrayTest' => 'Test 2'
        //         ),
        //         2 => array(
        //             'arrayTest' => 'Test 3'
        //         )
        //     ),
        //     'iteration2' => array(
        //         'arrayTest' => array(
        //             'Test 1',
        //             'Test 2',
        //             'Test 3'
        //         )
        //     ),
        //     'navigation' => array(
        //         (object) array(
        //             'link' => "/Practices/footandankle/index.html",
        //             'label' => "Home",
        //             'id' => "Home"
        //         ),
        //         (object) array(
        //             'link' => "/Practices/footandankle/Providers.html",
        //             'label' => "Meet Our Team",
        //             'id' => "Providers"
        //         ),
        //         (object) array(
        //             'link' => "/Practices/footandankle/Services.html",
        //             'label' => "Services",
        //             'id' => "Services"
        //         ),
        //         (object) array(
        //             'link' => "/Practices/footandankle/Common Disorders.html",
        //             'label' => "Common Disorders",
        //             'id' => "CommonDisorders"
        //         ),
        //         (object) array(
        //             'link' => "/Practices/footandankle/Fellowship.html",
        //             'label' => "Fellowship",
        //             'id' => "Fellowship"
        //         ),
        //         (object) array(
        //             'link' => "/Practices/footandankle/About Us.html",
        //             'label' => "About Us",
        //             'id' => "AboutUs"
        //         )
        //     )
        // );
        //
        // $_POST['test'] = 'Testing $_POST variable';
        //
        // $_GET['test'] = 'Testing $_GET variable';
        //
        // $_SERVER['test'] = 'Testing $_SERVER variable';
        //
        // $_ENV['test'] = 'Testing $_ENV variable';
        //
        // $_COOKIE['test'] = 'Testing $_COOKIE variable';
        //
        // $_SESSION['test'] = 'Testing $_SESSION variable';
        //
        // $GLOBALS['test'] = 'Testing $GLOBALS variable';
        //
        // $this->template = file_get_contents(dirname(__FILE__).'/TXT/Syntax.txt');
        //
        // $this->parseTemplate($this->template);

        if ($this->shellArgumentExists('test', '--test'))
        {
            switch ($this->getShellArgumentValue('test', '--test'))
            {
                case 'iteration':
                {
                    $this->iteration();
                    break;
                }
            }
        }
    }

    public function iteration()
    {
        $template = file_get_contents(dirname(__FILE__).'/TXT/Iteration.txt');

        $this->console($template);

        echo $this->parseTemplate(
            $template,
            array(
                'navigation' => array(
                    (object) array(
                        'link' => "/Practices/footandankle/index.html",
                        'label' => "Home",
                        'id' => "Home"
                    ),
                    (object) array(
                        'link' => "/Practices/footandankle/Providers.html",
                        'label' => "Meet Our Team",
                        'id' => "Providers"
                    ),
                    (object) array(
                        'link' => "/Practices/footandankle/Services.html",
                        'label' => "Services",
                        'id' => "Services"
                    ),
                    (object) array(
                        'link' => "/Practices/footandankle/Common Disorders.html",
                        'label' => "Common Disorders",
                        'id' => "CommonDisorders"
                    ),
                    (object) array(
                        'link' => "/Practices/footandankle/Fellowship.html",
                        'label' => "Fellowship",
                        'id' => "Fellowship"
                    ),
                    (object) array(
                        'link' => "/Practices/footandankle/About Us.html",
                        'label' => "About Us",
                        'id' => "AboutUs"
                    )
                )
            )
        );
    }

    public function expandTemplate($matches)
    {
        if (preg_match('/^(\$|\w|\d)*$/', $matches[1]))
        {
            $variableMatch = $matches[1];

            if (substr($variableMatch, 0, 1) == '$')
            {
                $variableMatch = substr($variableMatch, 1);
            }

            if (isset($this->templateVariables[$variableMatch]))
            {
                return $this->templateVariables[$variableMatch];
            }
        }

        $subMatches = array();

        return '{'.preg_replace_callback($this->matchCurlyBraces, array($this, 'expandTemplate'), $matches[1]).'}';
    }
}

?>