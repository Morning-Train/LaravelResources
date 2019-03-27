<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use MorningTrain\Laravel\Resources\Http\Controllers\ResourceController;
use MorningTrain\Laravel\Support\Traits\StaticCreate;

abstract class Operation
{
    use StaticCreate;

    protected $slug;
    protected $name;

    public $data = null;

    public function __construct($resource, $slug)
    {
        $this->resource = $resource;
        $this->slug = $slug;
        $this->name = static::getName();
    }

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
    /// Basic helpers
    /////////////////////////////////

    public static function getName()
    {
        return Str::snake(class_basename(get_called_class()));
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

    /////////////////////////////////
    /// Resource
    /////////////////////////////////

    protected $resource;

    public function resource($value = null)
    {
        return $this->genericGetSet('resource', $value);
    }

    /////////////////////////////////
    /// Permissions
    /////////////////////////////////

    protected $restricted = false;

    public function restrict($value = true)
    {
        return $this->genericGetSet('restricted', $value);
    }

    public function getPermissionSlug()
    {
        return implode('.', [
            $this->resource()->namespace,
            $this->resource()->name,
            $this->slug
        ]);
    }

    public function canExecute()
    {
        if (!$this->restricted) {
            return true;
        }

        if (Auth::check() && Auth::user()->can($this->getPermissionSlug())) {
            return true;
        }

        return false;
    }

    /////////////////////////////////
    /// Exporting
    /////////////////////////////////

    public function export()
    {
        return [
            "name" => $this->name,
            "slug" => $this->slug
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

    public function getControllerMethodName()
    {
        return static::getName() . 'Operation';
    }

    public function matchesControllerMethod($method_name)
    {
        return $this->getControllerMethodName() === $method_name;
    }

    public function getRoutePath()
    {

        $key = $this->resource->name;
        $route_path = Str::plural($this->resource->name) . '/' . $this->slug . "/{" . $key . "?}"; // TODO <- abstract getter on Operation

        return $route_path;
    }

    public function routes()
    {

        $route_group_props = ['operation' => $this->slug, 'resource_namespace' => $this->resource()->namespace];

        $middlewares = [];

        if ($this->restricted) {
            $middlewares[] = 'permission:' . $this->getPermissionSlug();
        }

        if (!empty($middlewares)) {
            $route_group_props['middleware'] = $middlewares;
        }

        Route::group($route_group_props, function () {

            $route_name = $this->resource()->namespace . '.resources.' . $this->resource->name . '.' . $this->slug;
            $route_path = $this->getRoutePath();
            $route_controller = '\\' . ResourceController::class . '@' . $this->getControllerMethodName();

            $route = Route::name($route_name);

            $callable = [$route, static::ROUTE_METHOD];

            if (is_callable($callable)) {
                call_user_func($callable, $route_path, $route_controller);
            }

        });

    }

}
