<?php

namespace MorningTrain\Laravel\Resources\Http\Controllers;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;
use MorningTrain\Laravel\Resources\Support\Contracts\Resource;

class ResourceController
{

    protected $resource_class;
    protected $operation_class;
    protected $resource = null;
    protected $operation = null;

    public function __construct()
    {
        if ($current_route = Route::getCurrentRoute()) {
            $this->resource_class = $current_route->action['resource'];
            $this->operation_class = $current_route->action['operation'];
        }
    }

    public function isValidMethod($method)
    {
        return $method === ($this->operation_class)::getControllerMethodName();
    }

    /**
     * @return Resource
     */
    protected function resource()
    {
        if ($this->resource === null) {
            $this->resource = ($this->resource_class)::instance();
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

    protected function canPerformOperation()
    {
        return $this->operation() !== null && $this->resource()->canPerformOperation($this->operation_class);
    }

    protected function getActionName()
    {
        return ($this->operation_class)::getName();
    }

    /////////////////////////////////
    /// Response helpers
    /////////////////////////////////

    protected function buildCollectionPayload(Collection $collection)
    {
        $response = [
            'collection' => $collection->map(function ($model) {
                return $this->modelResponse($model);
            })
        ];

        /// Add filter metadata to response
        $metadata = ["meta" => $this->operation()->getMetadata()];

        if (is_array($metadata)) {
            $response = array_merge($response, $metadata);
        }

        return $response;
    }

    protected function modelResponse(Model $model)
    {
        return $model;
    }

    protected function buildPayload($payload_data)
    {

        if ($payload_data instanceof Model) {
            return $payload_data; /// TODO - Should we nest the model like with collection? Or should the collection be unnested?
        }

        if ($payload_data instanceof Collection) {
            return $this->buildCollectionPayload($payload_data);
        }

        return $payload_data;
    }

    protected function response($data, $status = 200, $headers = [], $options = 0)
    {

        $res = [
            'payload' => $this->buildPayload($data),
            'message' => 'Success'
        ];

        return response()->json($res, $status, $headers, $options);
    }


    /**
     * @param string $method
     * @param array $parameters
     * @return \Illuminate\Http\JsonResponse|mixed
     * @throws \Exception
     */
    public function __call($method, $parameters)
    {

        /// Magic method to catch all calls to this controller
        /// It allows us to dynamically route the request to a specific operation
        /// An operation in this case, is a sort of request -> response template
        /// A certain operation might be used be different resources (A collection of operations)

        /// First we should validate to see if the requested method is valid
        /// If that is not the case, then we might assume that something is misconfigured
        if (!$this->isValidMethod($method)) {
            throw new \Exception("Tried to execute method ($method) on ResourceController, but it does not match an operation and is deemed invalid.");
        }

        /// We check to see if the current operation can be performed
        /// It will factor in if the resource has been configured with the operation
        /// It will also check to see if the current user has access to it
        if (!$this->canPerformOperation()) {
            return $this->response(['message' => 'Unable to perform action'], 400);
        }

        /// We are ready to execute the operation
        return $this->response(
            $this->operation()->execute($parameters)
        );
    }

}
