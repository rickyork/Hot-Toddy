<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Location Library
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
#    The location API provides methods for performing simply location conversion tasks.
#
# </p>
# @end

class hLocationLibrary extends hPlugin {

    public function getCountries($prependValue = false, $prependOption = null)
    {
        # @return array

        # @description
        # <h2>Retrieving a List of All Countries</h2>
        # <p>
        #    Returns a list of all countries suitable for use in
        #    <a href='/Hot Toddy/Documentation?hForm/hForm.library.php'>hForm</a>'s
        #    <var>&lt;select&gt;</var> API.
        # </p>
        # @end

        if ($prependValue)
        {
            $this->hDatabase->setPrependResult($prependOption);
        }

        return $this->hLocationCountries->selectColumnsAsKeyValue(
            array(
                'hLocationCountryId',
                'hLocationCountryName'
            ),
            array(),
            'ASC',
            'hLocationCountryName'
        );
    }

    public function getCountriesForTemplate($locationCountryId = 0)
    {
        # @return array

        # @description
        # <h2>Retrieving a List of Countries for a Template</h2>
        # <p>
        #    Returns a list of all countries suitable for use in
        #    a Hot Toddy template.  Pass <var>$locationCountryId</var>
        #    to select a country.
        # </p>
        # @end

        $query = $this->hLocationCountries->select(
            array(
                'hLocationCountryId',
                'hLocationCountryName',
                'hLocationCountryISO2',
            ),
            array(),
            'AND',
            'hLocationCountryName'
        );

        $results = array();

        foreach ($query as $data)
        {
            $results['hLocationCountryId'][]         = $data['hLocationCountryId'];
            $results['hLocationCountryName'][]       = $data['hLocationCountryName'];
            $results['hLocationCountryISO2'][]       = $data['hLocationCountryISO2'];
            $results['hLocationCountryIsSelected'][] = ($data['hLocationCountryId'] == $locationCountryId);
        }

        return $results;
    }

    public function getStates($locationCountryId, $default = true)
    {
        # @return array

        # @description
        # <h2>Retrieving a List of States</h2>
        # <p>
        #    Returns a list of all states (provinces, counties, etc, depending on the country)
        #    suitable for use in <a href='/Hot Toddy/Documentation?hForm/hForm.library.php'>hForm</a>'s
        #    <var>&lt;select&gt;</var> API.
        # </p>
        # <p>
        #    <var>$locationCountryId</var> can be an integer or an array of countries.
        # </p>
        # @end

        if ($default)
        {
            $this->hDatabase->setPrependResult(
                'Please select a '.$this->getStateLabel($locationCountryId)
            );
        }

        return $this->hLocationStates->selectColumnsAsKeyValue(
            array(
                'hLocationStateId',
                'hLocationStateName'
            ),
            $this->getStateWhere($locationCountryId),
            'OR',
            'hLocationStateName'
        );
    }

    public function getStatesForTemplate($locationCountryId, $locationStateId = 0)
    {
        # @return array

        # @description
        # <h2>Retrieving a List States for a Template</h2>
        # <p>
        #    Returns a list of all states (provinces, counties, etc, depending on the country)
        #    suitable for use in a Hot Toddy template.  Pass <var>$locationCountryId</var> to
        #    choose a country to get states, provinces, et al for.  Pass <var>$locationStateId</var>
        #    to select a state in the returned data.
        # </p>
        # <p>
        #    <var>$locationCountryId</var> can be an integer or an array of countries.
        # </p>
        # @end

        if (!empty($locationCountryId))
        {
            $query = $this->hLocationStates->select(
                array(
                    'hLocationStateId',
                    'hLocationStateCode',
                    'hLocationStateName'
                ),
                $this->getStateWhere($locationCountryId),
                'OR',
                'hLocationStateName'
            );

            $results = array();

            foreach ($query as $data)
            {
                $results['hLocationStateId'][] = $data['hLocationStateId'];
                $results['hLocationStateCode'][] = $data['hLocationStateCode'];
                $results['hLocationStateName'][] = $data['hLocationStateName'];
                $results['hLocationStateIsSelected'][] = ($data['hLocationStateId'] == $locationStateId);
            }

            return $results;
        }
        else
        {
            return '';
        }
    }

    private function getStateWhere($locationCountryId)
    {
        # @return array

        # @description
        # <h2>A Reusable Method of Selecting States</h2>
        # <p>
        #    This method creates the SQL <var>WHERE</var> clause for selecting states
        #    based on the <var>$locationCountryId</var> argument provided.  <var>$locationCountryId</var>
        #    can be provided as an integer (a single country), or an array (multiple countries).  This
        #    is useful to create a "state" selection field that includes both U.S. states and Canadian
        #    provinces, for example.
        # </p>
        # @end

        if (strstr($locationCountryId, ','))
        {
            $n = 0;

            $countries = explode(',', $locationCountryId);

            $where = array();

            foreach ($countries as $country)
            {
                $where[] = array('=', (int) $country);
                $n++;
            }

            $where['hLocationCountryId'] = $where;
        }
        else
        {
            $where['hLocationCountryId'] = (int) $locationCountryId;
        }

        return $where;
    }

