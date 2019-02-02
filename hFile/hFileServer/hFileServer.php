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
# <h1>File Server API</h1>
# <p>
#   <var>hFileServer</var> provides a bridge for viewing any file on the server that the
#   web server has permission to read.
# </p>
# @end

class hFileServer extends hPlugin {

    public function hConstructor()
    {
        $path = $this->getServerFileSystemPath($this->hFileServerPath);

        $this->hFileSystemDocument = false;

        if ($this->hFileServerPath && ($this->isLoggedIn() && $this->inGroup('root') || $this->isDocumentRootPath($path)))
        {
            $this->hFileCSS = "";
            $this->hFileJavaScript = "";
            $this->hPrivatePlugin = '';

            $this->hFrameworkToolboxLoad = false;
            $this->hTemplatePath = '/hFile/hFileServer/hFileServer.template.php';

            if (file_exists($path))
            {
                if (is_readable($path))
                {
                    $this->hFileSize = filesize($path);
                    $ext = strstr($path, '.')? $this->getExtension($path) : 'txt';

                    $this->hFileMIME = exec('file -ib '.$this->hFileServerPath);

                    // Yes, feel free to add your favorite enscript supported language.
                    switch ($ext)
                    {
                        case 'php':
                        {
                            $lang = 'php';
                            break;
                        }
                        case 'js':
                        {
                            $lang = 'javascript';
                            break;
                        }
                        case 'htm':
                        case 'xml':
                        case 'html':
                        {
                            $lang = 'xml';
                            break;
                        }
                        case 'sql':
                        {
                            $lang = 'sql';
                            break;
                        }
                        case 'css':
                        {
                            $lang = 'css';
                            break;
                        }
                        case 'sql':
                        {
                            $lang = 'sql';
                            break;
                        }
                        default:
                        {
                            $lang = '';
                        }
                    }

                    if (!empty($lang))
                    {
                        if (!isset($_GET['hFileServerDownload']))
                        {
                            # This file, up until this point, is considered a straight-up file system document.
                            # That means that the file is treated like it'll be a downloaded resource. Since it
                            # is a file system document, certain APIs aren't included, since they aren't typically
                            # used with file system documents.
                            #
                            # In this case, you want to force the document to be treated like a templated
                            # HTML document, rather than a file system document.  Calling getDocumentFrameworks()
                            # will make various APIs available, such as hFile/hFileDocument, hFile/hFileBreadcrumbs,
                            # and so on.
                            $this->getDocumentFrameworks();

                            $this->getPluginFiles();

                            $file = basename($path);

                            $this->hFileDocument = $this->getTemplate(
                                'File',
                                array(
                                    'hFileServerPath' => $path,
                                    'hFileServerDownloadPath' => $this->hServerPath.'?hFileServerDownload=1',
                                    'lang' => $lang,
                                    'file' => htmlSpecialChars(
                                        file_get_contents($path)
                                    )
                                )
                            );

                            $this->hFileMIME = 'text/html';
                            $this->hFileDownload = false;
                            $this->hFileSystemDocument = false;
                            $this->hFileHTMLHeaders = true;

                            $title = $path;

                            if ($this->beginsPath($path, $this->hFrameworkPath))
                            {
                                $title = $this->getEndOfPath($path, $this->hFrameworkPath);
                            }

                            $this->hFileTitle .= ' - '.$title;
                            $this->hFileName = basename($path); #server.html';
                        }
                        else
                        {
                            $this->hTemplatePath = '';
                            $this->hFileMIME = 'text/plain';
                            $this->hFileDocument = file_get_contents($path);
                        }
                    }
                    else
                    {
                        $ext = $this->getExtension($path);

                        if (empty($ext))
                        {
                            $ext = 'txt';
                        }

                        //$this->hFileDocument       = file_get_contents($path);
                        $this->hFileSize = filesize($path);
                        $this->hFileDownload = false;
                        $this->hFileSystemDocument = true;
                        $this->hFileLastModified = filemtime($path);
                        $this->hTemplatePath = '';
                        $this->hFileName = basename($path);
                        $this->hFileSystemPath = $path;
                        $this->hFilePath = '';

                        switch ($ext)
                        {
                            case 'png':
                            {
                                $this->hFileMIME = 'image/png';
                                break;
                            }
                            case 'jpg':
                            case 'jpeg':
                            case 'jpe':
                            {
                                $this->hFileMIME = 'image/jpeg';
                                break;
                            }
                            case 'gif':
                            {
                                $this->hFileMIME = 'image/gif';
                                break;
                            }
                            case 'pdf':
                            {
                                $this->hFileMIME = 'application/pdf';
                                break;
                            }
                            case 'conf':
                            case 'txt':
                            case 'ini':
                            {
                                $this->hFileMIME = 'text/plain';
                                break;
                            }
                            default:
                            {
                                $this->hFileMIME = 'text/plain';
                            }
                        }
                    }
                }
                else
                {
                    $this->hFileDocument = "The file isn't readable.";
                }
            }
            else
            {
                $this->hFileDocument = 'The file doesn\'t exist on the server.';
            }
        }
        else
        {
            $this->notAuthorized();
        }
    }
}

?>