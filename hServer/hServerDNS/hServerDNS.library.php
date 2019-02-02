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

class hServerDNSLibrary extends hPlugin {

    const name = "[a-z|A-Z|0-9|\.|\:|\@|\*]*";
    const type = "SOA|NS|AAAA|A|MX|CNAME|AFSDB|APL|CERT|DHCId|DLV|DNAME|DNSKEY|DS|HIP|IPSECKEY|KEY|KX|LOC|NAPTR|NSEC|NSEC3|NSEC3PARAM|PTR|RRSIG|RP|SIG|SPF|SRV|SSHFP|TA|TKEY|TLSA|TSIG|TXT|AXFR|IXFR|OPT";

    public function parseZoneFile($path)
    {
        $zoneFile = file_get_contents($path);

        $zoneFile = preg_replace_callback(
            '/(\;.*)$/m',
            array(
                $this,
                'replaceComments'
            ),
            $zoneFile
        );

        preg_match_all(
            '/^('.self::name.')\s*(\d*?)\s*?(IN?)\s*?('.self::type.')\s*((\d*)\s+?)('.self::name.')\s*(('.self::name.')\s*\(?)$/m',
            $zoneFile,
            $matches
        );

        $records = array();

        foreach ($matches[4] as $i => $type)
        {
            $type = trim(
                strtoupper($type)
            );

            $recordName = strtolower($matches[1][0]);

            $name = strtolower(
                trim($matches[1][$i])
            );

            $server = strtolower(
                trim($matches[7][$i])
            );

            $minimum = (int) trim($matches[2][$i]);

            $priority = (int) trim($matches[5][$i]);

            $record = array(
                'name' => !empty($name)? $name : $recordName,
                'server' => $type != 'MX'? ($server? $server : '') : '',
                'type' => $type,
                'minimum' => $minimum
            );

            if ($type == 'MX')
            {
                $record['mx'] = array(
                    'priority' => $priority,
                    'server' => $server
                );
            }

            array_push($records, $record);
        }

        if (!empty($matches[9][0]))
        {
            $zone['email'] = $matches[9][0];
        }

        $zone = array_merge(
            $this->getInParenthesis($zoneFile),
            $zone
        );

        $zone['records'] = $records;

        return $zone;
    }

    public function replaceComments($matches)
    {
        //var_dump($matches);

        return '';
    }

    public function getInParenthesis($string)
    {
        $characters = str_split($string);

        $inParenthesis = false;
        $nesting = 0;
        $offsetCounter = 0;
        $parenthesisCount = 0;
        $comment = false;

        $capture = '';

        while (list($i, $character) = each($characters))
        {
            $current = current($characters);

            switch ($character)
            {
                case '(':
                {
                    $inParenthesis = true;
                    $parenthesisCount++;
                    $nesting++;
                    break;
                }
                case ')':
                {
                    $nesting--;

                    if (!$nesting)
                    {
                        $inParenthesis = false;
                    }

                    break;
                }
                case ';':
                {
                    $comment = true;
                    break;
                }
                case "\n":
                {
                    $comment = false;
                    break;
                }
            }

            if ($inParenthesis && $nesting > 0 && !$comment)
            {
                $capture .= $character;

                if ($current == ')')
                {
                    $capture .= ')';
                }
            }
        }

        $capture = substr(
            trim($capture),
            1,
            -1
        );

        preg_match_all(
            '/^\s*([\d|A-Z|a-z|\.]+)\s*$/m',
            $capture,
            $matches
        );

        return array(
            'serial'  => $matches[1][0],
            'refresh' => $matches[1][1],
            'retry'   => $matches[1][2],
            'expire'  => $matches[1][3],
            'minimum' => $matches[1][4]
        );
    }
}

?>