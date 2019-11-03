<?php

namespace MorningTrain\Laravel\Resources\Http\Controllers;

use Illuminate\Support\Facades\Route;
use MorningTrain\Laravel\Resources\ResourceRepository;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;
use MorningTrain\Laravel\Resources\Support\Contracts\Resource;

class ResourceController
{

    protected $resource_namespace;
    protected $resource_name;
    protected $operation_class;
    protected $resource = null;
    protected $operation = null;

    public function __construct()
    {
        if ($current_route = Route::getCurrentRoute()) {
            $this->resource_namespace = $current_route->action['resource_namespace'];
            $this->resource_name = $current_route->action['resource'];
            $this->operation_class = $current_route->action['operation'];
        }
    }

    /**
     * @return Resource
     */
    protected function resource()
    {
        if ($this->resource === null) {
            $this->resource = ResourceRepository::get($this->resource_namespace, $this->resource_name);
        }

        return $this->resource;
    }

    /**
     * @return Operation
     */
    protected function operation()
    {
        if ($this->operation === null) {
            $this->operation = $this->resource()->operation($this->operation_class);
        }
        return $this->operation;
    }

    /**
     * @param string $method
     * @param array $parameters
     * @return \Illuminate\Http\JsonResponse|mixed
     * @throws \Exception
     */
    public function executeOperation()
    {
        /// Magic method to catch all calls to this controller
        /// It allows us to dynamically route the request to a specific operation
        /// An operation in this case, is a sort of request -> response template
        /// A certain operation might be used be different resources (A collection of operations)
        $operation = $this->operation();

        /// First we should validate to see if the requested method is valid
        /// If that is not the case, then we might assume that something is misconfigured
        if (($operation instanceof Operation) === false) {
            throw new \Exception("Tried to execute method, but it does not match an operation and is deemed invalid.");
        }

        /// We are ready to execute the operation
        return $operation->execute(...func_get_args());
    }

}
