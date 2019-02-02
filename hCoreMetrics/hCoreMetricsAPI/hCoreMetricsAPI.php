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

class hCoreMetricsAPI extends hPlugin {

    public function getConversionTag($conversionId, $isCompleted, $eventId, $points = 0)
    {
        return(
            "<script type='text/javascript'>\n".
                "  cmCreateConversionEventTag('{$conversionId}', ".($isCompleted? 2 : 1).", '{$eventId}', ".(int) $points.");\n".
            "</script>\n"
        );
    }

    public function getRegistrationTag($registration)
    {
        if (is_array($registration))
        {
            hString::arrayToUTF8($registration, false);

            $html .=
                "<script type='text/javascript'>\n".
                "  cmCreateRegistrationTag(".
                    (int) $registration['hContactId'].", ".
                    "\"{$registration['hContactEmailAddress']}\", ".
                    "\"{$registration['hContactAddressCity']}\", ".
                    "\"{$registration['hLocationStateCode']}\", ".
                    "\"{$registration['hContactAddressPostalCode']}\", ";

            if (isset($registration['newsletterName']))
            {
                $html .= "\"{$registration['newsletterName']}\", ";
            }
            else
            {
                $html .= "null, ";
            }

            if (isset($registration['newsletterSubscribed']))
            {
                $html .= "\"".($registration['newsletterSubscribed']? 'Y' : 'N')."\", ";
            }
            else
            {
                $html .= "null, ";
            }

            $html .=
                    "\"{$registration['hLocationCountryName']}\", ".
                    "\"{$registration['hContactCategory']}\", ".
                    "\"{$registration['hContactTitle']}\", ".
                    "\"{$registration['hContactCompany']}\", ".
                    "\"".($registration['hContactRegistered']? 'Y' : 'N')."\"".
                  ");\n".
                "</script>\n";

            return $html;
        }
    }
}

?>