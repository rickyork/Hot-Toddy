<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Core Metrics API
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