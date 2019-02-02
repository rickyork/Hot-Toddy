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
# <h1>User Agent Plugin</h1>
# <p>
#    Parses the user's user agent information and sets various framework variables.
# </p>
# <h2>User Agent Variables</h2>
# <p>
#    The following variables are created by this plugin.  This information is gathered
#    based on data in the HTTP user agent variable, which might not be reliable, and
#    can potentially be forged.
# </p>
# <table>
#    <thead>
#        <tr>
#            <th>Variable</th>
#            <th>Description</th>
#        </tr>
#    </thead>
#    <tbody>
#        <tr id='userAgent'>
#            <td class='code'>userAgent</td>
#            <td>Contains all user agent information (this is an array)</td>
#        </tr>
#        <tr id='hUserAgentIsRobot'>
#            <td class='code'>hUserAgentIsRobot</td>
#            <td>Whether or not the user agent is a robot</td>
#        </tr>
#        <tr id='hUserInterfaceIdiom'>
#            <td class='code'>hUserInterfaceIdiom</td>
#            <td>The user interface idiom (one of desktop, pad, phone, or TV)</td>
#        </tr>
#        <tr id='interfaceIdiom'>
#            <td class='code'>interfaceIdiom</td>
#            <td>The user interface idiom (one of desktop, pad, phone, or TV)</td>
#        </tr>
#        <tr id='hUserInterfaceIdiomIsDesktop'>
#            <td class='code'>hUserInterfaceIdiomIsDesktop</td>
#            <td>Whether or not the "desktop" user interface idiom should be used.</td>
#        </tr>
#        <tr id='hUserInterfaceIdiomIsPad'>
#            <td class='code'>hUserInterfaceIdiomIsPad</td>
#            <td>Whether or not the "pad" user interface idiom should be used.</td>
#        </tr>
#        <tr id='hUserInterfaceIdiomIsPhone'>
#            <td class='code'>hUserInterfaceIdiomIsPhone</td>
#            <td>Whether or not the "phone" user interface idiom should be used.</td>
#        </tr>
#        <tr id='hUserInterfaceIdiomIsTV'>
#            <td class='code'>hUserInterfaceIdiomIsTV</td>
#            <td>Whether or not the "TV" user interface idiom should be used.</td>
#        </tr>
#        <tr id='hUserAgentOS'>
#            <td class='code'>hUserAgentOS</td>
#            <td>A string representing the user's OS, if the OS is known.</td>
#        </tr>
#        <tr id='hUserAgentOSVersion'>
#            <td class='code'>hUserAgentOSVersion</td>
#            <td>A string, float or integer representing the user's OS version, if the OS version is known.</td>
#        </tr>
#        <tr id='hUserAgent'>
#            <td class='code'>hUserAgent</td>
#            <td>A string representing the user's browser, if the browser is known.</td>
#        </tr>
#        <tr id='hUserAgentVersion'>
#            <td class='code'>hUserAgentVersion</td>
#            <td>A string, float, or integer representing the user's browser version, if the browser version is known.</td>
#        </tr>
#        <tr id='hUserAgentIsMobile'>
#            <td class='code'>hUserAgentIsMobile</td>
#            <td>Whether or not the user agent is that of a mobile device.</td>
#        </tr>
#        <tr id='hUserAgentChromeFrame'>
#            <td class='code'>hUserAgentChromeFrame</td>
#            <td>Whether or not the Google Chrome Frame plugin is being used with Internet Explorer.</td>
#        </tr>
#        <tr id='hUserAgentPrefix'>
#            <td class='code'>hUserAgentPrefix</td>
#            <td>The vendor prefix used with CSS with this browser.</td>
#        </tr>
#        <tr id='iPad'>
#            <td class='code'>iPad</td>
#            <td>Is the device an Apple iPad?</td>
#        </tr>
#        <tr id='iPhone'>
#            <td class='code'>iPhone</td>
#            <td>Is the device an Apple iPhone?</td>
#        </tr>
#        <tr id='iOS'>
#            <td class='code'>iOS</td>
#            <td>Is the device Apple iOS?</td>
#        </tr>
#        <tr id='AppleTV'>
#            <td class='code'>AppleTV</td>
#            <td>Is the device an Apple TV?</td>
#        </tr>
#        <tr id='isAppleTV'>
#            <td class='code'>isAppleTV</td>
#            <td>Is the device an Apple TV?</td>
#        </tr>
#        <tr id='isMobile'>
#            <td class='code'>isMobile</td>
#            <td>Is the device a mobile device?</td>
#        </tr>
#        <tr id='isNintendoWii'>
#            <td class='code'>isNintendoWii</td>
#            <td>Is the device a Nintendo Wii?</td>
#        </tr>
#        <tr id='isAndroid'>
#            <td class='code'>isAndroid</td>
#            <td>Is the device an Android OS device?</td>
#        </tr>
#        <tr id='isBlackberry'>
#            <td class='code'>isBlackberry</td>
#            <td>Is the device a Blackberry device?</td>
#        </tr>
#        <tr id='isNokia'>
#            <td class='code'>isNokia</td>
#            <td>Is the device a Nokia device?</td>
#        </tr>
#        <tr id='isWindowsPhone'>
#            <td class='code'>isWindowsPhone</td>
#            <td>Is the device a Windows Phone?</td>
#        </tr>
#        <tr id='isMobileSafari'>
#            <td class='code'>isMobileSafari</td>
#            <td>Is the browser mobile Safari?</td>
#        </tr>
#        <tr id='os'>
#            <td class='code'>os</td>
#            <td>The user's OS</td>
#        </tr>
#        <tr id='OS'>
#            <td class='code'>OS</td>
#            <td>The user's OS</td>
#        </tr>
#        <tr id='osVersion'>
#            <td class='code'>osVersion</td>
#            <td>The user's OS version.</td>
#        </tr>
#        <tr id='OSVersion'>
#            <td class='code'>OSVersion</td>
#            <td>The user's OS version.</td>
#        </tr>
#        <tr id='isDesktop'>
#            <td class='code'>isDesktop</td>
#            <td>Is the device a desktop computer?</td>
#        </tr>
#        <tr id='desktop'>
#            <td class='code'>desktop</td>
#            <td>Is the device a desktop computer?</td>
#        </tr>
#        <tr id='isMacMountainLion'>
#            <td class='code'>isMacMountainLion</td>
#            <td>Is the user using Mac OS X 10.8, Mountain Lion</td>
#        </tr>
#        <tr id='isMacLion'>
#            <td class='code'>isMacLion</td>
#            <td>Is the user using Mac OS X 10.7, Lion</td>
#        </tr>
#        <tr id='isMacSnowLeopard'>
#            <td class='code'>isMacSnowLeopard</td>
#            <td>Is the user using Mac OS X 10.6, Snow Leopard</td>
#        </tr>
#        <tr id='isMacLeopard'>
#            <td class='code'>isMacLeopard</td>
#            <td>Is the user using Mac OS X 10.5, Leopard</td>
#        </tr>
#        <tr id='isMacTiger'>
#            <td class='code'>isMacTiger</td>
#            <td>Is the user using Mac OS X 10.4, Tiger</td>
#        </tr>
#        <tr id='isMacPanther'>
#            <td class='code'>isMacPanther</td>
#            <td>Is the user using Mac OS X 10.3, Panther</td>
#        </tr>
#        <tr id='isMacJaguar'>
#            <td class='code'>isMacJaguar</td>
#            <td>Is the user using Mac OS X 10.2, Jaguar</td>
#        </tr>
#        <tr id='isMacPuma'>
#            <td class='code'>isMacPuma</td>
#            <td>Is the user using Mac OS X 10.1, Puma</td>
#        </tr>
#        <tr id='isMacCheetah'>
#            <td class='code'>isMacCheetah</td>
#            <td>Is the user using Mac OS X 10.0, Cheetah</td>
#        </tr>
#        <tr id='isMac'>
#            <td class='code'>isMac</td>
#            <td>Is the user using a Mac?</td>
#        </tr>
#        <tr id='mac'>
#            <td class='code'>mac</td>
#            <td>Is the user using a Mac?</td>
#        </tr>
#        <tr id='isWindowsCE'>
#            <td class='code'>isWindowsCE</td>
#            <td>Is the user using Windows CE?</td>
#        </tr>
#        <tr id='isWindows95'>
#            <td class='code'>isWindows95</td>
#            <td>Is the user using Windows 95?</td>
#        </tr>
#        <tr id='isWindowsNT'>
#            <td class='code'>isWindowsNT</td>
#            <td>Is the user using Windows NT?</td>
#        </tr>
#        <tr id='isWindows98'>
#            <td class='code'>isWindows98</td>
#            <td>Is the user using Windows '98?</td>
#        </tr>
#        <tr id='isWindowsME'>
#            <td class='code'>isWindowsME</td>
#            <td>Is the user using Windows ME?</td>
#        </tr>
#        <tr id='isWindowsServer2003'>
#            <td class='code'>isWindowsServer2003</td>
#            <td>Is the user using Windows Server 2003?</td>
#        </tr>
#        <tr id='isWindowsXP'>
#            <td class='code'>isWindowsXP</td>
#            <td>Is the user using Windows XP?</td>
#        </tr>
#        <tr id='isWindows2000'>
#            <td class='code'>isWindows2000</td>
#            <td>Is the user using Windows 2000?</td>
#        </tr>
#        <tr id='isWindowsVista'>
#            <td class='code'>isWindowsVista</td>
#            <td>Is the user using Windows Vista?</td>
#        </tr>
#        <tr id='isWindows7'>
#            <td class='code'>isWindows7</td>
#            <td>Is the user using Windows 7?</td>
#        </tr>
#        <tr id='isWindows8'>
#            <td class='code'>isWindows8</td>
#            <td>Is the user using Windows 8?</td>
#        </tr>
#        <tr id='isWindows'>
#            <td class='code'>isWindows</td>
#            <td>Is the user using Windows?</td>
#        </tr>
#        <tr id='windows'>
#            <td class='code'>windows</td>
#            <td>Is the user using Windows?</td>
#        </tr>
#        <tr id='isLinux'>
#            <td class='code'>isLinux</td>
#            <td>Is the user using Linux?</td>
#        </tr>
#        <tr id='isGoogleBot'>
#            <td class='code' id='isGoogle'>isGoogleBot, isGoogle</td>
#            <td>Is the user the Google Robot?</td>
#        </tr>
#        <tr id='isYahooBot'>
#            <td class='code' id='isYahoo'>isYahooBot, isYahoo</td>
#            <td>Is the user the Yahoo Robot?</td>
#        </tr>
#        <tr id='isAskJeevesBot'>
#            <td class='code' id='isAskJeeves'>isAskJeevesBot, isAskJeeves</td>
#            <td>Is the user the Ask Jeeves Robot?</td>
#        </tr>
#        <tr id='isBingBot'>
#            <td class='code' id='isBing'>isBingBot, isBing</td>
#            <td>Is the user the Bing Robot?</td>
#        </tr>
#        <tr id='isRobot'>
#            <td class='code'>isRobot</td>
#            <td>Is the user a Robot?</td>
#        </tr>
#        <tr id='browser'>
#            <td class='code'>browser</td>
#            <td>The user's browser</td>
#        </tr>
#        <tr id='browserVersion'>
#            <td class='code'>browserVersion</td>
#            <td>The user's browser version</td>
#        </tr>
#        <tr id='isOpera'>
#            <td class='code'>isOpera</td>
#            <td>Is the user using Opera?</td>
#        </tr>
#        <tr id='opera'>
#            <td class='code'>opera</td>
#            <td>Is the user using Opera?</td>
#        </tr>
#        <tr id='isPresto'>
#            <td class='code'>isPresto</td>
#            <td>Is the user using the Presto rendering engine (used in Opera)?</td>
#        </tr>
#        <tr id='isIE'>
#            <td class='code'>isIE</td>
#            <td>Is the user using Internet Explorer?</td>
#        </tr>
#        <tr id='ie'>
#            <td class='code'>ie</td>
#            <td>Is the user using Internet Explorer?</td>
#        </tr>
#        <tr id='isTrident'>
#            <td class='code'>isTrident</td>
#            <td>Is the user using the Trident Rendering engine (used in IE)?</td>
#        </tr>
#        <tr id='trident'>
#            <td class='code'>trident</td>
#            <td>Is the user using the Trident Rendering engine (used in IE)?</td>
#        </tr>
#        <tr id='isTridentLTE6'>
#            <td class='code'>isTridentLTE6</td>
#            <td>Is the user using the Trident Rendering engine less than or equal to version 6 (used in IE)?</td>
#        </tr>
#        <tr id='isTridentLT6'>
#            <td class='code'>isTridentLT6</td>
#            <td>Is the user using the Trident Rendering engine less than version 6 (used in IE)?</td>
#        </tr>
#        <tr id='isTridentGTE6'>
#            <td class='code'>isTridentGTE6</td>
#            <td>Is the user using the Trident Rendering engine greater than or equal to version 6 (used in IE)?</td>
#        </tr>
#        <tr id='isTridentGT6'>
#            <td class='code'>isTridentGT6</td>
#            <td>Is the user using the Trident Rendering engine greater than version 6 (used in IE)?</td>
#        </tr>
#        <tr id='isTridentLTE7'>
#            <td class='code'>isTridentLTE7</td>
#            <td>Is the user using the Trident Rendering engine less than or equal to version 7 (used in IE)?</td>
#        </tr>
#        <tr id='isTridentLT7'>
#            <td class='code'>isTridentLT7</td>
#            <td>Is the user using the Trident Rendering engine less than version 7 (used in IE)?</td>
#        </tr>
#        <tr id='isTridentGTE7'>
#            <td class='code'>isTridentGTE7</td>
#            <td>Is the user using the Trident Rendering engine greater than or equal to version 7 (used in IE)?</td>
#        </tr>
#        <tr id='isTridentGT7'>
#            <td class='code'>isTridentGT7</td>
#            <td>Is the user using the Trident Rendering engine greater than version 7 (used in IE)?</td>
#        </tr>
#        <tr id='isTridentLTE8'>
#            <td class='code'>isTridentLTE8</td>
#            <td>Is the user using the Trident Rendering engine less than or equal to version 8 (used in IE)?</td>
#        </tr>
#        <tr id='isTridentLT8'>
#            <td class='code'>isTridentLT8</td>
#            <td>Is the user using the Trident Rendering engine less than version 8 (used in IE)?</td>
#        </tr>
#        <tr id='isTridentGTE8'>
#            <td class='code'>isTridentGTE8</td>
#            <td>Is the user using the Trident Rendering engine greater than or equal to version 8 (used in IE)?</td>
#        </tr>
#        <tr id='isTridentGT8'>
#            <td class='code'>isTridentGT8</td>
#            <td>Is the user using the Trident Rendering engine greater than version 8 (used in IE)?</td>
#        </tr>
#        <tr id='isTridentLTE9'>
#            <td class='code'>isTridentLTE9</td>
#            <td>Is the user using the Trident Rendering engine less than or equal to version 9 (used in IE)?</td>
#        </tr>
#        <tr id='isTridentLT9'>
#            <td class='code'>isTridentLT9</td>
#            <td>Is the user using the Trident Rendering engine less than version 9 (used in IE)?</td>
#        </tr>
#        <tr id='isTridentGTE9'>
#            <td class='code'>isTridentGTE9</td>
#            <td>Is the user using the Trident Rendering engine greater than or equal to version 9 (used in IE)?</td>
#        </tr>
#        <tr id='isTridentGT9'>
#            <td class='code'>isTridentGT9</td>
#            <td>Is the user using the Trident Rendering engine greater than version 9 (used in IE)?</td>
#        </tr>
#        <tr id='isTridentLTE10'>
#            <td class='code'>isTridentLTE10</td>
#            <td>Is the user using the Trident Rendering engine less than or equal to version 10 (used in IE)?</td>
#        </tr>
#        <tr id='isTridentLT10'>
#            <td class='code'>isTridentLT10</td>
#            <td>Is the user using the Trident Rendering engine less than version 10 (used in IE)?</td>
#        </tr>
#        <tr id='isTridentGTE10'>
#            <td class='code'>isTridentGTE10</td>
#            <td>Is the user using the Trident Rendering engine greater than or equal to version 10 (used in IE)?</td>
#        </tr>
#        <tr id='isTridentGT10'>
#            <td class='code'>isTridentGT10</td>
#            <td>Is the user using the Trident Rendering engine greater than version 10 (used in IE)?</td>
#        </tr>
#        <tr id='isGecko'>
#            <td class='code'>isGecko</td>
#            <td>Is the user using the Gecko rendering engine? (used in Firefox)</td>
#        </tr>
#        <tr id='isMozilla'>
#            <td class='code'>isMozilla</td>
#            <td>Is the user using the Gecko rendering engine? (used in Firefox)</td>
#        </tr>
#        <tr id='gecko'>
#            <td class='code'>gecko</td>
#            <td>Is the user using the Gecko rendering engine? (used in Firefox)</td>
#        </tr>
#        <tr id='isWebkit'>
#            <td class='code'>isWebkit</td>
#            <td>Is the user using the Webkit rendering engine? (used in Safari, Chrome, et al)</td>
#        </tr>
#        <tr id='webkit'>
#            <td class='code'>webkit</td>
#            <td>Is the user using the Webkit rendering engine? (used in Safari, Chrome, et al)</td>
#        </tr>
#        <tr id='isKHTML'>
#            <td class='code'>isKHTML</td>
#            <td>Is the user using the KHTML rendering engine? (used in Konqueror prior to Apple's derived Webkit)</td>
#        </tr>
#        <tr id='khtml'>
#            <td class='code'>khtml</td>
#            <td>Is the user using the KHTML rendering engine? (used in Konqueror prior to Apple's creation of Webkit)</td>
#        </tr>
#        <tr id='isW3C'>
#            <td class='code'>isW3C</td>
#            <td>Is the user using the W3C validator?</td>
#        </tr>
#        <tr id='w3c'>
#            <td class='code'>w3c</td>
#            <td>Is the user using the W3C validator?</td>
#        </tr>
#        <tr id='isNetscape'>
#            <td class='code'>isNetscape</td>
#            <td>Is the user using the Netscape browser?</td>
#        </tr>
#        <tr id='netscape'>
#            <td class='code'>netscape</td>
#            <td>Is the user using the Netscape browser?</td>
#        </tr>
#        <tr id='isChrome'>
#            <td class='code'>isChrome</td>
#            <td>Is the user using the Chrome browser?</td>
#        </tr>
#        <tr id='chrome'>
#            <td class='code'>chrome</td>
#            <td>Is the user using the Chrome browser?</td>
#        </tr>
#        <tr id='isSafari'>
#            <td class='code'>isSafari</td>
#            <td>Is the user using the Safari browser?</td>
#        </tr>
#        <tr id='safari'>
#            <td class='code'>safari</td>
#            <td>Is the user using the Safari browser?</td>
#        </tr>
#        <tr id='useGoogleChromeFrame'>
#            <td class='code'>useGoogleChromeFrame</td>
#            <td>Will the page be rendered with Google Chrome Frame? (this is true if the framework variable <var>hGoogleChromeFrame</var> is <var>true</var>)</td>
#        </tr>
#        <tr id='isChromeFrame'>
#            <td class='code'>isChromeFrame</td>
#            <td>Is the browser capable of using the Google Chrome Frame plugin?</td>
#        </tr>
#        <tr id='vendorPrefix'>
#            <td class='code'>vendorPrefix</td>
#            <td>The vendor prefix used with CSS properties.</td>
#        </tr>
#    </tbody>
# </table>
# @end

