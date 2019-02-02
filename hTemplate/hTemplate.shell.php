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

class hTemplateShell extends hShell {

    private $hTemplateDatabase;

    public function hConstructor()
    {
        if ($this->shellArgumentExists('-d', '--default'))
        {
            $this->hTemplateDatabase = $this->database('hTemplate');

            $templatePath = $this->getShellArgumentValue('-d', '--default');

            echo "Updating the default template path to {$templatePath}.\n";
            
            $isPrivate = (substr($plugin, 0, 1) !== 'h');

            $this->hTemplateDatabase->save(1, '/'.$templatePath);

            echo "Template path updated!\n";

            # Create the template file, if it doesn't already exist.
            $file = ($isPrivate? $this->hFrameworkPath.$this->hFrameworkPluginRoot('/Plugins') : $this->hServerDocumentRoot).'/'.$templatePath;

            $basename = basename($file);

            $bits = explode('.', $basename);

            $templateName = array_shift($bits);

            if (!file_exists($file))
            {
                echo "Template file does not exist, creating the file at {$file}\n";
                file_put_contents(
                    $file,
                    $this->getTemplatePHP(
                        'template',
                        array(
                            'templateName' => $templateName
                        )
                    )
                );
            }

            $HTMLDir = dirname($file).'/HTML';

            if (!file_exists($HTMLDir))
            {
                echo "Making a new HTML directory at: {$HTMLDir}\n";
                `mkdir {$HTMLDir}\n`;
            }

            $HTMLFile = dirname($file).'/HTML/'.$templateName.'.html';

            if (!file_exists($HTMLFile))
            {
                echo "Making HTML template file at: {$HTMLFile}\n";

                file_put_contents(
                    $HTMLFile,
                    $this->getTemplate('template')
                );
            }

            echo "Default template successfully updated!\n";
        }
        
        if ($this->shellArgumentExists('-i', '--install'))
        {
            # Install a new template... 

        }
    }
}

?>