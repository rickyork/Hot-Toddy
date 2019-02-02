<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Database Structure Library
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

class hDatabaseStructureLibrary extends hPlugin {

    private $hFileUtilities;
    private $folders;

    public function hConstructor()
    {
        $this->hFileUtilities = $this->library(
            'hFile/hFileUtilities',
            array(
                'includeFileTypes' => array(),
                'excludeFolders' => array(),
                'excludeFiles' => array(),
                'includeFolders' => array(
                    'Database'
                ),
                'autoScanEnabled' => true
            )
        );

        $this->folders = $this->hFileUtilities->getIncludeFolderMatches();
    }

    public function versions($table = nil)
    {
        foreach ($this->folders as $folder)
        {
            $this->iterate(
                $folder,
                'Versions',
                $table
            );
        }
    }

    public function update($table = nil)
    {
        foreach ($this->folders as $folder)
        {
            $this->iterate(
                $folder,
                'Update',
                $table
            );
        }
    }

    public function revert($table = nil)
    {
        foreach ($this->folders as $folder)
        {
            $this->iterate(
                $folder,
                'Revert',
                $table
            );
        }
    }

    public function install($table = nil)
    {
        foreach ($this->folders as $folder)
        {
            $this->iterate(
                $folder,
                'Install',
                $table
            );
        }
    }

