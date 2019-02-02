<?php

class func {

    public function call(callable $fn, _array $arguments = null)
    {
        if ($arguments && $arguments->length)
        {
            return call_user_func_array($fn, $arguments->get);
        }
        else
        {
            return call_user_func($fn);
        }
    }
}

class _function {

    public $self;

    public function __construct(callable $fn)
    {
        $this->self = $fn;
    }
    
    public function call(_array $arguments = null)
    {
        return fn::$function->call($this->self, $arguments);
    }
}

fn::$function = new func();


?>