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

class hContactValidationLibrary extends hPlugin {

    private $countryId;
    private $phoneNumberTemplate;

    private $tollFreeAreaCodes = array(
        800,
        822,
        833,
        844,
        855,
        866,
        877,
        880,
        881,
        882,
        888
    );

    public function isPhoneNumber($phoneNumber)
    {
        # @return boolean

        # @description
        # <h2>Validating a Phone Number</h2>
        # <p>
        #   Validates the phone number provided by the user.  e.g., it ensures
        #   that the phone number is entered in a specific format,  <var>(XXX) XXX-XXXX</var>.
        # </p>
        # <p>
        #   At present phone number validation is only triggered if the user
        #   specifies a "+1" country telephone prefix country.
        # </p>
        # <p>
        #   "+1" country telephone prefix countries:
        # </p>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>Country</th>
        #           <th>hLocationCountryId</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td>United States</td>
        #           <td>223</td>
        #       </tr>
        #       <tr>
        #           <td>Canada</td>
        #           <td>38</td>
        #       </tr>
        #       <tr>
        #           <td>Anguilla, +1 (264)</td>
        #           <td>7</td>
        #       </tr>
        #       <tr>
        #           <td>Antigua and Barbuda, +1 (268)</td>
        #           <td>9</td>
        #       </tr>
        #       <tr>
        #           <td>Bahamas, +1 (242)</td>
        #           <td>16</td>
        #       </tr>
        #       <tr>
        #           <td>Barbados, +1 (246)</td>
        #           <td>19</td>
        #       </tr>
        #       <tr>
        #           <td>Bermuda +1 (441)</td>
        #           <td>24</td>
        #       </tr>
        #       <tr>
        #           <td>British Virgin Islands, +1 (284)</td>
        #           <td>231</td>
        #       </tr>
        #       <tr>
        #           <td>Cayman Islands, +1 (345)</td>
        #           <td>40</td>
        #       </tr>
        #       <tr>
        #           <td>Dominica, +1 (767)</td>
        #           <td>59</td>
        #       </tr>
        #       <tr>
        #           <td>Dominican Republic, +1 (809), +1 (829)</td>
        #           <td>60</td>
        #       </tr>
        #       <tr>
        #           <td>Grenada/Carricou, +1 (473)</td>
        #           <td>86</td>
        #       </tr>
        #       <tr>
        #           <td>Jamaica, +1 (876)</td>
        #           <td>106</td>
        #       </tr>
        #       <tr>
        #           <td>Montserrat, +1 (664)</td>
        #           <td>143</td>
        #       </tr>
        #       <tr>
        #           <td>Puerto Rico, +1 (787), +1 (939)</td>
        #           <td>172</td>
        #       </tr>
        #       <tr>
        #           <td>St Kitts and Nevis, +1 (869)</td>
        #           <td>178</td>
        #       </tr>
        #       <tr>
        #           <td>St Lucia, +1 (758)</td>
        #           <td>179</td>
        #       </tr>
        #       <tr>
        #           <td>St Vincent/Grenadines, +1 (784)</td>
        #           <td>180</td>
        #       </tr>
        #       <tr>
        #           <td>Trinidad and Tobago, +1 (868)</td>
        #           <td>213</td>
        #       </tr>
        #       <tr>
        #           <td>Turks and Caicos, +1 (649)</td>
        #           <td>217</td>
        #       </tr>
        #       <tr>
        #           <td>U.S. Virgin Islands, +1 (340)</td>
        #           <td>232</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        if (!empty($phoneNumber))
        {
            $pattern = $this->getPhoneValidationPattern();

            if (!empty($pattern))
            {
                return preg_match($pattern, $phoneNumber);
            }
        }

        return true;
    }

