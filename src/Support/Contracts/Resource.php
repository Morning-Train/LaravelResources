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

}
