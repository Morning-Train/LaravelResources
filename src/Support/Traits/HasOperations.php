<?php

namespace MorningTrain\Laravel\Resources\Support\Traits;


trait HasOperations
{

    protected static $operations = [];
    protected static $operation_instances = [];

    public function operation($slug)
    {
        return static::$operation_instances[get_called_class()][$slug];
    }

    public static function getOperationInstances()
    {
        return static::$operation_instances[get_called_class()];
    }

    protected function instantiateOperation($operationClass)
    {
        if (!isset(static::$operation_instances[get_called_class()])) {
            static::$operation_instances[get_called_class()] = [];
        }
        static::$operation_instances[get_called_class()][$operationClass] = new $operationClass($this);
    }

    public static function getOperations(bool $trans = false)
    {
        return static::$operations;
    }

    protected static function hasOperations()
    {
        return is_array(static::getOperations()) && !empty(static::getOperations());
    }

    public function hasOperation($operationSlug)
    {
        return in_array($operationSlug, static::getOperations());
    }

}
