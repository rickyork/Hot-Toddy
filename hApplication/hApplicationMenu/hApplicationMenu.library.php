<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Application Menu
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

class hApplicationMenuLibrary extends hPlugin {

    public function hConstructor()
    {
        $this->getPluginFiles();
        $this->getPluginCSS('ie7');
    }

//    private $menu = array(
//        'Framework' => array(),
//        'File' => array(
//            'New','Open', '-', 'Stuff' => array('much', 'more'), 'Print'
//        ),
//        'Edit' => array(),
//        'View' => array(),
//        'Help' => array()
//    );

    public function getMenu($menu)
    {
        if (is_array($menu))
        {
            return "<div class='hApplicationMenuWrapper'><ul class='hApplicationMenu'>".$this->iterateMenuItems($menu)."</ul></div>\n";
        }
        else
        {
            // Bullox!
            $this->warning('Object passed to hApplicationMenu is not an array.', __FILE__, __LINE__);
        }
    }

    private function iterateMenuItems($menu, $recursion = false)
    {
        $html = '';

        if ($recursion)
        {
            $html .=
                "<div class='hApplicationSubMenu'>".
                    "<div class='hApplicationSubMenuTopOuter'>".
                        "<div class='hApplicationSubMenuTopMiddle'>".
                            "<div class='hApplicationSubMenuTopInner'>".
                            "</div>".
                        "</div>".
                    "</div>".
                    "<div class='hApplicationSubMenuOuter'>".
                        "<div class='hApplicationSubMenuMiddle'>".
                            "<div class='hApplicationSubMenuInner'>".
                                "<ul>";
        }

        // Drugs are bad, Mkay.
        $i = 0;

        foreach ($menu as $submenu => $item)
        {
            $html .= '<li';

            if ($item <> '-')
            {
                $html .= " id='hApplicationMenu-".$this->getId(is_array($item)? $submenu : $item)."'";
            }


            switch (true)
            {
                case $item == '-':
                {
                    $html .= " class='hApplicationMenuSeparator'";
                    break;
                }
                case (!$recursion):
                {
                    $html .= " class='hApplicationMenu".(!$i? ' hApplicationName' : '')."'";
                    break;
                }
                default:
                {
                    $html .= " class='hApplicationMenuItem'";
                }
            }

            $html .= '>';

            switch (true)
            {
                case is_array($item):
                {
                    $html .= "<span class='hApplicationMenuItem".(!$i? ' hApplicationName' : '')."'>{$submenu}</span>".$this->iterateMenuItems($item, true);
                    break;
                }
                case ($item == '-'):
                {
                    $html .= "<div class='hApplicationMenuSeparator'></div>";
                    break;
                }
                default:
                {
                    $html .= "<span class='hApplicationMenuItem'>{$item}</span>";
                }
            }

            $html .= "</li>";
            $i++;
        }

        if ($recursion)
        {
            $html .=
                                "</ul>".
                            "</div>".
                        "</div>".
                    "</div>".
                    "<div class='hApplicationSubMenuBottomOuter'>".
                        "<div class='hApplicationSubMenuBottomMiddle'>".
                            "<div class='hApplicationSubMenuBottomInner'>".
                            "</div>".
                        "</div>".
                    "</div>".
                "</div>";
        }

        return $html;
    }

    private function getId($name)
    {
        return str_replace(array('.', '-', ' '), '', $name);
    }
}

?>