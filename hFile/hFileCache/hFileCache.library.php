<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File Cache Library
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
# <h1>Cache API</h1>
# <p>
#   Hot Toddy's caching works by assigning a cached item a name, which becomes the <var>hFileCacheResource</var>
#   in the database, which is used to categorize and class multiple cached resources. Then each cached item is
#   also given either a <var>hFileCacheResourceId</var>, which is a unique integer that you provide, used in
#   conjunction with the <var>hFileCacheResource</var>, uniquely identifies a cached item. Alternatively,
#   instead of a <var>hFileCacheResourceId</var>, you may provide instead a <var>hFileCacheResourcePath</var>.
#   When <var>hFileCacheResourcePath</var> is provided, it is used instead of <var>hFileCacheResourceId</var>
#   to uniquely identify a cached item.
# </p>
# @end

class hFileCacheLibrary extends hPlugin {

    # Expired items are deleted at first load of this plugin, every time.
    public function hConstructor()
    {
        # @return void

        $sql = $this->getTemplateSQL('deleteExpired');

        # Clear the cache
        $this->hDatabase->query($sql);
    }

    public function isCached($fileCacheResource, $fileCacheResourceId, $fileCacheLastModified)
    {
        # @return boolean

        # @description
        # <h2>Determining if data is cached</h2>
        # <p>
        #   Determines whether a cache exists for a given resource.
        # </p>
        # @end

        # @argument $fileCacheResource string
        # <p>
        #   A short unique name for the cached information that you invent to
        #   identify the cached information.  For example hCalendarNewsPosts.
        # </p>
        # @end

        # $fileCacheResourceId
        #   A unique identifier for cached information, like an hFileId,
        #   for example.  The identifier can be an integer or a string.
        #
        # $fileCacheLastModified
        #   The last time the item was modified.  The unix timestamp prodvided
        #   in this argument is compared to the unix timestamp first recorded
        #   when the cache was made.  If the item has been modified since the
        #   cache was made, the cache is automatically discarded.

        if ($this->hFileCacheDisabled(false))
        {
            return false;
        }

        if (is_array($fileCacheLastModified))
        {
            foreach ($fileCacheLastModified as $lastModified)
            {
                $exists = $this->hFileCache->selectExists(
                    'hFileCacheId',
                    $this->getWhere(
                        $fileCacheResource,
                        $fileCacheResourceId,
                        $lastModified
                    )
                );

                if (!$exists)
                {
                    return false;
                }
            }

            return true;
        }
        else
        {
            $exists = $this->hFileCache->selectExists(
                'hFileCacheId',
                $this->getWhere(
                    $fileCacheResource,
                    $fileCacheResourceId,
                    $fileCacheLastModified
                )
            );
        }

        return $exists;
    }

    public function getCacheId($fileCacheResource, $fileCacheResourceId)
    {
        # @return integer

        # @description
        # <h2>Getting the Cache Id</h2>
        # <p>
        #   Returns a <var>hFileCacheId</var> for the provided <var>$fileCacheResource</var> and
        #   <var>$fileCacheResourceId</var>.
        # </p>

        # @end

        $cacheId = $this->hFileCache->selectColumn(
            'hFileCacheId',
            $this->getWhere(
                $fileCacheResource,
                $fileCacheResourceId
            )
        );

        return $cacheId? (int) $cacheId : nil;
    }

    public function getCachedTimestamp($fileCacheResource, $fileCacheResourceId)
    {
        return $this->hFileCache->selectColumn(
            'hFileCacheLastModified',
            $this->getWhere(
                $fileCacheResource,
                $fileCacheResourceId
            )
        );
    }

    private function getWhere($fileCacheResource, $fileCacheResourceId, $fileCacheLastModified = nil)
    {
        $where = array(
            //'hLanguageId' => (int) $this->hLanguageId(1),
            'hFileCacheResource' => $fileCacheResource
        );

        $where['hFileCacheResource'.(is_numeric($fileCacheResourceId)? 'Id' : 'Path')] = $fileCacheResourceId;

        if (!empty($fileCacheLastModified))
        {
            $where['hFileCacheLastModified'] = array('>=', $fileCacheLastModified);
        }

        return $where;
    }

