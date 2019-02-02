<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Code Style HTML Library
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

class hCodeStyleHTMLLibrary extends hPlugin {

    private $isTag = false;
    private $tag = '';
    private $isTagType = false;
    private $tagType = '';
    private $items = array();
    private $isData = false;
    private $data = '';

    // This long nest of booleans isn't used yet, but might be used in the future,
    // if I decide I want more fine-grained control over the markup styling of
    // HTML documents.
    private $is = array(
        'html' => false,
        'head' => false,
        'meta' => false,
        'link' => false,
        'script' => false,
        'noscript' => false,
        'style' => false,
        'title' => false,
        'base' => false,
        'body' => false,
        'p' => false,
        'div' => false,
        'section' => false,
        'nav' => false,
        'article' => false,
        'aside' => false,
        'address' => false,
        'main' => false,
        'span' => false,
        'a' => false,
        'b' => false,
        'i' => false,
        'u' => false,
        'strong' => false,
        'em' => false,
        'font' => false,
        'center' => false,
        'small' => false,
        'sub' => false,
        'sup' => false,
        'ins' => false,
        'del' => false,
        'mark' => false,
        'ruby' => false,
        'rt' => false,
        'rp' => false,
        'bdi' => false,
        'bdo' => false,
        's' => false,
        'hr' => false,
        'br' => false,
        'wbr' => false,
        'pre' => false,
        'code' => false,
        'command' => false,
        'var' => false,
        'blockquote' => false,
        'cite' => false,
        'q' => false,
        'header' => false,
        'hgroup' => false,
        'h1' => false,
        'h2' => false,
        'h3' => false,
        'h4' => false,
        'h5' => false,
        'h6' => false,
        'footer' => false,
        'figure' => false,
        'figcaption' => false,
        'ul' => false,
        'ol' => false,
        'li' => false,
        'dl' => false,
        'dd' => false,
        'dt' => false,
        'dfn' => false,
        'abbr' => false,
        'data' => false,
        'time' => false,
        'block' => false,
        'inlineBlock' => false,
        'inline' => false,
        'table' => false,
        'colgroup' => false,
        'col' => false,
        'thead' => false,
        'tr' => false,
        'th' => false,
        'tbody' => false,
        'td' => false,
        'tfoot' => false,
        'caption' => false,
        'samp' => false,
        'kdb' => false,
        'img' => false,
        'iframe' => false,
        'embed' => false,
        'object' => false,
        'param' => false,
        'video' => false,
        'audio' => false,
        'source' => false,
        'track' => false,
        'canvas' => false,
        'map' => false,
        'area' => false,
        'svg' => false,
        'math' => false,
        'fieldset' => false,
        'legend' => false,
        'label' => false,
        'input' => false,
        'button' => false,
        'select' => false,
        'datalist' => false,
        'optgroup' => false,
        'option' => false,
        'textarea' => false,
        'keygen' => false,
        'output' => false,
        'progress' => false,
        'meter' => false,
        'details' => false,
        'summary' => false,
        'menuitem' => false,
        'menu' => false
    );

    private $html = array(
        'html' => array(
            'type' => 'block',
            'indentation' => 0
        ),
        'head' => array(
            'type' => 'block',
            'indentation' => 1
        ),
        'meta' => array(
            'type' => 'block',
            'indentation' => 2
        ),
        'link' => array(
            'type' => 'block',
            'indentation' => 2
        ),
        'script' => array(
            'type' => 'block',
            'indentation' => 2
        ),
        'noscript' => array(
            'type' => 'block',
            'indentation' => 2
        ),
        'style' => array(
            'type' => 'block',
            'indentation' => 2
        ),
        'title' => array(
            'type' => 'block',
            'indentation' => 2
        ),
        'base' => array(
            'type' => 'block',
            'indentation' => 2
        ),
        'body' => array(
            'type' => 'block',
            'indentation' => 1
        ),
        'p' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'div' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'section' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'nav' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'article' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'aside' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'address' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'main' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'span' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'a' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'b' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'i' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'u' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'strong' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'em' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'font' => array(
            'type' => 'inline',
            'indentation' => 0,
            'remove' => true
        ),
        'center' => array(
            'type' => 'inline',
            'indentation' => 0,
            'remove' => true
        ),
        'small' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'sub' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'sup' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'ins' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'del' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'mark' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'ruby' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'rt' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'rp' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'bdi' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'bdo' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        's' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'hr' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'br' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'wbr' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'pre' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'code' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'command' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'var' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'blockquote' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'cite' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'q' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'header' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'hgroup' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'h1' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'h2' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'h3' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'h4' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'h5' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'h6' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'footer' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'figure' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'figcaption' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'ul' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'ol' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'li' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'dl' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'dd' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'dt' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'dfn' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'abbr' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'data' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'time' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'table' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'colgroup' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'col' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'thead' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'tr' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'th' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'tbody' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'td' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'tfoot' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'caption' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'samp' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'kdb' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'img' => array(
            'type' => 'inline',
            'indentation' => 0
        ),
        'iframe' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'embed' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'object' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'param' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'video' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'audio' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'source' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'track' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'canvas' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'map' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'area' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'svg' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'math' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'fieldset' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'legend' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'label' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'input' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'button' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'select' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'datalist' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'optgroup' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'option' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'textarea' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'keygen' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'output' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'progress' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'meter' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'details' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'summary' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'menuitem' => array(
            'type' => 'block',
            'indentation' => 'auto'
        ),
        'menu' => array(
            'type' => 'block',
            'indentation' => 'auto'
        )
    );

