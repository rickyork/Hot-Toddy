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
# <h1>Map Service Google API</h1>
# <p>
#   Provides a map service for the Google Maps API.
# </p>
# @end

class hMapServiceGoogleLibrary extends hMapInterface {

    private $hJSON;

    public function hConstructor(array $arguments = array())
    {
        if (!class_exists('hJSONLibrary'))
        {
            include $this->hServerDocumentRoot.'/hJSON/hJSON.library.php';
        }

        $this->hJSON = new hJSONLibrary('/hJSON/hJSON.library.php');
    }

    public function getCoordinates($query)
    {
        # @return array | false

        # @description
        # <h2>Querying Google Maps for Coordinates</h2>
        # <p>
        #   Queries the Google Maps API for location coordinates.
        # </p>
        # @end

        $query = is_array($query)? urlencode(implode(',', $query)) : urlencode($query);

        $json = $this->hJSON->getJSON(
            'http://maps.googleapis.com/maps/api/geocode/json?address='.$query.'&sensor=false'
        );

        if (isset($json->results[0]) && isset($json->results[0]->geometry) && isset($json->results[0]->geometry->location))
        {
            return array(
                'latitude' => $json->results[0]->geometry->location->lat,
                'longitude' => $json->results[0]->geometry->location->lng
            );
        }

        return false;
    }

    public function getAddressCoordinates($address)
    {
        # @return array | false

        # @description
        # <h2>Querying Google Maps for Address Coordinates</h2>
        # <p>
        #   Alias of <a href='#getCoordinates' class='code'>getCoordinates()</a>
        # </p>
        # @end

        return $this->getCoordinates($address);
    }

    public function getZipCodeCoordinates($zipCode)
    {
        # @return array | false

        # @description
        # <h2>Querying Google Maps for Zip Code Coordinates</h2>
        # <p>
        #   Alias of <a href='#getCoordinates' class='code'>getCoordinates()</a>
        # </p>
        # @end

        return $this->getCoordinates($zipCode);
    }

    public function getCityCoordinates($city)
    {
        # @return array | false

        # @description
        # <h2>Querying Google Maps for City Coordinates</h2>
        # <p>
        #   Alias of <a href='#getCoordinates' class='code'>getCoordinates()</a>
        # </p>
        # @end

        return $this->getCoordinates($city);
    }
}

?>