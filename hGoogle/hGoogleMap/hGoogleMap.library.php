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
# <h1>Google Maps API</h1>
# <p>
#   Hot Toddy provides some basic support for getting started using Google Maps in a
#   Hot Toddy plugin.
# </p>
# <h2>Framework Variables</h2>
# <p>
#   To get started, configure the following in your plugin or JSON configuration file:
# </p>
# <table>
#   <thead>
#       <tr>
#           <th>Variable</th>
#           <th>Description</th>
#           <th>Default Value</th>
#           <th>Required?</th>
#       </tr>
#   </thead>
#   <tbody>
#       <tr>
#           <td class='code'>hGoogleMapKey</td>
#           <td>Key required to access the Google maps API.</td>
#           <td>null</td>
#           <td>Yes</td>
#       </tr>
#       <tr>
#           <td class='code'>hGoogleMapSelector</td>
#           <td>The element on the page you wish to turn into a Google Maps view.</td>
#           <td>div#hGoogleMap</td>
#           <td>No</td>
#       </tr>
#       <tr>
#           <td class='code'>hGoogleMapInitialLatitude</td>
#           <td>The latitude value for the location you wish to show on the map when it loads.</td>
#           <td>39.768074</td>
#           <td>No</td>
#       </tr>
#       <tr>
#           <td class='code'>hGoogleMapInitialLongitude</td>
#           <td>The longitude value for the location you wish to show on the map when it loads.</td>
#           <td>-86.15854</td>
#           <td>No</td>
#       </tr>
#       <tr>
#           <td class='code'>hGoogleMapInitialZoomLevel</td>
#           <td>An integer representing where the map is zoomed.</td>
#           <td>16</td>
#           <td>No</td>
#       </tr>
#       <tr>
#           <td class='code'>hGoogleMapSensor</td>
#           <td>
#               Possible values are "true" or "false". If "true", you are telling the Google Maps
#               application that you are using a senor (such as a GPS locator) to determine the user's
#               location.
#           </td>
#           <td>false</td>
#           <td>No</td>
#       </tr>
#   </tbody>
# </table>
# <p>
#   Once configured, and <var>hGoogleMapLibrary</var> is included in a plugin, it will automatically
#   generate HTML and JavaScript to include the Google Maps application JavaScript directly from
#   Google and display a map based on the settings you create.  Presently, Hot Toddy uses Google
#   Maps v3.
# </p>
# @end

class hGoogleMapLibrary extends hPlugin {

    public function hConstructor()
    {
        $this->getPluginJS();

        $this->hFileJavaScript .= $this->getTemplate(
            'Configuration',
            array(
                'hGoogleMapKey' => $this->hGoogleMapKey(
                    $this->hGoogleAPIKey(null)
                ),
                'hGoogleMapInitialLatitude' => $this->hGoogleMapInitialLatitude(39.768074),
                'hGoogleMapInitialLongitude' => $this->hGoogleMapInitialLongitude(-86.15854),
                'hGoogleMapInitialZoomLevel' => $this->hGoogleMapInitialZoomLevel(16),
                'hGoogleMapSensor' => $this->hGoogleMapSensor(false) ? 'true' : 'false',
                'hGoogleMapSelector' => $this->hGoogleMapSelector('div#hGoogleMap')
            )
        );
    }
}

?>