<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

abstract class Resource
{

    public $namespace;
    public $name;

    public function __construct()
    {
        $this->name = static::getName();
    }

    /////////////////////////////////
    /// Basic helpers
    /////////////////////////////////

    public static function getName()
    {
        return Str::snake(class_basename(get_called_class()));
    }

    /////////////////////////////////
    /// Setup
    /////////////////////////////////

    protected $has_booted = false;

    public function boot($namespace)
    {
        if (!$this->has_booted) {
            $this->namespace = $namespace;
            $this->configureOperations();
            $this->has_booted = true;
        }
    }

    /////////////////////////////////
    /// Export to JS
    /////////////////////////////////

    public function export()
    {
        $exportData = [];

        if($this->hasOperations()){
            foreach($this->getOperations() as $operation) {
                $exportData[$operation->name] = $operation->export();
            }
        }

        return $exportData;
    }

    /////////////////////////////////
    /// Routes
    /////////////////////////////////

    public function routes()
    {
        Route::group(['resource' => get_called_class()], function () {
            if($this->hasOperations()) {
                foreach($this->getOperations() as $operation) {
                    $operation->routes();
                }
            }
        });
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

        throw new \Exception("Tried to get operation ($slug), but it is not found on resource (" . $this->name . ")");

    }

    public function getOperations()
    {
        if (!isset(static::$_cached_operations[get_class($this)])) {

            $raw_class_operations = static::$operations;

            $operations = [];

            if (is_array($raw_class_operations) && !empty($raw_class_operations)) {
                foreach ($raw_class_operations as $key => $operation) {

                    if (!class_exists($operation)) {
                        throw new \Exception("Supplied operation ($operation) on resource (" . get_class($this) . "), but it is not a class!");
                    }

                    if (is_int($key)) {
                        $key = Str::snake(strtolower(class_basename($operation)));
                    }
                    $operations[$key] = new $operation;
                }
            }

            $instance_operations = $this->operations();

            if (is_array($instance_operations) && !empty($instance_operations)) {
                foreach ($instance_operations as $key => $operation) {
                    if (is_int($key)) {
                        $key = Str::snake(strtolower(class_basename(get_class($operation))));
                    }
                    $operations[$key] = $operation;
                }
            }

            if (empty($operations)) {
                throw new \Exception('Looking for operations on resource: ' . get_class($this) . ', but none was found!');
            }

            static::$_cached_operations[get_class($this)] = $operations;
        }

        return static::$_cached_operations[get_class($this)];
    }

    public function hasOperations()
    {
        return is_array($this->getOperations()) && !empty($this->getOperations());
    }

    public function hasOperation($operationName)
    {
        return isset($this->getOperations()[$operationName]);
    }

    public function configureOperations()
    {
        if ($this->hasOperations()) {
            foreach ($this->getOperations() as $operationName => $operation) {

                /// Set current resource on operation
                $operation->resource($this);
                $operation->name($operationName);

                // Method to be called for this specific operation
                $method = Str::camel('configure_' . $operation->name . '_operation');
                $callable = [$this, $method];
                if (method_exists($this, $method) && is_callable($callable)) {
                    call_user_func($callable, $operation);
                }

                /// Generic function to always be called on the operation
                $method = Str::camel('configure_operation');
                $callable = [$this, $method];
                if (method_exists($this, $method) && is_callable($callable)) {
                    call_user_func($callable, $operation);
                }

            }
        }
    }

}
