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

class hGoogleWeatherLibrary extends hPlugin {

    public function hConstructor()
    {

    }

    public function getForecastFromGoogle($location)
    {
        $xml = simplexml_load_file('http://www.google.com/ig/api?weather='.urlencode($location));

        //var_dump($xml);

        $weather = array();

        if (isset($xml->weather->forecast_conditions))
        {
            $n = 0;

            foreach ($xml->weather->forecast_conditions as $i => $data)
            {
                $weather[$n]['day']       = $this->getAttributeValue($data->day_of_week, 'data');
                $weather[$n]['low']       = $this->getAttributeValue($data->low, 'data');
                $weather[$n]['high']      = $this->getAttributeValue($data->high, 'data');
                $weather[$n]['image']     = $this->getAttributeValue($data->icon, 'data');
                $weather[$n]['condition'] = $this->getAttributeValue($data->condition, 'data');

                $n++;
            }
        }

        return $weather;
    }

    private function getAttributeValue($obj, $attribute)
    {
        foreach ($obj->attributes() as $attr => $value)
        {
            if ($attribute == $attr)
            {
                return (string) $value[0];
            }
        }

        return null;
    }

    public function getForecastByCity($city)
    {
        return $this->getForecastFromGoogle($city);
    }

    public function isDaytime()
    {
        $time = time();

        $sunrise = date_sunrise(
            $time,
            SUNFUNCS_RET_TIMESTAMP,
            $this->IntranetAddressLatitude,
            $this->IntranetAddressLongitude,
            96,  // Civilian Zenith
            -5   // GMT Offset
        );

        $sunset = date_sunset(
            $time,
            SUNFUNCS_RET_TIMESTAMP,
            $this->IntranetAddressLatitude,
            $this->IntranetAddressLongitude,
            96,
            -5
        );

        return ($time > $sunrise && $time < $sunset);
    }
}

?>