<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Traits\Macroable;
use MorningTrain\Laravel\Resources\Http\Controllers\ResourceController;
use MorningTrain\Laravel\Resources\Support\Pipes\IsPermitted;
use MorningTrain\Laravel\Resources\Support\Traits\HasPipes;
use MorningTrain\Laravel\Resources\Support\Traits\Respondable;
use MorningTrain\Laravel\Support\Traits\StaticCreate;

class Operation
{
    use StaticCreate;
    use Macroable;
    use Respondable;
    use HasPipes;

    protected Resource $resource;
    public string $name;

    function __construct(Resource $resource, string $name)
    {
        $this->resource = $resource;
        $this->name = $name;
    }

    /////////////////////////////////
    /// Request helpers
    /////////////////////////////////

    public function handle()
    {
        return null;
    }

    /////////////////////////////////
    /// Helpers
    /////////////////////////////////

    public function identifier()
    {
        return $this->resource->identifier($this->name);
    }

    /////////////////////////////////
    /// Pipeline setup
    /////////////////////////////////

    protected function authPipes()
    {
        return [
            /// We check to see if the current operation can be performed
            /// It will factor in if the resource has been configured with the operation
            /// It will also check to see if the current user has access to it
            IsPermitted::create()
        ];
    }

    protected function pipes()
    {
        return [
            function (Payload $payload, $next) {

                $payload->response = $this->handle();

                return $next($payload);
            }
        ];
    }

    /////////////////////////////////
    /// Middleware
    /////////////////////////////////

    protected $middlewares = [];

    public function middlewares($middlewares = null)
    {
        $this->middlewares = $middlewares;

        return $this;
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

    public function getRouteParameters()
    {
        return [
            $this->resource->base_name => ['optional' => true]
        ];
    }

    protected function buildRouteParameters()
    {
        $parameters = $this->getRouteParameters();
        $compiled_parameters = [];

        if (is_array($parameters) && !empty($parameters)) {
            foreach ($parameters as $parameter_key => $parameter_options) {

                $suffix = (isset($parameter_options['optional']) && $parameter_options['optional'] === true) ? '?' : '';

                $compiled_parameters[] = '{' . $parameter_key . $suffix . '}';
            }
        }

        return $compiled_parameters;
    }

    public function getRoutePath()
    {
        return join(
            '/',
            array_merge(
                [
                    $this->resource->getBasePath(),
                    $this->name
                ],
                $this->buildRouteParameters()
            )
        );
    }

    public function route($options = [])
    {

        $route_group_props = [
            'operationName' => $this->name,
            'resource_namespace' => $this->resource->namespace
        ];

        $middlewares = $this->middlewares;

        if (static::hasMacro('isRestricted') || method_exists($this, 'isRestricted')) {
            if ($this->isRestricted($this->identifier())) {

                $guard = isset($options['guard']) ? $options['guard'] : $this->resource->namespace;

                $middlewares[] = 'auth:' . $guard;
            }
        }

        if (!empty($middlewares)) {
            $route_group_props['middleware'] = $middlewares;
        }

        Route::group($route_group_props,
            function () {

                $route_path = $this->getRoutePath();
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
