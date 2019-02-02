<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework Backup Library
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

class hFrameworkBackupLibrary extends hPlugin {

    private $hDatabaseExport;

    public function hConstructor()
    {

    }

    public function backupDatabase()
    {


    }

    public function backup()
    {
        $this->hDatabaseExport = $this->library('hDatabase/hDatabaseExport');

        ini_set('memory_limit', -1);

        $this->console("Backup of '{$this->hFrameworkSite}' started");

        $this->hDatabaseExport->execute();

        $websiteFolder = $this->hFrameworkPath;

        $this->console("Website folder is '".$websiteFolder."'");

        $this->pipeCommand(
            '/usr/bin/zip',
            '-uqr '.escapeshellarg($websiteFolder.'.zip').' '.escapeshellarg($websiteFolder.'/')
        );

        $this->console("Backup server is '".$this->hFrameworkBackupServer."'");
        $this->console("Backup share is '".$this->hFrameworkBackupShare."'");

        if ($this->hFrameworkBackupServer && $this->hFrameworkBackupShare)
        {
            if (!file_exists('/Volumes/'.$this->hFrameworkBackupShare(nil)))
            {
                $this->mkdir('/Volumes/'.$this->hFrameworkBackupShare(nil));
            }

            $this->console("Backup protocol is '".$this->hFrameworkBackupServerProtocol('afs')."'");

            switch ($this->hFrameworkBackupServerProtocol('afs'))
            {
                case 'smb':
                {
                    // mount_smbfs '//user:pass@server/share' '/Volumes/share'
                    $this->pipeCommand(
                        '/sbin/mount_smbfs',
                        escapeshellarg(
                            '//'.
                            urlencode($this->hFrameworkBackupUser($this->hContactDirectoryAdministratorUser)).':'.
                            urlencode($this->hFrameworkBackupPassword($this->hContactDirectoryAdministratorPassword)).'@'.
                            $this->hFrameworkBackupServer(nil).'/'.
                            $this->hFrameworkBackupShare(nil)
                        ).' '.
                        escapeshellarg('/Volumes/'.$this->hFrameworkBackupShare(nil))
                    );

                    break;
                }
            }

            if (!file_exists('/Volumes/'.$this->hFrameworkBackupShare(nil)))
            {
                $this->fatal(
                    "Failed to mount share '{$this->hFrameworkBackupShare}'.",
                    __FILE__,
                    __LINE__
                );
            }

            $destination = '/Volumes/'.$this->hFrameworkBackupShare.'/'.basename($websiteFolder).'.zip';

            $this->rm($destination, true);

            $this->copy($websiteFolder.'.zip', $destination);

            $this->pipeCommand(
                '/sbin/umount',
                escapeshellarg('/Volumes/'.$this->hFrameworkBackupShare(nil))
            );
        }
        else
        {
            $this->warning(
                "Either a backup server or share was not specified.",
                __FILE__,
                __LINE__
            );
        }

        $this->console("Backup of '{$this->hFrameworkSite}' completed");

        # Loading a Daemon             launchctl load /Library/LaunchDaemons/com.hframework.backup.plist
        # Listing all loaded Daemons   launchctl list
        # Unloading a Daemon           launchctl unload /Library/LaunchDaemons/com.hframework.backup.plist
    }
}