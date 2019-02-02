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

class hUserSession extends hPlugin {

    public function hConstructor()
    {
        if (!isset($GLOBALS['hUserSessionLoaded']))
        {
            $GLOBALS['hUserSessionLoaded'] = true;

            // session.cookie_domain
            // session.cookie_lifetime
            session_name('sid');

            if ($this->hFileSSLEnabled(false))
            {
                $cookieDomain = $this->hFrameworkSite;
            }
            else
            {
                if (!empty($_SERVER['HTTP_HOST']))
                {
                    if (strstr($_SERVER['HTTP_HOST'], ':'))
                    {
                        $host = substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], ':'));
                    }
                    else
                    {
                        $host = $_SERVER['HTTP_HOST'];
                    }
                }
                else
                {
                    $host = $this->hFrameworkSite;
                }

                $cookieDomain = $host;
            }

            ini_set('session.gc_maxlifetime', $this->hUserSessionLifetime(7200));

            $this->hUserSessionExpires = 0;

            if (!isset($_GET['logout']))
            {
                if (!empty($_POST['hUserLoginCookie']))
                {
                    session_set_cookie_params(
                        (strtotime($this->hUserSessionCookieLifetime('+1 Year')) - time()), '/', $cookieDomain
                    );

                    $this->hUserSessionExpires = time() + 31556926;
                }
                else
                {
                    ini_set('session.cookie_domain', $cookieDomain);
                    ini_set('session.cookie_path', '/');
                }
            }

            session_set_save_handler(
                array($this, 'open'),
                array($this, 'close'),
                array($this, 'read'),
                array($this, 'write'),
                array($this, 'destroy'),
                array($this, 'garbageCollection')
            );

            register_shutdown_function('session_write_close');

            // Variables set to $_SESSION prior to session_start() will be lost upon calling session_start(),
            // So this work-around keeps those variables in the session.
            if (isset($_SESSION) && is_array($_SESSION) && count($_SESSION))
            {
                $session = $_SESSION;
            }

            session_start();

            if (isset($session) && is_array($session) && count($session))
            {
                foreach ($session as $key => $value)
                {
                    $_SESSION[$key] = $value;
                }
            }
        }
    }

    public function open($save_path, $session_name)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($userSessionId)
    {
        if (empty($userSessionId))
        {
            return null;
        }

        return hString::decodeHTML(
            $this->hUserSessions->selectColumn(
                'hUserSessionData',
                array(
                    'hUserSessionId' => $userSessionId
                )
            )
        );
    }

    public function write($userSessionId, $userSessionData)
    {
        if (empty($userSessionId))
        {
            return;
        }

        $exists = $this->hUserSessions->selectExists(
            'hUserSessionId',
            array(
                'hUserSessionId' => $userSessionId
            )
        );

        if ($exists)
        {
            $this->hUserSessions->update(
                array(
                    'hUserSessionData' => hString::encodeHTML($userSessionData),
                    'hUserSessionLastAccessed' => date('Y-m-d H:i:s')
                ),
                array(
                    'hUserSessionId' => $userSessionId
                )
            );
        }
        else
        {
            $this->hUserSessions->insert(
                array(
                    'hUserSessionId' => $userSessionId,
                    'hUserSessionData' => hString::encodeHTML($userSessionData),
                    'hUserSessionLastAccessed' => date('Y-m-d H:i:s')
                )
            );
        }

        if ($this->hUserSessionExpires)
        {
            $this->hUserSessions->update(
                array(
                    'hUserSessionExpires' => $this->hUserSessionExpires
                ),
                array(
                    'hUserSessionId' => $userSessionId
                )
            );
        }

        $this->hDatabase->close();
    }

    public function destroy($userSessionId)
    {
        if (empty($userSessionId))
        {
            return;
        }

        $this->hUserSessions->delete(
            'hUserSessionId',
            $userSessionId
        );
    }

    public function garbageCollection($lifetime)
    {
        $this->hDatabase->query(
            "DELETE
               FROM `hUserSessions`
              WHERE UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`hUserSessionLastAccessed`) > {$lifetime}
                AND (
                    `hUserSessionExpires` = 0
                 OR `hUserSessionExpires` < UNIX_TIMESTAMP(NOW())
             )"
        );
    }

}

?>
