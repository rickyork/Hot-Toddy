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

// If this wasn't called from the command line, bail out.
if (!isset($argv) || !is_array($argv))
{
    echo "This script must be called from the command line.\n";
    exit;
}

// This script sets up the Hot Toddy database...

if (isset($dbHost) && isset($dbUser) && isset($dbPass))
{
    $link = mysql_connect($dbHost, $dbUser, $dbPass);

    mysql_query("DROP DATABASE `{$db}`");

    if (!mysql_select_db($db, $link))
    {
        mysql_query("CREATE DATABASE `{$db}` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci", $link);
        echo "Database {$db} successfully created.\n";
        mysql_select_db($db);
    }

    $folder = $installPath.'/Hot Toddy';

    $dbFiles = array();

    function getDatabaseFiles($folder)
    {
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

                if (is_dir($folder.'/'.$file))
                {
                    if ($file == 'Database')
                    {
                        $subDirectory = opendir($folder.'/'.$file);

                        while (false !== ($subFolder = readdir($subDirectory)))
                        {
                            if ($subFolder == '.' || $subFolder == '..' || $subFolder == '.svn' || substr($subFolder, 0, 1) == '.')
                            {
                                continue;
                            }

                            $subPath = $folder.'/'.$file.'/'.$subFolder;

                            if (is_dir($subPath))
                            {
                                $subDirectoryFiles = opendir($subPath);

                                while (false !== ($subFile = readdir($subDirectoryFiles)))
                                {
                                    if ($subFile == '.' || $subFile == '..' || $subFile == '.svn' || substr($subFile, 0, 1) == '.')
                                    {
                                        continue;
                                    }
                                    else if (strstr($subFile, '.sql'))
                                    {
                                        array_push($GLOBALS['dbFiles'], $subPath.'/'.$subFile);
                                    }
                                }
                            }
                        }
                    }
                    else
                    {
                        getDatabaseFiles($folder.'/'.$file);
                    }
                }
            }
        }
    }

    getDatabaseFiles($folder);

    sort($dbFiles);

    foreach ($dbFiles as $path)
    {
        if (!strstr($path, '.insert.sql'))
        {
            $query = mysql_query(file_get_contents($path), $link);

            if (!$query)
            {
                echo mysql_error()."\n";
                echo "Failed: '{$path}'\n";
            }
            else
            {
                echo "Database table ".basename($path)." created from file: '{$path}'\n";
            }
        }
    }

    foreach ($dbFiles as $path)
    {
        if (strstr($path, '.insert.sql'))
        {
            $query = mysql_query(file_get_contents($path), $link);

            if (!$query)
            {
                echo mysql_error()."\n";
                echo "Failed: '{$path}'\n";
            }
            else
            {
                echo "Database table content ".basename($path)." inserted from file: '{$path}'\n";
            }
        }
    }

//
//      var_dump($GLOBALS['dbFiles']);
//
//
//
//          if (file_exists($folder) && is_dir($folder))
//          {
//              if ($dh = opendir($folder))
//              {
//                  while (($file = readdir($dh)) !== false)
//                  {
//                      if ($file == '.' || $file == '..' || $file == '.svn')
//                      {
//                          continue;
//                      }
//
//                      $sql    = $folder.'/'.$file.'/'.$file.'.sql';
//                      $insert = $folder.'/'.$file.'/'.$file.'.insert.sql';
//
//                      // Building initial database tables
//                      if (file_exists($sql))
//                      {
//                          $query = mysql_query(file_get_contents($sql), $link);
//
//                          if (!$query)
//                          {
//                              echo mysql_error()."\n";
//                          }
//                          else
//                          {
//                              echo "Database Table {$file} imported from file: {$sql}\n";
//                          }
//                      }
//
//                      if (file_exists($insert))
//                      {
//                          $query = mysql_query(file_get_contents($insert), $link);
//
//                          if (!$query)
//                          {
//                              echo mysql_error()."\n";
//                          }
//                          else
//                          {
//                              echo "Database Table {$file} imported from file: {$insert}\n";
//                          }
//                      }
//                  }
//              }

        // Now check that those tables are really there.
        $query = mysql_query("SHOW TABLES FROM `{$db}`");

        echo "Verifying database table creation...\n";

        $tables = array();

        while ($data = mysql_fetch_array($query, MYSQL_NUM))
        {
            $tables[] = $data[0];

            echo "Table {$data[0]} successfully created!\n";
        }

        if (!count($tables))
        {
            echo "Error: No tables successfully created!\n";
        }
/*
        $this->hFileDomains->insert(
            array(
                'hFileDomainId'        => null,
                'hFileDomain'          => str_replace('www.', '', $this->hServerHost),
                'hFileId'              => $this->getFileIdByFilePath('/'.$this->hFrameworkSite.'/index.html'),
                'hFrameworkSite'       => $this->hFrameworkSite,
                'hFileDomainIsDefault' => 1
            )
        );
*/
        mysql_query(
            "INSERT INTO `hFileDomains` (
                `hFileDomainId`,
                `hFileDomain`,
                `hFileId`,
                `hFrameworkSite`,
                `hFileDomainIsDefault`
            ) VALUES (
                null,
                '{$hostname}',
                1,
                '{$frameworkSite}',
                1
            )"
        );

/*
    }
    else
    {
        echo "Error: Unable to open the folder {$folder}, either it does not exist or it is not a folder.\n";
    }
*/
}
else
{
    echo "Error: One or more database configurations are unspecified.\n";
}

?>