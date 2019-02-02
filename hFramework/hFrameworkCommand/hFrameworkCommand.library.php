<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework Command
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
# <h1>Framework Command API</h1>
# <p>
#    This object provides command-line APIs.  This API is automatically included and fused
#    with the global framework API.  All methods includes in this object are available
#    globally.
# </p>
# <p>
#    Methods in this object are included on-demand, meaning this object is not fused
#    with the global framework API until a method within it is called upon, when a method
#    within this object is called, this plugin is immediately included.
# </p>
# @end

class hFrameworkCommandLibrary extends hPlugin {

    public function command($command)
    {
        # @return string

        # @description
        # <h2>Executing a Command</h2>
        # <p>
        #   A simple way of executing a command is to use the <var>command()</var> method.
        #   If the return value of the command (its exit code) is greater than zero, the
        #   exit code will be logged in Hot Toddy's error console as a warning.
        # </p>
        # @end

        $output = array();
        $returnValue = 0;
        $rtn = '';

        $rtn = exec(
            $command,
            $output,
            $returnValue
        );

        if ((int) $returnValue > 0)
        {
            $this->verbose(
                "Command '{$command}' exited with status '{$returnValue}'.",
                __FILE__,
                __LINE__
            );
        }

        return $rtn;
    }

    public function pipeCommand($command, $arguments, $returnPipe = 1, $errorReporting = true)
    {
        # @return void | string
        # <p>
        #   Returns either <var>stdout</var> or <var>stderr</var> depending on which <var>$returnPipe</var>
        #   is specified.
        # </p>
        # @end

        # @description
        # <h2>Piping a Command</h2>
        # <p>
        #   A call to <var>pipeCommand()</var> allows you to verify that a given command is
        #   executable.  The string passed in the argument <var>$command</var> must be an
        #   absolute path to the command you wish to execute.
        # </p>
        # <p>
        #   Arguments for the command are passed in the <var>$arguments</var> argument.
        # </p>
        # <p>
        #   You can choose whether you'd like to return the content of <var>stdout</var> or
        #   <var>stderr</var>.  To do so you provide 1 for <var>stdout</var> and 2 for <var>stderr</var>
        #   in the <var>$returnPipe</var> argument, the default is <var>stdout</var>.
        # </p>
        # <p>
        #   Finally, whether or not you want errors written to <var>stderr</var> to be reported in
        #   the error console, can be controlled with the <var>$errorReporting</var> argument,
        #   which is <var>true</var>, by default.  When <var>$errorReporting</var> is <var>true</var>,
        #   the return value of the command is also written to the error console.
        # </p>
        # @end
        if (is_executable($command))
        {
            $descriptor = array(
                0 => array('pipe', 'r'),  // stdin is a pipe that the child will read from
                1 => array('pipe', 'w'),  // stdout is a pipe that the child will write to
                2 => array('pipe', 'w')   // stderr is a pipe that the child will write to
            );

            $pipes = array();

            $process = proc_open(
                $command.' '.$arguments,
                $descriptor,
                $pipes
            );

            if (is_resource($process))
            {
                // $pipes now looks like this:
                // 0 => writable handle connected to child stdin
                // 1 => readable handle connected to child stdout
                // 2 => readable handle connected to child stderr

                // fwrite($pipes[0], '');
                fclose($pipes[0]);

                $output = stream_get_contents($pipes[1]);
                fclose($pipes[1]);

                $stderr = stream_get_contents($pipes[2]);
                fclose($pipes[2]);

                // It is important that you close any pipes before calling
                // proc_close in order to avoid a deadlock

                $return = proc_close($process);

                if ($errorReporting)
                {
                    if ($stderr)
                    {
                        $this->verbose("Command '{$command}' wrote to stderr:\n".$stderr);
                    }

                    $this->verbose(
                        "Command '{$command}' exited with return value '{$return}'.",
                        __FILE__,
                        __LINE__
                    );
                }
            }

            switch ($returnPipe)
            {
                case 0:
                {
                    return $return;
                }
                case 2:
                {
                    return $stderr;
                }
                case 1:
                default:
                {
                    return $output;
                }
            }
        }
        else
        {
            $this->warning(
                "Command '{$command}' is not executable.",
                __FILE__,
                __LINE__
            );
        }
    }

