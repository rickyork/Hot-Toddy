<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy User Agent
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
# <h1>User Agent API</h1>
# <p>
#    Detects the user agent, and sets information about it in various framework variables.
# </p>
# @end

class hUserAgentLibrary extends hPlugin {

    private $variables = array(
        'interfaceIdiom'           => null,
        'interfaceIdiomIsDesktop'  => false,
        'interfaceIdiomIsPad'      => false,
        'interfaceIdiomIsPhone'    => false,
        'interfaceIdiomIsTV'       => false,
        'os'                       => null,
        'osVersion'                => 0,
        'browser'                  => null,
        'browserVersion'           => 0,
        'isMobile'                 => false,
        'vendorPrefix'             => null,
        'iPad'                     => false,
        'iPhone'                   => false,
        'iOS'                      => false,
        'isAppleTV'                => false,
        'isMobile'                 => false,
        'isNintendoWii'            => false,
        'isAndroid'                => false,
        'isBlackberry'             => false,
        'isNokia'                  => false,
        'isWindowsPhone'           => false,
        'isMobileSafari'           => false,
        'isDesktop'                => false,
        'isMac'                    => false,
        'isMacMountainLion'        => false,
        'isMacLion'                => false,
        'isMacSnowLeopard'         => false,
        'isMacLeopard'             => false,
        'isMacTiger'               => false,
        'isMacPanther'             => false,
        'isMacJaguar'              => false,
        'isMacPuma'                => false,
        'isMacCheetah'             => false,
        'isWindows'                => false,
        'isWindowsCE'              => false,
        'isWindows95'              => false,
        'isWindowsNT'              => false,
        'isWindows98'              => false,
        'isWindowsME'              => false,
        'isWindowsServer2003'      => false,
        'isWindowsXP'              => false,
        'isWindows2000'            => false,
        'isWindowsVista'           => false,
        'isWindows7'               => false,
        'isWindows8'               => false,
        'isLinux'                  => false,
        'isRobot'                  => false,
        'isGoogleBot'              => false,
        'isGoogle'                 => false,
        'isYahooBot'               => false,
        'isYahoo'                  => false,
        'isAskJeevesBot'           => false,
        'isAskJeeves'              => false,
        'isBingBot'                => false,
        'isBing'                   => false,
        'isOpera'                  => false,
        'isPresto'                 => false,
        'isGecko'                  => false,
        'isWebkit'                 => false,
        'isKHTML'                  => false,
        'isW3C'                    => false,
        'isNetscape'               => false,
        'isSafari'                 => false,
        'isChrome'                 => false,
        'useGoogleChromeFrame'     => false,
        'isChromeFrame'            => false,
        'isIE'                     => false,
        'isTrident'                => false,
        'isTridentLTE6'            => false,
        'isTridentLT6'             => false,
        'isTridentGTE6'            => false,
        'isTridentGT6'             => false,
        'isTridentLTE7'            => false,
        'isTridentLT7'             => false,
        'isTridentGTE7'            => false,
        'isTridentGT7'             => false,
        'isTridentLTE8'            => false,
        'isTridentLT8'             => false,
        'isTridentGTE8'            => false,
        'isTridentGT8'             => false,
        'isTridentLTE9'            => false,
        'isTridentLT9'             => false,
        'isTridentGTE9'            => false,
        'isTridentGT9'             => false,
        'isTridentLTE10'           => false,
        'isTridentLT10'            => false,
        'isTridentGTE10'           => false,
        'isTridentGT10'            => false
    );

    public function hConstructor()
    {

    }

    public function __isset($key)
    {
        return isset($this->variables[$key]);
    }

    public function __unset($key)
    {
        unset($this->variables[$key]);
    }

    public function __set($key, $value)
    {
        $this->variables[$key] = $value;
    }

    public function &__get($key)
    {
        if (isset($this->variables[$key]))
        {
            return $this->variables[$key];
        }

        $rtn = false;

        return $rtn;
    }