    public function getAddressTemplateByCountry($locationCountryId)
    {
        # @return string

        # @description
        # <h2>Getting a Country's Address Template</h2>
        # <p>
        #    Returns an address template for the provided <var>$locationCountryId</var>, if
        #    one exists.  The address template can then be used to properly format addresses
        #    for that country.
        # </p>
        # @end

        return $this->hContactAddressTemplates->selectColumn(
            'hContactAddressTemplate',
            array(
                'hContactAddressTemplateId' => $this->hLocationCountries->selectColumn(
                    'hContactAddressTemplateId',
                    (int) $locationCountryId
                )
            )
        );
    }

    public function getStateLabel($locationCountryId)
    {
        # @return string

        # @description
        # <h2>Getting the "State" Label</h2>
        # <p>
        #    Returns the correct label to use for a country's subregions.  In the U.S.A., we call those
        #    "states".  In Canada it is "provinces".  This function provides the correct label to use,
        #    provided one has been entered for that country.
        # </p>
        # @end

        $label = $this->getLocationField(
            'hLocationCountries',
            'hLocationStateLabel',
            'hLocationCountryId',
            $locationCountryId
        );

        return empty($label)? 'region' : $label;
    }

    public function getStateName($locationStateId = 0, $field = 'hLocationStateCode', $locationCountryId = 223)
    {
        # @return string

        # @description
        # <h2>Getting a State's Name</h2>
        # <p>
        #    Returns the full name or two-letter abbreviation for a state.  The two-letter abbreviation is
        #    returned, by default (<var>hLocationStateCode</var>).
        # </p>
        # @end

        if (!is_numeric($locationStateId))
        {
            $where = array();

            $where[($field == 'hLocationStateCode'? 'hLocationStateCode' : 'hLocationStateName')] = $locationStateId;
            $where['hLocationCountryId'] = (int) $locationCountryId;

            return $this->hLocationStates->selectColumn(
                ($field == 'hLocationStateCode'? 'hLocationStateName' : 'hLocationStateCode'),
                $where
            );
        }

        return $this->getLocationField(
            'hLocationStates',
            $field,
            'hLocationStateId',
            $locationStateId
        );
    }

    public function getStateId($locationCountryId, $locationState)
    {
        # @return integer

        # @description
        # <h2>Getting a State's Numeric Id</h2>
        # <p>
        #    Returns a state's numeric id for use within Hot Toddy wherever an <var>hLocationStateId</var>
        #    is called for.  To get a numeric id, you must provided the country <var>$locationCountryId</var>
        #    and the state's two-letter abbreviation in <var>$locationState</var>.
        # </p>
        # @end

        return $this->hLocationStates->selectColumn(
            'hLocationStateId',
            array(
                'hLocationCountryId' => (int) $locationCountryId,
                'hLocationStateCode' => $locationState
            )
        );
    }

    public function getStateByName($locationCountryId, $locationState)
    {
        return $this->hLocationStates->selectColumn(
            'hLocationStateId',
            array(
                'hLocationCountryId' => (int) $locationCountryId,
                'hLocationStateName' => $locationState
            )
        );
    }

    public function getStateCountry($locationStateId)
    {
        # @return integer

        # @description
        # <h2>Getting a State's Country</h2>
        # <p>
        #    Returns an <var>hLocationCountryId</var> for the provided <var>hLocationStateId</var>.
        # </p>
        # @end
        return $this->getLocationField(
            'hLocationStates',
            'hLocationCountryId',
            'hLocationStateId',
            $locationStateId
        );
    }

    public function getCountryName($locationCountryId = 0, $field = 'hLocationCountryName')
    {
        # @return string | mixed

        # @description
        # <h2>Getting a Country's Name</h2>
        # <p>
        #    Returns an <var>hLocationCountryName</var> for the provided <var>hLocationCountryId</var>.
        # </p>
        # <p>
        #    The returned field from <var>hLocationCountries</var> can be customized in <var>$field</var>.
        #    Some options are: <var>hLocationCountryISO2</var>, <var>hLocationCountryISO3</var>, et al.
        # </p>
        # @end

        return $this->getLocationField(
            'hLocationCountries',
            $field,
            'hLocationCountryId',
            $locationCountryId
        );
    }

    public function getCountyName($locationCountyId)
    {
        # @return string

        # @description
        # <h2>Getting a County's Name</h2>
        # <p>
        #    Returns an <var>hLocationCounty</var> for the provided <var>hLocationCountyId</var>.
        # </p>
        # @end

        return $this->getLocationField(
            'hLocationCounties',
            'hLocationCounty',
            'hLocationCountyId',
            $locationCountyId
        );
    }

