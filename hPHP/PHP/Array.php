<?php


class arr {

    private function &unpackage(&$item)
    {
        $item = is_object($item)? $item->self : $item;
        return $this;
    }
    
    public function &__call($method, $arguments)
    {
        foreach ($arguments as &$argument)
        {
            $this->unpackage($argument);
        }

        switch ($method)
        {
            case 'includes':     return type::factory(in_array($arguments[0], $arguments[1], isset($arguments[2])? $arguments[2] : false));
            case 'next':         return type::factory(next($arguments[0]));
        }
    }

    public function &includes($array, $needle, $strict = false)
    {
        $this->unpackage($array)->unpackage($needle)->unpackage($strict);
        return type::factory(in_array($needle, $array, $strict));
    }

    public function &next(&$array)
    {
        $this->unpackage($array);
        return type::factory(next($array));
    }
    
    public function &previous(&$array)
    {
        $this->unpackage($array);
        return type::factory(prev($array));
    }

    public function &current(&$array)
    {
        $this->unpackage($array);
        return type::factory(current($array));
    }
    
    public function &end(&$array)
    {
        $this->unpackage($array);
        return type::factory(end($array));
    }

    public function &length($array)
    {
        $this->unpackage($array);
        return type::factory(count($array));
    }
    
    public function &count($array)
    {
        $this->unpackage($array);
        return type::factory(count($array));
    }

    public function &reverse(&$array, $preserveKeys = true)
    {
        $this->unpackage($array)->unpackage($preserveKeys);
        return type::factory(array_reverse($array, $preserveKeys));
    }

    public function &random($array, $howMany = 1)
    {
        $this->unpackage($array)->unpackage($howMany);
        return type::factory(array_rand($array, $howMany));
    }
    
    public function &shift(&$array)
    {
        $this->unpackage($array);
        return type::factory(array_shift($array));
    }
    
    public function &pop(&$array)
    {
        $this->unpackage($array);
        return type::factory(array_pop($array));
    }
    
    public function &push(&$array)
    {
        $this->unpackage($array);

        $arguments = func_get_args();
        unset($arguments[0]);

        foreach ($arguments as $argument)
        {
            array_push($array, $argument);
        }

        return type::factory(count($arguments));
    }

    public function &unshift(&$array)
    {
        $this->unpackage($array);

        $arguments = func_get_args();
        unset($arguments[0]);
        
        foreach ($arguments as $argument)
        {
            array_unshift($array, $argument);
        }

        return type::factory(count($arguments));
    }

    public function &shuffle(&$array)
    {
        $this->unpackage($array);
        return type::factory(shuffle($array));
    }
    
    public function &join($array, $glue = null)
    {
        $this->unpackage($array)->unpackage($glue);
        return type::factory(implode($glue, $array));
    }
    
    public function &implode($array, $glue = null)
    {
        $this->unpackage($array)->unpackage($glue);
        return type::factory(implode($glue, $array));
    }
    
    public function &keys($array)
    {
        $this->unpackage($array);
        return type::factory(array_keys($array));
    }
    
    public function &values($array)
    {
        $this->unpackage($array);
        return type::factory(array_values($array));
    }

    public function &sort(&$array, $preserveKeys = true, $flags = fn::sortDefault)
    {
        $this->unpackage($array)->unpackage($preserveKeys)->unpackage($flags);

        if ($preserveKeys)
        {
            return type::factory(asort($array, $flags));
        }
        else
        {
            return type::factory(sort($array, $flags));
        }
    }

    public function &reverseSort(&$array, $preserveKeys = true, $flags = fn::sortDefault)
    {
        $this->unpackage($array);

        if ($preserveKeys)
        {
            return type::factory(arsort($array, $flags));
        }
        else
        {
            return type::factory(rsort($array, $flags));
        }
    }
    
    public function &sortByKey(&$array, $flags = fn::sortDefault)
    {
        $this->unpackage($array);

        return type::factory(ksort($array, $flags));
    }
    
    public function &reverseSortByKey(&$array, $flags = fn::sortDefault)
    {
        $this->unpackage($array);

        return type::factory(krsort($array, $flags));
    }
    
    public function &flip(&$array)
    {
        $this->unpackage($array);
        return type::factory(array_flip($array));
    }
    