    public function &rename($source, $destination)
    {
        # @return hFilePath

        # @description
        # <h2>Renaming Files and Folders</h2>
        # <p>
        #   Creates the folder specified in <var>$path</var>.
        # </p>
        # <p>
        #   This function will use the PHP function <var>rename()</var>
        #   if the framework variable <var>hFilePHPFunctions</var>
        #   is <var>true</var>.  Whether or not <var>hFilePHPFunctions</var> is
        #   <var>true</var>, by default, depends on the value of the PHP ini
        #   <var>safe_mode</var> setting. If <var>safe_mode</var> is off,
        #   this method will instead use the unix <var>mv</var> command,
        #   by default.
        # </p>
        # @end

        if ($this->hFilePHPFunctions(ini_get('safe_mode')))
        {
            if (!rename($source, $destination))
            {
                $this->warning(
                    "Rename failed: {$source} to {$destination}".'.',
                    __FILE__,
                    __LINE__
                );
            }
        }
        else
        {
            $this->command('mv '.escapeshellarg($source).' '.escapeshellarg($destination));
        }

        return $this;
    }

    public function &mkdir($path)
    {
        # @return hFilePath

        # @description
        # <h2>Creating Folders</h2>
        # <p>
        #   Creates the folder specified in <var>$path</var>.
        # </p>
        # <p>
        #   This function will use the PHP function <var>mkdir()</var>
        #   if the framework variable <var>hFilePHPFunctions</var>
        #   is <var>true</var>.  Whether or not <var>hFilePHPFunctions</var> is
        #   <var>true</var>, by default, depends on the value of the PHP ini
        #   <var>safe_mode</var> setting. If <var>safe_mode</var> is off,
        #   this method will instead use the unix <var>chmod</var> command,
        #   by default.
        # </p>
        # @end

        if ($this->hFilePHPFunctions(ini_get('safe_mode')))
        {
            if (!mkdir($path))
            {
                $this->warning('mkdir failed.', __FILE__, __LINE__);
            }
        }
        else
        {
            $this->command('mkdir '.escapeshellarg($path));
        }

        return $this;
    }

    public function &chmod($path, $mode, $recursive = false)
    {
        # @return hFilePath

        # @description
        # <h2>Modifying File and Folder Modes</h2>
        # <p>
        #     Modifies the mode of the file to <var>$mode</var> specified
        #     in <var>$path</var>.  Optionally, the <var>$recursive</var> argument
        #     can be set to recursively modify sub folders in a directory tree.
        # </p>
        # <p>
        #     This function will use the PHP function <var>chmod()</var>
        #     if the framework variable <var>hFilePHPFunctions</var>
        #     is <var>true</var>.  Whether or not <var>hFilePHPFunctions</var> is
        #     <var>true</var>, by default, depends on the value of the PHP ini
        #     <var>safe_mode</var> setting. If <var>safe_mode</var> is off,
        #     this method will instead use the unix <var>chmod</var> command,
        #     by default.
        # </p>
        # <p class='hDocumentationWarning'>
        #     <b>Warning:</b> Changing the mode of a file or folder will likely
        #     require super-user privileges.
        # </p>
        # @end

        if ($this->hFilePHPFunctions(ini_get('safe_mode')))
        {
            if (!chmod($path, $mode))
            {
                $this->warning('chmod failed.', __FILE__, __LINE__);
            }
        }
        else
        {
            $this->command('chmod '.($recursive? '-R ' : '').$mode.' '.escapeshellarg($path));
        }

        return $this;
    }

