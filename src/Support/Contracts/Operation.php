<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use MorningTrain\Laravel\Resources\Http\Controllers\ResourceController;
use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;
use MorningTrain\Laravel\Resources\Support\Pipes\ToResponse;
use MorningTrain\Laravel\Support\Traits\StaticCreate;

abstract class Operation
{
    use StaticCreate;
    use Macroable;

    public $data = null;

    /////////////////////////////////
    /// Request helpers
    /////////////////////////////////

    public function prepare($parameters)
    {
        $this->resetMessage();
        $this->resetStatusCode();
    }

    public function handle($model_or_collection = null)
    {
        return $model_or_collection;
    }

    /////////////////////////////////
    /// Message helpers
    /////////////////////////////////

    protected $message = null;
    protected $success_message = null;
    protected $error_message = null;

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
        return implode('.',
            [
                $this->resource()->namespace,
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

    protected function responsePipes()
    {
        return [
            ToResponse::create()->operation($this)
        ];
    }

    protected function buildPipes()
    {
        return array_merge(
            //$this->initialPipes(),
            $this->beforePipes(),
            $this->pipes(),
            $this->afterPipes(),
            $this->responsePipes()
        );
    }

    public function execute()
    {
        return $this->pipeline()
            ->send($this->data)
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

    protected $restricted = false;

    /**
     * @deprecated
     */
    public function restrict($value = true)
    {
        return $this->genericGetSet('restricted', $value);
    }

    public function canExecute()
    {
        $data = $this->data instanceof Collection ?
            $this->data : collect([$this->data]);

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

        if(static::hasMacro('isRestricted') || method_exists($this, 'isRestricted')) {
            if($this->isRestricted($this->identifier())) {
                $middlewares[] = 'auth:' . $this->resource()->namespace;
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
