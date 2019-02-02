<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Language Text
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

class hLanguageTextService extends hService {

    public function search()
    {
        $xml = 1;

        switch (true)
        {
            case !isset($_GET['hLanguageText']):
            {
                $xml = -5;
                break;
            }
            case !$this->isLoggedIn():
            {
                $xml = -6;
                break;
            }
            case !$this->inGroup('Website Administrators'):
            {
                $xml = -1;
                break;
            }
        }

        if ($xml > 0)
        {
            $hLanguageText = hString::decodeHTML($_GET['hLanguageText']);
            $hLanguageText = hString::encodeHTML($hLanguageText);

            $query = db::query(
                "SELECT `hLanguageTextId`
                   FROM `hLanguageText`
                  WHERE `hLanguageId` = 1
                    AND `hLanguageText` = '{$hLanguageText}'"
            );

            if (db::numRows($query))
            {
                $hLanguageTextId = (int) db::result($query);

                $xml = 
                    "<hLanguageText hLanguageId='1' hLanguageTextId='{$hLanguageTextId}'>".
                        hString::entitiesToUTF8($hLanguageText).
                    "</hLanguageText>\n";

                $query = db::query(
                    "SELECT `hLanguageTextTo`
                       FROM `hLanguageTranslation`
                      WHERE `hLanguageTextFrom` = ". $hLanguageTextId
                );

                while ($data = db::fetchArray($query))
                {
                    $hLanguage = db::fetchArray(
                        "SELECT `hLanguageId`,
                                `hLanguageText`
                           FROM `hLanguageText`
                          WHERE `hLanguageTextId` = ". (int) $data['hLanguageTextTo']
                    );

                    $xml .=
                        "<hLanguageText hLanguageId='{$hLanguage['hLanguageId']}' hLanguageTextId='{$data['hLanguageTextTo']}'>".
                            hString::entitiesToUTF8($hLanguage['hLanguageText']).
                        "</hLanguageText>\n";
                }
            }
            else
            {
                $xml = "<hLanguageTextId>0</hLanguageTextId>\n";
            }
        }
        
        $this->XML($xml);
    }

    public function save()
    {
        $xml = 1;
        
        switch (true)
        {
            case !isset($_GET['hLanguage']):
            {
                $xml = -5;
                break;
            }
            case !$this->isLoggedIn():
            {
                $xml = -6;
                break;
            }
            case !$this->inGroup('Website Administrators'):
            {
                $xml = -1;
                break;
            }
        }

        if ($xml > 0)
        {
            $englishTextId = isset($_GET['hLanguage'][1]['hLanguageTextId'])? (int) $_GET['hLanguage'][1]['hLanguageTextId'] : 0;

            foreach ($_GET['hLanguage'] as $hLanguageId => $text)
            {
                if (!empty($text['hLanguageText']))
                {
                    //$text['hLanguageText'] = hString::encodeHTML($text['hLanguageText']);

                    if (!empty($text['hLanguageTextId']))
                    {
                        // If the word or phrase has been updated, 
                        // it may already exist in the database under a different Id.
                        //
                        // Look to see if this word or phrase does already exist in 
                        // the database.                     
                        $query = db::query(
                            "SELECT `hLanguageTextId`
                               FROM `hLanguageText` 
                              WHERE `hLanguageId`   = ". $hLanguageId ."
                                AND `hLanguageText` = '{$text['hLanguageText']}'"
                        );
    
                        if (db::numRows($query))
                        {
                            $hLanguageTextId = db::result($query);
                            
                            // The word or phrase does exist in the database 
                            // already.  If the Id of the matching entry does 
                            // not match this entry.. set the current Id
                            // to the matching entry.
                            if ($hLanguageTextId != $text['hLanguageTextId'])
                            {
                                $_GET['hLanguage'][$hLanguageId]['hLanguageTextId'] = $hLanguageTextId;
                            }

                            // If the Id does match the existing entry, that means that 
                            // this word or phrase has not been edited and there is nothing to update
                        }
                        else
                        {
                            // The word or phrase doesn't already exist in the database under a 
                            // different Id, so just update the current Id to the revised word 
                            // or phrase.
                            db::query(
                                "UPDATE `hLanguageText`
                                    SET `hLanguageText`   = '{$text['hLanguageText']}'
                                  WHERE `hLanguageTextId` = ". (int) $text['hLanguageTextId']
                            );
                        }
                    }
                    else
                    {
                        // The Id is empty... see if the word or phrase already exists.
                        $query = db::query(
                            "SELECT `hLanguageTextId`
                               FROM `hLanguageText` 
                              WHERE `hLanguageId`   = ". $hLanguageId ."
                                AND `hLanguageText` = '{$text['hLanguageText']}'"
                        );

                        if (db::numRows($query))
                        {
                            // If the Id does match the existing entry, that means that 
                            // this word or phrase has not been edited and there is nothing to update
                            $_GET['hLanguage'][$hLanguageId]['hLanguageTextId'] = db::result($query);
                        }
                        else
                        {                    
                            db::query(
                                "INSERT INTO `hLanguageText` (
                                    `hLanguageTextId`,
                                    `hLanguageId`,
                                    `hLanguageText`
                                ) VALUES (
                                    null,
                                    ". (int) $hLanguageId .",
                                    '{$text['hLanguageText']}'
                                )"
                            );
    
                            $_GET['hLanguage'][$hLanguageId]['hLanguageTextId'] = db::insertId();
                        }
                    }
                }
                else
                {
                    unset($_GET['hLanguage'][$hLanguageId]);
                }
            }

            if (isset($_GET['hLanguage'][1]))
            {
                // Now that all the text is inserted / and all of the text has an Id the text must be associated
                foreach ($_GET['hLanguage'] as $hLanguageId => $text)
                {
                    if ($hLanguageId > 1)
                    {
                        $query = db::query(
                            "SELECT `hLanguageTranslationId`
                               FROM `hLanguageTranslation`
                              WHERE `hLanguageTextFrom` = ". (int) $_GET['hLanguage'][1]['hLanguageTextId'] ."
                                AND `hLanguageTextTo`   = ". (int) $_GET['hLanguage'][$hLanguageId]['hLanguageTextId']
                        );

                        if (!db::numRows($query))
                        {
                            db::query(
                                "INSERT INTO `hLanguageTranslation` (
                                    `hLanguageTranslationId`,
                                    `hLanguageTextFrom`,
                                    `hLanguageTextTo`
                                ) VALUES (
                                    null,
                                    ". (int) $_GET['hLanguage'][1]['hLanguageTextId'] .",
                                    ". (int) $_GET['hLanguage'][$hLanguageId]['hLanguageTextId'] ."
                                )"
                            );
                        }
                    }
                }
            }
        }

        $this->XML($xml);
    }
}

?>