<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use MorningTrain\Laravel\Resources\Support\Traits\HasOperations;

abstract class Resource
{
    use HasOperations;


    protected $resource_instance;
    protected $resource_type;
    public $name;

    public function __construct($resource = null)
    {
        $this->resource_instance = $resource;

        $this->name = static::getName();

    }

    public static function instance($resource = null)
    {
        return new static($resource);
    }


    /////////////////////////////////
    /// Request helpers
    /////////////////////////////////

    // TODO - Move to Operation + update in favor of permissions package
    //public static function getPermittedOperations(bool $trans = false)
    //{
    //    $user = \Auth::user();
    //    $ops = static::getOperations($trans);

    //    return Arr::where($ops, function ($val, $key) use ($user) {
    //        return $user->can($key, static::instance());
    //    });
    //}

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

    public function boot()
    {
        $this->configureOperations();
    }

    /////////////////////////////////
    /// Operations
    /////////////////////////////////

    public function configureOperation($operationSlug, $operationClass)
    {

        //Create action instance
        $this->instantiateOperation($operationSlug, $operationClass);

        //Call method to configure action
        $method = Str::camel('configure_' . $operationSlug . '_operation');

        $callable = [$this, $method];

        $operation = $this->operation($operationSlug);

        $operation->resource($this);

        if (method_exists($this, $method) && is_callable($callable)) {
            call_user_func($callable, $operation);
        }

    }

    public function canPerformOperation($operationSlug)
    {
        $has_access = true; /// TODO - Permissions check
        return $this->hasOperation($operationSlug) === true && $has_access;
    }

    /////////////////////////////////
    /// Export to JS
    /////////////////////////////////

    public function exportOperations()
    {
        $actions = [];

        if (static::hasOperations()) {
            foreach (static::getOperations() as $operationSlug => $operationClass) {
                $instance = $this->operation($operationSlug);
                $actions[($operationClass::getName())] = $instance->export();
            }
        }

        return $actions;
    }

    public function getModelKeyName()
    {
        return null;
    }

    /////////////////////////////////
    /// Routes
    /////////////////////////////////

    public static function routes($namespace)
    {

        Route::group(['resource' => get_called_class()], function () use ($namespace) {

            if (static::hasOperations()) {
                foreach (static::getOperations() as $operationSlug => $operationClass) {
                    static::getOperationInstance($operationSlug)->namespace($namespace)->routes();
                }
            }

        });

    }

}
