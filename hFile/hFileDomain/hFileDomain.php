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
# @description
# <h1>Hosting Multiple Domains</h1>
# <p>
#   Hot Toddy is capable of hosting multiple domains.  Domains can be mirrors, or
#   have the appearance of completely independent sites.  The advantage of sharing
#   a single installation of Hot Toddy between multiple domains, is those multiple
#   domains will share a database and file system, making it easier for users to
#   login to multiple sites using the same credientials, and making it easier for
#   admins to distribute the same content among multiple sites, as well as to
#   propagate updates to all sites at once.
# </p>
# <p>
#   The <var>hFile/hFileDomain</var> plugin faciliates using multiple hostnames with
#   Hot Toddy.  It decides whether or not a hostname is a mirror of another hostname,
#   or whether or not it is an independent site, and allows you to define a default
#   hostname that will be used when Hot Toddy comes across a hostname it has never seen
#   before.
# </p>
# @end

class hFileDomain extends hPlugin {

    private $hJSON;

    public function hConstructor()
    {
        # @return void

        # @description
        # <h2>Sites, Domains, and Templates</h2>
        # <p>
        #
        # </p>

        if (!$this->hFrameworkConfigurationRoot)
        {
            $this->hFrameworkConfigurationRoot = '/Configuration';
        }

        if (!$this->hFrameworkConfigurationPath)
        {
            $this->hFrameworkConfigurationPath = $this->hFrameworkPath.$this->hFrameworkConfigurationRoot;
        }

        $exists = false;

        $host = '';

        if (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST']))
        {
            $host = str_replace('www.', '', $_SERVER['HTTP_HOST']);
        }

        if ($this->shellArgumentExists('site', '--site'))
        {
            $this->hFrameworkSite = $this->getShellArgumentValue('site', '--site');
            $host = str_replace('www.', '', $this->hFrameworkSite);
        }

        if ($this->shellArgumentExists('hFrameworkSite', '--hFrameworkSite'))
        {
            $this->hFrameworkSite = $this->getShellArgumentValue(
                'hFrameworkSite',
                '--hFrameworkSite'
            );

            $host = str_replace(
                'www.',
                '',
                $this->hFrameworkSite
            );
        }

        if (!empty($host))
        {
            $exists = $this->hFileDomains->selectExists(
                'hFileDomainId',
                array(
                    'hFileDomain' => $host
                )
            );
        }

        if (!$exists || $this->hServerHostIsIP(false))
        {
            $where['hFileDomainIsDefault'] = 1;
        }
        else
        {
            $where['hFileDomain'] = $host;
        }

        $domain = $this->hFileDomains->selectAssociative(
            array(
                'hFileDomainId',
                'hFileId',
                'hFrameworkSite',
                'hTemplateId'
            ),
            $where
        );

        if (isset($_SERVER['REQUEST_URI']))
        {
            @$uri = parse_url($_SERVER['REQUEST_URI']);
        }
        else
        {
            $uri['path'] = '/';
        }

        if (!isset($uri['path']))
        {
            $uri['path'] = '/';
        }

        if (count($domain))
        {
            $this->hFileDomainId = $domain['hFileDomainId'];

            if (!empty($domain['hFrameworkSite']))
            {
                $this->hFrameworkSite = $domain['hFrameworkSite'];
            }

            $this->hTemplateId = (int) $domain['hTemplateId'];

            if ($uri['path'] == '/')
            {
                $this->setPath($this->getFilePathByFileId($domain['hFileId']));
            }
        }

        // Load configurations per hostname, if they exist.
        $path = $this->hFrameworkConfigurationPath.'/'.$this->hFrameworkSite.'.json';

        if (file_exists($path))
        {
            // Something in the chain when calling $this->library('hJSON') is causing a
            // big delay in code execution.  Directly including and instantiating the
            // hJSONLibrary works around that performance hit.
            if (!class_exists('hJSONLibrary'))
            {
                include_once $this->hServerDocumentRoot.'/hJSON/hJSON.library.php';
            }

            $this->hJSON = new hJSONLibrary('/hJSON/hJSON.library.php');

            //$this->addLoadedPath('Hostname Configuration: '.$path);

            $json = $this->hJSON->getJSON($path);

            $this->setVariable('hFrameworkSiteJSON', $json);

            $this->setVariables($json);
        }

        # <h3>Framework Path Variables</h3>
        # <p>
        # <var>hFileDomain</var> defines 'path' and 'root' variables for each Hot Toddy
        # folder.  This makes it possible to configure the location for each folder.
        # </p>
        # <table>
        #   <tbody>
        #     <tr>
        #       <td class='code'>hFrameworkConfigurationRoot</td>
        #       <td class='code'>/Configuration</td>
        #       <td></td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hFrameworkConfigurationPath</td>
        #       <td class='code'>{/hFrameworkPath}{/hFrameworkConfigurationRoot}</td>
        #       <td class='code'>{hFrameworkPath}{hFrameworkConfigurationRoot}</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hFrameworkRoot</td>
        #       <td class='code'>/Hot Toddy</td>
        #       <td></td>
        #     </tr>

        if (!$this->hFrameworkRoot)
        {
            $this->hFrameworkRoot = '/Hot Toddy';
        }

        #     <tr>
        #       <td class='code'>hFrameworkApplicationRoot</td>
        #       <td class='code'>/Applications</td>
        #       <td></td>
        #     </tr>

        if (!$this->hFrameworkApplicationRoot)
        {
            $this->hFrameworkApplicationRoot = '/Applications';
        }

        #     <tr>
        #       <td class='code'>hFrameworkApplicationPath</td>
        #       <td class='code'>{/hFrameworkPath}{/hFrameworkApplicationRoot}</td>
        #       <td class='code'>{hFrameworkPath}{hFrameworkApplicationRoot}</td>
        #     </tr>

        if (!$this->hFrameworkApplicationPath)
        {
            $this->hFrameworkApplicationPath = $this->hFrameworkPath.$this->hFrameworkApplicationRoot;
        }

        #     <tr>
        #       <td class='code'>hFrameworkTemporaryRoot</td>
        #       <td class='code'>/Temporary</td>
        #       <td></td>
        #     </tr>

        if (!$this->hFrameworkTemporaryRoot)
        {
            $this->hFrameworkTemporaryRoot = '/Temporary';
        }

        #     <tr>
        #       <td class='code'>hFrameworkTemporaryPath</td>
        #       <td class='code'>{/hFrameworkPath}{/hFrameworkTemporaryRoot}</td>
        #       <td class='code'>{hFrameworkPath}{hFrameworkTemporaryRoot}</td>
        #     </tr>

        if (!$this->hFrameworkTemporaryPath)
        {
            $this->hFrameworkTemporaryPath = $this->hFrameworkPath.$this->hFrameworkTemporaryRoot;
        }

        #     <tr>
        #       <td class='code'>hFrameworkCompiledRoot</td>
        #       <td class='code'>/Compiled</td>
        #       <td></td>
        #     </tr>

        if (!$this->hFrameworkCompiledRoot)
        {
            $this->hFrameworkCompiledRoot = '/Compiled';
        }

        #     <tr>
        #       <td class='code'>hFrameworkCompiledPath</td>
        #       <td class='code'>{/hFrameworkPath}{/hFrameworkCompiledRoot}</td>
        #       <td class='code'>{hFrameworkPath}{hFrameworkCompiledRoot}</td>
        #     </tr>

        if (!$this->hFrameworkCompiledPath)
        {
            $this->hFrameworkCompiledPath = $this->hFrameworkPath.$this->hFrameworkCompiledRoot;
        }

        #     <tr>
        #       <td class='code'>hFrameworkLibraryRoot</td>
        #       <td class='code'>/Library</td>
        #       <td></td>
        #     </tr>

        if (!$this->hFrameworkLibraryRoot)
        {
            $this->hFrameworkLibraryRoot = '/Library';
        }

        #     <tr>
        #       <td class='code'>hFrameworkLibraryPath</td>
        #       <td class='code'>{/hFrameworkPath}{/hFrameworkLibraryRoot}</td>
        #       <td class='code'>{hFrameworkPath}{hFrameworkLibraryRoot}</td>
        #     </tr>

        if (!$this->hFrameworkLibraryPath)
        {
            $this->hFrameworkLibraryPath = $this->hFrameworkPath.$this->hFrameworkLibraryRoot;
        }

        #     <tr>
        #       <td class='code'>hFrameworkIconRoot</td>
        #       <td class='code'>/Icons</td>
        #       <td></td>
        #     </tr>

        if (!$this->hFrameworkIconRoot)
        {
            $this->hFrameworkIconRoot = '/Icons';
        }

        #     <tr>
        #       <td class='code'>hFrameworkIconPath</td>
        #       <td class='code'>{/hFrameworkPath}{/hFrameworkIconRoot}</td>
        #       <td class='code'>{hFrameworkPath}{hFrameworkIconRoot}</td>
        #     </tr>

        if (!$this->hFrameworkIconPath)
        {
            $this->hFrameworkIconPath = $this->hFrameworkPath.$this->hFrameworkIconRoot;
        }

        #     <tr>
        #       <td class='code'>hFrameworkPluginRoot</td>
        #       <td class='code'>/Plugins</td>
        #       <td></td>
        #     </tr>

        if (!$this->hFrameworkPluginRoot)
        {
            $this->hFrameworkPluginRoot = '/Plugins';
        }

        #     <tr>
        #       <td class='code'>hFrameworkPluginPath</td>
        #       <td class='code'>{/hFrameworkPath}{/hFrameworkPluginRoot}</td>
        #       <td class='code'>{hFrameworkPath}{hFrameworkPluginRoot}</td>
        #     </tr>

        if (!$this->hFrameworkPluginPath)
        {
            $this->hFrameworkPluginPath = $this->hFrameworkPath.$this->hFrameworkPluginRoot;
        }

        #     <tr>
        #       <td class='code'>hFrameworkFileSystemRoot</td>
        #       <td class='code'>/HtFS</td>
        #       <td></td>
        #     </tr>

        if (!$this->hFrameworkFileSystemRoot)
        {
            $this->hFrameworkFileSystemRoot = '/HtFS';
        }

        #     <tr>
        #       <td class='code'>hFrameworkFileSystemPath</td>
        #       <td class='code'>{/hFrameworkPath}{/hFrameworkFileSystemRoot}</td>
        #       <td class='code'>{hFrameworkPath}{hFrameworkFileSystemRoot}</td>
        #     </tr>

        if (!$this->hFrameworkFileSystemPath)
        {
            $this->hFrameworkFileSystemPath = $this->hFrameworkPath.$this->hFrameworkFileSystemRoot;
        }

        #     <tr>
        #       <td class='code'>hFileSystemPath</td>
        #       <td class='code'>{/hFrameworkPath}{/hFrameworkFileSystemRoot}</td>
        #       <td class='code'>{hFrameworkPath}{hFrameworkFileSystemRoot}</td>
        #     </tr>

        if (!$this->hFileSystemPath)
        {
            $this->hFileSystemPath = $this->hFrameworkPath.$this->hFrameworkFileSystemRoot;
        }

        #     <tr>
        #       <td class='code'>hFrameworkPicturesRoot</td>
        #       <td class='code'>/Template/Pictures</td>
        #       <td></td>
        #     </tr>

        if (!$this->hFrameworkPicturesRoot)
        {
            $this->hFrameworkPicturesRoot = '/Template/Pictures';
        }

        #     <tr>
        #       <td class='code'>hFrameworkPicturesPath</td>
        #       <td class='code'>{/hFrameworkPath}/Pictures</td>
        #       <td class='code'>{hFrameworkPath}/Pictures</td>
        #     </tr>

        if (!$this->hFrameworkPicturesPath)
        {
            $this->hFrameworkPicturesPath = $this->hFrameworkPath.'/Pictures';
        }

        #     <tr>
        #       <td class='code'>hDirectoryTemplatePictures</td>
        #       <td class='code'>{/hFrameworkPath}/Pictures</td>
        #       <td class='code'>{hFrameworkPath}/Pictures</td>
        #     </tr>

        if (!$this->hDirectoryTemplatePictures)
        {
            $this->hDirectoryTemplatePictures = $this->hFrameworkPath.'/Pictures';
        }

        #     <tr>
        #       <td class='code'>hFrameworkLogRoot</td>
        #       <td class='code'>/Log</td>
        #       <td></td>
        #     </tr>

        if (!$this->hFrameworkLogRoot)
        {
            $this->hFrameworkLogRoot = '/Log';
        }

        #     <tr>
        #       <td class='code'>hFrameworkLogPath</td>
        #       <td class='code'>{/hFrameworkPath}{/hFrameworkLogRoot}</td>
        #       <td class='code'>{hFrameworkPath}{hFrameworkLogRoot}</td>
        #     </tr>

        if (!$this->hFrameworkLogPath)
        {
            $this->hFrameworkLogPath = $this->hFrameworkPath.$this->hFrameworkLogRoot;
        }

        #   </tbody>
        # </table>

        # @end
    }
}

?>