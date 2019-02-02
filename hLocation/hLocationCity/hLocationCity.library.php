<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Location City Library
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
# <h1>Location API</h1>
# <p>
#    The location API provides methods for performing simple location conversion tasks.
#
# </p>
# @end

class hLocationCityLibrary extends hPlugin {

    public function hConstructor()
    {
        $this->uses('hLocationCities');
    }

    public function getId($city, $stateId, $countryId = 223)
    {
        # @return integer

        # @description
        # <h2>Retreiving a City Id</h2>
        # <p>
        #   Retrieves the <var>hLocationCityId</var> for the provided <var>$city</var>,
        #   <var>$stateId</var>, and <var>$countryId</var>.
        # </p>

        return $this->hLocationCities->selectColumn(
            'hLocationCityId',
            array(
                'hLocationCity' => $city,
                'hLocationStateId' => $stateId,
                'hLocationCountryId' => $countryId
            )
        );
    }

    public function exists($city, $stateId, $countryId = 223)
    {
        # @return boolean

        # @description
        # <h2>Checking for City Existence</h2>
        # <p>
        #   Determines whether or not a city exists in the <var>hLocationCities</var> database
        #   table based on the provided <var>$city</var>, <var>$stateId</var>, and <var>$countryId</var>.
        # </p>
        # @end

        return $this->hLocationCities->selectExists(
            'hLocationCityId',
            array(
                'hLocationCity' => $city,
                'hLocationStateId' => $stateId,
                'hLocationCountryId' => $countryId
            )
        );
    }

    public function getLatitudeLongitude($locationCityId)
    {
        # @return array

        # @description
        # <h2>Retrieving Latitude and Longitude Data for a City</h2>
        # <p>
        #   Retrieves latitude and longitude data for the specified <var>$locationCityId</var>.
        # </p>
        # @end

        return $this->hLocationCities->selectAssociative(
            array(
                'hLocationCityLatitude',
                'hLocationCityLongitude'
            ),
            $locationCityId
        );
    }

    public function setLatitudeLongitude($locationCityId, $latitude, $longitude)
    {
        # @return void

        # @description
        # <h2>Setting Latitude and Longitude Data for a City</h2>
        # <p>
        #   Sets latitude and longitude for the <var>$locationCityId</var>.
        # </p>
        # @end

        $this->hLocationCities->modify();

        $this->hLocationCities->update(
            array(
                'hLocationCityLatitude' => (float) $latitude,
                'hLocationCityLongitude' => (float) $longitude
            ),
            (int) $locationCityId
        );
    }

    public function save($columns)
    {
        # @return integer | false

        # @description
        # <h2>Saving a City</h2>
        # <p>
        #   Performs an insert or update operation for the provided city data based on
        #   whether or not the <var>hLocationCityId</var> value is present and not empty.
        #   If the value is empty, an insert is performed. If the value is not empty, an
        #   update is performed.
        # </p>
        # @end

        if (!isset($columns['hLocationCityId']) || empty($columns['hContactCity']) || empty($columns['hLocationStateId']))
        {
            return false;
        }

        if (!empty($columns['hLocationCityId']))
        {
            return $this->update($columns);
        }
        else
        {
            return $this->insert($columns);
        }
    }

    public function insert($columns)
    {
        # @return integer

        # @description
        # <h2>Inserting a City Record</h2>
        # <p>
        #   Creates a record in <var>hLocationCities</var> based on the provided <var>$columns</var>.
        # </p>
        # @end

        if (empty($columns['hLocationCity']) || empty($columns['hLocationStateId']))
        {
            return false;
        }

        $this->hLocationCities->modify();

        return $this->hLocationCities->insert(
            array(
                'hLocationCityId' => 0,
                'hLocationCity' => $columns['hLocationCity'],
                'hLocationCountyId' => isset($columns['hLocationCountyId'])? $columns['hLocationCountyId'] : 0,
                'hLocationStateId' => $columns['hLocationStateId'],
                'hLocationCountryId' => empty($columns['hLocationCountryId'])? 223 : (int) $columns['hLocationCountryId'],
                'hLocationCityLatitude' => !empty($columns['hLocationCityLatitude'])? (float) $columns['hLocationCityLatitude'] : 0,
                'hLocationCityLongitude' => !empty($columns['hLocationCityLongitude'])? (float) $columns['hLocationCityLongitude'] : 0,
                'hLocationCityCreated' => time(),
                'hLocationCityLastModifiedBy' => $this->isLoggedIn()? (int) $_SESSION['hUserId'] : 0
            )
        );
    }

    public function update($columns)
    {
        # @return integer

        # @description
        # <h2>Updating a City Record</h2>
        # <p>
        #   Updates a record in <var>hLocationCities</var> based on the provided <var>$columns</var>.
        # </p>
        # @end

        $this->hLocationCities->modify();

        return $this->hLocationCities->update(
            array(
                'hLocationCity' => $columns['hLocationCity'],
                'hLocationCountyId' => isset($columns['hLocationCountyId'])? $columns['hLocationCountyId'] : 0,
                'hLocationStateId' => $columns['hLocationStateId'],
                'hLocationCountryId' => empty($columns['hLocationCountryId'])? 223 : (int) $columns['hLocationCountryId'],
                'hLocationCityLatitude' => !empty($columns['hLocationCityLatitude'])? (float) $columns['hLocationCityLatitude'] : 0,
                'hLocationCityLongitude' => !empty($columns['hLocationCityLongitude'])? (float) $columns['hLocationCityLongitude'] : 0,
                'hLocationCityLastModified' => time(),
                'hLocationCityLastModifiedBy' => $this->isLoggedIn()? (int) $_SESSION['hUserId'] : 0
            ),
            array(
                'hLocationCityId' => (int) $columns['hLocationCityId']
            )
        );
    }
}

?>