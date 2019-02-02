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

class hGoogleMapGeocodeLibrary extends hPlugin {

    private $hJSON;
    private $hLocation;
    private $hLocationCity;

    public function hConstructor()
    {    
        if (!class_exists('hJSONLibrary'))
        {
            include $this->hServerDocumentRoot.'/hJSON/hJSON.library.php';
        }

        $this->hJSON = new hJSONLibrary('/hJSON/hJSON.library.php');
    }

    public function queryCoordinates($query)
    {
        $query = is_array($query)? 
            urlencode(implode(',', $query)) : urlencode($query);

        return $this->hJSON->getJSON(
            'http://maps.googleapis.com/maps/api/geocode/json?address='.$query.'&sensor=false'
        );
    }

    public function queryAddress($contactAddressId)
    {
        if (!empty($contactAddressId))
        {
            $address = $this->hContactAddresses->selectAssociative('*', $contactAddressId);

            // Replace any existing latitude / longitude coordinates with new 
            // coordinates fetched from Google
            $template = $this->hContactAddressTemplates->selectColumn(
                'hContactAddressTemplate',
                $this->hLocationCountries->selectColumn(
                    'hContactAddressTemplateId',
                    $address['hLocationCountryId']
                )
            );

            $state = '';

            if (!empty($address['hLocationStateId']))
            {
                $useStateCode = (int) $this->hLocationCountries->selectColumn(
                    'hLocationUseStateCode',
                    $address['hLocationCountryId']
                );
        
                $state = $this->hLocationStates->selectColumn(
                    $useStateCode? 'hLocationStateCode' : 'hLocationStateName',
                    $address['hLocationStateId']
                );
            }
    
            $country = $this->hLocationCountries->selectColumn(
                'hLocationCountryName',
                $address['hLocationCountryId']
            );
            
            $address = str_replace(
                array(
                    '{$street}',
                    '{$city}',
                    '{$state}',
                    '{$postalCode}',
                    '{$country}',
                    '|'
                ),
                array(
                    str_replace("\n", ', ', $address['hContactAddressStreet']),
                    $address['hContactAddressCity'],
                    $state,
                    $address['hContactAddressPostalCode'],
                    $country,
                    ', '
                ),
                $template
            );
            
            $this->console("Geocoding address: '{$address}'");

            $json = $this->queryCoordinates($address);

            if (isset($json->results[0]) && isset($json->results[0]->geometry) && isset($json->results[0]->geometry->location))
            {
                $this->console("Coordinates: {$json->results[0]->geometry->location->lat}, {$json->results[0]->geometry->location->lng}");

                $this->hContactAddresses->update(
                    array(
                        'hContactAddressLatitude'  => $json->results[0]->geometry->location->lat,
                        'hContactAddressLongitude' => $json->results[0]->geometry->location->lng
                    ),
                    $contactAddressId
                );
            }
        }
    }

    public function queryZipCode($zipCode, $radius = 25)
    {
        if (!empty($zipCode) && !is_array($zipCode))
        {
            $json = $this->queryCoordinates($zipCode);

            if (isset($json->results[0]) && isset($json->results[0]->geometry) && isset($json->results[0]->geometry->location))
            {
                $this->hLocationZipCodes->update(
                    array(
                        'hLocationZipCodeLatitude'  => $json->results[0]->geometry->location->lat,
                        'hLocationZipCodeLongitude' => $json->results[0]->geometry->location->lng
                    ),
                    array(
                        'hLocationZipCode' => $zipCode
                    )
                );
            }

            $location = $this->hLocationZipCodes->selectAssociative(
                array(
                    'hLocationZipCodeLatitude',
                    'hLocationZipCodeLongitude'
                ),
                array(
                    'hLocationZipCode' => $zipCode
                )
            );

            return $this->getGeofence($location['hLocationZipCodeLatitude'], $location['hLocationZipCodeLongitude'], $radius);
        }

        return array();
    }

