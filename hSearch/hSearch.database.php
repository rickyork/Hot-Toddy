<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Search Database
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

class hSearchDatabase extends hPlugin {

    private $format = 'getResultsForTemplate';
    private $resultCount = '';

    private $hSearch;
    private $hFileIcon;

    public function hConstructor()
    {
        $this->hSearch = $this->library('hSearch');
        $this->hFileIcon = $this->library('hFile/hFileIcon');
    }

    public function &setResultCount()
    {
        # @return hSearchDatabase

        # @description
        # <h2>Setting the Result Count</h2>
        # <p>
        #
        # </p>
        # @end

        $this->resultCount = $this->hDatabase->getResultCount();
        return $this;
    }

    public function getResultCount()
    {
        # @return integer

        # @description
        # <h2>Getting Result Count</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->resultCount;
    }

    public function querySource($searchTerms, $searchLimit)
    {
        # @return array

        # @description
        # <h2>Querying Text and Source Code</h2>
        # <p>
        #
        # </p>
        # @end

        $results = $this->hDatabase->getResultsForTemplate(
            $this->getTemplateSQL(get_defined_vars())
        );

        $this->setResultCount();

        $this->prepareResults($results, $searchTerms);

        return $results;
    }

    public function &prepareResults(&$results, $searchTerms)
    {
        # @return hSearchDatabase

        # @description
        # <h2>Preparing Search Results</h2>
        # <p>
        #
        # </p>
        # @end

        if (isset($results['hFileId']) && is_array($results))
        {
            foreach ($results['hFileId'] as $i => $fileId)
            {
                if (isset($results['hFileHeadingTitle'][$i]))
                {
                    //$results['hFileTitle'][$i] = hString::entitiesToUTF8($results['hFileTitle'][$i]);
                    $results['hFileHeadingTitle'][$i] = hString::decodeHTML($results['hFileHeadingTitle'][$i]);
                }

                $results['hFileIconPath'][$i] = $this->hFileIcon->getFileIconPath(
                    $fileId,
                    nil,
                    nil,
                    $this->hFileIconResolution('32x32')
                );

                if ($this->hSearchIncludeCreated(false))
                {
                    if (isset($results['hFileCreated'][$i]))
                    {
                        if (!empty($results['hFileCreated'][$i]))
                        {
                            $results['hFileCreated'][$i] = date(
                                $this->hSearchFileLastModifiedFormat('m/d/y h:i a'),
                                $results['hFileCreated'][$i]
                            );
                        }
                        else
                        {
                            $results['hFileCreated'][$i] = 0;
                        }
                    }
                }
                else
                {
                    $results['hFileCreated'][$i] = '';
                }

                if ($this->hSearchIncludeLastModified(true))
                {
                    if (isset($results['hFileLastModified'][$i]))
                    {
                        if (!empty($results['hFileLastModified'][$i]))
                        {
                            $results['hFileLastModified'][$i] = date(
                                $this->hSearchFileLastModifiedFormat('m/d/y h:i a'),
                                $results['hFileLastModified'][$i]
                            );
                        }
                        else
                        {
                            $results['hFileLastModified'][$i] = 0;
                        }
                    }
                }
                else
                {
                    $results['hFileLastModified'][$i] = '';
                }

                if (isset($results['hFileLastAccessed'][$i]))
                {
                    $results['hFileLastAccessed'][$i] = date(
                        $this->d('m/d/y h:i a'),
                        $results['hFileLastAccessed'][$i]
                    );
                }

                if ($this->hSearchIncludeCreatedBy(true))
                {
                    if (isset($results['hUserId'][$i]))
                    {
                        if (empty($results['hUserId'][$i]))
                        {
                            $results['hUserId'][$i] = 1;
                        }

                        $results['hFileCreatedBy'][$i] = $this->user->getFullName($results['hUserId'][$i]);
                    }
                }
                else
                {
                    $results['hFileCreatedBy'][$i] = '';
                }

                if ($this->hSearchIncludeLastModifiedBy(true))
                {
                    if (isset($results['hFileLastModifiedBy'][$i]))
                    {
                        if (empty($results['hFileLastModifiedBy'][$i]))
                        {
                            $results['hFileLastModifiedBy'][$i] = 1;
                        }

                        $results['hFileLastModifiedBy'][$i] = $this->user->getFullName($results['hFileLastModifiedBy'][$i]);
                    }
                }
                else
                {
                    $results['hFileLastModifiedBy'][$i] = '';
                }

                $results['hFileDocument'][$i] = trim($results['hFileDocument'][$i]);

                $document = false;

                if (!empty($results['hFileDocument'][$i]))
                {
                    $results['hFileDocument'][$i] = $this->hSearch->highlightTerms(
                        $this->hSearchSource(false) ? $results['hFileDocument'][$i] : hString::decodeHTML($results['hFileDocument'][$i]),
                        $searchTerms
                    );

                    $document = true;
                }
                else
                {
                    $results['hFileDescription'][$i] = $this->hSearch->highlightTerms(
                        $this->hSearchSource(false) ? $results['hFileDescription'][$i] : hString::decodeHTML($results['hFileDescription'][$i]),
                        $searchTerms
                    );
                }

                $results['hSearchDescription'][$i] = $document? $results['hFileDocument'][$i] : $results['hFileDescription'][$i];
            }
        }

        return $this;
    }

