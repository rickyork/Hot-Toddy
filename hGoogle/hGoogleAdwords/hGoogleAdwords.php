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