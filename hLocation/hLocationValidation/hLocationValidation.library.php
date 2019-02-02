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
# <h1>Location Validation API</h1>
# <p>
#     This API provides methods for validating location data.
# </p>
# @end

class hLocationValidationLibrary extends hPlugin {

    private $countryId  = null;
    private $stateId    = null;
    private $postalCode = null;
    private $hLocation;

    public function hConstructor()
    {
        $this->hLocation = $this->library('hLocation');
    }

    public function isPostalCode($postalCode = null)
    {
        # @return boolean

        # @description
        # <h2>Determining a Postal Code is Correct</h2>
        # <p>
        #    Analyzes a postal code for correct formatting.  Postal code validation is built-in and
        #    available for the following countries:
        # </p>
        # <ul>
        #    <li>Brunei</li>
        #    <li>Bulgaria</li>
        #    <li>Canada</li>
        #    <li>Cape Verde</li>
        #    <li>China</li>
        #    <li>Czech Republic</li>
        #    <li>Denmark</li>
        #    <li>Finland</li>
        #    <li>France</li>
        #    <li>Germany</li>
        #    <li>Greece</li>
        #    <li>Hungary</li>
        #    <li>India</li>
        #    <li>Indonesia</li>
        #    <li>Israel</li>
        #    <li>Italy</li>
        #    <li>Japan</li>
        #    <li>Liechtenstein</li>
        #    <li>Malaysia</li>
        #    <li>Mexico</li>
        #    <li>Moldova</li>
        #    <li>Morocco</li>
        #    <li>Netherlands</li>
        #    <li>New Zealand</li>
        #    <li>Norway</li>
        #    <li>Philippines</li>
        #    <li>Switzerland</li>
        #    <li>United Kingdom</li>
        #    <li>United States</li>
        # </ul>
        # @end

        if (empty($postalCode))
        {
            if (!empty($this->postalCode))
            {
                $postalCode = $this->postalCode;
            }
            else
            {
                $this->warning(
                    "No postal code provided for validation.",
                    __FILE__,
                    __LINE__
                );

                return false;
            }
        }

        $pattern = $this->getPostalCodePattern();
        return empty($pattern)? true : preg_match($pattern, $postalCode);
    }

    public function getPostalCodePattern()
    {
        switch ($this->countryId)
        {
            case 223: // US
            {
                return '/^([0-9]{1,5})(\-{1}[0-9]{1,4})?$/';
            }
            case 222: // UK
            {
                return '/^[A-Z]{1,2}[0-9|A-Z]?[0-9|A-Z]? [0-9][A-Z]{2}$/i';
            }
            // Two letters, followed by four digits
            case 32: // Brunei
            {
                return '/^[A-Z]{2}[0-9]{4}$/i';
            }
            case 38: // Canada
            {
                return '/^[a-z]\d[a-z] ?\d[a-z]\d$/i';
            }
            // 3 or 4 digits
            case 57: // Denmark
            {
                return '/^[0-9]{3,4}$/';
            }
            // Five digits with an optional space after three.
            case 56: // Czech Republic
            {
                return '/^[0-9]{3} ?[0-9]{2}$/';
            }
            case 150: // Netherlands
            {
                return '/^[0-9]{4} ?[A-Z]{2}$/';
            }
            case 107: // Japan
            {
                return '/^[0-9]{3}\-?[0-9]{4}$/';
            }
            // 6 digit
            case 44: // China
            case 99: // India
            {
                return '/^[0-9]{6}$/';
            }
            // 5 digit postal codes
            case 72:  // Finland
            case 73:  // France
            case 81:  // Germany
            case 84:  // Greece
            case 100: // Indonesia
            case 104: // Israel
            case 105: // Italy
            case 129: // Malaysia
            case 138: // Mexico
            case 144: // Morocco
            {
                return '/^[0-9]{5}$/';
            }
            // 4 Digit postal codes
            case 39:  // Cape Verde
            case 33:  // Bulgaria
            case 97:  // Hungary
            case 153: // New Zealand
            case 160: // Norway
            case 168: // Philippines
            case 204: // Switzerland
            case 122: // Liechtenstein
            {
                return '/^[0-9]{4}$/';
            }
            case 140: // Moldova
            {
                return '/^MD\-[0-9]{4}$/i';
            }
            default:
            {
                return '';
            }
        }
    }

    public function &setPostalCode(&$postalCode)
    {
        # @return hLocationValidationLibrary

        # @description
        # <h2>Setting the Postal Code for Validation</h2>
        # <p>
        #    Sets the postal code to be used for postal code validation.
        # </p>
        # @end


        if (isset($postalCode))
        {
            $this->postalCode = &$postalCode;
        }

        return $this;
    }

    public function isCountryId($locationCountryId)
    {
        # @return boolean

        # @description
        # <h2>Determining a Country Id is Correct</h2>
        # <p>
        #    Looks at the user-submitted countryId and ensures it is a valid countryId.
        # </p>
        # <p>
        #    If the country field is not required, the value provided will be empty,
        #    and this method will report no input is a valid countryId, since it is
        #    possible the field might not be required.
        # </p>
        # @end

        if (empty($locationCountryId))
        {
            if (!empty($this->countryId))
            {
                $locationCountryId = $this->countryId;
            }
            else
            {
                return true;
            }
        }

        return $this->hLocationCountries->selectExists(
            'hLocationCountryId',
            (int) $locationCountryId
        );
    }

    public function isStateId($locationStateId)
    {
        # @return boolean

        # @description
        # <h2>Determining a State Id is Correct</h2>
        # <p>
        #    Looks at the user-submitted stateId and ensures the value provided is a valid stateId.
        # </p>
        # <p>
        #    If the state field is not required, the value provided will be empty,
        #    and this method will report no input is a valid stateId, since it is
        #    possible the field might not be required.
        # </p>
        # @end

        if (empty($locationStateId))
        {
            if (!empty($this->stateId))
            {
                $locationStateId = $this->stateId;
            }
            else
            {
                return true;
            }
        }

        if ($this->hLocation->hasStates((int) $this->countryId))
        {
            return $this->hLocationStates->selectExists(
                'hLocationStateId',
                (int) $locationStateId
            );
        }
        else
        {
            return empty($locationStateId);
        }
    }

    public function &setCountryId(&$countryId)
    {
        # @return hLocationValidationLibrary

        # @description
        # <h2>Setting a Country Id</h2>
        # <p>
        #    Sets the countryId to use with country, state, and postal code validation.
        #    If you are doing state or postal code validation, the countryId is required
        #    for successful validatdion.
        # </p>
        # @end

        if (isset($countryId))
        {
            $this->countryId = &$countryId;
        }

        return $this;
    }

    public function &setStateId(&$stateId)
    {
        # @return hLocationValidationLibrary

        # @description
        # <h2>Setting a State Id</h2>
        # <p>
        #    Sets the stateId to use for state validation.
        # </p>
        # @end

        if (isset($stateId))
        {
            $this->stateId = &$stateId;
        }

        return $this;
    }
}

?>