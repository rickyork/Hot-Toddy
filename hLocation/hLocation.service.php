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
# <h1>Location Services API</h1>
# <p>
#    The location services API provides URL-accessible methods for retrieving
#    location-related data.
# </p>
# @end

class hLocationService extends hService {

    private $hLocation;

    public function hConstructor()
    {
        $this->hLocation = $this->library('hLocation');
    }

    public function getState()
    {
        if (!isset($_GET['locationStateId']))
        {
            $this->JSON(-5);
            return;
        }

        $locationStateId = (int) $_GET['locationStateId'];

        $this->JSON(
            array(
                'stateName' => $this->hLocation->getStateName(
                    $locationStateId,
                    'hLocationStateName'
                ),
                'stateCode' => $this->hLocation->getStateName(
                    $locationStateId,
                    'hLocationStateCode'
                ),
                'stateLabel' => $this->hLocation->getStateLabel(
                    $this->hLocation->getStateCountry($locationStateId)
                )
            )
        );
    }

    public function getStates()
    {
        # @return JSON

        # @description
        # <h2>Getting States</h2>
        # <p>
        #    Returns a country's states, provinces, et al, as may be the case, in
        #    addition to a country's term for its sub region masses, the country's
        #    two and three letter abbreviations, and the complete list of subregions.
        #    In the U.S.A. the subregions are called states, in Canada the subregions
        #    are called provinces, and so on.
        #    This functionality is used in conjunction with the <i>country</i> select
        #    box on a contact or registration form, so that as a user changes the
        #    selected country the list of subregions and the label used for
        #    subregions are also changed.
        # </p>
        # <p>
        #    The <var>GET</var> argument <var>hLocationCountryId</var> is the only
        #    required input data.
        # </p>
        # @end

        $data = array();

        if (!empty($_GET['countryId']) && is_numeric($_GET['countryId']))
        {
            $query = $this->hLocationCountries->selectAssociative(
                array(
                    'hLocationCountryISO2',
                    'hLocationCountryISO3',
                    'hLocationStateLabel'
                ),
                (int) $_GET['countryId']
            );

            $data = array(
                'iso2' => $query['hLocationCountryISO2'],
                'iso3' => $query['hLocationCountryISO3'],
                'stateLabel' => $query['hLocationStateLabel'],
                'states' => array()
            );

            $states = $this->hLocationStates->select(
                array(
                    'hLocationStateId',
                    'hLocationStateName'
                ),
                array(
                    'hLocationCountryId' => (int) $_GET['countryId']
                )
            );

            foreach ($states as $state)
            {
                array_push(
                    $data['states'],
                    array(
                        $state['hLocationStateId'],
                        $state['hLocationStateName']
                    )
                );
            }
        }

        $this->JSON($data);
    }

    public function getZipCodeCounties()
    {
        # @return JSON

        # @description
        # <h2>Getting a Zip Code's Counties</h2>
        # <p>
        #    Returns all of the U.S. counties associated with a U.S. zip code.
        #    The <var>GET</var> argument <var>hLocationZipCode</var> is the only required
        #    input argument.
        # </p>
        # @end

        if (!isset($_GET['hLocationZipCode']))
        {
            $this->JSON(-5);
            return;
        }

        $this->JSON(
            $this->hLocation->getZipCodeCounties(
                (int) $_GET['hLocationZipCode']
            )
        );
    }

    public function getStateCounties()
    {
        # @return JSON

        # @description
        # <h2>Getting a State's Counties</h2>
        # <p>
        #    Returns all counties for the specified state.  The <var>GET</var> argument
        #    <var>hLocationStateId</var> is the only required input argument.
        # </p>
        # @end

        if (!isset($_GET['hLocationStateId']))
        {
            $this->JSON(-5);
            return;
        }

        $this->JSON(
            $this->hLocation->getStateCounties(
                (int) $_GET['hLocationStateId']
            )
        );
    }

    public function getLocationByPostalCode()
    {

    }
}

?>