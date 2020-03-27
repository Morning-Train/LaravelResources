<?php

namespace MorningTrain\Laravel\Resources\Operations\Eloquent;


use MorningTrain\Laravel\Resources\Support\Contracts\EloquentOperation;
use MorningTrain\Laravel\Resources\Support\Pipes\QueryModel;

class Count extends EloquentOperation
{

    const ROUTE_METHOD = 'get';

    protected function beforePipes()
    {
        return [
            QueryModel::create()->model($this->model)->filters($this->filters)->operation($this),
        ];
    }

    public function handle($query)
    {
        return [
            'model' => [
                'count' => $query->count(),
            ],
        ];
    }

}
