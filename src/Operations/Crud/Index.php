<?php

namespace MorningTrain\Laravel\Resources\Operations\Crud;

use MorningTrain\Laravel\Resources\Support\Contracts\EloquentOperation;
use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\QueryToCollection;
use MorningTrain\Laravel\Resources\Support\Pipes\QueryModel;
use MorningTrain\Laravel\Resources\Support\Pipes\TransformToView;

class Index extends EloquentOperation
{

    const ROUTE_METHOD = 'get';

    protected function pipes()
    {
        return [
            QueryModel::create()->model($this->model)->filters($this->filters),
            QueryToCollection::create(),
            TransformToView::create()->appends($this->appends),
        ];
    }

}
