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

class hDocumentationService extends hService {

    private $hDocumentation;

    public function hConstructor()
    {
        $this->hDocumentation = $this->library('hDocumentation');
    }

    public function search()
    {
        # @return HTML

        # @description
        # <h2>Searching Documentation</h2>
        # <p>
        #    <var>search</var> is a service that allows you to easily search through documentation.
        #    The method of search is controlled using the <var>GET</var> <var>option</var> argument.  If the
        #    option is <var>'methods'</var>, methods are searched.  If no methods are found, then
        #    files are automatically searched instead.  If the <var>GET</var> <var>option</var> is <var>'files'</var>
        #    then files are searched first, and if no files are found, then methods are searched
        #    instead.
        # </p>
        # @end

        if (!isset($_GET['search']) || !isset($_GET['option']))
        {
            $this->JSON(-5);
            return;
        }

        if (empty($_GET['search']))
        {
            $_GET['search'] = 'h';
        }

        switch ($_GET['option'])
        {
            case 'methods':
            {
                $html = $this->hDocumentation->searchMethods(
                    $_GET['search']
                );

                break;
            }
            case 'files':
            default:
            {
                $html = $this->hDocumentation->searchFiles(
                    $_GET['search']
                );
            }
        }

        $this->HTML($html);
    }

    public function getMethods()
    {
        # @return HTML

        # @description
        # <h2>Getting a File's Methods</h2>
        # <p>
        #    Returns all methods associated with the supplied <var>GET</var> <var>documentationFileId</var> argument.
        # </p>
        # @end

        if (!isset($_GET['documentationFileId']))
        {
            $this->JSON(-5);
            return;
        }

        $this->HTML(
            $this->hDocumentation->getMethodsTemplate(
                (int) $_GET['documentationFileId']
            )
        );
    }

    public function getMethodArguments()
    {
        # @return HTML

        # @description
        # <h2>Getting a Method's Arguments</h2>
        # <p>
        #    Gets all of the method arguments associated with the supplied <var>GET</var> <var>methodId</var>.
        # </p>
        # @end

        if (!isset($_GET['methodId']))
        {
            $this->JSON(-5);
            return;
        }

        $this->HTML(
            $this->getTemplate(
                'Arguments',
                array(
                    'arguments' => $this->hDocumentation->getMethodArguments(
                        (int) $_GET['methodId']
                    )
                )
            )
        );
    }
}

?>