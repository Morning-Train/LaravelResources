<?php

namespace MorningTrain\Laravel\Resources\Operations\Crud;

use MorningTrain\Laravel\Resources\Support\Contracts\EloquentOperation;

class Index extends EloquentOperation
{

    const ROUTE_METHOD = 'get';

    public function isSingular()
    {
        return false;
    }

}