    public function getPhoneValidationPattern($pattern = true)
    {
        # @return string | false
        # <p>
        #   Returns a regular expression pattern (preg-compatible) for matching a phone number
        #   within a string.  If <var>$pattern</var> is false, the default template for that
        #   phone number is returned.  The pattern or template returned is based on the
        #   the country set in <a href='#setCountryId'>setCountryId()</a>.
        # </p>
        # @end

        # @description
        # <h2>Getting a Validation Regular Expression or Template for a Phone Number</h2>
        # <p>
        #   Returns a regular expression to use to validate a phone number
        #   based on the country provided by the user.
        # </p>
        # <p>
        #   The country must be set via a call to <a href='#setCountryId' class='code'>setCountryId()</a>,
        #   which expects an <var>hLocationCountryId</var>.
        # </p>
        # @end

        if (!empty($this->countryId))
        {
            switch ($this->countryId)
            {
                # Country Prefix +1
                case 223:   # US
                case 38:    # Canada
                case 7:     # Anguilla (264)
                case 9:     # Antigua and Barbuda (268)
                case 16:    # Bahamas (242)
                case 19:    # Barbados (246)
                case 24:    # Bermuda (441)
                case 231:   # British Virgin Islands (284)
                case 40:    # Cayman Islands (345)
                case 59:    # Dominica (767)
                case 60:    # Dominican Republic (809), (829)
                case 86:    # Grenada/Carricou (473)
                case 106:   # Jamaica (876)
                case 143:   # Montserrat (664)
                case 172:   # Puerto Rico (787), (939)
                case 178:   # St Kitts and Nevis (869)
                case 179:   # St Lucia (758)
                case 180:   # St Vincent/Grenadines (784)
                case 213:   # Trinidad and Tobago (868)
                case 217:   # Turks and Caicos (649)
                case 232:   # U.S. Virgin Islands (340)
                {
                    # +1 (XXX) XXX-XXXX, spaces and  "1" prefix are optional.
                    return $pattern? '/^\+{0,1}1{0,1}\s{0,1}\(\d{3}\)\s{0,1}\d{3}\-{1}\d{4}$/' : '(XXX) XXX-XXXX';
                }
            }
        }

        return false;
    }

    public function parsePhoneNumber($phoneNumber)
    {
        # @return array

        # @description
        # <h2>Parsing a Phone Number</h2>
        # <p>
        #   Extracts all digits from a string and returns those digits in an array.
        #   For example, say you have provided the string '1 (317) 555-1212' in
        #   the <var>$phoneNumber</var> argument.  This method will return the
        #   following:
        # </p>
        # <code>
        #   array(
        #       0 => 1,
        #       2 => 3,
        #       3 => 1,
        #       4 => 7,
        #       5 => 5,
        #       6 => 5,
        #       7 => 5,
        #       8 => 1,
        #       9 => 2,
        #       10 => 1,
        #       11 => 2
        #   )
        # </code>
        # <p>
        #   The phone number extracted as an array of the digits it contains can
        #   now be used to format the phone number to conform to a template, or
        #   other validation purposes.
        # </p>
        # @end

        if (!empty($phoneNumber))
        {
            $matches = array();

            preg_match_all('/\d+/', $phoneNumber, $matches);

            if ($matches && is_array($matches) && isset($matches[0]) && isset($matches[0][0]))
            {
                $numbers = str_split(implode('', $matches[0]));

                foreach ($numbers as &$digit)
                {
                    $digit = (int) $digit;
                }

                return $numbers;
            }
        }

        return array();
    }

    public function isTollFree($phoneNumber = nil, $tollFreeAreaCodes = array())
    {
        # @return boolean

        # @decription
        # <h2>Determining if a Number is Toll-Free</h2>
        # <p>
        #   Analyzes the provided <var>$phoneNumber</var> against either the
        #   provided and optional <var>$tollFreeAreaCodes</var> argument, which
        #   allows you to specify which toll free area code prefixes should
        #   be used as acceptable toll-free prefixed.  If <var>$tollFreeAreaCodes</var>
        #   is not provided, the value of the <var>$this-&gt;tollFreeAreaCodes</var>
        #   property is used instead.  This property presently specifies the
        #   following as toll-free prefixes:
        #   <var>800, 822, 833, 844, 855, 866, 877, 880, 881, 882, 888</var>.
        #   Using the default prefixes, if any of these are discovered to be
        #   used as an area code in the provided phone number, the function wil
        #   return true, and the number will be considered toll-free.
        # </p>

        return (
            $this->hasAreaCode(
                $phoneNumber,
                $tollFreeAreaCodes? $tollFreeAreaCodes : $this->tollFreeAreaCodes
            )
        );
    }