    public function queryCity($city, $stateId, $radius = 25)
    {
        if (!is_object($this->hLocationCity))
        {
            $this->hLocationCity = $this->library('hLocation/hLocationCity');
        }

        if (!is_object($this->hLocation))
        {
            $this->hLocation = $this->library('hLocation');
        }

        $locationCityId = $this->hLocationCity->getId($city, $stateId);

        if (!empty($locationCityId))
        {
            $location = $this->hLocationCity->getLatitudeLongitude($locationCityId);

            if (empty($location['hLocationCityLatitude']) && empty($location['hLocationCityLongitude']))
            {
                $json = $this->queryCoordinates("{$city}, ".$this->hLocation->getStateName($stateId));

                if (isset($json->results[0]) && isset($json->results[0]->geometry) && isset($json->results[0]->geometry->location))
                {
                    $this->hLocationCity->setLatitudeLongitude(
                        $locationCityId,
                        $json->results[0]->geometry->location->lat,
                        $json->results[0]->geometry->location->lng
                    );
                }
            }
        }
        else
        {
            $json = $this->queryCoordinates("{$city}, ".$this->hLocation->getStateName($stateId));

            if (isset($json->results[0]) && isset($json->results[0]->geometry) && isset($json->results[0]->geometry->location))
            {
                $locationCityId = $this->hLocationCity->insert(
                    array(
                        'hLocationCity' => $city,
                        'hLocationStateId' => (int) $stateId,
                        'hLocationCityLatitude'  => (float) $json->results[0]->geometry->location->lat,
                        'hLocationCityLongitude' => (float) $json->results[0]->geometry->location->lng
                    )
                );
            }
        }

        if (!empty($locationCityId))
        {
            if (!isset($location) || isset($location) && empty($location['hLocationCityLatitude']) && empty($location['hLocationCityLongitude']))
            {
                $location = $this->hLocationCity->getLatitudeLongitude($locationCityId);
            }

            return $this->getGeofence($location['hLocationCityLatitude'], $location['hLocationCityLongitude'], $radius);
        }
        
        return array();
    }

    public function getGeofenceByLocation(array $options, $radius = 25)
    {
        if (!empty($options['zipCode']))
        {
            return $this->queryZipCode($options['zipCode'], $radius);
        }
        else if (!empty($options['city']) && !empty($options['stateId']))
        {               
            return $this->queryCity($options['city'], $options['stateId'], $radius);
        }
        else if (!empty($options['byCoordinates']))
        {
            return $this->getGeofence($options['latitude'], $options['longitude'], $radius);
        }
        
        return array();
    }
    
    public function getGeofence(&$latitude, &$longitude, $radius)
    {
        # @return array
        
        # @description
        # <h2>Getting a Geofence</h2>
        # <p>
        #   Returns a geofence for the specified <var>$latitude</var> and <var>$longitude</var>,
        #   in the size of <var>$radius</var>.  The returned dimensions create a square around the 
        #   location, <var>north</var>, <var>south</var>, <var>east</var>, and <var>west</var>.  This
        #   can then be supplied to a proximity query or other to limit the results returned to the 
        #   fenced region.
        # </p>
        # @end
        if (isset($latitude) && isset($longitude))
        {
            return array(
                'north'     => $longitude - $radius / abs(cos(deg2rad($latitude)) * 69),
                'south'     => $longitude + $radius / abs(cos(deg2rad($latitude)) * 69),
                'east'      => $latitude  - ($radius / 69),
                'west'      => $latitude  + ($radius / 69),
                'latitude'  => (float) $latitude,
                'longitude' => (float) $longitude, 
                'radius'    => (int) $radius,
                'proximity' => true
            );
        }
        
        return array();
    }
    
    public function useProximity(&$results)
    {
        return (isset($results['north']) && isset($results['south']) && isset($results['east']) && isset($results['west']));
    }
}

?>