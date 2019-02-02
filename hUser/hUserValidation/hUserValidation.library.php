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

class hUserValidationLibrary extends hPlugin {

    private $password;
    private $email;

    public function isValidUserName($username)
    {
        // return preg_match('/^[\w|\.|\-|\@]{2,25}$/', $username);
        return strlen($username) < 40;
    }

    public function isUniqueUserName($username)
    {
        return !$this->hUsers->selectExists(
            'hUserId',
            array(
                'hUserName' => $username
            )
        );
    }

//    public function isValidEmailAddress($email)
//    {
//        if (substr_count($email, '@') !== 1)
//        {
//            return false;
//        }
//        else
//        {
//            // letters, digits, -, _, . + @ + letters, digits, -, _, .
//            // Catches most invalid addresses without being too restrictive.
//            return preg_match('/^.{1,64}\@.{1,255}$/i', $email);
//        }
//    }

    /**
     * Check email address validity
     * @param   $email   Email address to be checked
     * @return  True if email is valid, false if not
     */
    public function isValidEmailAddress($email)
    {
        // Control characters are not allowed
        if (preg_match('/[\x00-\x1F\x7F-\xFF]/', $email))
        {
            return false;
        }

        // Split it into sections using last instance of "@"
        $position = strrpos($email, '@');

        if ($position === false)
        {
            // No "@" symbol in email.
            return false;
        }

        $mailbox = substr($email, 0, $position);
        $domain = substr($email, $position + 1);

        // Count the "@" symbols. Only one is allowed, except where
        // contained in quote marks in the local part. Quickest way to
        // check this is to remove anything in quotes.
        // Then check - should be no "@" symbols.
        if (strrpos(preg_replace('/"[^"]+"/','', $mailbox).$domain, '@') !== false)
        {
            // "@" symbol found
            return false;
        }

        // Check local portion
        if (!$this->isValidMailbox($mailbox))
        {
            return false;
        }

        // Check domain portion
        if (!$this->isValidDomain($domain))
        {
            return false;
        }

        // If we're still here, all checks above passed. Email is valid.
        return true;
    }

    /**
     * Checks email section before "@" symbol for validity
     * @param   $local     Text to be checked
     * @return  True if local portion is valid, false if not
     */
    public function isValidMailbox($mailbox)
    {
        // Mailbox portion can only be from 1 to 64 characters, inclusive.
        // Please note that servers are encouraged to accept longer local
        // parts than 64 characters.
        if (!$this->isLength($mailbox, 1, 64))
        {
            return false;
        }

        // Local portion must be:
        // 1) a dot-atom (strings separated by periods)
        // 2) a quoted string
        // 3) an obsolete format string (combination of the above)
        $bits = explode('.', $mailbox);

        for ($i = 0, $max = count($bits); $i < $max; $i++)
        {
            $match = preg_match(
                '.^('.
                    '([A-Za-z0-9!#$%&\'*+/=?^_`{|}~-]'.
                    '[A-Za-z0-9!#$%&\'*+/=?^_`{|}~-]{0,63})'.
                    '|'.
                    '("[^\\\"]{0,62}")'.
                ')$.',
                $bits[$i]
            );

            if (!$match)
            {
                return false;
            }
        }

        return true;
    }

    /**
    * Checks email section after "@" symbol for validity
    * @param   domain     Text to be checked
    * @return  True if domain portion is valid, false if not
    */
    public function isValidDomain($domain)
    {
        // Total domain can only be from 1 to 255 characters, inclusive
        if (!$this->isLength($domain, 1, 255))
        {
            return false;
        }

        $match =
            preg_match(
                '/^(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])'.
                '(\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])){3}$/',
                $domain
            ) ||
            preg_match(
                '/^\[(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])'.
                '(\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])){3}\]$/',
                $domain
            );

        // Check if domain is IP, possibly enclosed in square brackets.
        if ($match)
        {
            return true;
        }
        else
        {
            $bits = explode('.', $domain);

            if (sizeof($bits) < 1)
            {
                return false; // Not enough parts to domain // Apparently, these guys haven't heard of "localhost"
            }

            for ($i = 0, $max = count($bits); $i < $max; $i++)
            {
                // Each portion must be between 1 and 63 characters, inclusive
                if (!$this->isLength($bits[$i], 1, 63))
                {
                    return false;
                }

                if (!preg_match('/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/', $bits[$i]))
                {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check given text length is between defined bounds
     * @param   text     Text to be checked
     * @param   minimum  Minimum acceptable length
     * @param   maximum  Maximum acceptable length
     * @return  True if string is within bounds (inclusive), false if not
     */
    private function isLength($text, $minimum, $maximum)
    {
        // Minimum and maximum are both inclusive
        $length = strlen($text);
        return !(($length < $minimum) || ($length > $maximum));
    }

    public function isUniqueEmailAddress($email)
    {
        return !$this->hUsers->selectExists(
            'hUserId',
            array(
                'hUserEmail' => $email
            )
        );
    }

    public function setPassword(&$password)
    {
        if (isset($password))
        {
            $this->password = &$password;
        }
    }

    public function setEmailAddress(&$email)
    {
        if (isset($email))
        {
            $this->email = &$email;
        }
    }

    public function confirmPasswordMatches($value)
    {
        return ($this->password === $value);
    }

    public function confirmEmailMatches($value)
    {
        return ($this->email === $value);
    }
}

?>