    private function &castNumbers(&$numbers)
    {
        # @return hContactValidationLibrary

        # @description
        # <h2>Casting Numbers</h2>
        # <p>
        #   Casts each number in an array as an integer.
        # </p>
        # @end

        if (isset($numbers) && is_array($numbers) && count($numbers))
        {
            foreach ($numbers as &$digit)
            {
                $digit = (int) $digit;
            }
        }

        return $this;
    }

    public function hasAreaCode($phoneNumber, $areaCodes = array())
    {
        # @return boolean

        # @description
        # <h2>Determining if a Phone Number Has an Area Code</h2>
        # <p>
        #   Analyzes the provided <var>$phoneNumber</var> to see if it
        #   contains the one or more area codes provided in the <var>$areaCodes</var>
        #   argument.
        # </p>
        # @end

        if (!is_array($areaCodes) && !empty($areaCodes))
        {
            $areaCodes = array($areaCodes);
        }

        $this->castNumbers($areaCodes);

        $numbers = $this->parsePhoneNumber($phoneNumber);

        if (count($numbers) == 11 && $numbers[0] == 1)
        {
            array_shift($numbers);
        }

        if (count($numbers) == 10)
        {
            $phoneNumberAreaCode = $numbers[0].$numbers[1].$numbers[3];

            foreach ($areaCodes as $areaCode)
            {
                if ($phoneNumberAreaCode == $areaCode)
                {
                    return true;
                }
            }
        }

        return false;
    }

    public function &setPhoneNumberTemplate($template)
    {
        $this->phoneNumberTemplate = $template;

        return $this;
    }

    public function &formatPhoneNumber(&$phoneNumber, $template = '1 (___) ___-____')
    {
        if ($this->phoneNumberTemplate)
        {
            $template = $this->phoneNumberTemplate;
        }

        # @return hContactValidationLibrary

        # @description
        # <h2>Formatting Phone Numbers</h2>
        # <p>
        #   Forces all phone numbers to appear in the format provided in template.
        #   The default template is <var>1 (___) ___-____</var>.
        # </p>
        # <p>
        #   Once a phone number is provided, and if the country is supported, this method
        #   determines if that phone number is a U.S. number (or a number for one of the
        #   other supported countries) by counting the number of digits present in the number.
        #   For the U.S. and countries with phone numbers similar to the United States (they begin
        #   with country code 1), a 10 or 11 digit number will trigger re-formatting in the
        #   provided template.
        # </p>
        # <p>
        #   Expansion to this method to include rules for formatting phone numbers specific
        #   to other countries is welcome either in the form of a patch or detailed
        #   message explaining how to implement support for formatting that country's phone
        #   numbers.
        # </p>
        # @end

        if (empty($phoneNumber))
        {
            # Basically, make sure that $phoneNumber is an empty, nil string.
            $phoneNumber = '';
            return $this;
        }

        # "Country Prefix One" means a number that originates from the U.S.,
        # Canada, or various island countries / states / territories.
        $numbers = $this->parsePhoneNumber($phoneNumber);

        if (count($numbers))
        {
            if (count($numbers) < 10)
            {
                # This phone number should have failed validation previous to this.
                # If we should accomodate extentions, 911, mobile text numbers, etc,
                # this should be done by some sort of plugin or callback.
                return $this;
            }

            if (!empty($this->countryId))
            {
                switch ($this->countryId)
                {
                    # Country Prefix +1
                    case 223:   # US
                    case 38:    # Canada
                    case 7:     # Anguilla (264)
                    case 9:     # Antigua and Barbuda (268)
                    case 16:    # Bahamas (242)
                    case 19:    # Barbados (246)
                    case 24:    # Bermuda (441)
                    case 231:   # British Virgin Islands (284)
                    case 40:    # Cayman Islands (345)
                    case 59:    # Dominica (767)
                    case 60:    # Dominican Republic (809), (829)
                    case 86:    # Grenada/Carricou (473)
                    case 106:   # Jamaica (876)
                    case 143:   # Montserrat (664)
                    case 172:   # Puerto Rico (787), (939)
                    case 178:   # St Kitts and Nevis (869)
                    case 179:   # St Lucia (758)
                    case 180:   # St Vincent/Grenadines (784)
                    case 213:   # Trinidad and Tobago (868)
                    case 217:   # Turks and Caicos (649)
                    case 232:   # U.S. Virgin Islands (340)
                    {
                        $phoneNumber = $this->formatCountryPrefixOneNumber($numbers, $template);
                        break;
                    }
                }
            }
            else
            {
                $phoneNumber = $this->formatCountryPrefixOneNumber($numbers, $template);
            }
        }

        return $this;
    }

