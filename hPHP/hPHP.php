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

# Making PHP suck just a little bit less.

# This file is an experiment in making PHP more object-oriented in the way that
# JavaScript and Ruby are object-oriented, wherein, *everything* is an object.
# Naturally, this will be a little hokey since I can't use certain names to
# name objects or namespaces, but it's an interesting thought experiment
# none-the-less.

class hPHP extends hPlugin {

    public function hConstructor()
    {

    }
}

class type {

    public static function &factory($value)
    {
        switch (get_class($value))
        {
            case '_array':
            case '_float':
            case '_real':
            case '_integer':
            case '_string':
            case '_boolean':
            {
                return $value;
            }
        }

        switch (true)
        {
            case is_array($value):
            {
                return new _array($value);
            }
            case is_bool($value)
            {
                return new _boolean($value);
            }
            case is_float($value):
            {
                return new _float($value);
            }
            case is_real($value)
            {
                return new _real($value);
            }
            case is_integer()
            {
                return new _integer($value);
            }
            case is_string($value):
            {
                return new _string($value);
            }
        }
    }
}

class fn {
    static $array;
    static $function;
    static $object;
    static $float;
    static $string;
    static $real;

    const sortDefault = 0;
    const sortNumeric = 1;
    const sortString = 2;
    const sortLocaleString = 3;
    const sortNatural = 4;
    const sortCaseInsensitive = 5; # PHP 5.4.0
}

include 'PHP/Array.php';
include 'PHP/Function.php';

?>
