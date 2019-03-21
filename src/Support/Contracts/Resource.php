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
        if (static::hasOperations()) {
            foreach (static::$operations as $action) {
                $this->configureOperation($action);
            }
        }
    }

    /////////////////////////////////
    /// Operations
    /////////////////////////////////

    public function configureOperation($operationSlug)
    {

        //Create action instance
        $this->instantiateOperation($operationSlug);

        //Determine the pretty name of the operation
        $action_name = (strtolower($operationSlug))::getName();

        //Call method to configure action
        $method = Str::camel('configure_' . $action_name . '_action');

        $callable = [$this, $method];

        $action_instance = $this->operation($operationSlug);

        $action_instance->resource($this);

        if (method_exists($this, $method) && is_callable($callable)) {
            call_user_func($callable, $action_instance);
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
            foreach (static::$operations as $action) {
                $instance = $this->operation($action);
                $actions[($action::getName())] = $instance->export();
            }
        }

        return $actions;
    }

    /////////////////////////////////
    /// Routes
    /////////////////////////////////

    public static function routes($namespace)
    {

        $resource = get_called_class();
        $name = static::getName();

        Route::group(['resource' => $resource], function () use ($namespace, $name) {

            if (static::hasOperations()) {
                foreach (static::$operations as $operation) {
                    ($operation)::routes($namespace, $name);
                }
            }

        });

    }

}