    public function getVariables()
    {
        # @return array

        # @description
        # <h2>Retrieving Variables</h2>
        # <p>
        #   Retrieves user agent variables.
        # </p>
        # @end

        if (substr($this->browser, 0, 2) == 'is')
        {
            $this->browser = substr(strtolower($this->browser), 2);
        }

        return $this->variables;
    }

    public function parse($userAgent)
    {
        # @return array

        # @description
        # <h2>Parsing the User Agent String</h2>
        # <p>
        #   Parses and extracts various bits of data from the user agent string and
        #   sets variables based on that data, reporting things like the user's operating
        #   system, whether or not the device is a mobile device, desktop computer,
        #   or television, and information about the browser being used.
        # </p>
        # @end

        if ($GLOBALS['hFramework']->hGoogleChromeFrame && strstr($userAgent, 'chromeframe/') && strstr($userAgent, 'MSIE'))
        {
            $matches = array();

            # The dirty, dirty hack prevents chrome frame from misidentifying as MSIE
            preg_match('/Windows\ NT\ (\d*\.\d*)/', $userAgent, $matches);

            $version = 5.1;

            if ($matches && is_array($matches) && count($matches))
            {
                $version = isset($matches[1])? (float) $matches[1] : null;
            }

            $userAgent = 'Mozilla/5.0 (Windows NT '.$matches[1].'; chromeframe/25.0.1364.97) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.97 Safari/537.22';
        }

        $this->isRobot = (bool) preg_match('!(b|B)ot!', $userAgent);

        $matches = '';

        $this->interfaceIdiomIsPad = false;
        $this->interfaceIdiomIsPhone = false;
        $this->interfaceIdiomIsTV = false;
        $this->interfaceIdiomIsDesktop = false;

        $this->iPad = false;
        $this->iPhone = false;
        $this->isAppleTV = false;

        if (preg_match('!iPad!', $userAgent, $matches))
        {
            $this->interfaceIdiom = 'Pad';
            $this->interfaceIdiomIsPad = true;
            $this->iPad = true;
        }
        else if (preg_match('!iPhone!', $userAgent, $matches))
        {
            $this->interfaceIdiom = 'Phone';
            $this->interfaceIdiomIsPhone = true;
            $this->iPhone = true;
        }
        else if (preg_match('!Apple TV|AppleTV!', $userAgent, $matches))
        {
            $this->interfaceIdiom = 'TV';
            $this->interfaceIdiomIsTV = true;
            $this->isAppleTV = true;
        }
        else
        {
            $this->interfaceIdiom = 'Desktop';
            $this->interfaceIdiomIsDesktop = true;
            $this->isDesktop = true;
        }

        $OSes = array(
            'Windows CE'          => '!Windows CE!',
            'Windows 95'          => '!Windows[\s]?95!',
            'Windows 98'          => array(
                '!Win98!',
                '!Windows 98!'
            ),
            'Windows ME'          => array(
                '!Windows ME!'
            ),
            'Windows Server 2003' => '!Windows NT 5\.2!',
            'Windows XP'          => array(
                '!Windows NT 5\.1!',
                '!Windows XP!'
            ),
            'Windows 2000'        => '!Windows NT 5\.0!',
            'Windows Vista'       => '!Windows NT 6\.0!',
            'Windows 7'           => '!Windows NT 6\.1!',
            'Windows 8'           => '!Windows NT 6\.2!',
            'Windows Phone'       => '!Windows Phone OS (\d*)(\.)?(\d*)!',
            'Android'             => '!Android!',
            'BlackBerry'          => '!BlackBerry!',
            'iOS'                 => array(
                '!iPhone!',
                '!iPad!',
                '!Apple TV!',
                '!AppleTV!'
            ),
            'Mac OS X'            => array(
                '!Intel Mac OS X!',
                '!PPC Mac OS X!',
                '!Macintosh!',
                '!Mac\_PowerPC!'
            ),
            'Nintendo Wii'        => '!Nintendo Wii!',
            'Nokia'               => '!Nokia!',
            'Windows NT'          => '!Windows NT!',
            'Windows'             => '!Windows!',    // Default Windows Fallback
            'Linux'               => '!Linux!',
            'Google'              => '!Googlebot!',
            'Yahoo'               => '!Yahoo!',
            'Ask Jeeves'          => '!Ask Jeeves!',
            'Bing'                => '!Bing|bing|msnbot!',
            'Other Bot'           => '!bot|spider|crawl!i'
        );

        $matches = '';

        foreach ($OSes as $OS => $patterns)
        {
            if ($this->os)
            {
                break;
            }

            if (!is_array($patterns))
            {
                if (preg_match($patterns, $userAgent, $matches))
                {
                    $this->os = $OS;
                    break;
                }
            }
            else
            {
                foreach ($patterns as $pattern)
                {
                    if (preg_match($pattern, $userAgent, $matches))
                    {
                        $this->os = $OS;
                        break;
                    }
                }
            }
        }

        $this->isWindows = false;
        $this->isMac = false;

        if (strstr($this->os, 'Windows '))
        {
            $this->osVersion = str_replace('Windows ', '', $this->os);
            $this->os = 'Windows';

            $this->isWindows = true;
        }
        else
        {
            switch ($this->os)
            {
                case 'Mac OS X':
                case 'iOS':
                {
                    preg_match(
                        '!'.($this->os == 'Mac OS X'? 'Mac OS X' : 'OS').' (\d+\_\d+\_*\d*)!',
                        $userAgent,
                        $matches
                    );

                    if (isset($matches[1]))
                    {
                        $this->osVersion = str_replace('_', '.', $matches[1]);
                    }

                    $this->isMac = true;
                    break;
                }
                default:
                {
                    $this->osVersion = null;
                }
            }
        }

        $this->OS = $this->os;
        $this->OSVersion = $this->osVersion;

        if ($this->isWindows)
        {
            $this->isWindowsCE = $this->os == 'Windows CE';
            $this->isWindows95 = $this->os == 'Windows 95';
            $this->isWindowsNT = $this->os == 'Windows NT';
            $this->isWindows98 = $this->os == 'Windows 98';
            $this->isWindowsME = $this->os == 'Windows ME';
            $this->isWindowsServer2003 = $this->os == 'Windows Server 2003';
            $this->isWindowsXP = $this->os == 'Windows XP';
            $this->isWindows2000 = $this->os == 'Windows 2000';
            $this->isWindowsVista = $this->os == 'Windows Vista';
            $this->isWindows7 = $this->os == 'Windows 7';
            $this->isWindows8 = $this->os == 'Windows 8';
        }

        $this->iOS = $this->os == 'iOS';
        $this->isWindowsPhone = $this->os == 'Windows Phone';
        $this->isAndroid = $this->os == 'Android';
        $this->isBlackberry = $this->os == 'Backberry';
        $this->isNokia = $this->os == 'Nokia';

        if ($this->isMac)
        {
            $this->isMacMountainLion    = $this->os == 'Mac OS X' && (strstr($this->osVersion, '10.8.') || $this->osVersion == 10.8);
            $this->isMacLion            = $this->os == 'Mac OS X' && (strstr($this->osVersion, '10.7.') || $this->osVersion == 10.7);
            $this->isMacSnowLeopard     = $this->os == 'Mac OS X' && (strstr($this->osVersion, '10.6.') || $this->osVersion == 10.6);
            $this->isMacLeopard         = $this->os == 'Mac OS X' && (strstr($this->osVersion, '10.5.') || $this->osVersion == 10.5);
            $this->isMacTiger           = $this->os == 'Mac OS X' && (strstr($this->osVersion, '10.4.') || $this->osVersion == 10.4);
            $this->isMacPanther         = $this->os == 'Mac OS X' && (strstr($this->osVersion, '10.3.') || $this->osVersion == 10.3);
            $this->isMacJaguar          = $this->os == 'Mac OS X' && (strstr($this->osVersion, '10.2.') || $this->osVersion == 10.2);
            $this->isMacPuma            = $this->os == 'Mac OS X' && (strstr($this->osVersion, '10.1.') || $this->osVersion == 10.1);
            $this->isMacCheetah         = $this->os == 'Mac OS X' && (strstr($this->osVersion, '10.0.') || $this->osVersion == 10.0);
        }

        $this->isLinux              = $this->os == 'Linux';

        $this->isNintendoWii        = $this->os == 'Nintendo Wii';

        $this->isGoogleBot          = $this->os == 'Google';
        $this->isYahooBot           = $this->os == 'Yahoo';
        $this->isAskJeevesBot       = $this->os == 'Ask Jeeves';
        $this->isBingBot            = $this->os == 'Bing';
        $this->isRobot              = $this->os == 'Other Bot' || $this->isGoogleBot || $this->isYahooBot || $this->isAskJeevesBot || $this->isBingBot;

        $this->isMobileSafari = false;

        if (preg_match('!Mobile Safari!', $userAgent))
        {
            $this->isMobileSafari = true;
            $this->interfaceIdiom = 'Phone';
            $this->interfaceIdiomIsTV = false;
            $this->interfaceIdiomIsPad = false;
            $this->interfaceIdiomIsPhone = true;
            $this->interfaceIdiomIsDesktop = false;
        }

        switch (true)
        {
            # Android tablets don't have 'Mobile' before 'Safari'
            case $this->isAndroid && !preg_match('!Mobile Safari!', $userAgent):

            # Windows Tablets
            case preg_match('!Tablet PC!', $userAgent):

            # Opera on a tablet
            case preg_match('!Opera Tablet!', $userAgent):
            {
                $this->interfaceIdiom = 'Pad';
                $this->interfaceIdiomIsTV = false;
                $this->interfaceIdiomIsPad = true;
                $this->interfaceIdiomIsPhone = false;
                $this->interfaceIdiomIsDesktop = false;
                break;
            }

        }

        switch (true)
        {
            case $this->iPad:
            case $this->iPhone:
            case $this->iOS:
            case $this->isWindowsPhone:
            case $this->isWindowsCE:
            case $this->isAndroid:
            case $this->isBlackberry:
            case $this->isNokia:
            {
                $this->isMobile = true;

                if ($this->interfaceIdiom != 'Pad' && $this->interfaceIdiom != 'TV')
                {
                    $this->interfaceIdiom = 'Phone';
                    $this->interfaceIdiomIsTV = false;
                    $this->interfaceIdiomIsPad = false;
                    $this->interfaceIdiomIsPhone = true;
                    $this->interfaceIdiomIsDesktop = false;
                }

                break;
            }
        }

        if ($this->interfaceIdiom == 'Pad')
        {
            $this->isMobile = true;
        }

        if (!$this->isMobile)
        {
            if (preg_match('!IEMobile!', $userAgent) || preg_match('!Opera Mini!', $userAgent) || $this->isMobileSafari)
            {
                $this->isMobile = true;

                if ($this->interfaceIdiom != 'Pad' && $this->interfaceIdiom != 'TV')
                {
                    $this->interfaceIdiom = 'Phone';
                    $this->interfaceIdiomIsTV = false;
                    $this->interfaceIdiomIsPad = false;
                    $this->interfaceIdiomIsPhone = true;
                    $this->interfaceIdiomIsDesktop = false;
                }
            }
        }

        if (!$this->os)
        {
            $this->os = 'Other';
        }

        $this->browser        = 'isOther';
        $this->browserVersion = 0;

        $this->isOpera       = false;
        $this->isPresto      = false;
        $this->isIE          = false;
        $this->isTrident     = false;
        $this->isWebkit      = false;
        $this->isKHTML       = false;
        $this->isGecko       = false;
        $this->isW3C         = false;
        $this->isGoogle      = false;
        $this->isBing        = false;
        $this->isAskJeeves   = false;
        $this->isYahoo       = false;
        $this->isNetscape    = false;
        $this->isSafari      = false;
        $this->isChrome      = false;
        $this->isChromeFrame = false;

        $matches = '';

        // The order that these are called is important!
        // Many user agents try to fudge their identity.
        $browsers = array(
            'isOpera'       => '!Opera[/ ]?((\d*)(\.)?(\d*))!',    // Opera
            'isIE'          => array(
                '!MSIE[/ ]?((\d*)(\.)?(\d*))!',     // Explorer
                '!Explorer[/ ]?((\d*)(\.)?(\d*))!', // Explorer
            ),
            'isTrident'   => '!Trident[/ ]?((\d*)(\.)?(\d*))!',
            'isWebkit'    => '!AppleWebKit[/ ]?((\d*)(\.)?(\d*))!i', // Webkit
            'isKHTML'     => '!Konqueror[/ ]?((\d*)(\.)?(\d*))!',   // Konquorer
            'isGecko'     => '!Gecko[/ ]?(\d*)!',                   // Netscape, Mozilla, Mozilla Firefox, AOL Mac, etc.
            'isW3C'       => '!W3C_Validator[/ ]?(\d*)!',
            'isGoogle'    => '!Googlebot[/ ]?((\d*)(\.)?(\d*))!',
            'isBing'      => '!msnbot[/ ]?((\d*)(\.)?(\d*))!',
            'isAskJeeves' => '!Ask Jeeves!',
            'isYahoo'     => '!Yahoo!',
            'isNetscape'  => '!Mozilla[/ ]?((\d*)(\.)?(\d*))!'  // Old Netscape version 4 and less.
        );

        foreach ($browsers as $browser => $patterns)
        {
            if ($this->browser && $this->browserVersion)
            {
                break;
            }

            if (!is_array($patterns))
            {
                if ($this->userAgentMatch($patterns, $browser, $userAgent))
                {
                    break;
                }
            }
            else
            {
                foreach ($patterns as $pattern)
                {
                    if ($this->userAgentMatch($pattern, $browser, $userAgent))
                    {
                        break;
                    }
                }
            }
        }

        $this->{"{$this->browser}"} = true;

        if (substr($this->browser, 0, 2) == 'is')
        {
            $this->browser = substr(strtolower($this->browser), 2);
        }

        $this->{"{$this->browser}"} = true;

        if ($this->browser == 'webkit')
        {
            if (preg_match('!Chrome!', $userAgent, $matches))
            {
                $this->isChrome = true;
            }

            if (!$this->isChrome && preg_match('!Safari!', $userAgent, $matches))
            {
                $this->isSafari = true;
            }
        }

        $this->useGoogleChromeFrame = $GLOBALS['hFramework']->hGoogleChromeFrame;

        if ($this->browser == 'ie' && $GLOBALS['hFramework']->hGoogleChromeFrame(true))
        {
            $matches = array();

            if (preg_match('!chromeframe!', $userAgent, $matches))
            {
                $this->browser = 'webkit';
                $this->isChromeFrame = true;
            }
        }

        $this->vendorPrefix = '-webkit-';

        switch ($this->browser)
        {
            case 'ie':
            case 'trident':
            {
                $this->vendorPrefix = '-ms-';
                break;
            }
            case 'gecko':
            {
                $this->vendorPrefix = '-moz-';
                break;
            }
            case 'opera':
            case 'presto':
            {
                $this->vendorPrefix = '-o-';
                break;
            }
            case 'khtml':
            {
                $this->venforPrefix = '-khtml-';
                break;
            }
        }

        if ($this->isOpera)
        {
            $this->isPresto = true;
        }

        if ($this->isIE)
        {
            $this->isTrident = true;
        }

        if ($this->isTrident)
        {
            $this->isTridentLTE6  = $this->isTrident && $this->browserVersion <= 6;
            $this->isTridentLT6   = $this->isTrident && $this->browserVersion <  6;
            $this->isTridentGTE6  = $this->isTrident && $this->browserVersion >= 6;
            $this->isTridentGT6   = $this->isTrident && $this->browserVersion >  6;

            $this->isTridentLTE7  = $this->isTrident && $this->browserVersion <= 7;
            $this->isTridentLT7   = $this->isTrident && $this->browserVersion <  7;
            $this->isTridentGTE7  = $this->isTrident && $this->browserVersion >= 7;
            $this->isTridentGT7   = $this->isTrident && $this->browserVersion >  7;

            $this->isTridentLTE8  = $this->isTrident && $this->browserVersion <= 8;
            $this->isTridentLT8   = $this->isTrident && $this->browserVersion <  8;
            $this->isTridentGTE8  = $this->isTrident && $this->browserVersion >= 8;
            $this->isTridentGT8   = $this->isTrident && $this->browserVersion >  8;

            $this->isTridentLTE9  = $this->isTrident && $this->browserVersion <= 9;
            $this->isTridentLT9   = $this->isTrident && $this->browserVersion <  9;
            $this->isTridentGTE9  = $this->isTrident && $this->browserVersion >= 9;
            $this->isTridentGT9   = $this->isTrident && $this->browserVersion >  9;

            $this->isTridentLTE10 = $this->isTrident && $this->browserVersion <= 10;
            $this->isTridentLT10  = $this->isTrident && $this->browserVersion <  10;
            $this->isTridentGTE10 = $this->isTrident && $this->browserVersion >= 10;
            $this->isTridentGT10  = $this->isTrident && $this->browserVersion >  10;

            // No more. IE 10 is the last. Don't use these unless you need to be
            // compatible with old versions of IE.
        }

        $this->originalInterfaceIdiom          = $this->interfaceIdiom;
        $this->originalInterfaceIdiomIsDesktop = $this->interfaceIdiomIsDesktop;
        $this->originalInterfaceIdiomIsPad     = $this->interfaceIdiomIsPad;
        $this->originalInterfaceIdiomIsPhone   = $this->interfaceIdiomIsPhone;
        $this->originalInterfaceIdiomIsTV      = $this->interfaceIdiomIsTV;
        $this->originalIsDesktop               = $this->isDesktop;
        $this->originalIsMobile                = $this->isMobile;

        return $this->variables;
    }

    private function userAgentMatch($pattern, $browser, $userAgent)
    {
        # @return boolean

        # @description
        # <h2>Matching Information About the Browser</h2>
        # <p>
        #
        # </p>
        # @end

        $matches = array();

        if (preg_match($pattern, $userAgent, $matches))
        {
            if (!empty($matches[1]))
            {
                 $this->browserVersion = (float) $matches[1];
            }

            if ($browser == 'isTrident')
            {
                // IE 11 shifts the U/A string so that Trident has its own version,
                // apart from IE. I don't understand the point of this other than
                // breaking forward compatibility for old U/A dependent IE-specific
                // hacks. So I've just put the IE version number as the trident
                // version number so forward compatibility works again.
                // Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko
                preg_match(
                    '!Trident[/ ]?\d*\.\d*\;\s+rv\:((\d*)(\.)?(\d*))!',
                    $userAgent,
                    $subMatches
                );

                $this->browserVersion = (float) $subMatches[1];
            }

            $this->browser = $browser;
            return true;
        }
        else
        {
            return false;
        }
    }
}

?>