<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use Illuminate\Support\Arr;

class Payload
{

    protected $_data = [];

    public function __construct(Operation $operation)
    {
        $this->operation = $operation;
    }

    public function setRequestArguments()
    {
        $this->args = func_get_args();
    }

    public function set($path, $value)
    {
        Arr::set($this->_data, $path, $value);
    }

    public function get($path, $default = null)
    {
        return Arr::get($this->_data, $path, $default);
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

}