    private function getCategories(array $categories)
    {
        # @return string

        # @description
        # <h2>Getting Results Limited to Categories</h2>
        # <p>
        #
        # </p>
        # @end

        $sql = array();

        foreach ($categories as $categoryId)
        {
            $sql[] = "`hCategoryFiles`.`hCategoryId` = ". (int) $categoryId;
        }

        return count($sql)? implode(' OR ', $sql) : '';
    }

    private function getQueryResults($sql, $searchTerms = null)
    {
        # @return array

        # @description
        # <h2>Getting Query Results</h2>
        # <p>
        #
        # </p>
        # @end

        $results = $this->hDatabase->getResults($sql);

        $this->setResultCount();

        $results = $this->hDatabase->getResultsForTemplate($results);

        $this->prepareResults($results, $searchTerms);

        return $results;
    }

    private function getQueryArguments($template, $searchTerms, $searchLimit, $searchDirectory, array $categories = array(), $userId = 0)
    {
        # @return void

        # @description
        # <h2>Performing a Query</h2>
        # <p>
        #
        # </p>
        # @end

        $searchDirectories = null;

        if (is_array($searchDirectory))
        {
            $directories = array();

            foreach ($searchDirectory as $directory)
            {
                array_push(
                    $directories,
                    "`hDirectories`.`hDirectoryPath` = '{$directory}'"
                );

                array_push(
                    $directories,
                    "`hDirectories`.`hDirectoryPath` LIKE '{$directory}/%'"
                );
            }

            $searchDirectories = implode(' OR ', $directories);
            $searchDirectory = null;
        }

        $variables = array();

        if (!empty($userId))
        {
            $variables = array(
                'userId' => (int) $userId
            );
        }

        $sql = $this->getTemplateSQL(
            $template,
            array_merge(
                array(
                    // Double quotes can be used as syntax in a boolean mode fulltext query
                    'searchTerms'       => str_replace('&quot;', '"', $searchTerms),
                    'searchLimit'       => $searchLimit,
                    'searchDirectory'   => $searchDirectory,
                    'searchDirectories' => $searchDirectories,
                    'categories'        => $this->getCategories($categories)
                ),
                $this->getPermissionsVariablesForTemplate(true, false, 'r'),
                $variables
            )
        );

        return $this->getQueryResults(
            $sql,
            $searchTerms
        );
    }

    public function query($searchTerms, $searchLimit, $searchDirectory = null, array $categories = array())
    {
        # @return void

        # @description
        # <h2>Performing a Fulltext Query</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->getQueryArguments(
            'query',
            $searchTerms,
            $searchLimit,
            $searchDirectory,
            $categories
        );
    }

    public function queryLike($searchTerms, $searchLimit, $searchDirectory = null, array $categories = array())
    {
        # @return void

        # @description
        # <h2>Performing a Wildcard Query</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->getQueryArguments(
            'queryLike',
            $searchTerms,
            $searchLimit,
            $searchDirectory,
            $categories
        );
    }

    public function queryEmpty($searchLimit, $searchDirectory = null, array $categories = array())
    {
        # @return void

        # @description
        # <h2>Performing a Default Query</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->getQueryArguments(
            'queryEmpty',
            null,
            $searchLimit,
            $searchDirectory,
            $categories
        );
    }

    public function queryHistory($searchTerms, $searchLimit, $searchDirectory = null, array $categories = array(), $userId = 0)
    {
        # @return void

        # @description
        # <h2>Performing a Query of Documents Within User Histories</h2>
        # <p>
        #
        # </p>
        # @end

        $this->user->whichUserId($userId);

        return $this->getQueryArguments(
            'queryHistory',
            $searchTerms,
            $searchLimit,
            $searchDirectory,
            $categories,
            $userId
        );
    }
}

?>