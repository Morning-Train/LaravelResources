<?php

namespace MorningTrain\Laravel\Resources\Operations\Crud;

use MorningTrain\Laravel\Resources\Support\Contracts\EloquentOperation;

class Delete extends EloquentOperation
{

    const ROUTE_METHOD = 'delete';

    public function handle($model = null)
    {
        return $model->delete();
    }
}
