<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Open Search
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
#
# This is an implementation of Amazon's Open Search Specification.
#

class hSearchOpen extends hPlugin {

    public function hConstructor()
    {
        $this->hFileMIME     = isset($_GET['test'])? 'application/xml' : 'application/opensearchdescription+xml';
        $this->hTemplatePath = '';

        $this->hFileDocument = $this->getTemplateXML(
            'Open Search',
            array(
                'hSearchOpenName' => $this->hSearchOpenName(
                    $this->hFrameworkName('Hot Toddy')
                ),
                'hSearchOpenLongName' => $this->hSearchOpenLongName(
                    $this->hFrameworkName('Hot Toddy').' Search'
                ),
                'hSearchOpenIcon16x16' => $this->hSearchOpenIcon16x16(
                    $this->hFileFavicon(null)
                ),
                'hSearchOpenPNG64x64' => $this->hSearchOpenPNG64x64(nil),
                'hSearchOpenDescription' => $this->hSearchOpenDescription(nil),
                'hSearchOpenTags' => $this->hSearchOpenTags(nil),
                'hSearchOpenContact' => $this->hSearchOpenContact(
                    $this->hFrameworkAdministrator(nil)
                ),
                'hServerHost' => $this->hServerHost,
                'hSearchOpenSyndicationRight' => $this->hSearchOpenSyndicationRight('open'),
                'hSearchOpenAdultContent' => $this->hSearchOpenAdultContent('false'),
                'hSearchOpenLanguage' => $this->hSearchOpenLanguage(
                    $this->hLanguageLocalization('en-us')
                ),
                'hSearchOutputEncoding' => $this->hSearchOutputEncoding(
                    $this->hLanguageCharset(
                        $this->hFileEncoding('utf-8')
                    )
                ),
                'hSearchInputEncoding' => $this->hSearchInputEncoding(
                    $this->hLanguageCharset(
                        $this->hFileEncoding('utf-8')
                    )
                ),
                'hSearchOpenAttribution' => $this->hSearchOpenAttribution(
                    "Copyright ".date('Y').", ".$this->hFrameworkName.", All Rights Reserved."
                )
            )
        );
    }
}

?>