class hUserAgent extends hPlugin {

    private $hUserAgentLib;

    public function hConstructor()
    {
        switch (true)
        {
            case isset($_GET['interfaceIdiomIsDesktop']):
            {
                $this->setUIIdiomForSession(
                    true,
                    false,
                    false,
                    false
                );

                break;
            }
            case isset($_GET['interfaceIdiomIsPad']):
            {
                $this->setUIIdiomForSession(
                    false,
                    true,
                    false,
                    false
                );

                break;
            }
            case isset($_GET['interfaceIdiomIsPhone']):
            {
                $this->setUIIdiomForSession(
                    false,
                    false,
                    true,
                    false
                );

                break;
            }
            case isset($_GET['interfaceIdiomIsTV']):
            {
                $this->setUIIdiomForSession(
                    false,
                    false,
                    false,
                    true
                );

                break;
            }
        }

        unset($_GET['interfaceIdiomIsDesktop'], $_GET['interfaceIdiomIsPad'], $_GET['interfaceIdiomIsPhone'], $_GET['interfaceIdiomIsTV']);

        if (isset($_GET['hGoogleChromeFrame']))
        {
            $GLOBALS['hFramework']->hGoogleChromeFrame = (int) $_GET['hGoogleChromeFrame'];
        }

        $userAgent = $this->library('hUser/hUserAgent');

        if (!isset($_SERVER['HTTP_USER_AGENT']))
        {
            $_SERVER['HTTP_USER_AGENT'] = 'Terminal';
        }

        $variables = $userAgent->parse($_SERVER['HTTP_USER_AGENT']);

        switch (true)
        {
            case !empty($_SESSION['interfaceIdiomIsDesktop']):
            {
                $this->forceUIIdiom(
                    $variables,
                    $userAgent,
                    true,
                    false,
                    false,
                    false
                );

                break;
            }
            case !empty($_SESSION['interfaceIdiomIsPad']):
            {
                $this->forceUIIdiom(
                    $variables,
                    $userAgent,
                    false,
                    true,
                    false,
                    false
                );

                break;
            }
            case !empty($_SESSION['interfaceIdiomIsPhone']):
            {
                $this->forceUIIdiom(
                    $variables,
                    $userAgent,
                    false,
                    false,
                    true,
                    false
                );

                break;
            }
            case !empty($_SESSION['interfaceIdiomIsTV']):
            {
                $this->forceUIIdiom(
                    $variables,
                    $userAgent,
                    false,
                    false,
                    false,
                    true
                );

                break;
            }
        }

        $this->hUserAgentOS         = $userAgent->os;
        $this->hUserAgentOSVersion  = $userAgent->osVersion;
        $this->hUserAgent           = $userAgent->browser;
        $this->hUserAgentVersion    = (float) $userAgent->browserVersion;
        $this->hUserAgentIsMobile   = $userAgent->isMobile;
        $this->hUserAgentPrefix     = $userAgent->vendorPrefix;
    }

