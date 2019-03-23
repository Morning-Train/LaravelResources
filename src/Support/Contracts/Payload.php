<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Payload
{

    protected $operation;
    protected $data;

    public function __construct($operation, $response_data)
    {
        $this->operation = $operation;
        $this->data = $response_data;
    }

    protected function buildModelPayload(Model $model)
    {
        $response = [
            'model' => $this->modelResponse($model)
        ];

        /// Add filter metadata to response
        $metadata = ["meta" => $this->operation()->getMetadata()];

        $response = array_merge($response, $metadata);

        return $response;
    }

    protected function buildCollectionPayload(Collection $collection)
    {
        $response = [
            'collection' => $collection->map(function ($model) {
                return $this->modelResponse($model);
            })
        ];

        /// Add filter metadata to response
        $metadata = ["meta" => $this->operation->getMetadata()];

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
            return $this->buildModelPayload($payload_data);
        }

        if ($payload_data instanceof Collection) {
            return $this->buildCollectionPayload($payload_data);
        }

        return $payload_data;
    }

    public function response()
    {

        if($this->data instanceof View) {
            return $this->data;
        }

        $status = 200;
        $headers = [];
        $options = 0;

        $res = [
            'payload' => $this->buildPayload($this->data),
            'message' => 'Success'
        ];

        return response()->json($res, $status, $headers, $options);
    }


}
