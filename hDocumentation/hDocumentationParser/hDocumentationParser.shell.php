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
# @description
# <h1>Documentation Parser Shell</h1>
# <p>
#   Parses Hot Toddy source code files and extracts code snippets and documentation from
#   source code.
# </p>
# @end

class hDocumentationParserShell extends hShell {

    private $hDocumentationParser;

    public function hConstructor()
    {
        $this->hDocumentationParser = $this->library('hDocumentation/hDocumentationParser');

        if ($this->shellArgumentExists('help'))
        {
            $this->console($this->getTemplateTXT('Help'));
        }
        else if ($this->shellArgumentExists('tokenize'))
        {
            if ($this->shellArgumentExists('all'))
            {
                $this->hDocumentationParser->parseFiles();
            }
            else
            {
                $this->hDocumentationParser->tokenize(
                    $this->getShellArgumentValue('tokenize')
                );
            }
        }
    }
}

?>