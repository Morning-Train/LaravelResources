<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;

class ToPayload extends Pipe
{

    protected function buildModelPayload(Model $model)
    {
        $payload = [
            'model' => $this->modelResponse($model)
        ];

        return $payload;
    }

    protected function buildCollectionPayload(Collection $collection)
    {
        $payload = [
            'collection' => $collection->map(function ($model) {
                return $this->modelResponse($model);
            })
        ];

        return $payload;
    }

    protected function modelResponse($model)
    {
        return $model;
    }

    public function handle($data, Closure $next)
    {

        $payload = $data;

        if(!isset($payload) || empty($payload) || $payload instanceof Operation) {
            $payload = [];
        }

        if ($data instanceof Model) {
            $payload = $this->buildModelPayload($data);
        }

        if ($data instanceof Collection) {
            $payload = $this->buildCollectionPayload($data);
        }

        if (!is_object($data) && !is_array($data)) {
            $payload = [$data];
        }

        return $next($payload);
    }

}
