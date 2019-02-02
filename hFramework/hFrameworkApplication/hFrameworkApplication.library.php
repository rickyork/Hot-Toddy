<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework Application Library
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
# <h1>Framework Application Library API</h1>
# <p>
#
# </p>
# @end

class hFrameworkApplicationLibrary extends hPlugin {

    public function &prepareApplication($arguments)
    {
        # @return hFramework

        # @description
        # <h2>Prepping a New Application</h2>
        # <p>
        #   A call to <var>prepareApplication()</var> creates a blank slate for application
        #   development by resetting several key framework variables.
        # </p>
        # <table>
        #   <thead>
        #     <tr>
        #       <th>Variable</th>
        #       <th>Description</th>
        #     </tr>
        #   </thead>
        #   <tbody>
        #     <tr>
        #       <td class='code'>hFileCSS</td>
        #       <td></td>
        #     </tr>
        #     <tr>
        #        <td class='code'>hFileJavaScript</td>
        #        <td></td>
        #     </tr>
        #     <tr>
        #        <td class='code'>hFileRSS</td>
        #        <td></td>
        #     </tr>
        #     <tr>
        #        <td class='code'>hFileTitlePrepend</td>
        #        <td></td>
        #     </tr>
        #     <tr>
        #        <td class='code'>hFileTitleAppend</td>
        #        <td></td>
        #     </tr>
        #     <tr>
        #        <td class='code'>hFileDocument</td>
        #        <td></td>
        #     </tr>
        #   </tbody>
        # </table>
        # <p>
        #   Additionally, caching is disabled.
        # </p>
        # <p>
        #   The <var>hFileTitle</var> can be passed in as <var>$arguments['title']</var>.
        # </p>
        # <p>
        #   Enabling <var>hApplication/hApplicationForm</var> can be done by passing in
        #   <var>$arguments['formTemplate']</var> as <var>true</var>.
        # </p>
        # @end

        $this->hFileCSS = '';
        $this->hFileRSS = '';
        $this->hFileJavaScript = '';
        $this->hFileTitlePrepend = '';
        $this->hFileTitleAppend  = '';
        $this->hFileDocument = '';

        if (isset($arguments['title']))
        {
            $this->hFileTitle = $arguments['title'];
        }

        if (!empty($arguments['formTemplate']))
        {
            $this->plugin('hApplication/hApplicationForm');
        }

        $this->hFileDisableCache = true;
        $this->hFileEnableCache  = false;

        return $this;
    }

}

?>