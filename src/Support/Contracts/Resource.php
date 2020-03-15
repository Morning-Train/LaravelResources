<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

abstract class Resource
{

    public $namespace;
    public $name;
    public $base_name;

    public function __construct(string $namespace, string $name)
    {
        $this->namespace = $namespace;
        $this->name      = $name;
        $this->base_name = static::getBaseName($name);
    }

    /////////////////////////////////
    /// Basic helpers
    /////////////////////////////////

    protected $_identifiers = [];

    public function identifier($operationName = null)
    {

        if(!isset($this->_identifiers[$operationName])) {

            $parts = [
                $this->namespace,
                $this->name,
            ];

            if($operationName !== null) {
                array_push($parts, $operationName);
            }

            $this->_identifiers[$operationName] = implode('.', $parts);

        }

        return $this->_identifiers[$operationName];
    }

    public static function getBaseName(string $name)
    {
        return Str::snake(Arr::last(explode('.', $name)));
    }

    /////////////////////////////////
    /// Export to JS
    /////////////////////////////////

    public function export()
    {
        $exportData = [];

        if ($this->hasOperations()) {
            foreach ($this->getOperations() as $operation) {
                $exportData[$operation->name] = $operation->export();
            }
        }

        return [
            "name"       => $this->name,
            "operations" => $exportData,
        ];
    }

    /////////////////////////////////
    /// Routes
    /////////////////////////////////

    public function routes($options = [])
    {
        Route::group(['resource' => $this->name],
            function () use($options) {
                if ($this->hasOperations()) {
                    foreach ($this->getOperations() as $operation) {
                        $operation->route($options);
                    }
                }
            });
    }

    public function getBasePath()
    {
        return preg_replace('/\./', '/', $this->name);
    }

    /////////////////////////////////
    /// Operations
    /////////////////////////////////

    protected static $_cached_operations = [];

    /// Should be overridden if providing operation class names
    protected static $operations = [];

    public function operations()
    {
        /// Should be overridden if providing operation instances instead of classes
        return [];
    }

    public function operation($slug)
    {
        if ($this->hasOperation($slug)) {
            return $this->getOperations()[$slug];
        }

        throw new \Exception("Tried to get operation ($slug), but it is not found on resource (" . $this->identifier() . ")");

    }

    public function getOperations()
    {
        if (!isset(static::$_cached_operations[$this->identifier()])) {

            $raw_class_operations = static::$operations;

            $operations = [];

            if (is_array($raw_class_operations) && !empty($raw_class_operations)) {
                foreach ($raw_class_operations as $key => $operation) {

                    if (!class_exists($operation)) {
                        throw new \Exception("Supplied operation ($operation) on resource (" . $this->identifier() . "), but it is not a class!");
                    }

                    if (is_int($key)) {
                        $key = Str::snake(class_basename($operation));
                    }
                    $operations[$key] = new $operation;
                }
            }

            $instance_operations = $this->operations();

            if (is_array($instance_operations) && !empty($instance_operations)) {
                foreach ($instance_operations as $key => $operation) {
                    if (is_int($key)) {
                        $key = Str::snake(class_basename(get_class($operation)));
                    }
                    $operations[$key] = $operation;
                }
            }

            if (empty($operations)) {
                throw new \Exception('Looking for operations on resource: ' . $this->identifier() . ', but none was found!');
            }

            foreach ($operations as $name => $operation) {
                $this->bootOperation($name, $operation);
            }

            static::$_cached_operations[$this->identifier()] = $operations;
        }

        return static::$_cached_operations[$this->identifier()];
    }

    public function hasOperations()
    {
        return is_array($this->getOperations()) && !empty($this->getOperations());
    }

    public function hasOperation($operationName)
    {
        return isset($this->getOperations()[$operationName]);
    }

    public function bootOperation(string $name, Operation $operation)
    {
        /// Set current resource on operation
        $operation->resource($this);
        $operation->name($name);

        // Method to be called for this specific operation
        $method   = Str::camel('configure_' . $operation->name . '_operation');
        $callable = [$this, $method];
        if (method_exists($this, $method) && is_callable($callable)) {
            call_user_func($callable, $operation);
        }

        /// Generic function to always be called on the operation
        $method   = Str::camel('configure_operation');
        $callable = [$this, $method];
        if (method_exists($this, $method) && is_callable($callable)) {
            call_user_func($callable, $operation);
        }
    }

}