    public function &chown($path, $user, $recursive = false)
    {
        # @return hFilePath

        # @description
        # <h2>Modifying User Ownership</h2>
        # <p>
        #     Modifies the user owner of the file to <var>$user</var> specified
        #     in <var>$path</var>.  Optionally, the <var>$recursive</var> argument
        #     can be set to recursively modify sub folders in a directory tree.
        # </p>
        # <p>
        #     This function will use the PHP function <var>chown()</var>
        #     if the framework variable <var>hFilePHPFunctions</var>
        #     is <var>true</var>.  Whether or not <var>hFilePHPFunctions</var> is
        #     <var>true</var>, by default, depends on the value of the PHP ini
        #     <var>safe_mode</var> setting. If <var>safe_mode</var> is off,
        #     this method will instead use the unix <var>chown</var> command,
        #     by default.
        # </p>
        # <p class='hDocumentationWarning'>
        #     <b>Warning:</b> Changing the user owner of a file or folder will
        #     likely require super-user privileges.
        # </p>
        # @end

        if ($this->hFilePHPFunctions(ini_get('safe_mode')))
        {
            if (!chown($path, $user))
            {
                $this->warning('chown failed.', __FILE__, __LINE__);
            }
        }
        else
        {
            $this->command('chown '.($recursive? '-R ' : '').escapeshellarg($user).' '.escapeshellarg($path));
        }

        return $this;
    }

    public function &chgrp($path, $group, $recursive = false)
    {
        # @return hFilePath

        # @description
        # <h2>Modifying Group Ownership</h2>
        # <p>
        #     Modifies the group owner of the file to <var>$group</var> specified
        #     in <var>$path</var>.  Optionally, the <var>$recursive</var> argument
        #     can be set to recursively modify sub folders in a directory tree.
        # </p>
        # <p>
        #     This function will use the PHP function <var>chgrp()</var>
        #     if the framework variable <var>hFilePHPFunctions</var>
        #     is <var>true</var>.  Whether or not <var>hFilePHPFunctions</var> is
        #     <var>true</var>, by default, depends on the value of the PHP ini
        #     <var>safe_mode</var> setting. If <var>safe_mode</var> is off,
        #     this method will instead use the unix <var>chgrp</var> command,
        #     by default.
        # </p>
        # <p class='hDocumentationWarning'>
        #     <b>Warning:</b> Changing the group on a file or folder will likely
        #     require super-user privileges.
        # </p>
        # @end

        if ($this->hFilePHPFunctions(ini_get('safe_mode')))
        {
            if (!chgrp($path, $group))
            {
                $this->warning('chgrp failed.', __FILE__, __LINE__);
            }
        }
        else
        {
            $this->command('chgrp '.($recursive? '-R ' : '').escapeshellarg($group).' '.escapeshellarg($path));
        }

        return $this;
    }

    public function &touch($path)
    {
        # @return hFilePath

        # @description
        # <h2>Touching Files</h2>
        # <p>
        #     Creates a the file specified in <var>$path</var> by touch.
        # </p>
        # <p>
        #     This function will use the PHP function <var>touch()</var>
        #     if the framework variable <var>hFilePHPFunctions</var>
        #     is <var>true</var>.  Whether or not <var>hFilePHPFunctions</var> is
        #     <var>true</var>, by default, depends on the value of the PHP ini
        #     <var>safe_mode</var> setting. If <var>safe_mode</var> is off,
        #     this method will instead use the unix <var>touch</var> command,
        #     by default.
        # </p>
        # @end

        if ($this->hFilePHPFunctions(ini_get('safe_mode')))
        {
            if (!touch($path))
            {
                $this->warning('touch failed.', __FILE__, __LINE__);
            }
        }
        else
        {
            $this->command('touch '.escapeshellarg($path));
        }

        return $this;
    }

    public function &copy($source, $destination, $isFolder = false)
    {
        # @return hFilePath

        # @description
        # <h2>Copying Files and Folders</h2>
        # <p>
        #     Copies a file or folder from <var>$source</var> to <var>$destination</var>.
        #     Specify whether the item being copied is a folder in the <var>$isFolder</var>
        #     argument.
        # </p>
        # <p>
        #     This function will use the PHP function <var>copy()</var>
        #     if the framework variable <var>hFilePHPFunctions</var>
        #     is <var>true</var>.  Whether or not <var>hFilePHPFunctions</var> is
        #     <var>true</var>, by default, depends on the value of the PHP ini
        #     <var>safe_mode</var> setting. If <var>safe_mode</var> is off,
        #     this method will instead use the unix <var>cp</var> command,
        #     by default.
        # </p>
        # @end

        if ($this->hFilePHPFunctions(ini_get('safe_mode')))
        {
            if (!copy($source, $destination))
            {
                $this->warning("Copy failed: {$source} to {$destination}", __FILE__, __LINE__);
            }
        }
        else
        {
            $this->command('cp -'.($isFolder? 'Rf' : 'f').' '.escapeshellarg($source).' '.escapeshellarg($destination));
        }

        return $this;
    }