    private function formatCountryPrefixOneNumber(array $numbers, $template)
    {
        # @return string

        # @description
        # <h2>Formatting a Phone Number With a Country Prefix of '1'</h2>
        # <p>
        #   <var>$numbers</var> is provided as an array, so instead
        #   of 1 (317) 555-1212, <var>$numbers</var> would be provided as
        #   an array:
        # </p>
        # <code>
        #   array(
        #       0 => 1,
        #       2 => 3,
        #       3 => 1,
        #       4 => 7,
        #       5 => 5,
        #       6 => 5,
        #       7 => 5,
        #       8 => 1,
        #       9 => 2,
        #       10 => 1,
        #       11 => 2
        #   )
        # </code>
        # <p>
        #   The template used to format the phone number is provided in <var>$template</var>.
        #   The default template is <var>1 (___) ___-____.
        #   (An underscrore '_' is supported as template placeholders).
        # </p>
        # @end

        $phoneNumber = '';

        if (is_array($numbers))
        {
            if (count($numbers) == 11 && $numbers[0] == 1)
            {
                array_shift($numbers);
            }

            if (count($numbers) == 10)
            {
                foreach ($numbers as $number)
                {
                    # Limit replacement to one 'X' at a time going from left to right.
                    $template = preg_replace('/\_/', $number, $template, 1);
                }

                $phoneNumber = $template;
            }
        }

        return $phoneNumber;
    }

    public function getFormattedPhoneNumber($phoneNumber, $template = '1 (___) ___-____')
    {
        # @return string

        # @description
        # <h2>Formatting Phone Numbers</h2>
        # <p>
        #   Forces all phone numbers to appear in the format provided in template.
        #   The default template is <var>1 (XXX) XXX-XXXX</var>.
        # </p>
        # <p>
        #   Once a phone number is provided, this method determines if that phone number
        #   is a U.S. number by counting the number of digits present in the number.  A
        #   10 or 11 digit number will trigger re-formatting in the provided template.
        # </p>
        # <p>
        #   Expansion to this method to include rules for formatting phone numbers specific
        #   to other countries is welcome either in the form of a patch or detailed
        #   message explaining how to implement support for formatting that country's phone
        #   numbers.
        # </p>
        # @end

        $this->formatPhoneNumber($phoneNumber, $template);
        return $phoneNumber;
    }

    public function setCountryId(&$countryId)
    {
        # @return hContactValidationLibrary

        # @description
        # <h2>Formatting Phone Numbers</h2>
        # <p>
        #   Forces all phone numbers to appear in the format provided in template.
        #   The default template is <var>1 (XXX) XXX-XXXX</var>.
        # </p>
        # <p>
        #   Once a phone number is provided, this method determines if that phone number
        #   is a U.S. number by counting the number of digits present in the number.  A
        #   10 or 11 digit number will trigger re-formatting in the provided template.
        # </p>
        # <p>
        #   Expansion to this method to include rules for formatting phone numbers specific
        #   to other countries is welcome either in the form of a patch or detailed
        #   message explaining how to implement support for formatting that country's phone
        #   numbers.
        # </p>
        # @end

        if (isset($countryId))
        {
            $this->countryId = $countryId;
        }

        return $this;
    }

    public function isFieldId($contactFieldId, $frameworkResourceId)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Field Id is Valid</h2>
        # <p>
        #   Determines if the provided <var>$contactFieldId</var> is valid for
        #   provided <var>$frameworkResourceId</var>.  Contact fields are used
        #   to categorize and explain the purpose of information stored with a
        #   contact record.  <var>hContactFieldId</var> describes, for example,
        #   what a phone number is; mobile, home, work, etc.  <var>hContactFieldId</var>
        #   is used for addresses, email addresses, phone numbers, and internet accounts.
        # </p>
        # @end

        return $this->hContactFields->selectExists(
            'hContactFieldId',
            array(
                'hContactFieldId' => (int) $contactFieldId,
                'hFrameworkResourceId' => (int) $frameworkResourceId
            )
        );
    }
}

?>