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
        $metadata = ["meta" => $this->operation->getMeta()];

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
        $metadata = ["meta" => $this->operation->getMeta()];

        if (is_array($metadata)) {
            $response = array_merge($response, $metadata);
        }

        return $response;
    }

    protected function modelResponse($model)
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

        if(!is_object($payload_data) && !is_array($payload_data)){
            $payload_data = [$payload_data];
        }

        return $payload_data;
    }

    public function response()
    {

        if($this->data instanceof \Illuminate\Http\Response) {
            return $this->data;
        }

        if($this->data instanceof View) {
            return $this->data;
        }

        $status = $this->operation->getStatusCode();
        $headers = [];
        $options = 0;

        $res = $this->buildPayload($this->data);
        $res['message'] = $this->operation->getMessage();

        return response()->json($res, $status, $headers, $options);
    }


}
