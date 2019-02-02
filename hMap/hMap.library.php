<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Map Library
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
# @description
# <h1>Map API Library</h1>
# <p>
#
# </p>
# @end

abstract class hMapInterface extends hPlugin {

    private $hJSON;

    abstract public function hConstructor(array $arguments = array());
    # {}

    abstract public function getCoordinates($query);
    # {}

    abstract public function getAddressCoordinates($address);
    # {}

    abstract public function getZipCodeCoordinates($zipCode);
    # {}

    abstract public function getCityCoordinates($city);
    # {}
}

class hMapLibrary extends hPlugin {

    private $hLocation;
    private $hLocationCity;
    private $hMapService;

    private $serviceName;

    public function hConstructor($arguments = array())
    {
        if (isset($arguments['serviceName']))
        {
            $this->serviceName = $arguments['serviceName'];
        }
        else
        {
            $this->serviceName = $this->hMapServiceName('google');
        }

        switch ($this->serviceName)
        {
            case 'google':
            {
                $this->hMapService = $this->library('hMap/hMapService/hMapServiceGoogle');
                break;
            }
            default:
            {
                if (!$this->hMapServiceName && $this->hMapServicePlugin)
                {
                    $this->hMapService = $this->library($this->hMapServicePlugin);
                }
            }
        }

        $this->hLocationCity = $this->library('hLocation/hLocationCity');
        $this->hLocation = $this->library('hLocation');
    }

    public function getCoordinates($query)
    {
        # @return array

        # @description
        # <h1>Querying a Location for Coordinates</h1>
        # <p>
        #   Queries the map service for latitude and longitude coordinates based on any
        #   arbitrary search string. e.g., an address, city, state, zip code, etc.
        # </p>
        # <p>
        #   Data returned in the array:
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td class='code'>latitude</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>longitude</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        return $this->hMapService->getCoordinates($query);
    }

    public function getAddressCoordinates($contactAddressId)
    {
        # @return array

        # @description
        # <h1>Getting Address Coordinates</h1>
        # <p>
        #   Retrieves latitude and longitude coordinates based on the
        #   provided <var>$contactAddressId</var>. If the address has been queried previously, the
        #   latitude and longitude is cached in the database based on information provided by the
        #   map service.
        # </p>
        # <p>
        #   Data returned:
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td class='code'>latitude</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>longitude</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        if (!empty($contactAddressId))
        {
            $exists = $this->hContactAddresses->selectExists('hContactAddressId', $contactAddressId);

            if ($exists)
            {
                $address = $this->hContactAddresses->selectAssociative('*', $contactAddressId);

                if (!empty($address['hContactAddressLatitude']) && !empty($address['hContactAddressLongitude']) && $this->hMapCacheDisabled(false))
                {
                    return array(
                        'latitude' => (float) $address['hContactAddressLatitude'],
                        'longitude' => (float) $address['hContactAddressLongitude']
                    );
                }

                # Replace any existing latitude / longitude coordinates with new
                # coordinates fetched from Google
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

                $coordinates = $this->hMapService->getAddressCoordinates($address);

                if ($coordinates !== false)
                {
                    if (isset($coordinates['latitude']) && isset($coordinates['longitude']))
                    {
                        $this->hContactAddresses->update(
                            array(
                                'hContactAddressLatitude'  => (float) $coordinates['latitude'],
                                'hContactAddressLongitude' => (float) $coordinates['longitude']
                            ),
                            $contactAddressId
                        );

                        return array(
                            'latitude' => (float) $coordinates['latitude'],
                            'longitude' => (float) $coordinates['longitude']
                        );
                    }
                    else
                    {
                        $this->warning(
                            "Map service '{$this->serviceName}' did not provide latitude and longitude ".
                            "coordinates for address: '{$address}'",
                            __FILE__,
                            __LINE__
                        );
                    }
                }
                else
                {
                    $this->notice(
                        "Map service '{$this->serviceName}' did not successfully query ".
                        "coordinates for address: '{$address}'",
                        __FILE__,
                        __LINE__
                    );
                }
            }
            else
            {
                $this->notice(
                    "Map service was not able to provide coordinates for contactAddressId: ".
                    "'{$contactAddressId}', because it does not exist.",
                    __FILE__,
                    __LINE__
                );
            }
        }
        else
        {
            $this->notice(
                "Map service was not able to provide address coordinates because no ".
                "contactAddressId was provided.",
                __FILE__,
                __LINE__
            );
        }

        return array();
    }

    public function getAddressGeofence($contactAddressId, $radius = 25)
    {
        # @return array

        # @description
        # <h2>Retrieving a Geofence</h2>
        # <p>
        #   Retrieves a geofence for the provided <var>$contactAddressId</var> and <var>$radius</var>.
        #   If no <var>$radius</var> is provided, the default radius of 25 miles is used.
        # </p>
        # <p>
        #   Data returned:
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td class='code'>north</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>south</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>east</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>west</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>latitude</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>longitude</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>radius</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>proximity</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        if (empty($radius))
        {
            $radius = 5;
        }

        $coordinates = $this->getAddressCoordinates($contactAddressId);

        return $this->getGeofence(
            $coordinates['latitude'],
            $coordinates['longitude'],
            (int) $radius
        );
    }

