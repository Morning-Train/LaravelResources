<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;
use MorningTrain\Laravel\Resources\Support\Contracts\Payload;

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

    public function handle(Payload $payload, Closure $next)
    {

        $data = $payload->get('data');

        if(!isset($data) || empty($data) || $data instanceof Operation) {
            $data = [];
        }

        if ($data instanceof Model) {
            $data = $this->buildModelPayload($data);
        }

        if ($data instanceof Collection) {
            $data = $this->buildCollectionPayload($data);
        }

        if (!is_object($data) && !is_array($data)) {
            $data = [$data];
        }

        $payload->set('data', $data);

        return $next($payload);
    }

}