    private $options = array();

    public function hConstructor($options)
    {
        $this->options['lineLength'] = isset($options['lineLength']) ? $options['lineLength'] : 70;
        $this->options['softTabs']   = isset($options['softTabs']) ? $options['softTabs'] : true;
        $this->options['tabWidth']   = isset($options['tabWidth']) ? $options['tabWidth'] : 4;
        $this->options['compress']   = isset($options['compress']) ? $options['compress'] : false;

        if (!is_numeric($this->options['tabWidth']))
        {
            $this->options['tabWidth'] = 4;
        }
    }

    public function &setOptions(array $options)
    {
        $this->options = $options
        return $this;
    }

    public function &setOption($option, $value)
    {
        $this->option[$option] = $value;
        return $this;
    }

    public function hasEncodedEntity(&$source, $counter)
    {
        $data = '';

        $characters = str_split($source);

        for ($offset = 0; $offset < count($characters); $offset++)
        {
            $character = $characters[$counter + $offset];

            $data .= $character;

            if ($character == ' ' && strlen($data) == 2)
            {
                return false;
            }
            else if ($character == ';')
            {
                $matches = array();

                preg_match('/^\&\#?[A-Z|a-z|0-9]+\;/', $data, $matches);

                if (count($matches))
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
        }

        return false;
    }

    public function splitDataIntoLines($data, $maxCharacterLength = 70)
    {
        $lastSpaceOffset = 0;
        $spaceCounters = array();

        $characters = str_split($data);

        for ($counter = 0, $characterCounter = 0; $counter < count($characters); $counter++)
        {
            $character = $characters[$counter];

            switch ($character)
            {
                case ' ':
                {
                    $lastSpaceOffset = $counter;
                    break;
                }
            }

            if ($characterCounter == $maxCharacterLength)
            {
                array_push($spaceCounters, $lastSpaceOffset);

                $characterCounter = 0;
            }

            $characterCounter++;
        }

        foreach ($spaceCounters as $key => $spacePosition)
        {
            $data = substr($data, 0, $spacePosition) . "\n" . substr($data, $spacePosition + 1);
        }

        return $data;
    }

    public function tidy($source)
    {
        $characters = str_split($source);

        for ($counter = 0; $counter < count($characters); $counter++)
        {
            $character = $characters[$counter];

            switch ($character)
            {
                case '<':
                {
                    if ($this->isData)
                    {
                        $data = trim($this->data);

                        if ($this->data)
                        {
                            $this->data = preg_replace('/\s+/g', ' ', $this->data);

                            array_push(
                                $items,
                                array(
                                    'type' => 'text',
                                    'data' => $this->options['compress'] ? $this->data : $this->splitDataIntoLines($this->data)
                                )
                            );
                        }

                        $this->isData = false;
                        $this->data = '';
                    }

                    $this->data = '<';
                    $this->isTag = true;
                    $this->isTagType = true;

                    break;
                }
                case '/':
                {
                    if ($this->isTag || $this->isData)
                    {
                        $this->data .= $character;
                    }

                    break;
                }
                case '>':
                {
                    if ($this->isTag)
                    {
                        $this->data .= '>';

                        $this->data = preg_replace('/\s+/g', ' ', $this->data);

                        array_push(
                            $items,
                            array(
                                'type' => $this->tagType,
                                'data' => $this->data,
                                'endTag' => strstr($this->data, '</')
                            )
                        );

                        $this->isTag = false;
                        $this->isTagType = false;

                        $this->data = '';
                        $this->tagType = '';
                    }

                    break;
                }
                case ' ':
                case "\t":
                case "\n":
                case "\r":
                case "\s":
                {
                    if ($this->isTagType)
                    {
                        $this->isTagType = false;
                    }

                    if ($this->isData || $this->isTag)
                    {
                        $this->data .= $character;
                    }

                    break;
                }
                case '&':
                {
                    if (!$this->isTag)
                    {
                        if (!$this->hasEncodedEntity($source, $counter))
                        {
                            $this->data .= '&amp;';
                        }
                        else
                        {
                            $this->data .= $character;
                        }
                    }
                    else
                    {
                        $this->data .= $character;
                    }

                    break;
                }
                default:
                {
                    if ($this->isTag)
                    {
                        $this->data .= $character;
                    }

                    if ($this->isTagType)
                    {
                        $this->tagType .= $character;
                    }

                    if (!$this->isTag && !$this->isTagType)
                    {
                        $this->isData = true;
                        $this->data .= $character;
                    }
                }
            }
        }

        $source = '';

        $inBlock = false;
        $blockCounter = 0;
        $inlineCounter = 0;

        foreach ($items as $key => $item)
        {
            if ($this->options['compress'])
            {
                $source .= $item['data'];
            }
            else
            {
                $indentation = '';
                $applyIndentation = true;
                $addNewLine = true;

                $type = isset($this->html[$item['type']]) ? $this->html[$item['type']]['type'] : 'text';

                $lastItem = isset($items[$key - 1]) ? $items[$key - 1] : array();
                $lastType = '';

                if (isset($lastItem['type']))
                {
                    $lastType = isset($this->html[$lastItem['type']]) ? $this->html[$lastItem['type']]['type'] : $lastItem['type'];
                }

                $nextItem = isset($items[$key + 1]) ? $items[$key + 1] : array();
                $nextType = '';

                if (isset($nextItem['type']))
                {
                    $nextType = isset($this->html[$nextItem['type']]) ? $this->html[$nextItem['type']]['type'] : $nextItem['type'];
                }

                $isEndTag = false;

                if ($type == 'inline' && !$item['endTag'])
                {
                    $inlineCounter++;
                }

                if ($type == 'inline' && $item['endTag'])
                {
                    $inlineCounter--;

                    if ($inlineCounter < 0)
                    {
                        $inlineCounter = 0;
                    }

                    if (!$inlineCounter)
                    {
                        $isEndTag = true;
                    }
                }

                if ($type == 'inline' && $inlineCounter == 1 && !$isEndTag)
                {
                    $applyIndentation = true;
                    $addNewLine = false;
                }
                else if ($type == 'text' && $inlineCounter == 1 && !$isEndTag)
                {
                    $applyIndentation = false;
                    $addNewLine = false;
                }
                else if ($inlineCounter > 1)
                {
                    $applyIndentation = false;
                    $addNewLine = false;
                }
                else if ($isEndTag)
                {
                    $applyIndentation = false;
                    $addNewLine = true;
                }

                if ($applyIndentation)
                {
                    if ($item['endTag'] && $type != 'inline')
                    {
                        $blockCounter--;

                        if ($blockCounter < 0)
                        {
                            $blockCounter = 0;
                        }
                    }

                    for ($tabCounter = 0; $tabCounter < $blockCounter; $tabCounter++)
                    {
                        if (!$this->options['softTabs'])
                        {
                            $indentation .= "\t";
                        }
                        else
                        {
                            for ($spaceCounter = 0; $spaceCounter < $this->options['tabWidth']; $spaceCounter++)
                            {
                                $indentation .= ' ';
                            }
                        }
                    }
                }

                if ($item['type'] == 'text')
                {
                    $lines = explode("\n", $item['data']);

                    for ($lineCounter = 0; $lineCounter < count($lines); $lineCounter++)
                    {
                        $lines[$lineCounter] = ($applyIndentation ? $indentation : '') . $lines[$lineCounter];
                    }

                    $source .= implode("\n", $lines);
                }
                else
                {
                    $source .= ($applyIndentation ? $indentation : '') . $item['data'];
                }

                if ($addNewLine)
                {
                    if ($item['type'] == 'text' || $type == 'block' || $item['endTag'] && $type == 'inline')
                    {
                        $source .= "\n";
                    }
                }

                if (isset($this->html[$type]) && $this->html[$type]['type'] == 'block')
                {
                    if (!$item['endTag'])
                    {
                        $blockCounter++;
                    }
                }
            }
        }

        return $source;
    }
}

?>