    public function getCountryCodeFromName($locationCountryName, $iso = '2')
    {
        # @return string

        # @description
        # <h2>Getting a Country's ISO Code</h2>
        # <p>
        #    Returns a country's ISO code <var>1</var> or <var>2</var> (<var>2</var> is returned by default)
        #    for the provided <var>hLocationCountryName</var>.
        # </p>
        # @end

        return $this->getLocationField(
            'hLocationCountries',
            'hLocationCountryISO'.$iso,
            'hLocationCountryName',
            $locationCountryName
        );
    }

    public function getCountyState($locationCountyId)
    {
        # @return string

        # @description
        # <h2>Getting a County's State</h2>
        # <p>
        #    Returns an <var>hLocationStateId</var> for the provided <var>hLocationCountyId</var>.
        # </p>
        # @end

        return $this->getLocationField(
            'hLocationCounties',
            'hLocationStateId',
            'hLocationCountyId',
            $locationCountyId
        );
    }

    public function getCountryId($locationCountry, $field = 'ISO2')
    {
        # @return integer

        # @description
        # <h2>Getting a Country's Id From Its ISO2 Code</h2>
        # <p>
        #    Returns a country's numeric id from its ISO2 code (two-letter abbreviation).
        # </p>
        # @end

        $where = array();

        switch (strtolower($countryField))
        {
            case 'name':
            {
                $field = 'hLocationCountryName';
                break;
            }
            case 'iso3':
            {
                $field = 'hLocationCountryISO3';
                break;
            }
            case 'iso2':
            default:
            {
                $field = 'hLocationCountryISO2';
            }
        }

        return $this->hLocationCountries->selectColumn(
            'hLocationCountryId',
            array(
                'hLocationCountryISO2' => $locationCountryISO2
            )
        );
    }

    public function getZipCodeState($locationZipCode)
    {
        # @return integer

        # @description
        # <h2>Getting a Zip Code's State</h2>
        # <p>
        #    Returns an <var>hLocationStateId</var> for the provided <var>hLocationZipCode</var>.
        # </p>
        # @end

        return $this->hLocationStates->selectColumn(
            'hLocationStateId',
            array(
                'hLocationStateCode' => $this->getLocationField(
                    'hLocationZipCodes',
                    'hLocationStateCode',
                    'hLocationZipCode',
                    $locationZipCode
                ),
                'hLocationCountryId' => 223
            )
        );
    }

    public function getZipCodeCity($locationZipCode)
    {
        # @return string

        # @description
        # <h2>Getting a Zip Code's State</h2>
        # <p>
        #    Returns the city associated with the provided zip code.
        # </p>
        # @end

        return $this->getLocationField(
            'hLocationZipCodes',
            'hLocationCity',
            'hLocationZipCode',
            $locationZipCode
        );
    }

    public function getZipCodeCounty($locationZipCode)
    {
        # @return string

        # @description
        # <h2>Getting a Zip Code's County</h2>
        # <p>
        #    Returns the county associated with the provided zip code.
        # </p>
        # @end

        return $this->hLocationCounties->selectColumn(
            'hLocationCountyId',
            array(
                'hLocationCounty' => $this->getLocationField(
                    'hLocationZipCodes',
                    'hLocationCounty',
                    'hLocationZipCode',
                    $locationZipCode
                ),
                'hLocationStateId' => $this->getZipCodeState($locationZipCode)
            )
        );
    }

    public function getZipCodeCounties($locationZipCode)
    {
        # @return array

        # @description
        # <h2>Getting a Zip Code's Counties</h2>
        # <p>
        #    Returns all counties associated with the provided zip code.
        # </p>
        # @end

        return $this->hLocationZipCodes->select(
            'hLocationCounty',
            array(
                 'hLocationZipCode' => $locationZipCode
            )
        );
    }

    public function getStateCounties($locationStateId)
    {
        # @return array

        # @description
        # <h2>Getting a State's Counties or Parishes</h2>
        # <p>
        #    Returns all counties associated with the provided <var>hLocationStateId</var>.
        # </p>
        # @end

        return $this->hLocationCounties->select(
            array(
                'hLocationCountyId',
                'hLocationCounty'
            ),
            array(
                'hLocationStateId' => $locationStateId
            ),
            'AND',
           'hLocationCounty'
        );
    }

    public function getLocationField($table, $column, $primaryKey, $primaryKeyValue)
    {
        # @return mixed

        # @description
        # <h2>Getting a Single Location Column's Value</h2>
        # <p>
        #    Returns a single column from a single row in the given database table by
        #    doing a lookup by primary key.
        # </p>
        # @end

        return $this->hDatabase->selectColumn(
            $column,
            $table,
            array(
                $primaryKey => $primaryKeyValue
            )
        );
    }

    public function hasStates($locationCountryId)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Country Has States, Provinces, et al</h2>
        # <p>
        #    Determines if a country has states, provinces, or other subregions.
        # </p>
        # @end

        return $this->hLocationStates->selectExists(
            'hLocationStateId',
            array(
                'hLocationCountryId' => (int) $locationCountryId
            )
        );
    }
}

?>