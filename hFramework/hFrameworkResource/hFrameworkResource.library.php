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
# <h1>Framework Resources API</h1>
# <p>
#   Framework resources in Hot Toddy refer to items in the database that can be used in
#   the following ways:
# </p>
# <ul>
#   <li>
#       Items that can be owned by one person.  Usually, any database table with a
#       <var>hUserId</var> field is setup so that the user defined in the <var>hUserId</var>
#       field owns the record in question.  As an owner, permissions can be set that
#       allow you, the owner, to access an item where others can not.
#   </li>
#   <li>
#       Items that can have permissions applied to them.  Items that can have permissions
#       applied to them always have a <var>hUserId</var> column, and the <var>hUserId</var>
#       column always refers to the owner.
#   </li>
#   <li>
#       Items that can be subscribed to.  You can subscribe to receive updates to certain
#       forum topics, or threads, for example.
#   </li>
#   <li>
#       Items that can be named.  Framework resources define a field in a database table
#       that can be used for names.
#   </li>
#   <li>
#       Territories and locations, or items that can be assigned to someone.
#   </li>
# </ul>
# <p>
#   The following are examples of resources that can be owned, and each can have permissions
#   applied to them:
# </p>
# <table>
#   <tbody>
#       <tr>
#           <td class='code'>hFiles</td>
#       </tr>
#       <tr>
#           <td class='code'>hDirectories</td>
#       </tr>
#       <tr>
#           <td class='code'>hForums</td>
#       </tr>
#       <tr>
#           <td class='code'>hContacts</td>
#       </tr>
#       <tr>
#           <td class='code'>hCalendars</td>
#       </tr>
#       <tr>
#           <td class='code'>hContactAddressBooks</td>
#       </tr>
#       <tr>
#           <td class='code'>hDirectories</td>
#       </tr>
#   </tbody>
# </table>
# <p>
#   Resources that can be owned, can in turn be controlled using Hot Toddy's permissions API.
#   The permissions API, when thoroughly implemented can prevent unauthorized access to items,
#   and access can be explicitly granted through the permissions dialogue.
# </p>
# @end

class hFrameworkResourceLibrary extends hPlugin {

    public function getResourceId($frameworkResource)
    {
        # @return integer | string

        # @description
        # <h2>Getting a Framework Resource or Resource Id</h2>
        # <p>
        #   If you supply a <var>$frameworkResource</var> (database table), you will get back a
        #   <var>$frameworkResourceId</var>.  If you supply a <var>$frameworkResourceId</var> you
        #   will get back a <var>$frameworkResource</var>.
        # </p>
        # @end

        $isNumeric = is_numeric($frameworkResource);

        $where['hFrameworkResource'.($isNumeric? 'Id' : 'Table')] = $frameworkResource;

        $frameworkResource = $this->hFrameworkResources->selectColumn(
            'hFrameworkResource'.($isNumeric? 'Table' : 'Id'),
            $where
        );

        if (!$frameworkResource)
        {
            $this->warning('The framework resource '.$frameworkResource.' does not exist.', __FILE__, __LINE__);
        }

        return $frameworkResource;
    }

    public function &numericResourceId(&$frameworkResourceId)
    {   
        # @return hFrameworkResource

        # @description
        # <h2>Setting a Numeric Resource Id</h2>
        # <p>
        #   Ensures the <var>$frameworkResourceId</var> provided is numeric,
        #   if not, the <var>$frameworkResourceId</var> is retrieved and
        #   assigned to <var>$frameworkResourceId</var>, which is passed by
        #   reference.
        # </p>
        # @end
        if (!is_numeric($frameworkResourceId))
        {
            $frameworkResourceId = $this->getResourceId($frameworkResourceId);
        }

        return $this;
    }

    public function isResource($frameworkResourceTable)
    {
        # @return boolean

        # @description
        # <h2>Is the Supplied Table a Database Resource?</h2>
        # <p>
        #   Determines whether or not the supplied <var>$frameworkResourceTable</var> is a
        #   framework resource table.
        # </p>
        # @end

        return $this->hFrameworkResources->selectExists(
            'hFrameworkResourceId',
            array(
                'hFrameworkResourceTable' => $frameworkResourceTable
            )
        );
    }

    public function getResource($frameworkResourceId)
    {
        # @return integer | string

        # @description
        # <h2>Getting a Framework Resource's Data</h2>
        # <p>
        #   Returns an array of information about the resource.
        # </p>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>Returned Data</th>
        #           <th>Description</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td>hFrameworkResourceTable</td>
        #           <td>The resource table</td>
        #       </tr>
        #       <tr>
        #           <td>hFrameworkResourcePrimaryKey</td>
        #           <td>The resourse table's primary key column</td>
        #       </tr>
        #       <tr>
        #           <td>hFrameworkResourceNameColumn</td>
        #           <td>The resource table's column containing name infotmation.</td>
        #       </tr>
        #       <tr>
        #           <td>hFrameworkResourceLastModifiedColumn</td>
        #           <td>The resource table's column containing last modified time information.</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        if (!is_numeric($frameworkResourceId))
        {
            $frameworkResourceId = $this->getResourceId($frameworkResourceId);
        }

        return $this->hFrameworkResources->selectAssociative(
            array(
                'hFrameworkResourceTable',
                'hFrameworkResourcePrimaryKey',
                'hFrameworkResourceNameColumn',
                'hFrameworkResourceLastModifiedColumn'
            ),
            (int) $frameworkResourceId
        );
    }

