<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Documentation Parser Shell
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