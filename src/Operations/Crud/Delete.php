<?php

namespace MorningTrain\Laravel\Resources\Operations\Crud;

use MorningTrain\Laravel\Resources\Support\Contracts\Operation;

class Delete extends Operation
{

    const ROUTE_METHOD = 'delete';

    public function handle($model)
    {
        return $model->delete();
    }
}
