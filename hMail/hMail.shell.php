<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Mail Shell
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

class hMailShell extends hShell {

    private $hMail;

    public function hConstructor()
    {
        $this->hMail = $this->library('hMail');

        $site = explode('.', str_replace('www.', '', $this->hFrameworkSite));

        array_reverse($site);

        $site = implode('.', $site);

        $plist = '/Library/LaunchDaemons/'.$site.'.mail.schedule.plist';

        if ($this->shellArgumentExists('-q', 'queue'))
        {
            $this->console('Emptying mail queue');
            $this->hMail->emptyQueue();
        }
        
        if ($this->shellArgumentExists('-schedule', 'schedule'))
        {
            $this->console("Creating the mail schedule.  This process should be ran as root.");

            $minutes = 10;

            if ($this->shellArgumentExists('-m', 'minutes'))
            {
                $minutes = $this->getShellArgumentValue('-m', 'minutes');
            }
            
            $interval = $minutes * 60;

            $this->console("The mail schedule will run once every {$minutes} minutes or {$interval} seconds.");

            $xml = $this->getTemplateXML(
                'Mail Schedule',
                array(
                    'site'        => $site,
                    'pathToShell' => $this->hFrameworkPath.'/hot',
                    'interval'    => $interval
                )
            );

            file_put_contents($plist, $xml);

            $this->chown($plist, 'root');
            $this->chgrp($plist, 'wheel');
            $this->chmod($plist, 644);

            $this->console("Mail schedule created at path '{$plist}'");

            # Loading a Daemon             launchctl load /Library/LaunchDaemons/{$site}.backup.plist
            # Listing all loaded Daemons   launchctl list
            $this->command('launchctl load '.escapeShellArg($plist));
            
            $this->console("Mail schedule loaded");
        }

        if ($this->shellArgumentExists('-unschedule', 'unschedule'))
        {
            # Unloading a Daemon           launchctl unload /Library/LaunchDaemons/{$site}.backup.plist
        
            $this->console("Removing the mail schedule.  This process should be ran as root.");

            $this->command('launchctl unload '.escapeShellArg($plist));
            $this->console("Mail schedule unloaded");

            $this->rm($plist);
            $this->console("Mail schedule removed");
        }
    }
}

?>