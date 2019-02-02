<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File Alias
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
# @description
# <h1>File Alias API</h1>
# <p>
#   Aliases are used to transparently give another file in Hot Toddy's file
#   system (HtFS) a shadow path.  The alias can work one of two ways, it can
#   redirect to the file it is added to (via HTTP 301 Moved Permanently), or
#   it can simply be another path that is used to access the file it is added to.
# </p>
# @end

class hFileAlias extends hPlugin {

    public function hConstructor()
    {
        if (!empty($GLOBALS['uri']['path']))
        {
            $alias = $GLOBALS['uri']['path'];

            if (!empty($GLOBALS['uri']['query']))
            {
                $alias .= '?'.$GLOBALS['uri']['query'];
            }

            if (!empty($GLOBALS['uri']['fragment']))
            {
                $alias .= '#'.$GLOBALS['uri']['fragment'];
            }

            $alias = hString::scrubValue($alias);

            $sql = str_replace(
                array(
                    '{alias}',
                    '{path}',
                    '{request}'
                ),
                array(
                    $alias,
                    $GLOBALS['uri']['path'],
                    $_SERVER['REQUEST_URI']
                ),
                file_get_contents(
                    dirname(__FILE__).'/SQL/queryAliases.sql'
                )
            );

            $data = $this->hDatabase->getAssociativeResults($sql);

            if (count($data))
            {
                $path = $this->getConcatenatedPath(
                    $data['hDirectoryPath'],
                    $data['hFileName']
                );

                if ($this->beginsPath($path, '/'.$this->hFrameworkSite))
                {
                    $path = $this->getEndOfPath(
                        $path,
                        '/'.$this->hFrameworkSite
                    );
                }

                if (!empty($data['hFileAliasDestination']))
                {
                    header('Location: '.$data['hFileAliasDestination']);
                    $this->fire->fileAliasToDestination($data);
                    exit;
                }
                else if (!empty($data['hFileAliasRedirect']))
                {
                    header('HTTP/1.1 301 Moved Permanently');
                    header('Location: http://'.$_SERVER['HTTP_HOST'].str_replace(' ', '+', $path));

                    $this->fire->fileAliasMovedPermanently($data);
                    exit;
                }
                else
                {
                    $this->hFileAliasPath = $alias;

                    $query = $this->hFileAliasArguments->select(
                        array(
                            'hFileAliasArgument',
                            'hFileAliasArgumentValue'
                        ),
                        array(
                            'hFileAliasId' => (int) $data['hFileAliasId']
                        )
                    );

                    foreach ($query as $data)
                    {
                        $_GET[$data['hFileAliasArgument']] = $data['hFileAliasArgumentValue'];
                    }

                    $this->setPath($path);

                    $this->fire->fileAliasShadow($data);
                }
            }
        }
    }
}

?>