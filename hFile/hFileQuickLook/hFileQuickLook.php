<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File Quick Look
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

class hFileQuickLook extends hPlugin {

    private $hFile;
    private $hFileIcon;

    private $qlPreview;
    private $path;
    private $type;
    private $size;
    private $width;
    private $height;

    public function hConstructor()
    {
        if (!isset($_GET['path']) || empty($_GET['path']))
        {
            $this->fatal(
                "Quick look failed because no path was provided.",
                __FILE__,
                __LINE__
            );
        }

        $this->qlPreview = $this->hFrameworkLibraryPath.'/qlpreview/qlpreview';

        if (!file_exists($this->qlPreview))
        {
            $this->fatal(
                "qlpreview is not installed to '{$this->qlPreview}'.  ".
                "The subversion checkout of Hot Toddy's 'Library' folder may need to be updated.",
                __FILE__,
                __LINE__
            );
        }

        if (!is_executable($this->qlPreview))
        {
            $this->fatal(
                "qlpreview located at '{$this->qlPreview}' is not executable.",
                __FILE__,
                __LINE__
            );
        }

        $previewFolder = $this->hFrameworkIconPath.'/Preview';

        if (!file_exists($previewFolder))
        {
            $this->mkdir($previewFolder);
        }

        if (isset($_GET['type']) && preg_match('/icon|preview/', $_GET['type']))
        {
            $this->type = $_GET['type'];
        }
        else
        {
            $this->type = 'icon';
        }

        if (isset($_GET['size']) && preg_match('/\d{1,}x\d{1,}/', $_GET['size']))
        {
            $this->size = $_GET['size'];
        }
        else
        {
            $this->size = '48x48';
        }

        list($this->width, $this->height) = explode('x', $this->size);

        hString::safelyDecodeURL($_GET['path']);

        $this->path = $_GET['path'];

        $this->hFile = $this->library('hFile');

        if (!$this->hFile->exists($this->path))
        {
            $this->notice(
                "Unable to provide a quick look for '{$this->path}' because the file or folder ".
                "referenced does not exist.",
                __FILE__,
                __LINE__
            );
        }

        if ($this->hFile->userIsReadAuthorized)
        {
            # qlpreview -imageType png -maxWidth 512 -maxHeight 512 -asIcon yes -inPath http://www.deadmarshes.com/index.html -outPath image.png
        }
        else
        {
            $this->notice(
                "Unable to provide a quick look for '{$this->path}' because the user is not ".
                "authorized to access the file.  ",
                __FILE__,
                __LINE__
            );
        }
    }
}

?>