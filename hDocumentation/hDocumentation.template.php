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

$html .= $this->getTemplate(
    dirname(__FILE__).'/HTML/Documentation.html',
    array(
        'hFileDocument'            => $this->getDocument(),
        'hDocumentationNaviation'  => $this->hDocumentationNaviation,
        'path'                     => $this->hFileWildcardPath,
        'TogglePrivate'            => $this->hDocumentationPrivate? 'Show' : 'Hide',
        'ToggleProtected'          => $this->hDocumentationProtected? 'Show' : 'Hide',
        'hDocumentationSourcePath' => $this->hDocumentationSourcePath
    )
);

?>