    public function &merge()
    {
        $arguments = func_get_args();

        $array = array();

        foreach ($arguments as $i => $argument)
        {
            if (is_array($argument))
            {
                foreach ($argument as $key => $value)
                {
                    $array[$key] = type::factory($value);
                }
            }
            else if (is_object($argument))
            {
                $_array = $argument->get;
                
                foreach ($_array as $key => $value)
                {
                    $array[$key] = type::factory($value);
                }
            }
            else
            {
                $array[] = $argument;
            }
        }

        return type::factory($array);
    }
}

fn::$array = new arr();

class _array {

    public $self;

    public function __construct()
    {
        $arguments = func_get_args();

        if (count($arguments) > 1)
        {
            $this->self = $arguments;
        }
        else
        {
            $this->self = $arguments[0];
        }
    }
    
    public function __set($key, $value)
    {
        switch ($key)
        {
            case 'set':
            {
                $this->self = $value;
                return;
            }
        }
    }

    public function &__get($key)
    {
        if (method_exists($this, $key))
        {
            return call_user_func_array(array($this, $key), array());
        }
    
        switch($key)
        {
            case 'get':
            {
                return $this->self;
            }
            case 'sizeOf':
            case 'count':
            {
                return $this->length();
            }
            case 'implode':
            {
                return $this->join();
            }
        }
    }

    public function &join($separator = null)
    {
        return fn::$array->join($this->self, $separator);
    }
    
    public function &implode($separator = null)
    {
        return fn::$array->implode($this->self, $separator);
    }

    public function &shuffle()
    {
        return fn::$array->shuffle($this->self);
    }
    
    public function &random($howMany = 1)
    {
        return fn::$array->random($this->self, $howMany);
    }

    public function &length()
    {
        return fn::$array->length($this->self);
    }
    
    public function count()
    {
        return fn::$array->length($this->self);
    }

    public function reverse($preserveKeys = true)
    {
        return fn::$array->reverse($this->self, $preserveKeys);
    }

    public function shift()
    {
        return fn::$array->shift($this->self);
    }
    
    public function pop()
    {
        return fn::$array->pop($this->self);
    }
    
    public function push()
    {
        $arguments = func_get_args();
        
        foreach ($arguments as $argument)
        {
            fn::$array->push($this->self, type::factory($argument));
        }

        return type::factory(count($arguments));
    }

    public function unshift()
    {
        $arguments = func_get_args();
        
        foreach ($arguments as $argument)
        {
            fn::$array->unshift($this->self, type::factory($argument));
        }

        return count($arguments);
    }
    
    public function keys()
    {
        return fn::$array->keys($this->self);
    }
    
    public function values()
    {
        return fn::$array->values($this->self);
    }
    
    public function sort($preserveKeys = true, $flags = fn::sortDefault)
    {
        return fn::$array->sort($this->self, $preserveKeys, $flags);
    }

    public function reverseSort($preserveKeys = true, $flags = fn::sortDefault)
    {
        return fn::$array->reverseSort($this->self, $preserveKeys, $flags);
    }
    
    public function sortByKey($flags = fn::sortDefault)
    {
        return fn::$array->sortByKey($this->self, $flags);
    }

    public function reverseSortByKey($flags = fn::sortDefault)
    {
        return fn::$array->reverseSortByKey($this->self, $flags);
    }
    
    public function flip()
    {
        return fn::$array->flip($this->self);
    }

    public function includes($search, $strict = false)
    {
        return fn::$array->includes($this->self, $search, $strict);
    }
    
    public function merge()
    {
        $arguments = func_get_args();
    
        return call_user_func_array(
            array(fn::$array, 'merge'), 
            array_merge($this->self, $arguments)
        );
    }

    public function each(_function $fn)
    {
        foreach ($this->self as $key => $value)
        {
            $result = $fn->call(
                new _array(
                    array(
                        'key' => $key,
                        'value' => $value
                    )
                )
            );
            
            if ($result === false)
            {
                break;
            }

            if ($result === true)
            {
                continue;
            }
        }
    }
}

# $array = new _array([1, 2, 3]);

# $array = type::factory([1, 2, 3]);

# $array->length
# $array->implode();
# 
# $array->random();
# $array->count();

?>