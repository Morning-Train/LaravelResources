<?php

namespace MorningTrain\Laravel\Resources\Operations\Eloquent;

use MorningTrain\Laravel\Resources\Support\Contracts\EloquentOperation;
use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\QueryModel;
use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\QueryToCount;

class Count extends EloquentOperation
{

    const ROUTE_METHOD = 'get';

    protected function beforePipes()
    {
        return [
            QueryModel::create()->model($this->model)->filters($this->filters),
        ];
    }

    public function pipes()
    {
        return [
            QueryToCount::create(),
        ];
    }

}
