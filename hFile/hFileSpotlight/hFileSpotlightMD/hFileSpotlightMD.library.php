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
# <h1>File Spotlight Metadata API</h1>
# <p>
#
# </p>
# @end

class hFileSpotlightMDLibrary extends hPlugin {

    public function hConstructor()
    {

    }

    public function get($path)
    {
        # @return array

        # @description
        # <h2>Using Mac OS X's Spotlight to Retrieve File Meta Data</h2>
        # <p>
        #    This method queries Mac OS X Spotlight for file meta data using the <var>mdls</var> command,
        #    the returned data is parsed and an array is created.  In order to use Spotlight,
        #    all folders must be visible (the name cannot be preceded with a dot).
        # </p>
        # <h3>Returned Data</h3>
        # <p>
        #    Following is a sample of data returned by the <var>mdls</var> command.  The data
        #    listed is a broad sampling, but there may be other fields returned.  To see
        #    all data returned by the <var>mdls</var> command, open a Terminal in Mac OS X and
        #    type <var>mdls <i>path</i></var>.
        # </p>
        # <table>
        #    <tbody>
        #        <tr>
        #            <td class='code'>Album</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>AudioBitRate</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>AudioChannelCount</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>AudioEncodingApplication</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>AudioSampleRate</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>AudioTrackNumber</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>Authors</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>BitsPerSample</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>Codecs</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>ColorSpace</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>Composer</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>ContentAccessedDate</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>ContentCreationDate</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>ContentModifiedDate</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>ContentModificationDate</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>ContentType</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>ContentTypeTree</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>Creator</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>DateAdded</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>DisplayName</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>DurationSeconds</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>EncodingApplications</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>Fonts</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>DurationSeconds</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>FSContentChangeDate</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>FSCreationDate</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>FSCreatorCode</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>FSFinderFlags</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>FSHasCustomIcon</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>FSInvisible</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>FSIsExtensionHidden</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>FSIsStationery</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>FSLabel</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>FSName</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>FSNodeCount</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>FSOwnerGroupId</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>FSOwnerUserId</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>FSSize</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>FSTypeCode</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>HasAlphaChannel</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>Keywords</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>Kind</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>LastUsedDate</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>LogicalSize</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>Orientation</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>MediaTypes</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>MusicalGenre</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>NumberOfPages</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>PageHeight</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>PageWidth</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>PhysicalSize</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>PixelCount</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>PixelHeight</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>PixelWidth</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>ProfileName</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>ResolutionHeightDPI</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>ResolutionWidthDPI</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>SecurityMethod</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>Streamable</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>Title</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>TotalBitRate</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>UseCount</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>UsedDates</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>VideoBitRate</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>Version</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>WhereFroms</td>
        #        </tr>
        #    </tbody>
        # </table>
        # @end


        // As of Mac OS X 10.6.3, Spotlight will NOT index files or provide meta data
        // for files that are contained within a hidden folder. mdimport will fail,
        // and mdls will only provide a minimal amount of information.
        $escapedPath = escapeshellarg($path);

        $metaData = `mdls {$escapedPath}`;

        $metaData = str_replace('(null)', 0, $metaData);

        preg_match_all('/\w{1,}\s{1,}=\s{1,}\d{1,}.*/', $metaData, $numericProperties);

        $data = array();

        if (isset($numericProperties[0]) && is_array($numericProperties[0]))
        {
            foreach ($numericProperties[0] as $metaItem)
            {
                list($property, $value) = explode('=', $metaItem);

                $property = trim(str_replace('kMDItem', '', $property));
                $value    = trim($value);

                switch ($property)
                {
                    case 'ContentCreationDate':
                    case 'ContentModificationDate':
                    case 'FSContentChangeDate':
                    case 'FSCreationDate':
                    case 'LastUsedDate':
                    {
                        // Convert these to Unix time
                        $value = strtotime($value);
                        break;
                    }
                    case 'FSSize':
                    {
                        if (!$value)
                        {
                            $value = $this->bytes(filesize($path));
                        }
                        else
                        {
                            $value = $this->bytes($value);
                        }

                        break;
                    }
                }

                $data[$property] = $value;
            }
        }

        preg_match_all('/\w{1,}\s{1,}=\s{1,}".*"/', $metaData, $stringProperties);

        if (isset($stringProperties[0]) && is_array($stringProperties[0]))
        {
            foreach ($stringProperties[0] as $metaItem)
            {
                list($property, $value) = explode('=', $metaItem);

                $property = trim(str_replace('kMDItem', '', $property));
                $value    = trim($value);

                // trim off the quotes.
                if (substr($value, 0, 1) == '"')
                {
                    $value = substr($value, 1);
                }

                if (substr($value, -1, 1) == '"')
                {
                    $value = substr($value, 0, -1);
                }

                $data[$property] = $value;
            }
        }

        preg_match_all('/\w{1,}\s{1,}=\s{1,}\(.*\)/Ums', $metaData, $multiProperties);

        if (isset($multiProperties[0]) && is_array($multiProperties[0]))
        {
            foreach ($multiProperties[0] as $metaItem)
            {
                list($property, $value) = explode('=', $metaItem);

                $property = trim(str_replace('kMDItem', '', $property));
                $value    = trim($value);

                // trim off the parenthesis.
                if (substr($value, 0, 1) == '(')
                {
                    $value = substr($value, 1);
                }

                if (substr($value, -1, 1) == ')')
                {
                    $value = substr($value, 0, -1);
                }

                $value = trim($value);

                $items = explode(',', $value);

                $value = array();

                foreach ($items as $item)
                {
                    $item = trim($item);

                    // Trim quotes off of the value if there are any.
                    if (substr($item, 0, 1) == '"')
                    {
                        $item = substr($item, 1);
                    }

                    if (substr($item, -1, 1) == '"')
                    {
                        $item = substr($item, 0, -1);
                    }

                    if ($property == 'UsedDates')
                    {
                        $item = strtotime($item);
                    }

                    array_push($value, $item);
                }

                $data[$property] = $value;
            }
        }

        return $data;
    }
}

?>