    private function setUIIdiomForSession($desktop, $pad, $phone, $tv)
    {
        $_SESSION['interfaceIdiomIsDesktop'] = $desktop;
        $_SESSION['interfaceIdiomIsPad']     = $pad;
        $_SESSION['interfaceIdiomIsPhone']   = $phone;
        $_SESSION['interfaceIdiomIsTV']      = $tv;
    }

    private function forceUIIdiom(&$variables, &$userAgent, $desktop, $pad, $phone, $tv)
    {
        $userAgent->interfaceIdiomIsDesktop = $desktop;
        $userAgent->interfaceIdiomIsPad     = $pad;
        $userAgent->interfaceIdiomIsPhone   = $phone;
        $userAgent->interfaceIdiomIsTV      = $tv;

        $variables['interfaceIdiomIsDesktop'] = $desktop;
        $variables['interfaceIdiomIsPad']     = $pad;
        $variables['interfaceIdiomIsPhone']   = $phone;
        $variables['interfaceIdiomIsTV']      = $tv;

        switch (true)
        {
            case $desktop:
            {
                $userAgent->interfaceIdiom = 'Desktop';
                $variables['interfaceIdiom'] = 'Desktop';
                $userAgent->isMobile = false;
                $variables['isMobile'] = false;
                $userAgent->isDesktop = true;
                $variables['isDesktop'] = true;
                break;
            }
            case $pad:
            {
                $userAgent->interfaceIdiom = 'Pad';
                $variables['interfaceIdiom'] = 'Pad';
                $userAgent->isMobile = true;
                $variables['isMobile'] = true;
                $userAgent->isDesktop = false;
                $variables['isDesktop'] = false;
                break;
            }
            case $phone:
            {
                $userAgent->interfaceIdiom = 'Phone';
                $variables['interfaceIdiom'] = 'Phone';
                $userAgent->isMobile = true;
                $variables['isMobile'] = true;
                $userAgent->isDesktop = false;
                $variables['isDesktop'] = false;
                break;
            }
            case $tv:
            {
                $userAgent->interfaceIdiom = 'TV';
                $variables['interfaceIdiom'] = 'TV';
                $userAgent->isMobile = false;
                $variables['isMobile'] = false;
                $userAgent->isDesktop = false;
                $variables['isDesktop'] = false;
                break;
            }
        }
    }
}

?>