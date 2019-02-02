<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework Update Library
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

class hFrameworkUpdateLibrary extends hPlugin {

    private $hJSON;

    public function hConstructor()
    {

    }

    public function find($installedVersion, $updateName, $folder)
    {
        $directory = opendir($folder);

        $this->console("Reading updates in folder {$folder}");

        if ($directory)
        {
            while (false !== ($file = readdir($directory)))
            {
                if ($file != '.' && $file != '..' && is_dir($folder.'/'.$file))
                {
                    // Parse versions...
                    $matches = array();

                    $this->console("Evaluating {$folder}/{$file}");

                    preg_match_all("/\d{1,}\.\d{1,}\.\d{1,}/", $file, $matches);

                    if (isset($matches[0][0]) && isset($matches[0][1]))
                    {
                        $this->console("Found {$updateName} from {$matches[0][0]} to {$matches[0][1]}");

                        if ($matches[0][0] == $installedVersion)
                        {
                            $this->console("{$matches[0][0]} == {$installedVersion}");

                            $this->console("Updating to version {$matches[0][1]}");

                            // Install the new version.
                            include $folder.'/'.$file.'/Update.php';

                            $obj = $updateName.'_'.str_replace('.', '', $matches[0][0])."To".str_replace('.', '', $matches[0][1]);

                            new $obj($folder.'/'.$file.'/Update.php');

                            if (!$this->hFrameworkUpdateTestRun(false))
                            {
                                $this->setVersion($updateName, $matches[0][1]);
                            }
                        }
                        else
                        {
                            $this->console("{$matches[0][0]} != {$installedVersion}");
                        }
                    }
                }
            }

            closedir($directory);
        }
    }

    private function setVersion($updateName, $version)
    {
        $this->hFrameworkVariables->save($updateName.'Version', $version);
    }

    public function getConfigurationFile($path)
    {
        if (!$this->hJSON)
        {
            if (!class_exists('hJSONLibrary'))
            {
                include $this->hServerDocumentRoot.'/hJSON/hJSON.library.php';
            }

            $this->hJSON = new hJSONLibrary('/hJSON/hJSON.library.php');
        }

        return $this->hJSON->getJSON($path.'.json');
    }
}


?>