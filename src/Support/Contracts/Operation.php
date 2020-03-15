<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use MorningTrain\Laravel\Resources\Http\Controllers\ResourceController;
use MorningTrain\Laravel\Resources\Support\Pipes\IsPermitted;
use MorningTrain\Laravel\Resources\Support\Traits\Respondable;
use MorningTrain\Laravel\Support\Traits\StaticCreate;

abstract class Operation
{
    use StaticCreate;
    use Macroable;
    use Respondable;

    public $data = null;

    /////////////////////////////////
    /// Request helpers
    /////////////////////////////////

    public function handle(Payload $payload)
    {
        return $payload;
    }

    /////////////////////////////////
    /// Message helpers
    /////////////////////////////////

    public $message = null;
    public $success_message = null;
    public $error_message = null;

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function resetMessage()
    {
        $this->setMessage(null);
    }

    public function successMessage($messageOrClosure)
    {
        $this->success_message = $messageOrClosure;

        return $this;
    }

    public function errorMessage($messageOrClosure)
    {
        $this->error_message = $messageOrClosure;

        return $this;
    }

    /////////////////////////////////
    /// Status code helpers
    /////////////////////////////////

    protected $status_code = 200;

    public function getStatusCode()
    {
        return $this->status_code;
    }

    public function setStatusCode($status_code)
    {
        $this->status_code = $status_code;
    }

    public function resetStatusCode()
    {
        $this->setStatusCode(200);
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
        return $this->resource()->identifier($this->name);
    }

    protected $is_public = false;

    public function public() {
        $this->is_public = true;

        return $this;
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
    /// Pipelines
    /////////////////////////////////

    public function pipeline()
    {
        return app(Pipeline::class);
    }

    protected function beforePipes()
    {
        return [];
    }

    protected function pipes()
    {
        return [
            function ($data, $next) {
                return $next($this->handle($data));
            }
        ];
    }

    protected function afterPipes()
    {
        return [];
    }

    protected function buildPipes()
    {
        return array_merge(
            $this->beforePipes(),
            [
                /// We check to see if the current operation can be performed
                /// It will factor in if the resource has been configured with the operation
                /// It will also check to see if the current user has access to it
                IsPermitted::create()
            ],
            $this->pipes(),
            $this->afterPipes()
        );
    }

    public function execute()
    {

        $payload = new Payload($this);

        $payload->setRequestArguments(func_get_args());

        return $this->pipeline()
            ->send($payload)
            ->through($this->buildPipes())
            ->thenReturn();

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

    public function canExecute($data = null)
    {
        if($this->is_public) {
            return true;
        }

        $data = $data instanceof Collection ?
            $data : collect([$data]);

        return $data->every(function ($model) {
            return Gate::allows($this->identifier(), $model);
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

    public function routes($options = [])
    {

        $route_group_props = [
            'operation' => $this->name,
            'resource_namespace' => $this->resource()->namespace
        ];

        $middlewares = $this->middlewares();

        if (static::hasMacro('isRestricted') || method_exists($this, 'isRestricted')) {
            if ($this->isRestricted($this->identifier()) && !$this->is_public) {

                $guard = isset($options['guard']) ? $options['guard'] : $this->resource()->namespace;

                $middlewares[] = 'auth:' . $guard;
            }
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
