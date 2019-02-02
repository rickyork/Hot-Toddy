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

interface hWYSIWYG {

    public function setDefaultDimensions($width, $height);
    // {}

    public function setDefaultToolbar($toolbar);
    // {}

    public function setDefaultStylesheet($path);
    // {}

    public function addEditor($id, $width = 0, $height = 0, $plugins = array(), $toolbar = '', $css = '', $configuration = '');
    // {}

    public function getJavaScript();
    // {}

    public function getToolbar($set = null);
    // {}
}

class hWYSIWYGLibrary extends hPlugin {

    private $plugins = array();
    private $editors = array();
    private $width   = 0;
    private $height  = 0;
    private $toolbar;
    private $stylesheet;

    private $hWYSIWYG;

    public function hConstructor()
    {
        $this->setEditor(
            $this->hWYSIWYGEditor('FCKEditor')
        );
    }

    public function &setEditor($type)
    {
        # @return hWYSIWYGLibrary

        # @description
        # <h2>Setting the WYSIWYG Editor</h2>
        # <p>
        #   Sets the WYSIWYG editor to the editor specified in <var>$type</var>.
        # </p>
        # @end

        switch ($type)
        {
            case 'FCKEditor':
            {
                $this->hWYSIWYG = $this->library('hWYSIWYG/hWYSIWYGFCKEditor');
                break;
            }
            case 'Xinha':
            {
                $this->hWYSIWYG = $this->library('hWYSIWYG/hWYSIWYGXinha');
                break;
            }
        }

        return $this;
    }

    public function &setDefaultDimensions($width, $height)
    {
        # @return hWYSIWYGLibrary

        # @description
        # <h2>Setting the Default Dimensions</h2>
        # <p>
        #   Sets the default dimensions of the editor to the specified <var>$width</var> and <var>$height</var>.
        #   These are the dimensions used when no dimensions are specified.
        # </p>
        # @end

        $this->hWYSIWYG->setDefaultDimensions($width, $height);

        return $this;
    }

    public function &setDefaultToolbar($toolbar)
    {
        # @return hWYSIWYGLibrary

        # @description
        # <h2>Setting the Default Toolbar</h2>
        # <p>
        #   Sets the default toolbar of the editor. This is the toolbar used when no toolbar is specified.
        # </p>
        # @end

        $this->hWYSIWYG->setDefaultToolbar($toolbar);

        return $this;
    }

    public function &setDefaultStylesheet($path)
    {
        $this->hWYSIWYG->setDefaultStylesheet($path);

        return $this;
    }

    public function &addEditor($id, $width = 0, $height = 0, $plugins = array(), $toolbar = '', $css = '', $configuration = '')
    {
        # @return hWYSIWYGLibrary

        # @description
        # <h2>Adding an Editor Configuration</h2>
        # <p>
        #   Adds an editor configuration.
        # </p>
        # @end

        $this->hWYSIWYG->addEditor(
            $id,
            $width,
            $height,
            $plugins,
            $toolbar,
            $css,
            $configuration
        );

        return $this;
    }

    public function getJavaScript()
    {
        # @return HTML

        # @description
        # <h2>Retrieving Editor HTML and JavaScript</h2>
        # <p>
        #   Retrieves editor HTML and JavaScript, which sets up the editor for the user.
        # </p>
        # @end

        return $this->hWYSIWYG->getJavaScript();
    }

    public function getToolbar($set = null)
    {
        # @return string

        # @description
        # <h2>Retrieving an Editor Toolbar</h2>
        # <p>
        #   Retrieves the editor toolbar.
        # </p>
        # @end

        return $this->getToolbar($set);
    }
}

?>