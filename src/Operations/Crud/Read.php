<?php

namespace MorningTrain\Laravel\Resources\Operations\Crud;

use MorningTrain\Laravel\Resources\Support\Contracts\EloquentOperation;
use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\ConstrainQueryToKey;
use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\QueryToModel;
use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\QueryModel;
use MorningTrain\Laravel\Resources\Support\Pipes\TransformToView;

class Read extends EloquentOperation
{

    const ROUTE_METHOD = 'get';

    protected function beforePipes()
    {
        return [
            QueryModel::create()->model($this->model)->filters($this->filters),
            ConstrainQueryToKey::create()->model($this->model),
            QueryToModel::create(),
            TransformToView::create()->appends($this->appends),
        ];
    }

}
