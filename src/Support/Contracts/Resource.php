<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use MorningTrain\Laravel\Resources\Support\Traits\HasOperations;

abstract class Resource
{
    use HasOperations;

    public $namespace;
    public $name;

    public function __construct()
    {
        $this->name = static::getName();
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

    /////////////////////////////////
    /// Export to JS
    /////////////////////////////////

    public function export()
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

    /////////////////////////////////
    /// Routes
    /////////////////////////////////

    public static function routes()
    {

        Route::group(['resource' => get_called_class()], function () {

            if (static::hasOperations()) {
                foreach (static::getOperations() as $operationSlug => $operationClass) {
                    static::getOperationInstance($operationSlug)->routes();
                }
            }

        });

    }

}