    public function &move($source, $destination)
    {
        # @return hFilePath

        # @description
        # <h2>Moving Files and Folders</h2>
        # <p>
        #     Moves a file or folder from <var>$source</var> to <var>$destination</var>.
        # </p>
        # <p>
        #     This function will use the PHP function <var>rename()</var>
        #     if the framework variable <var>hFilePHPFunctions</var>
        #     is <var>true</var>.  Whether or not <var>hFilePHPFunctions</var> is
        #     <var>true</var>, by default, depends on the value of the PHP ini
        #     <var>safe_mode</var> setting. If <var>safe_mode</var> is off,
        #     this method will instead use the unix <var>mv</var> command,
        #     by default.
        # </p>
        # @end

        if ($this->hFilePHPFunctions(ini_get('safe_mode')))
        {
            if (!rename($source, $destination))
            {
                $this->warning("Copy failed: {$source} to {$destination}", __FILE__, __LINE__);
            }
        }
        else
        {
            $this->command('mv -f '.escapeshellarg($source).' '.escapeshellarg($destination));
        }

        return $this;
    }

    public function getMIMEType($path)
    {
        # @return string

        # @description
        # <h2>Get MIME Types</h2>
        # <p>
        #    This method will return the MIME type for the file specified in <var>$path</var>.
        #    This method uses the Unix <var>file</var> command with the <var>--mime-type</var>
        #    switch. The MIME type returned by the <var>file</var> command will be returned
        #    by this function, <var>getMIMEType()</var>.
        # </p>
        # @end

        $result = $this->command('file --mime-type '.escapeshellarg($path));
        $result = explode(':', $result);
        return trim(array_pop($result));
    }

    public function hot($arguments)
    {
        # @return string

        # @description
        # <h2>Executing Hot Toddy Commands</h2>
        # <p>
        #    Executes a Hot Toddy command by supplying arguments to the <var>hot</var>
        #    shell script.  <var>$arguments</var> are expected to already be escaped for
        #    command line execution.
        # </p>
        # @end

        $string = '';

        if (is_array($arguments))
        {
            foreach ($arguments as &$argument)
            {
                $argument = escapeshellarg($argument);
            }

            $string = implode(' ', $arguments);
        }
        else
        {
            $string = $arguments;
        }

        $php = escapeshellarg($this->hFrameworkPathToPHP('/usr/bin/php'));
        $frameworkPath = escapeshellarg($this->hFrameworkPath.'/hot');

        return `{$php} {$frameworkPath} {$string}`;
    }

    public function &rm($path, $isFile = false)
    {
        # @return hFilePath

        # @description
        # <h2>Deleting Files and Folders</h2>
        # <p>
        #    The <var>rm()</var> method can be used to delete files or folders
        #    from the server's file system. Specify whether or not the item to
        #    be deleted is a file using the <var>$isFile</var> argument, which
        #    is <var>false</var>, by default.
        # </p>
        # <p>
        #    This function will use the PHP functions <var>unlink()</var> and
        #    <var>rmdir()</var> if the framework variable <var>hFilePHPFunctions</var>
        #    is <var>true</var>.  Whether or not <var>hFilePHPFunctions</var> is
        #    <var>true</var> depends on the value of the PHP ini <var>safe_mode</var>
        #    setting.  If <var>safe_mode</var> is off, this method will instead use the
        #    unix <var>rm</var> command, by default.
        # </p>
        # @end

        if ($this->hFilePHPFunctions(ini_get('safe_mode')))
        {
            if ($isFile)
            {
                if (!unlink($path))
                {
                    $this->warning("Delete file failed: {$path}", __FILE__, __LINE__);
                }
            }
            else if (!rmdir($path))
            {
                $this->warning("Delete directory failed: {$path}", __FILE__, __LINE__);
            }
        }
        else
        {
            $this->command('rm -f'.($isFile? '' : 'r').' '.escapeshellarg($path));
        }

        return $this;
    }
}

?>