    public function getZipCodeCoordinates($zipCode)
    {
        # @return array

        # @description
        # <h2>Retrieving Zip Code Coordinates</h2>
        # <p>
        #   Retrieves latitude and longitude coordinates from the database based on the
        #   provided <var>$zipCode</var>, if a Map API service is specified, such as Google Maps,
        #   the latitude and longitude is queried and retrieved from that source and cached in
        #   the database for future queries.
        # </p>
        # <p>
        #   Data returned:
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td class='code'>latitude</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>longitude</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        if (!empty($zipCode))
        {
            $coordinates = $this->hMapService->getZipCodeCoordinates($zipCode);

            if ($coordinates !== false)
            {
                if (isset($coordinates['latitude']) && isset($coordinates['longitude']))
                {
                    $this->hLocationZipCodes->update(
                        array(
                            'hLocationZipCodeLatitude'  => (float) $coordinates['latitude'],
                            'hLocationZipCodeLongitude' => (float) $coordinates['longitude']
                        ),
                        array(
                            'hLocationZipCode' => $zipCode
                        )
                    );

                    $location = $this->hLocationZipCodes->selectAssociative(
                        array(
                            'hLocationZipCodeLatitude',
                            'hLocationZipCodeLongitude'
                        ),
                        array(
                            'hLocationZipCode' => $zipCode
                        )
                    );

                    return array(
                        'latitude' => (float) $location['hLocationZipCodeLatitude'],
                        'longitude' => (float) $location['hLocationZipCodeLongitude']
                    );
                }
                else
                {
                    $this->warning(
                        "Map service '{$this->serviceName}' did not provide latitude and longitude ".
                        "coordinates for zip code: '{$zipCode}'",
                        __FILE__,
                        __LINE__
                    );
                }
            }
            else
            {
                $this->notice(
                    "Map service '{$this->serviceName}' did not successfully query coordinates for ".
                    "zip code: '{$zipCode}'",
                    __FILE__,
                    __LINE__
                );
            }
        }
        else
        {
            $this->notice(
                "Map service was not able to provide zip code coordinates because no zip code was ".
                "provided.",
                __FILE__,
                __LINE__
            );
        }

        return array();
    }

    public function getZipCodeGeofence($zipCode, $radius = 25)
    {
        # @return array

        # @description
        # <h2>Retrieving a Zip Code Geofence</h2>
        # <p>
        #   Retrieves a geofence for the provided <var>$zipCode</var> and <var>$radius</var>.
        #   If no <var>$radius</var> is provided, the default radius of 25 miles is used.
        # </p>
        # <p>
        #   Data returned:
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td class='code'>north</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>south</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>east</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>west</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>latitude</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>longitude</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>radius</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>proximity</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        if (empty($radius))
        {
            $radius = 5;
        }

        $coordinates = $this->getZipCodeCoordinates($zipCode);

        return $this->getGeofence(
            $coordinates['latitude'],
            $coordinates['longitude'],
            (int) $radius
        );
    }

    public function getCityCoordinates($city, $locationStateId, $locationCountryId = 223)
    {
        # @return array

        # @description
        # <h2>Retrieving City Coordinates</h2>
        # <p>
        #   Retrieves latitude and longitude coordinates from the database based on the
        #   provided <var>$city</var>, <var>$locationStateId</var>, and <var>$locationCountryId</var>.
        #   If a Map API service is specified, such as Google Maps,
        #   the latitude and longitude is queried and retrieved from that source and cached in
        #   the database for future queries.
        # </p>
        # <p>
        #   Data returned:
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td class='code'>latitude</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>longitude</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        if (!empty($city) && !empty($locationStateId) && !empty($locationCountryId))
        {
            $locationCityId = $this->hLocationCity->getId(
                $city,
                $locationStateId,
                $locationCountryId
            );

            if (!empty($locationCityId) && $this->hMapCacheDisabled(false))
            {
                $location = $this->hLocationCity->getLatitudeLongitude($locationCityId);

                return array(
                    'latitude' => $location['hLocationCityLatitude'],
                    'longitude' => $location['hLocationCityLongitude']
                );
            }

            $state = $this->hLocation->getStateName($locationStateId);

            $country = $this->hLocation->getCountryName(
                $locationCountryId,
                'hLocationCountryISO3'
            );

            $coordinates = $this->hMapService->getCityCoordinates("{$city}, {$state}, {$country}");

            if ($coordinates !== false)
            {
                if (isset($coordinates['latitude']) && isset($coordinates['longitude']))
                {
                    if (!empty($locationCityId))
                    {
                        $this->setCityCoordinates(
                            $locationCityId,
                            (float) $coordinates['latitude'],
                            (float) $coordinates['longitude']
                        );
                    }
                    else
                    {
                        $locationCityId = $this->createCityCoordinates(
                            $city,
                            $locationStateId,
                            $locationCountryId,
                            (float) $coordinates['latitude'],
                            (float) $coordinates['longitude']
                        );
                    }

                    if (!empty($locationCityId))
                    {
                        $location = $this->hLocationCity->getLatitudeLongitude($locationCityId);

                        return array(
                            'latitude' => $location['hLocationCityLatitude'],
                            'longitude' => $location['hLocationCityLongitude']
                        );
                    }
                }
                else
                {
                    $this->warning(
                        "Map service '{$this->serviceName}' did not provide latitude and longitude ".
                        "coordinates for city, state: '{$city}, {$state}, {$country}'",
                        __FILE__,
                        __LINE__
                    );
                }
            }
            else
            {
                $this->notice(
                    "Map service '{$this->serviceName}' did not successfully query coordinates for ".
                    "city, state: '{$city}, {$state}, {$country}'",
                    __FILE__,
                    __LINE__
                );
            }
        }
        else
        {
            $this->notice(
                "Map service was not able to provide city coordinates because a city, stateId, or ".
                "countryId was not provided.",
                __FILE__,
                __LINE__
            );
        }

        return array();
    }

