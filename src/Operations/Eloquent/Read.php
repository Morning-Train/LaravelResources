<?php

namespace MorningTrain\Laravel\Resources\Operations\Eloquent;

use MorningTrain\Laravel\Resources\Support\Contracts\EloquentOperation;
use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\FetchesModel;

class Read extends EloquentOperation
{

    const ROUTE_METHOD = 'get';

    protected function beforePipes()
    {
        return [
            FetchesModel::create()
                ->model($this->model)
                ->filters($this->filters)
                ->appends($this->appends),
        ];
    }

}
