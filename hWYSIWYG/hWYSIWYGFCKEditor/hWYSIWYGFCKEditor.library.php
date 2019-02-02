<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy WYSIWYG FCKEditor Library
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
# <h1>FCKEditor WYSIWYG Implementation</h1>
#
# @end

class hWYSIWYGFCKEditorLibrary extends hPlugin implements hWYSIWYG {

    private $width;
    private $height;

    private $toolbar;
    private $stylesheet;

    private $editors;

    public function &setDefaultDimensions($width, $height)
    {
        # @return hWYSIWYGFCKEditorLibrary

        # @description
        # <h2>Setting the Default Dimensions</h2>
        # <p>
        #   Sets the default dimenstions of the WYSIWYG editor to the specified <var>$width</var> and
        #   <var>$height</var>. These will be the dimensions when there are no dimensions specified
        #   by the editor's configuration.
        # </p>
        # @end

        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    public function &setDefaultToolbar($toolbar)
    {
        # @return hWYSIWYGFCKEditorLibrary

        # @description
        # <h2>Setting the Default Toolbar</h2>
        # <p>
        #   Sets the toolbar layout to the toolbar specified in <var>$toolbar</var>, this will
        #   be the default toolbar if no toolbar is specified by the editor's configuration.
        # </p>
        # @end

        $this->toolbar = $toolbar;

        return $this;
    }

    public function setDefaultStylesheet($path)
    {
        # @return hWYSIWYGFCKEditorLibrary

        # @description
        # <h2>Setting the Default Stylesheet</h2>
        # <p>
        #   Sets the default stylesheet to the path specified in <var>$path</var>, this will
        #   be the stylesheet when there is no stylesheet specified by the editor's
        #   configuration.
        # </p>
        # @end

        $this->stylesheet = $path;
    }

    public function addEditor($id, $width = 0, $height = 0, $plugins = array(), $toolbar = '', $css = '', $configuration = '')
    {
        # @return hWYSIWYGFCKEditorLibrary

        # @description
        # <h2>Adding an Editor Configuration</h2>
        # <p>
        #   Adds an editor configuration to the internal <var>$editors</var> property with the
        #   specified configuration.
        # </p>
        # @end

        if (empty($width))
        {
            $width = $this->width;
        }

        if (empty($height))
        {
            $height = $this->height;
        }

        if (empty($toolbar))
        {
            $toolbar = $this->toolbar;
        }

        if (empty($css))
        {
            $css = $this->stylesheet;
        }

        $this->editors[$id] = array(
            'plugins' => $plugins,
            'width' => $width,
            'height' => $height,
            'toolbar' => $toolbar,
            'css' => $css,
            'configuration' => $configuration
        );

        $this->plugins = array_merge($plugins);

        array_unique($this->plugins);
    }

    private function getEditorsList()
    {
        # @return array

        # @description
        # <h2>Retrieving the Editor Configurations</h2>
        # <p>
        #   Retrieves the editor configuration with any duplicates removed.
        # </p>
        # @end

        $editors = array_keys($this->editors);
        return array_unique($editors);
    }

    public function getJavaScript()
    {
        # @return HTML

        # @description
        # <h2>Retrieving Editor HTML and JavaScript</h2>
        # <p>
        #   Builds the HTML and JavaScript required to include FCKEditor in a document.
        # </p>
        # @end

        $editors = '';
        $space = "                            ";

        foreach ($this->editors as $id => $config)
        {
            $configuration = '/hWYSIWYG/hWYSIWYGFCKEditor/hWYSIWYGFCKEditor.js';

            if (!empty($this->editors[$id]['configuration']))
            {
                $configuration = $this->editors[$id]['configuration'];
            }

            $editors .=
                "\n".
                $space."this.editors['{$id}'] = new FCKeditor('{$id}');\n".
                $space."this.editors['{$id}'].Config['CustomConfigurationsPath'] = '{$configuration}';\n".
                $space."this.editors['{$id}'].BasePath = '{$this->hFrameworkLibraryRoot}/fckeditor/';\n";

            foreach ($this->editors[$id] as $config => $value)
            {
                if (!empty($value))
                {
                    switch ($config)
                    {
                        case 'plugins':
                        {
                            break;
                        }
                        case 'width':
                        {
                            $editors .=
                                $space."this.editors['{$id}'].Width = '{$value}';\n";

                            break;
                        }
                        case 'height':
                        {
                            $editors .=
                                $space."this.editors['{$id}'].Height = '{$value}';\n";

                            break;
                        }
                        case 'toolbar':
                        {
                            $editors .=
                                $space."this.editors['{$id}'].ToolbarSet = '".$this->getToolbar($value)."';\n";

                            break;
                        }
                        case 'fullpage':
                        {
                            $editors .=
                                $space."this.editors['{$id}'].FullPage = true;\n";

                            break;
                        }
                    }
                }
            }

            $editors .=
                $space."this.editors['{$id}'].ReplaceTextarea();\n";
        }

        return $this->getTemplate(
            'WYSIWYG',
            array(
                'editors' => $editors
            )
        );
    }

    public function getToolbar($set = null)
    {
        # @return string

        # @description
        # <h2>Retrieving a Toolbar</h2>
        # <p>
        #   Retrieves the toolbar preset.
        # </p>
        # @end

        switch ($set)
        {
            case 'minimum':
            {
                return 'Basic';
            }
            default:
            {
                return $set;
            }
        }
    }
}

?>