    # Create a cached resource.
    #
    # $fileCacheResource
    #   A short unique name for the cached information that you invent to
    #   identify the cached information.  For example hCalendarNewsPosts.
    #
    # $fileCacheResourceId
    #   A unique identifier for cached information, like an hFileId,
    #   for example.  The identifier can be an integer or a string.
    #
    # $fileCacheDocument
    #   The data to be cached.
    #
    # $fileCacheExpires
    #   A hard expiration date for the cached document expressed as a unix
    #   timestamp.  0 = no expiration.
    #
    # $languageId
    #   The language of the cached content (default is English).
    #
    public function saveDocumentToCache($fileCacheResource, $fileCacheResourceId, $fileCacheDocument, $fileCacheExpires = 0, $languageId = 0)
    {
        $isResourceId = is_numeric($fileCacheResourceId);

        $this->hFileCache->save(
            array(
                'hFileCacheId'           => $this->getCacheId($fileCacheResource, $fileCacheResourceId),
                'hLanguageId'            => empty($languageId)? $this->hLanguageId(1) : $languageId,
                'hFileCacheResourceId'   => $isResourceId? $fileCacheResourceId : 0,
                'hFileCacheResource'     => $fileCacheResource,
                'hFileCacheResourcePath' => !$isResourceId? $fileCacheResourceId : '',
                'hFileCacheDocument'     => hString::encodeHTML($fileCacheDocument),
                'hFileCacheLastModified' => time(),
                'hFileCacheExpires'      => (int) $fileCacheExpires
            )
        );
    }

    # Return a cached resource.
    #
    # $fileCacheResource
    #   A short unique name for the cached information that you invent to
    #   identify the cached information.  For example hCalendarNewsPosts.
    #
    # $fileCacheResourceId
    #   A unique identifier for cached information, like an hFileId,
    #   for example.  The identifier can be an integer or a string.
    #
    # $fileCacheLastModified
    #   The last time the item was modified.  The unix timestamp prodvided
    #   in this argument is compared to the unix timestamp first recorded
    #   when the cache was made.  If the item has been modified since the
    #   cache was made, the cache is automatically discarded.
    #
    public function getCachedDocument($fileCacheResource, $fileCacheResourceId, $fileCacheLastModified)
    {
        if (isset($_GET['update']) || isset($_GET['refresh']) || $this->hFileCacheDisabled(false))
        {
            return false;
        }

        if (false !== ($this->isCached($fileCacheResource, $fileCacheResourceId, $fileCacheLastModified)))
        {
            return hString::decodeHTML(
                $this->hFileCache->selectColumn(
                    'hFileCacheDocument',
                    $this->getWhere(
                        $fileCacheResource,
                        $fileCacheResourceId
                    )
                )
            );
        }
        else
        {
            return false;
        }
    }

    # Delete a cached resource.
    #
    # $fileCacheResource
    #   A short unique name for the cached information that you invent to
    #   identify the cached information.  For example hCalendarNewsPosts.
    #
    # $fileCacheResourceId
    #   A unique identifier for cached information, like an hFileId,
    #   for example.  The identifier can be an integer or a string.
    #
    public function deleteCachedDocument($fileCacheResource, $fileCacheResourceId)
    {
        $columns = array();

        $columns['hFileCacheResource'.(is_numeric($fileCacheResourceId)? 'Id' : 'Path')] = $fileCacheResourceId;
        $columns['hFileCacheResource'] = $fileCacheResource;

        $this->hFileCache->delete($columns);
    }

    public function deleteCachedDocuments($fileId)
    {
        # Delete all caches associated with a file Id
        $this->hFileCache->delete(
            array(
                'hFileCacheResourceId' => (int) $fileId,
                'hFileCacheResource' => 'hFileDocuments'
            )
        );

        $this->hFileCache->delete(
            array(
                'hFileCacheResourceId' => (int) $fileId,
                'hFileCacheResource' => 'hFileBreadcrumbs'
            )
        );
    }
}

?>