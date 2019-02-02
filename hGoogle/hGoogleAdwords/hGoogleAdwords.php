<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Google Adwords
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

class hGoogleAdwords extends hPlugin {

    public function hConstructor()
    {
        //$this->hGoogleAdwordsConversion = '<id>,<label>';

        if ($this->hGoogleAdwordsConversion)
        {
            list(
                $adwordsConversionId,
                $adwordsConversionLabel,
                $adwordsConversionFormat
            ) = explode(',', $this->hGoogleAdwordsConversion);

            $this->hFileDocument .= $this->getTemplate(
                'Conversion',
                array(
                    'adwordsConversionId' => $adwordsConversionId,
                    'adwordsConversionLabel' => $adwordsConversionLabel,
                    'adwordsConversionFormat' => $adwordsConversionFormat
                )
            );
        }
    }
}

?>