    public function getCityGeofence($city, $locationStateId, $locationCountryId = 223, $radius = 25)
    {
        # @return array

        # @description
        # <h2>Retrieving a City Geofence</h2>
        # <p>
        #   Retrieves a geofence for the provided <var>$city</var>, <var>$locationStateId</var>,
        #   and <var>$locationCountryId</var>.
        #   If no <var>$radius</var> is provided, the default radius of 25 miles is used.
        # </p>
        # <p>
        #   Data returned:
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td class='code'>north</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>south</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>east</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>west</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>latitude</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>longitude</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>radius</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>proximity</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        $coordinates = $this->getCityCoordinates(
            $city,
            $locationStateId,
            $locationCountryId
        );

        return $this->getGeofence(
            $coordinates['latitude'],
            $coordinates['longitude'],
            $radius
        );
    }

    public function createCityCoordinates($city, $locationStateId, $locationCountryId, $latitude, $longitude)
    {
        # @return integer

        # @description
        # <h2>Storing City Coordinates</h2>
        # <p>
        #   City coordinates are cached and stored in the <var>hLocationCities</var>
        #   database table.
        # </p>
        # @end

        return $this->hLocationCity->insert(
            array(
                'hLocationCity' => $city,
                'hLocationStateId' => (int) $locationStateId,
                'hLocationCountryId' => (int) $locationCountryId,
                'hLocationCityLatitude'  => (float) $latitude,
                'hLocationCityLongitude' => (float) $longitude
            )
        );
    }

    public function setCityCoordinates($locationCityId, $latitude, $longitude)
    {
        # @return void

        # @description
        # <h2>Setting City Coordinates</h2>
        # <p>
        #   Updates latitude and longitude for the specified <var>$locationCityId</var>
        #   in the <var>hLocationCities</var> database table.
        # </p>
        # @end

        $this->hLocationCity->setLatitudeLongitude(
            (int) $locationCityId,
            (float) $latitude,
            (float) $longitude
        );
    }

    public function getGeofenceByLocation(array $options, $radius = 25)
    {
        # @return array

        # @description
        # <h2>Retrieving a Geofence Based on Location</h2>
        # <p>
        #   Retrieves a geofence based on the location data passed in the <var>$options</var>
        #   argument. If the <var>$options</var> include a <var>zipCode</var>, a geofence is returned for the
        #   zip code. If the no <var>zipCode</var> is provided, but a <var>city</var> and <var>$stateId</var>
        #   are provided, a geofence is returned for the <var>$city</var> and <var>$stateId</var> instead.
        #   If no <var>zipCode</var>, <var>city</var>, or <var>stateId</var> are provided and a
        #   <var>byCoordinates</var> option is provided, a geofence is returned for the provided
        #   <var>latitude</var> and <var>longitude</var> coordinates.
        # </p>
        # @end

        if (!empty($options['zipCode']))
        {
            return $this->getZipCodeGeofence(
                $options['zipCode'],
                $radius
            );
        }
        else if (!empty($options['city']) && !empty($options['stateId']))
        {
            return $this->getCityGeofence(
                $options['city'],
                $options['stateId'],
                isset($options['countryId']) ? $options['countryId'] : 223,
                $radius
            );
        }
        else if (!empty($options['byCoordinates']))
        {
            return $this->getGeofence(
                $options['latitude'],
                $options['longitude'],
                $radius
            );
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
        return (
            isset($results['north']) &&
            isset($results['south']) &&
            isset($results['east']) &&
            isset($results['west'])
        );
    }
}

?>