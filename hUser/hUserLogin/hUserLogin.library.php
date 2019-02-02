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

interface hUserLoginFormTemplate {
    public function getLoginForm($dialogue = false);
}

class hUserLoginLibrary extends hPlugin {

    private $hUserDatabase;
    private $hUserLoginForm = nil;
    private $hUserDirectory;

    public function hConstructor()
    {
        //$this->login();
    }

    public function login($userName = nil, $userPassword = nil, $cookie = false)
    {
        # @return boolean

        # @description
        # <h2>Logging In</h2>
        # <p>
        #   This method is only executed if the user is not already logged in. Once the user
        #   is logged in, this plugin is no longer included.
        # </p>
        # <p>
        #   Credentials can be passed multiple ways, the following table outlines all the ways that
        #   credentials can be passed to attempt a login:
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td class='code'>$username, $password</td>
        #           <td>Passed in directly to the <var>login()</var> function</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>$_POST['username'], $_POST['password']</td>
        #           <td>Via POST</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>$_GET['username'], $_GET['password']</td>
        #           <td>Via GET</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>$_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']</td>
        #           <td>HTTP Authentication</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>$_GET['hUserAuthenticationToken']</td>
        #           <td>
        #               Authentication token constructed of <var>userId,userPassword</var>, where
        #               <var>userPassword</var> is the encrypted password, not the plain text
        #               password
        #           </td>
        #       </tr>
        #   </tbody>
        # </table>
        if ($this->isLoggedIn())
        {
            return false;
        }

        if (empty($userName) && empty($userPassword) && isset($_POST['hUserLoginExists']) && empty($_POST['hUserLoginExists']))
        {
            return false;
        }

        # <h3>Activating an Account</h3>
        # <p>
        #   An account can be activated by passed the confirmation token in with the
        #   userName in <var>$_GET['hUserName']</var> and <var>$_GET['hUserConfirmation']</var>.
        #   This will set the framework variable <var>hUserLoginAccountActivated</var>.
        # </p>

        if (isset($_GET['hUserName']) && isset($_GET['hUserConfirmation']))
        {
            $this->hUserDatabase = $this->database('hUser');
            $this->hUserDatabase->activateUser($_GET['hUserName'], trim($_GET['hUserConfirmation']));
            $this->hUserLoginAccountActivated = $_GET['hUserName'];
        }

        switch (true)
        {
            case (!empty($_POST['username']) && !empty($_POST['password'])):
            {
                $userName = $_POST['username'];
                $userPassword = $_POST['password'];
                break;
            }
            case (!empty($_GET['username']) && !empty($_GET['password'])):
            {
                $userName = $_GET['username'];
                $userPassword = $_GET['password'];
                break;
            }
            case (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])):
            {
                if (!$this->hFrameworkDevEnviornmentUser(nil) || $this->hFrameworkDevEnviornmentUser(nil) && !empty($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] != $this->hFrameworkDevEnviornmentUser(nil))
                {
                    $userName = $_SERVER['PHP_AUTH_USER'];
                    $userPassword = $_SERVER['PHP_AUTH_PW'];
                }
                else
                {
                    return;
                }

                break;
            }
            case !empty($_GET['hUserAuthenticationToken']):
            {
                list($userId, $userPassword) = explode(',', $_GET['hUserAuthenticationToken']);
                $userName = $this->user->getUserName($userId);
                break;
            }
            case (!empty($userName) && !empty($userPassword)):
            {
                break;
            }
            default:
            {
                if (empty($userName) && empty($userPassword))
                {
                    $GLOBALS['hUserNoLogin'] = true;
                }

                return false;
            }
        }

        # <p>
        #   When a login attempt is taking place, the framework variable <var>hUserLoginAttempt</var>
        #   is set to <var>true</var>.
        # </p>

        $this->hUserLoginAttempt = true;

        $userName = trim($userName);
        $userPassword = trim($userPassword);

        # <h3>Directory Logins</h3>
        # <p>
        #   Directory logins (Open Directory, Active Directory, local server accounts) are enabled via
        #   settings the framework variable <var>hContactDirectoryEnabled</var> to <var>true</var>.
        #   If enabled, the Mac OS X <var>/usr/bin/dscl</var> command is used to login.  The directory
        #   path is set in <var>hContactDirectoryPath</var>.
        # </p>
        # <h4>Directory Paths</h4>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td>.</td>
        #           <td>Local Server</td>
        #       </tr>
        #       <tr>
        #           <td>/127.0.0.1/LDAPv3</td>
        #           <td>Local Open Directory Server</td>
        #       </tr>
        #       <tr>
        #           <td>/www.example.net/LDAPv3</td>
        #           <td>Remote Open Directory Server</td>
        #       </tr>
        #       <tr>
        #           <td>/Active Directory/All Domains</td>
        #           <td>Active Directory Server</td>
        #       </tr>
        #   </tbody>
        # </table>
        # <h4>Successful Directory Login</h4>
        # <p>
        #   A successful directory login has nil output from the <var>dscl</var> command.
        # </p>
        # <p>
        #   A successful login passes the user's credentials to the <var>hUser/hUserDirectory</var>
        #   library, where the user's network account is synced with the user's Hot Toddy account.
        #   This syncing occurs at every login.
        # </p>

        if ($this->hContactDirectoryEnabled(false) && !empty($userName) && !empty($userPassword) && $userPassword !== $this->hFrameworkRootPassword(nil))
        {
            $networkUsername = hString::entitiesToUTF8($userName, false);
            $networkPassword = hString::entitiesToUTF8($userPassword, false);

            $command = $this->pipeCommand(
                '/usr/bin/dscl',
                escapeshellarg($this->hContactDirectoryPath('.')).' '.
                '-read '.escapeshellarg('/Users/'.$networkUsername).' RecordName',
                2
            );

            $this->console("Result of account query command: ".$command);

            if (!strstr($command, 'eDSRecordNotFound'))
            {
                $this->console("Directory account '{$userName}' exists");

                // This is a hack to force OS X to refresh Active Directory passwords when
                // passwords change.  Without this AD accounts report as 'expired' after
                // a password change.

                $this->pipeCommand(
                    '/usr/bin/dscl',
                    escapeshellarg($this->hContactDirectoryPath('.')).' '.
                    '-readall '.escapeshellarg('/Users/'.$networkUsername),
                    2
                );

                $command = trim(
                    $this->pipeCommand(
                        '/usr/bin/dscl',
                        escapeshellarg($this->hContactDirectoryPath('.')).' '.
                        '-authonly '.escapeshellarg($networkUsername).' '.escapeshellarg($networkPassword),
                        2
                    )
                );

                //if (!empty($command))
                //{
                    $userId = $this->user->getUserId($userName);

                    $this->hUserAuthenticationLog->insert(
                        array(
                            'hUserId' => $userId,
                            'hUserName' => $userName,
                            'hUserEmail' => $this->user->getUserEmail($userId),
                            //'hUserPassword' => $userPassword,
                            'hUserAuthenticationError' => $command,
                            'hUserAuthenticationTime' => mktime()
                        )
                    );

                if (!empty($command))
                {
                    $this->hUserLoginDirectoryFailureResponse = trim(str_replace('<dscl_cmd> DS Error: ', '', $command));
                    $this->loginFailed($userId, -31);
                    return false;
                }

                if (!$command)
                {
                    $this->console("Directory account '{$userName}' found to have valid credentials.");

                    $this->hUserDirectory = $this->library(
                        'hUser/hUserDirectory',
                        array(
                            'userName' => $userName,
                            'password' => $userPassword
                        )
                    );

                    # <h4>Re-assigning the User Name</h4>
                    # <p>
                    #   The <var>userName</var> can be changed in the <var>hUser/hUserDirectory</var> library, since
                    #   it's possible the user is using an alias.  The <var>userName</var> is retrieved and
                    #   re-assigned from the <var>hUser/hUserDirectory</var> library to make sure that the correct
                    #   username is being used in Hot Toddy, which officially supports only one user name, even
                    #   though it is technically possible to login using a network user name alias.
                    # </p>
                    $userName = $this->hUserDirectory->getUserName();
                }
                else
                {
                    $this->loginFailed(0, -26);
                    return false;
                }
            }
        }

        # <h4>Retrieving the User Id</h4>
        # <p>
        #   The <var>userId</var> is retrieved from the database using <var>$this-&gt;hUser-&gt;getUserId()</var>.
        #   If the retrieval fails, this means the user doesn't exist in the system and the login attempt fails.
        # </p>

        $userId = (int) $this->user->getUserId($userName); // hUserName can be either user's email address or user name

        if (empty($userId))
        {
            $userId = $this->hUserAliases->selectColumn(
                'hUserId',
                array(
                    'hUserNameAlias' => addslashes($userName)
                )
            );
        }

        if (empty($userId))
        {
            $this->loginFailed(0, -26);
            return false;
        }
        else if ($this->hUserLoginLimitFailedAttempts(true))
        {
            # <h3>Failed Login Attempt Threshold</h3>
            # <p>
            #   Failed login attempts are limited to the number specified in the framework variable
            #   <var>hUserLoginMaximumFailedAttempts</var>, the default is three, therefore, every user
            #   will be locked out of their account after just three failed attempts, although the
            #   threshold can be customized.
            # </p>

            $data = $this->hUserLog->selectAssociative(
                array(
                    'hUserFailedLoginCount',
                    'hUserLastFailedLogin'
                ),
                $userId
            );

            if (count($data))
            {
                if ((int) $data['hUserFailedLoginCount'] >= $this->hUserLoginMaximumFailedAttempts(3))
                {
                    # <h3>Failed Login Reset Timeframe</h3>
                    # <p>
                    #   When a user is locked out of their account, the lock remains in effect for the
                    #   number of minutes specified in the <var>hUserLoginFailedAttemptResetThreshold</var>
                    #   framework variable, the default is ten minutes.  So, once locked out, the lock out
                    #   remains in effect for ten minutes, unless customized.
                    # </p>

                    // If now is less than ten minutes since the last failed attempt, block the login attempt
                    if ((int) $data['hUserLastFailedLogin'] + ((int) $this->hUserLoginFailedAttemptResetThreshold(10) * 60) >= time())
                    {
                        $this->loginFailed($userId, -30);

                        $this->hUserLog->update(
                            array(
                                'hUserLastFailedLogin' => time()
                            ),
                            $userId
                        );

                        return;
                    }
                    else
                    {
                        // If now is greater than ten minutes since the 3rd failed attempt, allow login to continue
                        $this->hUserLog->update(
                            array(
                                'hUserFailedLoginCount' => 0
                            ),
                            $userId
                        );
                    }
                }
            }
            else
            {
                # <h3>User Account Log</h3>
                # <p>
                #   A log is kept in the database table <var>hUserLog</var>, which sets the following
                #   parameters.
                # </p>
                # <table>
                #   <tbody>
                #       <tr>
                #           <td>hUserLoginCount</td>
                #           <td>Number of times the user has logged in.</td>
                #       </tr>
                #       <tr>
                #           <td>hUserFailedLoginCount</td>
                #           <td>Number of times the user's login attempt failed.</td>
                #       </tr>
                #       <tr>
                #           <td>hUserCreated</td>
                #           <td>A Unix timestamp representing when the account was created.</td>
                #       </tr>
                #       <tr>
                #           <td>hUserLastLogin</td>
                #           <td>A Unix timestamp representing when the user last logged in.</td>
                #       </tr>
                #       <tr>
                #           <td>hUserLastModifiedBy</td>
                #           <td>The <var>userId</var> of the user that last modified the account.</td>
                #       </tr>
                #       <tr>
                #           <td>hUserReferredBy</td>
                #           <td>A customizable field, allows tracking of a referrer.</td>
                #       </tr>
                #       <tr>
                #           <td>hUserRegistrationTrackingId</td>
                #           <td>A customizable field, allows tracking of the registration.</td>
                #       </tr>
                #       <tr>
                #           <td>hFileId</td>
                #           <td>The fileId of the file that led to registration.</td>
                #       </tr>
                #   </tbody>
                # </table>

                $this->hUserDatabase = $this->database('hUser');
                $this->hUserDatabase->log($userId);
            }
        }

        # <h3>Default Passwords</h3>
        # <p>
        #   If the password matches the value of the framework variable <var>hUserLoginDefaultPassword</var>,
        #   the framework variable <var>hUserLoginResetPassword</var> is set to <var>true</var>.  A default
        #   password can be used to pre-create accounts for users, giving every account the same password,
        #   and upon logging in the first time, UI can be created that forces the used to set a new password.
        # </p>

        if ($this->hUserLoginDefaultPassword(nil))
        {
            if ($userPassword === $this->hUserLoginDefaultPassword)
            {
                $this->hUserLoginResetPassword = true;
            }
        }

        if (!empty($userName) && !empty($userPassword) && !empty($userId))
        {
            # <h3>Completing a Login</h3>

            $account = false;
            $login   = false;

            if (!$account && !$login)
            {
                $and = '';

                # <h4>Login By Authentication Token</h4>
                # <p>
                #   Authentication tokens may be used by desktop applications that permanently store
                #   login credentials for the website.  Using these applications, authentication never
                #   expires.  The authentication token is comprised of the <var>userId</var> and the
                #   user's encrypted password.  Successful authentication occurs after a checks to
                #   make sure the user's encrypted password passed in via <var>$_GET['hUserAuthenticationToken']</var>
                #   matches the user's encrypted password on file.
                # </p>
                if (!empty($_GET['hUserAuthenticationToken']))
                {
                    $and = array(
                        'hUserPassword' => $userPassword
                    );
                }

                # <h4>Login Using MySQL's Password Encryption</h4>
                # <p>
                #   MySQL's database encryption can be turned on by setting the framework variable
                #   <var>hUserAuthenticateDatabaseEncryption</var> to <var>true</var>, it is <var>false</var>,
                #   by default.  The default encryption used for passwords involves an md5/salt algorithm.
                # </p>
                else if ($this->hUserAuthenticateDatabaseEncryption(false))
                {
                    $and = array(
                        'hUserPassword' => "password('{$userPassword}')"
                    );
                }

                $columns = array(
                    'hUserName',
                    'hUserEmail',
                    'hUserId'
                );

                foreach ($columns as $column)
                {
                    if (count($data = $this->getUserAccount($column, $userName, $and)))
                    {
                        // A valid account exists with this username, id, or email
                        $account = true;
                        break;
                    }
                }

                if (!$account && count($data = $this->getUserAccount('hUserId', $userId, $and)))
                {
                    $account = true;
                }

                # <h4>Framework Root Password</h4>
                # <p>
                #   The framework variable <var>hFrameworkRootPassword</var> can be set to contain a
                #   wildcard password that will allow a super-user to login using any user's account.
                #   This allows you to test applications as other users.  This setting should be
                #   used only when needed and disabled when not in use to enhance security, since this
                #   password is stored as plain text in a configuration file.  <var>hFrameworkRootPassword</var>,
                #   like regular passwords is case-sensitive.
                # </p>
                if ($this->hFrameworkRootPassword && $userPassword === $this->hFrameworkRootPassword)
                {
                    $login = true;
                }
                else if (empty($and) && !$this->isMd5Password($userPassword, $data['hUserPassword']) || !empty($and) && !$account)
                {
                    $this->loginFailed($userId, -27);
                    return false;
                }
                else
                {
                    $login = true;
                }

                # <h4>User Account Activation</h4>
                # <p>
                #   User account activation is turned on by setting the framework variable <var>hUserActivation</var>
                #   to <var>true</var>, it is <var>false</var>, by default.  User account activation forces a user
                #   to verify their email address as real and working before the user will be able to access their
                #   account.
                # </p>

                if ($this->hUserActivation(false) && empty($data['hUserIsActivated']))
                {
                    // Login is valid...
                    // Email has not been verified.
                    $this->loginFailed($userId, -28);
                    return false;
                }
            }

            if ($account && $login)
            {
                # <h4>Disabled User Accounts</h4>
                # <p>
                #   User accounts can be disabled eitehr temporarily or permanently by making the user a member of
                #   the <var>Disabled User Accounts</var> group.  Once an account is diabled, the user will no
                #   longer be able to login to it, and will see a message stating that their account has been
                #   disabled by an administrator.
                # </p>

                if ($this->groupExists('Disabled User Accounts') && $this->inGroup('Disabled User Accounts', $data['hUserId'], false))
                {
                    $this->loginFailed($userId, -29);
                    return false;
                }

                # <h4>Creating $_SESSION Variables</h4>
                # <p>
                #   Upon successful login, the following <var>$_SESSION</var> variables are
                #   created.
                # </p>
                # <table>
                #   <tbody>
                #       <tr>
                #           <td>hUserId</td>
                #       </tr>
                #       <tr>
                #           <td>hUserName</td>
                #       </tr>
                #       <tr>
                #           <td>hUserEmail</td>
                #       </tr>
                #       <tr>
                #           <td>hUserPassword</td>
                #       </tr>
                #       <tr>
                #           <td>hUserConfirmation</td>
                #       </tr>
                #       <tr>
                #           <td>hUserIsActivated</td>
                #       </tr>
                #       <tr>
                #           <td>HTTP_USER_AGENT</td>
                #       </tr>
                #       <tr>
                #           <td>REMOTE_ADDR</td>
                #       </tr>
                #   </tbody>
                # </table>

                foreach ($data as $key => $value)
                {
                    $_SESSION[$key] = $value;
                }

                // Fix the type, so that the data is stored in the session as an integer.
                if ($this->hUserLog->selectExists('hUserId', (int) $_SESSION['hUserId']))
                {
                    // Log activity
                    $this->hUserLog->update(
                        array(
                            'hUserLoginCount' => 'hUserLoginCount + 1',
                            'hUserFailedLoginCount' => 0,
                            'hUserLastLogin' => time()
                        ),
                        (int) $_SESSION['hUserId']
                    );
                }
                else
                {
                    $this->hUserLog->insert(
                        array(
                            'hUserId' => (int) $_SESSION['hUserId'],
                            'hUserCreated' => time()
                        )
                    );
                }

                $_SESSION['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
                $_SESSION['REMOTE_ADDR']     = $_SERVER['REMOTE_ADDR'];

                if ($this->hUserLoginOnLogin(nil))
                {
                    $plugin = $this->plugin($this->hUserLoginOnLogin);

                    if (method_exists($plugin, 'onLogin'))
                    {
                        $plugin->onLogin((int) $_SESSION['hUserId']);
                    }
                    else
                    {
                        $this->warning(
                            'Failed to execute the onLogin event, method "onLogin" does not exist in the plugin "'.$this->hUserLoginOnLogin.'.',
                            __FILE__,
                            __LINE__
                        );
                    }
                }

                if ($this->hUserLoginRedirect(nil))
                {
                    header('Location: '.$this->hUserLoginRedirect);
                    exit;
                }
            }

            return true;
        }
        else
        {
            return false;
        }

        # @end
    }

    public function &loginFailed($userId, $code)
    {
        # @return hUserLoginLibrary

        # @description
        # <h2>When a Login Fails</h2>
        # <p>
        #   When a login fails, a number of framework variables are created.
        #   These variables are documented below:
        # </p>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>Framework Variable</th>
        #           <th>Type</th>
        #           <th>Default</th>
        #           <th>Description</th>
        #           <th>Response Code</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td class='code'>hUserLoginFailed</td>
        #           <td class='code'>boolean</td>
        #           <td class='code'>false</td>
        #           <td>Indicates if the login failed.</td>
        #           <td></td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserLoginFailureCode</td>
        #           <td class='code'>integer</td>
        #           <td class='code'>nil</td>
        #           <td>Indicates why the login failed.</td>
        #           <td></td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserLoginResetPassword</td>
        #           <td class='code'>boolean</td>
        #           <td class='code'>false</td>
        #           <td>Whether or not the user should be forced to create a new password.</td>
        #           <td></td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserLoginInvalidAccount</td>
        #           <td class='code'>boolean</td>
        #           <td class='code'>false</td>
        #           <td>
        #               Whether or not the login failed because the account was not valid
        #               (invalid <var>hUserName</var> or <var>hUserEmail</var>).
        #           </td>
        #           <td>-26</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserLoginInvalidPassword</td>
        #           <td class='code'>boolean</td>
        #           <td class='code'>false</td>
        #           <td>Whether or not login failed because the password was incorrect.</td>
        #           <td>-27</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserLoginNotActivated</td>
        #           <td class='code'>boolean</td>
        #           <td class='code'>false</td>
        #           <td>
        #               Whether or not login failed because the account is not activated and activation is
        #               required.
        #           </td>
        #           <td>-28</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserLoginDisabled</td>
        #           <td class='code'>boolean</td>
        #           <td class='code'>false</td>
        #           <td>
        #               Whether or not login failed because the account is disabled by virtue of being a
        #               member of the <i>Disabled User Accounts</i> group.
        #           </td>
        #           <td>-29</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserLoginTooManyFailedAttempts</td>
        #           <td class='code'>boolean</td>
        #           <td class='code'>false</td>
        #           <td>
        #               Whether or not login failed because there have been too many failed attempts
        #               to login.
        #           </td>
        #           <td>-30</td>
        #       </tr>
        #   </tbody>
        # </table>

        # @end

        if (!empty($userId) && $code == -27 && $this->hUserLoginLimitFailedAttempts(true))
        {
            $this->hUserLog->update(
                array(
                    'hUserFailedLoginCount' => 'hUserFailedLoginCount + 1',
                    'hUserLastFailedLogin'  => time()
                ),
                $userId
            );

            $hUserFailedLoginCount = $this->hUserLog->selectColumn('hUserFailedLoginCount', $userId);

            if ((int) $hUserFailedLoginCount == (int) $this->hUserLoginMaximumFailedAttempts(3))
            {
                $code = -30;
            }
        }

        $this->hUserLoginFailed = true;
        $this->hUserLoginFailureCode = $code;
        $this->hUserLoginResetPassword = false;

        switch ($code)
        {
            case -26:
            {
                $this->hUserLoginInvalidAccount = true;
                break;
            }
            case -27:
            {
                $this->hUserLoginInvalidPassword = true;
                break;
            }
            case -28:
            {
                $this->hUserLoginNotActivated = true;
                break;
            }
            case -29:
            {
                $this->hUserLoginDisabled = true;
                break;
            }
            case -30:
            {
                $this->hUserLoginTooManyFailedAttempts = true;
                break;
            }
            case -31:
            {
                $this->hUserLoginDirectoryCommandFailed = true;
                break;
            }
        }

        return $this;
    }

    public function isMd5Password($plain, $encrypted)
    {
        # @return boolean

        # @description
        # <h2>Determines if a Password is Md5</h2>
        # <p>
        #   Determines if the provided password in <var>$plain</var> and the
        #   encypted passworde in <var>$encypted</var> is the same string.  Returns
        #   <var>true</var> if there is a match and <var>false</var> if there isn't.
        # </p>
        # @end

        if (!empty($plain) && !empty($encrypted))
        {
            // split apart the hash / salt
            $stack = explode(':', $encrypted);

            if (count($stack) != 2)
            {
                return false;
            }

            if (md5($stack[1] . $plain) == $stack[0])
            {
                return true;
            }
        }

        return false;
    }

    public function md5EncryptPassword($plain)
    {
        # @return string

        # @description
        # <h2>Encypting a Password Using Md5</h2>
        # <p>
        #   Encrypts the provided string using an md5 algorithm.
        # </p>
        # @end

        $password = '';

        for ($i = 0; $i < 10; $i++)
        {
            $password .= mt_rand(0, 1000000000);
        }

        $salt = substr(md5($password), 0, 2);

        return md5($salt.$plain).':'.$salt;
    }

    public function encrytPassword($password)
    {
        # @return string

        # @description
        # <p>
        #   A misspelled alias of <a href='#encryptPassword'>encryptPassword()</a>
        # </p>
        # @end

        return $this->encryptPassword($password);
    }

    public function encryptPassword($password)
    {
        # @return string

        # @description
        # <h2>Encypting a Password for Use in SQL</h2>
        # <p>
        #   Returns a string suited for inclusion in an SQL query using either
        #   MySQL provided password encryption via the MySQL password() method, or
        #   a string with the password pre-encrypted using an md5 encryption algorithm.
        # </p>
        # @end

        switch (true)
        {
            case ($this->hUserAuthenticateUseDatabaseHash(false)):
            {
                return "password('{$password}')";
            }
            case ($this->hUserAuthenticateUseMD5Hash(true)):
            default:
            {
                return "'".$this->md5EncryptPassword($password)."'";
            }
        }
    }

    public function generatePassword($length = 7)
    {
        # @return string

        # @description
        # <h2>Generating a Radom Password</h2>
        # <p>
        #   <b>DEPRECATED</b> instead use <a href='/Hot Toddy/Documentation?hFramework#getRandomString'>hFramework::getRandomString()</a>
        # </p>
        # @end

        return $this->getRandomString($length);
    }

    public function getUserAccount($column, $value, $and = array())
    {
        # @return array

        # @description
        # <h2>Retrieving User Account Data</h2>
        # <p>
        #   Returns user account data
        # </p>

        $columns[$column] = $value;

        if (!empty($and))
        {
            $columns = array_merge($columns, $and);
        }

        // Login by email address or by username?
        return $this->hUsers->selectAssociative(
            array(
                'hUserId',
                'hUserName',
                'hUserEmail',
                'hUserPassword',
                'hUserConfirmation',
                'hUserIsActivated'
            ),
            $columns,
            'AND',
            nil,
            1
        );
    }

    public function getLoginForm($dialogue = false)
    {
        $this->hUserLoginForm = $this->plugin(
            $this->hUserLoginFormPlugin('hUser/hUserLogin/hUserLoginForm')
        );

        return $this->hUserLoginForm->getLoginForm($dialogue);
    }
}

?>