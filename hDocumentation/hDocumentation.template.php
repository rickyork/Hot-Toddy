<?php

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