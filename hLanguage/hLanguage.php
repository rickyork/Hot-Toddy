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

class hLanguage extends hPlugin {

    private $languages = array(
        '.co.uk'  => 'en-uk',
        '.com'    => 'en-us',
        '.org'    => 'en-us',
        '.net'    => 'en-us',
        '.ca'     => 'en-ca',
        '.ru'     => 'ru-ru',
        '.de'     => 'de-de',
        '.fr'     => 'fr-fr',
        '.es'     => 'es-es',
        '.com.mx' => 'es-mx',
        '.pt'     => 'pt-pt'
    );

    private $langaugeDefaults = array(
        'en-uk' => 'en-us',
        'en-ca' => 'en-us',
        'es-mx' => 'es-es'
    );

    public function hConstructor()
    {
        if (!$this->hLanguageId(nil))
        {
            if (!isset($_GET['hLanguageId']))
            {
                // Determine the language
                if (!isset($_SESSION['hLanguageId']))
                {
                    foreach ($this->languages as $suffix => $localization)
                    {
                        if (isset($_SERVER['HTTP_HOST']) && substr($_SERVER['HTTP_HOST'], -strlen($suffix)) == $suffix)
                        {
                            $_SESSION['hLanguageId'] = $this->getLanguageId($localization);
                            break;
                        }
                    }

                    if (!isset($_SESSION['hLanguageId']))
                    {
                        $_SESSION['hLanguageId'] = $this->hLanguageId(1);
                    }
                }
            }
            else
            {
                $_SESSION['hLanguageId'] = (int) $_GET['hLanguageId'];
            }

            if (isset($_SESSION['hLanguageId']))
            {
                $this->hLanguageId = (int) $_SESSION['hLanguageId'];
            }
        }

        $this->setVariables(
            $this->hLanguages->selectAssociative(
                array(
                    'hLanguageCode',
                    'hLanguageLocalization',
                    'hLanguageCharset'
                ),
                (int) $this->hLanguageId
            )
        );
    }

    public function translate($text, $languageId = 0)
    {
        if ($this->hLanguageId > 1 || $languageId > 1)
        {
            $languageTextId = $this->hLanguageText->selectColumn(
                'hLanguageTextId',
                array(
                    'hLanguageId' => 1,
                    'hLanguageText' => $text
                )
            );

            if (!empty($languageTextId))
            {
                $this->hDatabase->setDefaultResult($text);

                return $this->hDatabase->selectColumn(
                    array(
                        'hLanguageText' => 'hLanguageText'
                    ),
                    array(
                        'hLanguageText',
                        'hLanguageTranslation'
                    ),
                    array(
                        'hLanguageText.hLanguageId' => !empty($languageId)? (int) $languageId : (int) $this->hLanguageId,
                        'hLanguageText.hLanguageTextId' => 'hLanguageTranslation.hLanguageTextTo',
                        'hLanguageTranslation.hLanguageTextFrom' => $languageTextId
                    )
                );
            }
            else
            {
                return $text;
            }
        }
        else
        {
            return $text;
        }
    }

    public function getLanguageId($localization, $secondTry = false)
    {
        $languageId = (int) $this->hLanguages->selectColumn(
            'hLanguageId',
            array(
                'hLanguageLocalization' => $localization
            )
        );

        if (empty($languageId))
        {
            if (!$secondTry && isset($this->langaugeDefaults[$localization]))
            {
                return $this->getLanguageId($this->langaugeDefaults[$localization], true);
            }

            return $this->hLanguageId(1);
        }
        else
        {
            return $languageId;
        }
    }
}

?>