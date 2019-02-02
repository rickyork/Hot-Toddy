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

class hUserMacOSXLibrary extends hPlugin {

    public function create($username, $name, $uid, $gid, $password, $remoteAccess = false, $isAdmin = false)
    {
        $this->command("dscl . -create /Users/{$username}");

        // Create and set the shell property to bash.
        $this->command("dscl . -create /Users/{$username} UserShell /bin/bash");

        // Create and set the userױs full name.
        $this->command("dscl . -create /Users/{$username} RealName \"{$name}\"");

        // Create and set the userױs Id.
        $this->command("dscl . -create /Users/{$username} UniqueId {$uid}");

        // Create and set the userױs group Id property.
        $this->command("dscl . -create /Users/{$username} PrimaryGroupId {$gid}");

        // Create and set the user home directory.
        $this->command("dscl . -create /Users/{$username} NFSHomeDirectory /Users/{$username}");

        // Set the password.
        $this->command("dscl . -passwd /Users/{$username} \"{$password}\"");

        if ($isAdmin)
        {
            //If you would like Dr. Harris to be able to perform administrative functions:
            $this->command("dscl . -append /Groups/admin GroupMembership {$username}");
        }

        if ($remoteAccess)
        {
            // SSH Access
            $this->command("dscl . -append /Groups/com.apple.access_ssh GroupMembership {$username}");
        }

        $this->command("mkdir /Users/{$username}");
        $this->command("chown {$username}:staff /Users/{$username}");
    }

}

?>