    public function getResourceName($frameworkResourceId, $frameworkResourceKey)
    {
        # @return string

        # @description
        # <h2>Getting a Framework Resource's Name</h2>
        # <p>
        #   Returns a name for the specified <var>$frameworkResource</var> (which can also be
        #   a <var>$frameworkResourceId</var>) and <var>$frameworkResourceKey</var> (the value
        #   of the primary key column of the specified resource).
        # </p>
        # @end

        if (!is_numeric($frameworkResourceId))
        {
            $frameworkResourceId = $this->getResourceId($frameworkResourceId);
        }

        $resource = $this->getResource($frameworkResourceId);

        $where[$resource['hFrameworkResourcePrimaryKey']] = (int) $frameworkResourceKey;

        return $this->hDatabase->selectColumn(
            $resource['hFrameworkResourceNameColumn'],
            $resource['hFrameworkResourceTable'],
            $where
        );
    }

    public function getFileOwner($fileId = 0)
    {
        # @return integer

        # @description
        # <h2>Getting a File's Owner</h2>
        # <p>
        #   Returns the owner <var>hUserId</var> of the supplied <var>$fileId</var>.
        # </p>
        # @end

        if (empty($fileId))
        {
            $fileId = (int) $this->hFileId;
        }

        return (int) $this->hFiles->selectColumn('hUserId', (int) $fileId);
    }

    public function getResourceLastModifiedColumn($frameworkResourceId)
    {
        # @return string

        # @description
        # <h2>Getting a Resource's Last Modified Column</h2>
        # <p>
        #   Returns the name of a resource's column designated to store last modified time
        #   information for each resource.
        # </p>
        # @end

        if (!is_numeric($frameworkResourceId))
        {
            $frameworkResourceId = $this->getResourceId($frameworkResourceId);
        }

        return $this->hFrameworkResources->selectColumn('hFrameworkResourceLastModifiedColumn', (int) $frameworkResourceId);
    }

    public function getResourceLastModified($frameworkResourceId, $frameworkResourceKey = 0)
    {
        # @return integer

        # @description
        # <h2>Getting a Resource's Last Modified Time</h2>
        # <p>
        #   Returns the last modified time of the specified resource (as a whole), or a specific
        #   resource's key.  The last modified time can in turn be used to cache resources using
        #   <a href='/Hot Toddy/Documentation?hFile/hFileCache/hFileCache.library.php'>hFileCacheLibrary</a>
        # </p>
        # @end

        if (!is_numeric($frameworkResourceId))
        {
            $frameworkResourceId = $this->getResourceId($frameworkResourceId);
        }

        if (empty($frameworkResourceKey))
        {
            return $this->hFrameworkResources->selectColumn('hFrameworkResourceLastModified', (int) $frameworkResourceId);
        }
        else
        {
            $resource = $this->getResource($frameworkResourceId);

            $where[$resource['hFrameworkResourcePrimaryKey']] = (int) $frameworkResourceKey;

            return $this->hDatabase->selectColumn(
                $resource['hFrameworkResourceLastModifiedColumn'],
                $resource['hFrameworkResourceTable'],
                $where
            );
        }
    }

    public function &modifyResource($frameworkResourceId, $frameworkResourceKey = 0)
    {
        # @return hFrameworkResource

        # @description
        # <h2>Modifying a Framework Resource</h2>
        # <p>
        #   Updates the modified time of the specified <var>$frameworkResourceId</var>.
        #   If a <var>$frameworkResourceKey</var> is specified, the last modified column is
        #   of the corresponding row in the resource table is updated.
        # </p>
        # @end

        if (!is_numeric($frameworkResourceId))
        {
            $frameworkResourceId = $this->getResourceId($frameworkResourceId);
        }

        $columns = array(
            'hFrameworkResourceLastModified' => time()
        );

        if ($this->isLoggedIn())
        {
            $columns['hFrameworkResourceLastModifiedBy'] = (int) $_SESSION['hUserId'];
        }

        $this->hFrameworkResources->update($columns, $frameworkResourceId);

        if (!empty($frameworkResourceKey))
        {
            $columns = array();

            $resource = $this->getResource($frameworkResourceId);

            $columns[$resource['hFrameworkResourceLastModifiedColumn']] = time();

            if (!empty($resource['hFrameworkResourceLastModifiedByColumn']) && $this->isLoggedIn())
            {
                $columns[$resource['hFrameworkResourceLastModifiedByColumn']] = (int) $_SESSION['hUserId'];
            }

            $this->hDatabase->update($columns, $frameworkResourceKey, $resource['hFrameworkResourceTable']);
        }

        return $this;
    }
}

?>