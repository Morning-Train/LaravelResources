<?php

namespace MorningTrain\Laravel\Resources\Support\Traits;


use Illuminate\Support\Str;

trait HasOperations
{

    protected static $operations = [];
    protected static $operation_instances = [];
    protected static $_cached_operations = [];

    public function operation($slug)
    {
        return static::$operation_instances[get_called_class()][$slug];
    }

    public static function getOperationInstances()
    {
        return static::$operation_instances[get_called_class()];
    }


    public static function getOperationInstance($slug)
    {
        return static::getOperationInstances()[$slug];
    }

    protected function instantiateOperation($operationSlug, $operationClass)
    {
        if (!isset(static::$operation_instances[get_called_class()])) {
            static::$operation_instances[get_called_class()] = [];
        }
        static::$operation_instances[get_called_class()][$operationSlug] = new $operationClass($this, $operationSlug);
    }

    public static function getOperations()
    {
        if (!isset(static::$_cached_operations[get_called_class()])) {
            $raw_operations = static::$operations;

            if (!is_array($raw_operations) || empty($raw_operations)) {
                throw new \Exception('About to get operations for ' . get_called_class() . ', but none was found!');
            }

            $operations = [];

            foreach ($raw_operations as $key => $operation) {
                if (is_int($key)) {
                    $key = Str::snake(strtolower(class_basename($operation)));
                }
                $operations[$key] = $operation;
            }

            static::$_cached_operations[get_called_class()] = $operations;
        }

        return static::$_cached_operations[get_called_class()];
    }

    protected static function hasOperations()
    {
        return is_array(static::getOperations()) && !empty(static::getOperations());
    }

    public function hasOperation($operationSlug)
    {
        return in_array($operationSlug, static::getOperations());
    }

    public function configureOperations()
    {
        if (static::hasOperations()) {
            foreach (static::getOperations() as $operationSlug => $operationClass) {
                $this->configureOperation($operationSlug, $operationClass);
            }
        }
    }

}