    public function iterate($folder, $action, $table = nil)
    {
        $this->console("Reading Database Tables in: {$folder}");

        $directory = opendir($folder);

        $continue = false;

        if ($directory)
        {
            while (false !== ($file = readdir($directory)))
            {
                if ($continue || $file == '.' || $file == '..' || $file == '.svn' || substr($file, 0, 1) == '.')
                {
                    continue;
                }

                if (is_dir($folder.'/'.$file) && (!empty($table) && $table == $file || empty($table)))
                {
                    $this->console("Reading database structure folder for: {$file}");

                    $subDirectory = opendir($folder.'/'.$file);

                    $subFiles = array();

                    while (false !== ($subFile = readdir($subDirectory)))
                    {
                        if ($file == '.' || $file == '..' || $file == '.svn' || substr($file, 0, 1) == '.')
                        {
                            continue;
                        }

                        if (is_file($folder.'/'.$file.'/'.$subFile))
                        {
                            array_push(
                                $subFiles,
                                $subFile
                            );
                        }
                    }

                    if (count($subFiles))
                    {
                        switch ($action)
                        {
                            case 'Versions':
                            {
                                # Make sure all database tables have a version number, and that that version number is logged
                                # in the database.
                                #
                                # this should probably only be ran when installing the framework.
                                if (!in_array($file.'.version.txt', $subFiles) && in_array($file.'.sql', $subFiles))
                                {
                                    $this->console("No version file exists for {$file}, creating a new one with version '1'");

                                    file_put_contents(
                                        $folder.'/'.$file.'/'.$file.'.version.txt',
                                        '1'
                                    );

                                    $version = 1;
                                }
                                else
                                {
                                    $version = file_get_contents(
                                        $folder.'/'.$file.'/'.$file.'.version.txt'
                                    );
                                }

                                $this->console("Checking that the installed version is logged.");

                                $this->insertVersion($file, $version);
                                break;
                            }
                            case 'Update':
                            {
                                $update = nil;

                                if ($this->shellArgumentExists('update', '-update'))
                                {
                                    $update = $this->getShellArgumentValue('update', '-update');
                                }

                                if ($update && $update != $file)
                                {
                                    $continue = true;
                                    break;
                                }

                                if ($this->hDatabase->tableExists($file))
                                {
                                    $this->hDatabase->query("REPAIR TABLE `{$file}`");
                                }

                                if (file_exists($folder.'/'.$file.'/'.$file.'.procedure.sql'))
                                {

                                }
                                else
                                {
                                    # Get that version number
                                    $version = (int) file_get_contents($folder.'/'.$file.'/'.$file.'.version.txt');

                                    # Figures out the update files to use.
                                    # Bugger...
                                    foreach ($subFiles as $subFile)
                                    {
                                        if ($subFile == '.' || $subFile == '..' || $subFile == '.svn' || substr($subFile, 0, 1) == '.')
                                        {
                                            continue;
                                        }

                                        $matches = array();

                                        if (preg_match('/(.*)\.update\.(\d*)\.(\d*)\.php/', $subFile, $matches) > 0)
                                        {
                                            $this->console("Found an update file for {$file}, versions {$matches[2]},{$matches[3]}");

                                            $lowerVersion  = $matches[2];
                                            $higherVersion = $matches[3];

                                            # Get the currently installed version from the database...
                                            $installedVersion = $this->hDatabaseStructure->selectColumn(
                                                'hDatabaseVersion',
                                                array(
                                                    'hDatabaseTable' => $file
                                                )
                                            );

                                            $this->console("Installed version is {$installedVersion}");

                                            if ($lowerVersion == $installedVersion)
                                            {
                                                $this->console(
                                                    "Lower version equals installed version!  Running the update script\n".
                                                    "{$folder}/{$file}/{$subFile}"
                                                );

                                                # Run this update!
                                                include $folder.'/'.$file.'/'.$subFile;

                                                $className = "hDatabaseStructureUpdate_{$file}";

                                                if (!class_exists($className))
                                                {
                                                    $className = "{$file}_{$lowerVersion}to{$higherVersion}";
                                                }

                                                if (!class_exists($className))
                                                {
                                                    $this->fatal(
                                                        "No suitable class name could be detected to update database {$file}".'.',
                                                        __FILE__,
                                                        __LINE__
                                                    );
                                                }

                                                $update = new $className(
                                                    $folder.'/'.$file.'/'.$subFile
                                                );

                                                $this->console("Updating the installed version to {$higherVersion}");

                                                # Update the version number...
                                                $this->updateVersion(
                                                    $file,
                                                    $higherVersion
                                                );
                                            }
                                        }
                                    }
                                }

                                break;
                            }
                            case 'Revert':
                            {
                                # Not implemented :-(
                                break;
                            }
                            case 'Install':
                            {
                                $install = nil;

                                if ($this->shellArgumentExists('install', '-install'))
                                {
                                    $install = $this->getShellArgumentValue('install', '-install');
                                }

                                if ($install && $install != $file)
                                {
                                    $continue = true;
                                    break;
                                }

                                # See if the table exists, if not, install it!
                                if (!$this->hDatabase->tableExists($file))
                                {
                                    if (file_exists($folder.'/'.$file.'/'.$file.'.sql'))
                                    {
                                        $this->hDatabase->query(
                                            file_get_contents($folder.'/'.$file.'/'.$file.'.sql')
                                        );

                                        $this->console("Installed database table {$file}");
                                    }
                                    else
                                    {
                                        $this->console("No SQL file exists for {$file}");
                                    }

                                    if (file_exists($folder.'/'.$file.'/'.$file.'.insert.sql'))
                                    {
                                        $this->hDatabase->query(
                                            file_get_contents($folder.'/'.$file.'/'.$file.'.insert.sql')
                                        );

                                        $this->console("Inserted data into table {$file}");
                                    }
                                    else
                                    {
                                        $this->console("No SQL INSERT file exists for {$file}");
                                    }

                                    if (file_exists($folder.'/'.$file.'/'.$file.'.install.php'))
                                    {
                                        # Run the installation script
                                        include $folder.'/'.$file.'/'.$file.'.install.php';

                                        $className = "{$file}Install";

                                        if (!class_exists($className))
                                        {
                                            $this->fatal(
                                                "Installation script '{$file}.install.php' could not be ".
                                                "instantiated because class '{$file}Install' does not exist."
                                            );
                                        }

                                        $install = new $className(
                                            $folder.'/'.$file.'/'.$file.'.install.php'
                                        );

                                        $this->console("Installed database '{$file}' from script");
                                    }
                                    else
                                    {
                                        $this->console("NO PHP installation file exists for {$file}");
                                    }

                                    if (file_exists($folder.'/'.$file.'/'.$file.'.version.txt'))
                                    {
                                        $version = file_get_contents(
                                            $folder.'/'.$file.'/'.$file.'.version.txt'
                                        );
                                    }
                                    else
                                    {
                                        $version = 1;

                                        file_put_contents(
                                            $folder.'/'.$file.'/'.$file.'.version.txt',
                                            '1'
                                        );

                                        $this->console("Created version file for {$file}");
                                    }

                                    $this->insertVersion($file, $version);
                                }

                                break;
                            }
                        }
                    }
                }
            }
        }

        closedir($directory);

        $this->console("\n");
    }

    public function insertVersion($table, $version)
    {
        $exists = $this->hDatabaseStructure->selectExists(
            'hDatabaseVersion',
            array(
                'hDatabaseTable' => $table
            )
        );

        if (!$exists)
        {
            $this->hDatabaseStructure->insert(
                array(
                    'hDatabaseTable' => $table,
                    'hDatabaseVersion' => $version
                )
            );

            $this->console("Inserted version ({$version}) for table {$table}");
        }
    }

    public function updateVersion($table, $version)
    {
        $this->hDatabaseStructure->update(
            array(
                'hDatabaseVersion' => $version
            ),
            array(
                'hDatabaseTable' => $table
            )
        );

        $this->console("Updated version ({$version}) for table {$table}");
    }
}

?>