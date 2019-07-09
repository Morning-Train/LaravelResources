<?php

namespace MorningTrain\Laravel\Resources\Operations\Crud;


use MorningTrain\Laravel\Resources\Support\Contracts\EloquentOperation;

class Count extends EloquentOperation
{

    const ROUTE_METHOD = 'get';

    public function handle($model_or_collection = null)
    {
        return [
            'model' => [
                'count' => $this->query()->count(),
            ],
        ];
    }

}
