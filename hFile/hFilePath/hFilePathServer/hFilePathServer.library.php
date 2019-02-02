<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File Path Server Library
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
# <h1>File Path Server API</h1>
# @end

class hFilePathServerLibrary extends hPlugin {

    public function isServerPath($path, $exactMatch = false)
    {
        # @return boolean

        # @description
        # <h2>Determining a Server Path</h2>
        # <p>
        #   In Hot Toddy a server path refers to a path to a file that resides outside of HtFS,
        #   Hot Toddy's database-driven virtual file system.  Hot Toddy creates several
        #   HtFS folders that are used to point to real folders in the server's file system.
        # </p>
        # <p>
        #   The optional argument <var>$exactMatch</var> is set to toggle whether or not
        #   the supplied path should match a server path exactly, rather than simply being a
        #   location within a server path.
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td class='code'>/System/Server</td>
        #           <td>Server Root, the entire server file system can be accessed from here.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>/System/Framework</td>
        #           <td>The framework installation folder, <var>hFrameworkPath</var></td>
        #       </tr>
        #       <tr>
        #           <td class='code'>/System/Documents</td>
        #           <td>?</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>/Template/Pictures</td>
        #           <td>The <var>Picutres</var> folder located in <var>hFrameworkPath</var></td>
        #       </tr>
        #       <tr>
        #           <td class='code'>/Library</td>
        #           <td>The root folder for third-party applications, scripts, and so on, <var>hFrameworkLibraryRoot</var></td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        if (!$exactMatch)
        {
            return $this->beginsPath(
                $path,
                array(
                    '/System/Server',
                    '/System/Framework',
                    '/System/Documents',
                    $this->hFrameworkPicturesRoot,
                    $this->hFrameworkLibraryRoot
                )
            );
        }
        else
        {
            return in_array(
                $path,
                array(
                    '/System/Server',
                    '/System/Framework',
                    '/System/Documents',
                    $this->hFrameworkPicturesRoot,
                    $this->hFrameworkLibraryRoot
                )
            );
        }
    }

    public function getVirtualFileSystemPath($path)
    {
        # @return string

        # @description
        # <h2>Get Virtual File System Path</h2>
        # <p>
        #   Files that exist on the server's file system can be output through Hot Toddy.
        #   To do this, each server path is prefixed with a reserved dynamic folder that
        #   exists in HtFS.  These dynamic folders are used so that Hot Toddy is able to
        #   know when a given path points to a file on the server's file system, rather than
        #   within HtFS.
        # </p>
        # <p>
        #   The method <var>getVirtualFileSystemPath()</var> is used to translate a real server
        #   path into a dynamic HtFS file path.
        # </p>
        # <p>
        #   The following table provides examples of server paths based on a Hot Toddy
        #   installation that resides in <var>/Websites/www.example.com</var>.
        # </p>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>Server Path</th>
        #           <th>HtFS Path(s)</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td class='code'>/Websites/www.example.com/Pictures</td>
        #           <td class='code'>/Template/Pictures</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>/Websites/www.example.com/Library</td>
        #           <td class='code'>/Library</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>/Websites/www.example.com</td>
        #           <td class='code'>/System/Framework</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>/Websites/www.example.com/HtFS</td>
        #           <td><var>/</var><br /><var>/System/Documents</var></td>
        #       </tr>
        #       <tr>
        #           <td class='code'>/</td>
        #           <td class='code'>/System/Server</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        switch (true)
        {
            case $this->beginsPath($path, $this->hFrameworkPicturesPath):
            {
                return $this->hFrameworkPicturesRoot.$this->getEndOfPath($path, $this->hFrameworkPicturesPath);
            }
            case $this->beginsPath($path, $this->hFrameworkLibraryPath):
            {
                return $this->hFrameworkLibraryRoot.$this->getEndOfPath($path, $this->hFrameworkLibraryPath);
            }
            case $this->beginsPath($path, $this->hFrameworkPath):
            {
                return '/System/Framework'.$this->getEndOfPath($path, $this->hFrameworkPath);
            }
            case $this->beginsPath($path, $this->hFileSystemPath):
            {
                return '/System/Documents'.$this->getEndOfPath($path, $this->hFileSystemPath);
            }
            default:
            {
                return '/System/Server'.$path;
            }
        }
    }

    public function getServerFileSystemPath($path)
    {
        # @return string

        # @description
        # <h2>Get Server File System Path</h2>
        # <p>
        #   See: <a href='#getVirtualFileSystemPath'>getVirtualFileSystemPath()</a>
        # </p>
        # <p>
        #   Produces the opposite result of <var>getVirtualFileSystemPath()</var>, it takes a
        #   virtual file system path and returns a real server file system path.
        # </p>
        # @end

        switch (true)
        {
            case $this->beginsPath($path, '/System/Server'):
            {
                $path = $this->getEndOfPath($path, '/System/Server');

                if (empty($path))
                {
                    $path = '/';
                }

                return $path;
            }
            case $this->beginsPath($path, '/System/Documents'):
            {
                return $this->hFileSystemPath.$this->getEndOfPath($path, '/System/Documents');
            }
            case $this->beginsPath($path, '/System/Framework'):
            {
                return $this->hFrameworkPath.$this->getEndOfPath($path, '/System/Framework');
            }
            case $this->beginsPath($path, $this->hFrameworkPicturesRoot):
            {
                return $this->hFrameworkPicturesPath.$this->getEndOfPath($path, $this->hFrameworkPicturesRoot);
            }
            case $this->beginsPath($path, $this->hFrameworkLibraryRoot):
            {
                return $this->hFrameworkLibraryPath.$this->getEndOfPath($path, $this->hFrameworkLibraryRoot);
            }
            default:
            {
                return $path;
            }
        }
    }
}

?>