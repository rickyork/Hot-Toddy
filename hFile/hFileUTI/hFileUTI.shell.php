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

class hFileUTIShell extends hPlugin {

    public function hConstructor()
    {

        if ($this->shellArgumentExists('-i', '--import'))
        {
            $path = $this->getShellArgumentValue('-i', '--import');

            if (file_exists($path))
            {
                $file = file_get_contents($path);

                $lines = explode("\n", $file);

                foreach ($lines as $line)
                {
                    $items = explode(',', $line);

                    if (!isset($items[1]))
                    {
                        $items[1] = '';
                    }

                    list($icns, $uti) = $items;

                    $icns = trim($icns);
                    $uti  = trim($uti);

                    $hFileIconId = $this->hFileMacICNS->selectColumn(
                        'hFileIconId',
                        array(
                            'hFileICNS' => str_replace("'", "''", $icns)
                        )
                    );

                    if (!$hFileIconId)
                    {
                        $this->hFileMacICNS->insert(0, str_replace("'", "''", $icns), $uti);
                        $this->console("Inserted: {$icns}");
                    }
                    else
                    {
                        $this->hFileMacICNS->update(
                            array(
                                'hFileICNS' => str_replace("'", "''", $icns),
                                'hFileUTI'  => $uti
                            ),
                            $hFileIconId
                        );

                        $this->console("Updated: {$icns}");
                    }

                    if (file_exists($icns))
                    {
                        $sourceFolder = $this->hFrameworkIconPath.'/Source';

                        if (!file_exists($sourceFolder))
                        {
                            `mkdir {$sourceFolder}`;
                        }

                        $icnsName = basename($icns);

                        $icnsBaseName = explode('.', $icnsName);
                        $icnsBaseName = array_shift($icnsBaseName);

                        $i = 0;

                        $fileName = $sourceFolder.'/'.$icnsBaseName.'.icns';

                        while (file_exists($fileName))
                        {
                            $fileName = $sourceFolder.'/'.$icnsBaseName.$i.'.icns';
                            $i++;
                        }

                        `cp "{$icns}" "{$fileName}"`;

                        $this->console("Copied to: ".$fileName);
                    }
                }
            }
        }
    }
}

?>