<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;
use MorningTrain\Laravel\Resources\Http\Controllers\ResourceController;
use MorningTrain\Laravel\Support\Traits\StaticCreate;

abstract class Operation
{
    use StaticCreate;

    public $data = null;

    /////////////////////////////////
    /// Request helpers
    /////////////////////////////////

    public function prepare($parameters)
    {
        //
    }

    public function execute()
    {
        return new Payload($this, $this->handle($this->data));
    }

    public function handle($model_or_collection = null)
    {
        return $model_or_collection;
    }

    /////////////////////////////////
    /// Helpers
    /////////////////////////////////

    public function genericGetSet($name, $value = null)
    {
        if ($value === null) {
            return $this->{$name};
        }
        $this->{$name} = $value;
        return $this;
    }

    public function identifier()
    {
        return implode('.',
            [
                $this->resource()->namespace,
                'resources',
                $this->resource()->name,
                $this->name,
            ]);
    }

    /////////////////////////////////
    /// Name
    /////////////////////////////////

    public $name;

    public function name($value = null)
    {
        return $this->genericGetSet('name', $value);
    }

    public static function getName()
    {
        return Str::snake(class_basename(get_called_class()));
    }

    /////////////////////////////////
    /// Resource
    /////////////////////////////////

    protected $resource;

    public function resource($value = null)
    {
        return $this->genericGetSet('resource', $value);
    }

    /////////////////////////////////
    /// Middleware
    /////////////////////////////////

    protected $middlewares = [];

    public function middlewares($value = null)
    {
        return $this->genericGetSet('middlewares', $value);
    }

    /////////////////////////////////
    /// Permissions
    /////////////////////////////////

    protected $restricted = false;

    public function restrict($value = true)
    {
        return $this->genericGetSet('restricted', $value);
    }

    public function canExecute()
    {
        if (!$this->restricted) {
            return true;
        }

        if (!Auth::check()) {
            return false;
        }

        $data = $this->data instanceof Collection ?
            $this->data : collect([$this->data]);

        return $data->every(function ($model) {
            return Auth::user()->can($this->identifier(), $model);
        });
    }

    /////////////////////////////////
    /// Exporting
    /////////////////////////////////

    public function export()
    {
        return [
            "name" => $this->name,
        ];
    }

    /////////////////////////////////
    /// Meta data for response payload
    /////////////////////////////////

    public function getMeta()
    {
        return [];
    }

    /////////////////////////////////
    /// Routes
    /////////////////////////////////

    const ROUTE_METHOD = 'get';

    const VALID_ROUTE_METHODS = [
        'ANY',
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'OPTIONS',
    ];


    public function getRoutePath()
    {
        return join('/',
            [
                $this->resource->getBasePath(),
                $this->name,
                "{" . $this->resource->base_name . "?}",
            ]);
    }

    public function routes()
    {

        $route_group_props = ['operation'          => $this->name,
                              'resource_namespace' => $this->resource()->namespace];

        $middlewares = $this->middlewares();

        if ($this->restricted) {
            /// TODO - this assumes the Spatie middleware is registered
            /// Either check if it is registered -
            /// Or throw a more useful exception
            /// throw new \Exception('A restricted operation requires the "permission" middleware to be registered in the application');
            $middlewares[] = 'permission:' . $this->identifier();

            $middlewares[] = 'auth:' . $this->resource()->namespace;
        }

        if (!empty($middlewares)) {
            $route_group_props['middleware'] = $middlewares;
        }

        Route::group($route_group_props,
            function () {

                $route_path       = $this->getRoutePath();
                $route_controller = '\\' . ResourceController::class . '@executeOperation';

                $route = Route::name($this->identifier());

                $callable = [$route, strtolower(static::ROUTE_METHOD)];

                if (!in_array(strtoupper(static::ROUTE_METHOD),
                    static::VALID_ROUTE_METHODS)) {
                    throw new \Exception('Invalid route method name provided');
                }

                call_user_func($callable, $route_path, $route_controller);
            });

    }

}
