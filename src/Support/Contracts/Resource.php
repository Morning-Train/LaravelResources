<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use ReflectionClass;

abstract class Resource
{

    public $namespace;
    public $name;
    public $base_name;
    public $identifier;
    protected $_operations;

    public function __construct(string $namespace, string $name)
    {
        $this->namespace = $namespace;
        $this->name = $name;
        $this->base_name = static::getBaseName($name);
        $this->identifier = $this->identifier();
        $this->_operations = static::$operations;
    }

    /////////////////////////////////
    /// Basic helpers
    /////////////////////////////////

    protected $_identifiers = [];

    public function identifier($operationName = null)
    {

        if (!isset($this->_identifiers[$operationName])) {

            $parts = [
                $this->namespace,
                $this->name,
            ];

            if ($operationName !== null) {
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
            foreach ($this->getOperations() as $operationName => $operationClass) {

                $operation = $this->operation($operationName);
                $exportData[$operation->name] = $operation->export();

            }
        }

        return [
            "name" => $this->name,
            "operations" => $exportData,
        ];
    }

    /////////////////////////////////
    /// Routes
    /////////////////////////////////

    public function routes($options = [])
    {
        Route::group(['resourceName' => $this->name],
            function () use ($options) {
                if ($this->hasOperations()) {
                    foreach ($this->getOperations() as $operationName => $operationClass) {

                        $operation = $this->operation($operationName);
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

    public function operation($operationName)
    {
        if ($this->hasOperation($operationName)) {
            return $this->bootOperation($operationName);
        }

        throw new \Exception("Tried to get operation ($operationName), but it is not found on resource (" . $this->identifier . ")");
    }

    public function getOperations()
    {
        $resource_identifier = $this->identifier;

        if (!isset(static::$_cached_operations[$resource_identifier])) {

            $raw_class_operations = $this->_operations;

            $operations = [];

            if (is_array($raw_class_operations) && !empty($raw_class_operations)) {
                foreach ($raw_class_operations as $operationName => $operation) {

                    if (!class_exists($operation)) {
                        throw new \Exception("Supplied operation ($operation) on resource (" . $resource_identifier . "), but it is not a class!");
                    }

                    if (is_int($operationName)) {
                        $operationName = Str::snake(class_basename($operation));
                    }

                    $operations[$operationName] = $operation;
                }
            }

            if (empty($operations)) {
                throw new \Exception('Looking for operations on resource: ' . $resource_identifier . ', but none was found!');
            }

            static::$_cached_operations[$resource_identifier] = $operations;
        }

        return static::$_cached_operations[$resource_identifier];
    }

    public function hasOperations()
    {
        return is_array($this->getOperations()) && !empty($this->getOperations());
    }

    public function hasOperation($operationName)
    {
        return isset($this->getOperations()[$operationName]);
    }

    public function getOperation($operationName)
    {
        return $this->getOperations()[$operationName];
    }

    public function getBootedOperations()
    {
        $operations = [];

        if ($this->hasOperations()) {
            foreach ($this->getOperations() as $operationName => $operationClass) {
                $operations[] = $this->operation($operationName);
            }
        }

        return $operations;
    }

    public function bootOperations()
    {

        $operationNames = $this->getOperations();

        if (!is_array($operationNames) || empty($operationNames)) {
            return;
        }

        foreach ($operationNames as $operationName => $operationClass) {
            $this->bootOperation($operationName);
        }
    }

    protected $booted_operations = [];

    public function bootOperation(string $operationName)
    {

        if (in_array($operationName, $this->booted_operations)) {
            return $this->booted_operations[$operationName];
        }

        if (!$this->hasOperation($operationName)) {
            throw new \Exception("Tried to boot operation $operationName in {$this->identifier}, but it is not configured.");
        }

        $operationClass = $this->getOperation($operationName);

        if (!class_exists($operationClass)) {
            throw new \Exception("Tried to boot operation $operationName in {$this->identifier}, but it does not exist.");
        }

        $operation = (new ReflectionClass($operationClass))->newInstanceArgs([$this, $operationName]);

        // Method to be called for this specific operation
        $method = Str::camel($operation->name . '_operation');
        $callable = [$this, $method];
        if (method_exists($this, $method) && is_callable($callable)) {
            call_user_func($callable, $operation);
        }

        $this->booted_operations[$operationName] = $operation;

        return $operation;
    }

}
