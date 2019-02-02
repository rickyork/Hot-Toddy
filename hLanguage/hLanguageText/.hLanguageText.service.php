<?php
  class hLanguageTextService extends hService { public function search() { $xml = 1; switch (true) { case !isset($_GET['hLanguageText']): { $xml = -5; break; } case !$this->isLoggedIn(): { $xml = -6; break; } case !$this->inGroup('Website Administrators'): { $xml = -1; break; } } if ($xml > 0) { $hLanguageText = hString::decodeHTML($_GET['hLanguageText']); $hLanguageText = hString::encodeHTML($hLanguageText); $query = db::query( "SELECT `hLanguageTextId`
                   FROM `hLanguageText`
                  WHERE `hLanguageId` = 1
                    AND `hLanguageText` = '{$hLanguageText}'" ); if (db::numRows($query)) { $hLanguageTextId = (int) db::result($query); $xml = "<hLanguageText hLanguageId='1' hLanguageTextId='{$hLanguageTextId}'>". hString::entitiesToUTF8($hLanguageText). "</hLanguageText>\n"; $query = db::query( "SELECT `hLanguageTextTo`
                       FROM `hLanguageTranslation`
                      WHERE `hLanguageTextFrom` = ". $hLanguageTextId ); while ($data = db::fetchArray($query)) { $hLanguage = db::fetchArray( "SELECT `hLanguageId`,
                                `hLanguageText`
                           FROM `hLanguageText`
                          WHERE `hLanguageTextId` = ". (int) $data['hLanguageTextTo'] ); $xml .= "<hLanguageText hLanguageId='{$hLanguage['hLanguageId']}' hLanguageTextId='{$data['hLanguageTextTo']}'>". hString::entitiesToUTF8($hLanguage['hLanguageText']). "</hLanguageText>\n"; } } else { $xml = "<hLanguageTextId>0</hLanguageTextId>\n"; } } $this->XML($xml); } public function save() { $xml = 1; switch (true) { case !isset($_GET['hLanguage']): { $xml = -5; break; } case !$this->isLoggedIn(): { $xml = -6; break; } case !$this->inGroup('Website Administrators'): { $xml = -1; break; } } if ($xml > 0) { $englishTextId = isset($_GET['hLanguage'][1]['hLanguageTextId'])? (int) $_GET['hLanguage'][1]['hLanguageTextId'] : 0; foreach ($_GET['hLanguage'] as $hLanguageId => $text) { if (!empty($text['hLanguageText'])) {  if (!empty($text['hLanguageTextId'])) {      $query = db::query( "SELECT `hLanguageTextId`
                               FROM `hLanguageText` 
                              WHERE `hLanguageId`   = ". $hLanguageId ."
                                AND `hLanguageText` = '{$text['hLanguageText']}'" ); if (db::numRows($query)) { $hLanguageTextId = db::result($query);     if ($hLanguageTextId != $text['hLanguageTextId']) { $_GET['hLanguage'][$hLanguageId]['hLanguageTextId'] = $hLanguageTextId; }   } else {    db::query( "UPDATE `hLanguageText`
                                    SET `hLanguageText`   = '{$text['hLanguageText']}'
                                  WHERE `hLanguageTextId` = ". (int) $text['hLanguageTextId'] ); } } else {  $query = db::query( "SELECT `hLanguageTextId`
                               FROM `hLanguageText` 
                              WHERE `hLanguageId`   = ". $hLanguageId ."
                                AND `hLanguageText` = '{$text['hLanguageText']}'" ); if (db::numRows($query)) {   $_GET['hLanguage'][$hLanguageId]['hLanguageTextId'] = db::result($query); } else { db::query( "INSERT INTO `hLanguageText` (
                                    `hLanguageTextId`,
                                    `hLanguageId`,
                                    `hLanguageText`
                                ) VALUES (
                                    null,
                                    ". (int) $hLanguageId .",
                                    '{$text['hLanguageText']}'
                                )" ); $_GET['hLanguage'][$hLanguageId]['hLanguageTextId'] = db::insertId(); } } } else { unset($_GET['hLanguage'][$hLanguageId]); } } if (isset($_GET['hLanguage'][1])) {  foreach ($_GET['hLanguage'] as $hLanguageId => $text) { if ($hLanguageId > 1) { $query = db::query( "SELECT `hLanguageTranslationId`
                               FROM `hLanguageTranslation`
                              WHERE `hLanguageTextFrom` = ". (int) $_GET['hLanguage'][1]['hLanguageTextId'] ."
                                AND `hLanguageTextTo`   = ". (int) $_GET['hLanguage'][$hLanguageId]['hLanguageTextId'] ); if (!db::numRows($query)) { db::query( "INSERT INTO `hLanguageTranslation` (
                                    `hLanguageTranslationId`,
                                    `hLanguageTextFrom`,
                                    `hLanguageTextTo`
                                ) VALUES (
                                    null,
                                    ". (int) $_GET['hLanguage'][1]['hLanguageTextId'] .",
                                    ". (int) $_GET['hLanguage'][$hLanguageId]['hLanguageTextId'] ."
                                )" ); } } } } } $this->XML($xml); } } ?>